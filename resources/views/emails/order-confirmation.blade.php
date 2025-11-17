<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .order-details {
            background-color: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .order-items {
            margin: 20px 0;
        }
        .order-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .total {
            font-size: 1.2em;
            font-weight: bold;
            text-align: right;
            padding: 15px;
            background-color: #f0f0f0;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Confirmation</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $order->customer_name }},</p>
        
        <p>Thank you for your order! We're pleased to confirm that we've received your order and it's being processed.</p>
        
        <div class="order-details">
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y h:i A') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            @if($order->shipping_address)
            <p><strong>Shipping Address:</strong><br>{{ $order->shipping_address }}</p>
            @endif
        </div>
        
        <h2>Order Items</h2>
        <div class="order-items">
            @foreach($order->orderItems as $item)
            <div class="order-item">
                <div>
                    <strong>{{ $item->product->name }}</strong><br>
                    <small>SKU: {{ $item->product->sku }}</small><br>
                    <small>Quantity: {{ $item->quantity }} × ${{ number_format($item->price, 2) }}</small>
                </div>
                <div>
                    ${{ number_format($item->subtotal, 2) }}
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="total">
            Total: ${{ number_format($order->total_amount, 2) }}
        </div>
        
        <p>We'll send you another email when your order ships.</p>
        
        <p>If you have any questions about your order, please contact our customer service team.</p>
    </div>
    
    <div class="footer">
        <p>Thank you for shopping with us!</p>
        <p>&copy; {{ date('Y') }} E-Commerce. All rights reserved.</p>
    </div>
</body>
</html>
