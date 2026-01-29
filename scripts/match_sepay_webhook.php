<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Orders;
use App\Models\OrderItems;

$transferAmount = isset($argv[1]) ? (int)$argv[1] : null; // VND
$account = isset($argv[2]) ? $argv[2] : null; // destination account
$content = isset($argv[3]) ? $argv[3] : null; // raw content

if (! $transferAmount) {
    echo "Usage: php match_sepay_webhook.php <amount_vnd> [account] [content]\n";
    exit(1);
}

$rate = (float) env('SEPAY_EXCHANGE_RATE', 23000);

$orders = Orders::where('payment', 'sepay')->where('status', 'pending')->get();
if ($orders->isEmpty()) {
    echo "No pending sepay orders\n";
    exit(0);
}

$matches = [];

foreach ($orders as $o) {
    $itemsTotal = OrderItems::where('order_id', $o->id)->get()->sum(function($it){ return $it->quantity * $it->price; });
    $calcVnd = (int) round((float)$itemsTotal * $rate);

    // parse coinbase_hosted_url params if present
    $parsed = [];
    if (! empty($o->coinbase_hosted_url)) {
        $u = $o->coinbase_hosted_url;
        $qpos = strpos($u, '?');
        if ($qpos !== false) {
            parse_str(substr($u, $qpos + 1), $parsed);
        }
    }

    $sepayCode = $o->sepay_code ? strtoupper($o->sepay_code) : null;

    $score = 0;
    if ($calcVnd === $transferAmount) $score += 5;
    if (! empty($parsed['amount']) && (int)$parsed['amount'] === $transferAmount) $score += 5;
    if ($sepayCode && $content && stripos($content, $sepayCode) !== false) $score += 10;
    if ($content && preg_match('/DK[0-9]{6,8}/i', $content, $m) && strtoupper($m[0]) === $sepayCode) $score += 20;
    if ($account && ! empty($parsed['acc']) && str_replace(['+', ' '], '', $parsed['acc']) === str_replace(['+', ' '], '', $account)) $score += 8;
    if ($account && $o->coinbase_hosted_url && stripos($o->coinbase_hosted_url, $account) !== false) $score += 8;

    if ($score > 0) {
        $matches[] = [
            'id' => $o->id,
            'order_code' => $o->order_code,
            'sepay_code' => $sepayCode,
            'calc_vnd' => $calcVnd,
            'parsed_amount' => $parsed['amount'] ?? null,
            'parsed_acc' => $parsed['acc'] ?? null,
            'coinbase_hosted_url' => $o->coinbase_hosted_url,
            'status' => $o->status,
            'score' => $score,
        ];
    }
}

if (empty($matches)) {
    echo "No matching pending orders found for amount {$transferAmount}\n";
    exit(0);
}

usort($matches, function($a, $b) { return $b['score'] <=> $a['score']; });

foreach ($matches as $m) {
    echo "ID: {$m['id']} | order_code: {$m['order_code']} | sepay_code: {$m['sepay_code']} | calc_vnd: {$m['calc_vnd']} | parsed_amount: {$m['parsed_amount']} | parsed_acc: {$m['parsed_acc']} | score: {$m['score']}\n";
}

?>