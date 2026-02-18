<?php

use App\Repositories\ProductSearchRepository;
use App\Services\ScrapingService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$repo = $app->make(ProductSearchRepository::class);
$scraper = $app->make(ScrapingService::class);

echo "\n--- TEST 1: TEXT SEARCH (Jordan) ---\n";
$results = $repo->search('Jordan');
echo "Found: " . $results->total() . " products.\n";
foreach ($results as $p) {
    echo "- [{$p->id}] {$p->name} ({$p->marketplace})\n";
}

echo "\n--- TEST 2: URL SCRAPING (Existing) ---\n";
// ID 4428977916 from seed (Jordan 1 Mocha)
$url = "https://weidian.com/item.html?itemID=4428977916";
try {
    $data = $scraper->scrapeUrl($url);
    echo "SUCCESS: Scraped {$data['title']}\n";
    echo "Source ID: {$data['source_id']}\n";
    echo "Cached: " . ($data['is_cached'] ? 'YES' : 'NO') . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n--- TEST 3: LIVE FEED ---\n";
$feed = $repo->getLatestQCImages(5);
echo "Got " . $feed->count() . " feed images.\n";
foreach ($feed as $img) {
    echo "- Image [{$img->id}] for Product [{$img->product->name}] (Type: {$img->type})\n";
}
