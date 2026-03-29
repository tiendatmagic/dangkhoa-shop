<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Products::find(175);
if ($p) {
    echo "Found Product 175:\n";
    echo "  Name: " . $p->name . "\n";
    echo "  Price in DB: " . $p->price . "\n";
    echo "  Product Type: " . $p->product_type . "\n";
    echo "  Quantity: " . ($p->quantity ?? 'NULL') . "\n";
} else {
    echo "Product 175 not found\n";
}
