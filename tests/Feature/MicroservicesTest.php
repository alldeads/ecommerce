<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MicroservicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Create test products
        $this->products = Product::factory()->count(3)->create([
            'is_active' => true,
            'stock' => 10,
            'price' => 99.99,
        ]);
    }

    /** @test */
    public function catalog_service_can_list_all_products()
    {
        $response = $this->getJson('/api/catalog/products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function catalog_service_can_show_single_product()
    {
        $product = $this->products->first();

        $response = $this->getJson("/api/catalog/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                ],
            ]);
    }

    /** @test */
    public function catalog_service_can_search_products()
    {
        Product::factory()->create([
            'name' => 'Unique Laptop',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/catalog/products?search=Unique');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Unique Laptop']);
    }

    /** @test */
    public function checkout_service_requires_authentication()
    {
        $response = $this->postJson('/api/checkout/orders', [
            'items' => [
                ['product_id' => 1, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function checkout_service_can_create_order()
    {
        $product = $this->products->first();

        $response = $this->actingAs($this->user)
            ->postJson('/api/checkout/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
                'customer_name' => 'Test Customer',
                'customer_email' => 'customer@example.com',
                'shipping_address' => '123 Test St',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'total_amount',
                    'status',
                    'order_items',
                ],
            ]);

        // Verify order was created in database
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'customer_email' => 'customer@example.com',
        ]);

        // Verify order item was created
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Verify stock was decreased
        $product->refresh();
        $this->assertEquals(8, $product->stock);
    }

    /** @test */
    public function checkout_service_validates_stock_availability()
    {
        $product = Product::factory()->create([
            'stock' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/checkout/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5, // More than available
                    ],
                ],
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function checkout_service_can_list_user_orders()
    {
        // Create an order first
        $product = $this->products->first();
        
        $this->actingAs($this->user)
            ->postJson('/api/checkout/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ]);

        // Now list orders
        $response = $this->actingAs($this->user)
            ->getJson('/api/checkout/orders');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function checkout_service_prevents_accessing_other_users_orders()
    {
        $otherUser = User::factory()->create();
        
        // Create order for other user
        $product = $this->products->first();
        $response = $this->actingAs($otherUser)
            ->postJson('/api/checkout/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ]);

        $orderId = $response->json('data.id');

        // Try to access with different user
        $response = $this->actingAs($this->user)
            ->getJson("/api/checkout/orders/{$orderId}");

        $response->assertStatus(403);
    }

    /** @test */
    public function email_service_sends_confirmation_on_order_creation()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $product = $this->products->first();

        $this->actingAs($this->user)
            ->postJson('/api/checkout/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
                'customer_email' => 'customer@example.com',
            ]);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OrderConfirmation::class, function ($mail) {
            return $mail->hasTo('customer@example.com');
        });
    }
}
