<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class CheckoutService
{
    public function __construct(
        private CatalogService $catalogService,
        private EmailService $emailService
    ) {}

    /**
     * Create an order from cart items.
     *
     * @param User $user
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @param array{customer_name?: string, customer_email?: string, shipping_address?: string} $orderData
     * @return Order
     * @throws Exception
     */
    public function createOrder(User $user, array $items, array $orderData = []): Order
    {
        // Validate items
        if (empty($items)) {
            throw new Exception('Order must contain at least one item');
        }

        // Get all products
        $productIds = array_column($items, 'product_id');
        $products = $this->catalogService->getProductsByIds($productIds);

        if ($products->count() !== count($productIds)) {
            throw new Exception('One or more products not found');
        }

        // Validate stock and calculate total
        $totalAmount = 0;
        $validatedItems = [];

        foreach ($items as $item) {
            $product = $products->firstWhere('id', $item['product_id']);

            if (!$product) {
                throw new Exception("Product with ID {$item['product_id']} not found");
            }

            if (!$product->isInStock($item['quantity'])) {
                throw new Exception("Product {$product->name} is out of stock or insufficient quantity");
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            $validatedItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $subtotal,
            ];
        }

        // Create order in a transaction
        return DB::transaction(function () use ($user, $validatedItems, $totalAmount, $orderData) {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'customer_email' => $orderData['customer_email'] ?? $user->email,
                'customer_name' => $orderData['customer_name'] ?? $user->name,
                'shipping_address' => $orderData['shipping_address'] ?? null,
            ]);

            // Create order items and decrease stock
            foreach ($validatedItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decreaseStock($item['quantity']);
            }

            // Load relationships for email
            $order->load('orderItems.product');

            // Send confirmation email
            $this->emailService->sendOrderConfirmation($order);

            return $order;
        });
    }

    /**
     * Get order by ID.
     */
    public function getOrder(int $orderId): ?Order
    {
        return Order::with('orderItems.product')->find($orderId);
    }

    /**
     * Get all orders for a user.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Order>
     */
    public function getUserOrders(User $user)
    {
        return Order::where('user_id', $user->id)
            ->with('orderItems.product')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
