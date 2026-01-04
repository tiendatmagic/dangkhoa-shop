<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminWalletSetting;
use App\Models\OrderPayout;
use App\Models\Orders;

$arg = $argv[1] ?? null;
if ($arg) {
    $arg = trim((string) $arg);
    $byId = Orders::query()->where('id', $arg)->first();
    $byCode = Orders::query()->where('order_code', $arg)->first();
    $target = $byId ?: $byCode;
    if (! $target) {
        echo "Order not found for argument: {$arg}\n";
        exit(1);
    }

    echo "Inspecting target order for arg={$arg}\n\n";
    $recent = collect([$target]);
} else {
    $recent = Orders::query()
    ->where('payment', 'coinbase')
    ->orderByDesc('created_at')
    ->limit(5)
    ->get();
}

if ($recent->isEmpty()) {
    echo "No coinbase orders found\n";
    exit(0);
}

if (! $arg) {
    echo "Recent Coinbase Orders (latest 5)\n";
    foreach ($recent as $o) {
        echo "  - {$o->id} code={$o->order_code} status={$o->status} paid_at={$o->paid_at} created_at={$o->created_at}\n";
    }
    echo "\n";

    $order = Orders::query()
        ->where('payment', 'coinbase')
        ->where('status', 'completed')
        ->orderByDesc('created_at')
        ->first();

    if (! $order) {
        echo "No completed coinbase orders found to inspect payouts.\n";
        $order = $recent->first();
        echo "Inspecting latest order instead.\n\n";
    }
} else {
    $order = $recent->first();
}

echo "Order\n";
echo "  id: {$order->id}\n";
echo "  status: {$order->status}\n";
echo "  paid_at: {$order->paid_at}\n";
echo "  note: {$order->note}\n";
echo "  created_at: {$order->created_at}\n";

echo "\nPayouts\n";
$payouts = OrderPayout::query()->where('order_id', $order->id)->orderBy('asset_symbol')->get();
echo "  count: {$payouts->count()}\n";
foreach ($payouts as $p) {
    echo "  - {$p->asset_symbol} chain={$p->chain_id} native=" . ($p->is_native ? '1' : '0') . " to={$p->to_address} sent_at={$p->sent_at} tx={$p->tx_hash} err={$p->error}\n";
}

echo "\nAdminWalletSetting\n";
$settings = AdminWalletSetting::query()->orderBy('id')->first();
if (! $settings) {
    echo "  none\n";
    exit(0);
}

echo "  chain_id: {$settings->chain_id}\n";
echo "  rpc_url: {$settings->rpc_url}\n";
echo "  contract_address: {$settings->contract_address}\n";
echo "  from_address: {$settings->from_address}\n";
echo "  has_private_key: " . ($settings->private_key ? '1' : '0') . "\n";
