<?php
// Bootstraps Laravel and lists recent pending Sepay orders
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Orders;
use App\Models\OrderItems;

$orders = Orders::where('payment', 'sepay')->where('status', 'pending')->orderBy('created_at', 'desc')->take(20)->get();
if ($orders->isEmpty()) {
    echo "No pending sepay orders found.\n";
    exit(0);
}

foreach ($orders as $o) {
    $total = OrderItems::where('order_id', $o->id)->get()->sum(function($it){ return $it->quantity * $it->price; });
    echo sprintf("ID: %s | SEPAY_CODE: %s | ORDER_CODE: %s | TOTAL_USD: %s | CREATED: %s\n", $o->id, $o->sepay_code ?: 'null', $o->order_code ?: 'null', $total, $o->created_at);
}
