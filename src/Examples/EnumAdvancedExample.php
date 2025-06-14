<?php

/**
 * Advanced enum usage example with Laravel Arc DTOs
 * Demonstrates real-world enum scenarios and best practices.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

// Order status with business rules (BackedEnum)
enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    // Business logic methods
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED], true);
    }

    public function isActive(): bool
    {
        return $this !== self::CANCELLED;
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::PENDING => '🕐 Pending Payment',
            self::CONFIRMED => '✅ Confirmed',
            self::PROCESSING => '📦 Processing',
            self::SHIPPED => '🚚 Shipped',
            self::DELIVERED => '✨ Delivered',
            self::CANCELLED => '❌ Cancelled',
        };
    }
}

// Payment method (UnitEnum)
enum PaymentMethod
{
    case CREDIT_CARD;
    case PAYPAL;
    case BANK_TRANSFER;
    case CRYPTO;

    public function requiresVerification(): bool
    {
        return $this === self::BANK_TRANSFER;
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CREDIT_CARD => '💳',
            self::PAYPAL => '🅿️',
            self::BANK_TRANSFER => '🏦',
            self::CRYPTO => '₿',
        };
    }
}

// Priority level (BackedEnum with integers)
enum Priority: int
{
    case LOW = 1;
    case NORMAL = 2;
    case HIGH = 3;
    case URGENT = 5;

    public function getColor(): string
    {
        return match ($this) {
            self::LOW => 'green',
            self::NORMAL => 'blue',
            self::HIGH => 'orange',
            self::URGENT => 'red',
        };
    }
}

// Complex order DTO with multiple enums
class OrderDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:20')]
    public string $id;

    #[Property(type: 'string', required: true, validation: 'email')]
    public string $customerEmail;

    #[Property('OrderStatus', enumClass: OrderStatus::class, required: true)]
    public OrderStatus $status;

    #[Property('PaymentMethod', enumClass: PaymentMethod::class, required: true)]
    public PaymentMethod $paymentMethod;

    #[Property('Priority', enumClass: Priority::class, default: Priority::NORMAL)]
    public Priority $priority;

    #[Property('float', required: true, validation: 'numeric|min:0')]
    public float $amount;

    #[Property('Carbon', required: true)]
    public Carbon $createdAt;

    #[Property('Carbon', required: false)]
    public ?Carbon $shippedAt = null;

    // Business logic methods
    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    public function getStatusDisplay(): string
    {
        return $this->status->getDisplayName();
    }

    public function isHighPriority(): bool
    {
        return $this->priority->value >= Priority::HIGH->value;
    }

    public function requiresPaymentVerification(): bool
    {
        return $this->paymentMethod->requiresVerification();
    }

    public function getDashboardSummary(): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customerEmail,
            'status' => $this->getStatusDisplay(),
            'payment' => $this->paymentMethod->getIcon() . ' ' . $this->paymentMethod->name,
            'priority' => $this->priority->getColor() . ' priority',
            'amount' => '$' . number_format($this->amount, 2),
            'created' => $this->createdAt->diffForHumans(),
            'shipped' => $this->shippedAt?->format('Y-m-d') ?? 'Not shipped',
            'actions' => [
                'cancel' => $this->canBeCancelled(),
                'verify_payment' => $this->requiresPaymentVerification(),
            ],
        ];
    }
}

echo "🛍️  Advanced Enum Usage Example with Laravel Arc\n";
echo "================================================\n\n";

// Example 1: Create order with string/name values (automatic enum casting)
echo "📝 Creating order from form data (strings)...\n";
$orderData = [
    'id' => 'ORD-2024-001',
    'customerEmail' => 'john.doe@example.com',
    'status' => 'pending',           // String -> OrderStatus::PENDING
    'paymentMethod' => 'PAYPAL',     // String -> PaymentMethod::PAYPAL
    'priority' => 3,                 // Integer -> Priority::HIGH
    'amount' => 299.99,
    'createdAt' => '2024-01-15 10:30:00',
];

$order = new OrderDTO($orderData);

echo "✅ Order created successfully!\n";
echo "   Status: {$order->status->value} (enum: " . $order->status::class . ")\n";
echo "   Payment: {$order->paymentMethod->name} (" . $order->paymentMethod->getIcon() . ")\n";
echo "   Priority: {$order->priority->name} (level {$order->priority->value})\n";
echo '   Can be cancelled: ' . ($order->canBeCancelled() ? 'Yes' : 'No') . "\n\n";

// Example 2: Create order with enum instances
echo "🔧 Creating order with enum instances...\n";
$premiumOrder = new OrderDTO([
    'id' => 'ORD-2024-002',
    'customerEmail' => 'premium@example.com',
    'status' => OrderStatus::PROCESSING,     // Direct enum instance
    'paymentMethod' => PaymentMethod::CRYPTO, // Direct enum instance
    'priority' => Priority::URGENT,          // Direct enum instance
    'amount' => 1999.99,
    'createdAt' => Carbon::now(),
    'shippedAt' => Carbon::now()->addHours(2),
]);

echo "✅ Premium order created!\n";
echo '   High priority: ' . ($premiumOrder->isHighPriority() ? 'Yes' : 'No') . "\n";
echo '   Needs verification: ' . ($premiumOrder->requiresPaymentVerification() ? 'Yes' : 'No') . "\n\n";

// Example 3: Order serialization
echo "📤 Serializing orders to array/JSON...\n";
$orderArray = $order->toArray();
echo "Array format: \n";
echo "   status: {$orderArray['status']} (serialized back to string)\n";
echo "   paymentMethod: {$orderArray['paymentMethod']} (serialized back to name)\n";
echo "   priority: {$orderArray['priority']} (serialized back to integer)\n\n";

// Example 4: Business logic with enums
echo "💼 Business logic examples...\n";
$orders = [$order, $premiumOrder];

foreach ($orders as $index => $ord) {
    echo 'Order #' . ($index + 1) . ":\n";
    $summary = $ord->getDashboardSummary();

    echo "   📊 Dashboard: {$summary['status']} | {$summary['payment']} | {$summary['priority']}\n";
    echo "   💰 Amount: {$summary['amount']}\n";
    echo "   🕐 Created: {$summary['created']}\n";
    echo "   📦 Shipped: {$summary['shipped']}\n";
    echo '   🎬 Actions: ';
    echo $summary['actions']['cancel'] ? 'Can Cancel | ' : '';
    echo $summary['actions']['verify_payment'] ? 'Needs Verification' : 'Payment OK';
    echo "\n\n";
}

// Example 5: Enum validation and error handling
echo "⚠️  Testing enum validation...\n";

try {
    $invalidOrder = new OrderDTO([
        'id' => 'ORD-INVALID',
        'customerEmail' => 'test@example.com',
        'status' => 'invalid_status',  // This will fail
        'paymentMethod' => 'PAYPAL',
        'amount' => 100,
        'createdAt' => Carbon::now(),
    ]);
} catch (Exception $e) {
    echo '❌ Validation failed as expected: ' . $e->getMessage() . "\n";
}

echo "\n🎉 Advanced enum example completed!\n";
echo "\nKey takeaways:\n";
echo "• Enums provide type safety and business logic encapsulation\n";
echo "• Automatic casting from strings/integers to enum instances\n";
echo "• Serialization back to original values for APIs/storage\n";
echo "• Perfect integration with DTO validation and transformation\n";
echo "• Enables clean, maintainable business logic\n";
