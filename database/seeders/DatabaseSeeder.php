<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create additional random users
        User::factory(10)->create();

        // Create sample products for the catalog
        Product::factory()->create([
            'name' => 'Laptop Pro 15',
            'description' => 'High-performance laptop with 15-inch display, 16GB RAM, and 512GB SSD',
            'price' => 1299.99,
            'stock' => 25,
            'sku' => 'LAP-PRO-15',
            'image_url' => 'https://picsum.photos/seed/laptop-pro-15/640/480',
        ]);

        Product::factory()->create([
            'name' => 'Wireless Mouse',
            'description' => 'Ergonomic wireless mouse with adjustable DPI and rechargeable battery',
            'price' => 39.99,
            'stock' => 150,
            'sku' => 'MOU-WIR-001',
            'image_url' => 'https://picsum.photos/seed/wireless-mouse/640/480',
        ]);

        Product::factory()->create([
            'name' => 'Mechanical Keyboard',
            'description' => 'RGB backlit mechanical keyboard with cherry MX switches',
            'price' => 129.99,
            'stock' => 75,
            'sku' => 'KEY-MEC-001',
            'image_url' => 'https://picsum.photos/seed/mechanical-keyboard/640/480',
        ]);

        Product::factory()->create([
            'name' => 'USB-C Hub',
            'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader',
            'price' => 49.99,
            'stock' => 200,
            'sku' => 'HUB-USB-C-001',
            'image_url' => 'https://picsum.photos/seed/usb-c-hub/640/480',
        ]);

        Product::factory()->create([
            'name' => 'Webcam HD Pro',
            'description' => '1080p HD webcam with built-in microphone and auto-focus',
            'price' => 79.99,
            'stock' => 50,
            'sku' => 'CAM-HD-001',
            'image_url' => 'https://picsum.photos/seed/webcam-hd-pro/640/480',
        ]);

        // Create additional random products
        Product::factory(15)->create();
    }
}
