<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .thank-you-icon {
            width: 80px;
            height: 80px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .content {
            padding: 30px;
        }

        .order-message {
            text-align: center;
            margin-bottom: 30px;
        }

        .order-message p {
            color: #666;
            margin-bottom: 20px;
        }

        .setup-btn {
            background: #4285f4;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .features {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            padding: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .feature {
            text-align: center;
            flex: 1;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 18px;
        }

        .feature h4 {
            font-size: 12px;
            color: #333;
            margin-bottom: 5px;
        }

        .order-details {
            margin-top: 30px;
        }

        .order-details h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .item-image {
            width: 60px;
            height: 60px;
            background: #e0e0e0;
            border-radius: 8px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #666;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .item-qty {
            color: #666;
            font-size: 12px;
        }

        .item-price {
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }

        .order-summary {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #666;
        }

        .summary-row.total {
            font-weight: bold;
            color: #333;
            font-size: 18px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }

        .shipping-info {
            display: flex;
            margin-top: 30px;
            gap: 30px;
        }

        .shipping-column {
            flex: 1;
        }

        .shipping-column h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .shipping-column p {
            color: #666;
            font-size: 12px;
            line-height: 1.4;
        }

        .contact-section {
            background: #f9f9f9;
            padding: 20px 30px;
            text-align: center;
        }

        .contact-section h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }

        .footer {
            background: #4285f4;
            color: white;
            padding: 20px 30px;
            text-align: center;
        }

        .footer-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .footer p {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-link {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 8px;
            }

            .header, .content, .contact-section, .footer {
                padding: 20px;
            }

            .features {
                flex-direction: column;
                gap: 20px;
            }

            .shipping-info {
                flex-direction: column;
                gap: 20px;
            }

            .contact-info {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header Section -->
    <div class="header">
        <div class="logo">{{ config('app.name') }}</div>
        <div class="thank-you-icon">❤️</div>
        <h1>Thanks for the Order</h1>
        <p>Great news! Your order is all set to hit the road. We're packing it up with care and it'll be on its way to
            you in no time.</p>
    </div>

    <!-- Content Section -->
    <div class="content">
        <div class="order-message">
            <p>We're excited to get your order to you as quickly as possible.</p>
        </div>

        <!-- Features Section -->
        <div class="features">
            <div class="feature">
                <div class="feature-icon">✓</div>
                <h4>Confirmed</h4>
            </div>
            <div class="feature">
                <div class="feature-icon">🚚</div>
                <h4>Processing</h4>
            </div>
            <div class="feature">
                <div class="feature-icon">📦</div>
                <h4>Shipped</h4>
            </div>
        </div>

        <!-- Order Details -->
        <div class="order-details">
            <h3>Your Item in this order</h3>
            <p style="color: #666; margin-bottom: 20px;">Order number: {{ $order->id }}</p>

            @foreach($order->items as $item)
                <div class="order-item">
                    <div class="item-image">
                        <img src="{{ $item->product->thumbnail }}" alt="">
                    </div>
                    <div class="item-details">
                        <div class="item-name">{{ $item->product->name }}</div>
                        <div class="item-qty">Qty: {{ $item->quantity }}</div>
                    </div>
                    <div class="item-price">${{ number_format($item->product->price, 2) }}</div>
                </div>
            @endforeach

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-row total">
                    <span>Total</span>
                    <span>${{ number_format($order->total , 2)  }}</span>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="shipping-info">
                <div class="shipping-column">
                    <h4>Shipping Address</h4>
                    {{ $order->shippingAddress->address_line1 }}<br>
                    @if($order->shippingAddress->address_line2 )
                        {{ $order->shippingAddress->address_line2  }}<br>
                    @endif
                    {{ $order->shippingAddress->state }}, {{ $order->shippingAddress->city }}
                    , {{ $order->shippingAddress->country }}<br>
                    <strong>ZIP CODE: </strong>{{ $order->shippingAddress->postal_code }}<br>
                    <strong>Phone: </strong>{{ $order->shippingAddress->phone }}
                </div>
                <div class="shipping-column">
                    <h4>Payment Info</h4>
                    <strong>Payment Method: </strong>{{ ucfirst($order->payment->payment_method) }}<br>
                    <strong>Payment Status: </strong>{{ ucfirst($order->payment->status) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="contact-section">
        <h3>Problems with the Order?</h3>
        <div class="contact-info">
            <div class="contact-item">
                <span>📧</span>
                <span>help@selymoon.com</span>
            </div>
            <div class="contact-item">
                <span>📞</span>
                <span>+1 (254) 567-8900</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-logo">{{ config('app.name') }}</div>
        <p>2019 White Sound Opinion Boulevard St SB, United Kingdom</p>
        <p>Unsubscribe or Change email preferences</p>
    </div>
</div>
</body>
</html>