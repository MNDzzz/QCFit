<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = \App\Models\Product::whereNotNull('original_link')
    ->whereDoesntHave('images', function ($q) {
        $q->where('type', 'qc');
    })
    ->first();

if ($product) {
    echo "PRODUCT_ID:" . $product->id . "\n";
    echo "ORIGINAL_LINK:" . $product->original_link . "\n";
} else {
    echo "NO_CANDIDATE_FOUND";
}
