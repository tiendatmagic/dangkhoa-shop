<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Orders;

$code = $argv[1] ?? null;
if (! $code) {
    echo "Usage: php find_order_by_sepay_code.php DKXXXXX\n";
    exit(1);
}

$order = Orders::where('sepay_code', strtoupper($code))->orWhere('order_code', $code)->first();
if (! $order) {
    echo "Order not found for code: $code\n";
    exit(0);
}

echo "ID: {$order->id}\n";
echo "order_code: {$order->order_code}\n";
echo "sepay_code: {$order->sepay_code}\n";
echo "payment: {$order->payment}\n";
echo "status: {$order->status}\n";
echo "paid_at: " . ($order->paid_at ?: 'null') . "\n";
echo "coinbase_hosted_url: {$order->coinbase_hosted_url}\n";

?>