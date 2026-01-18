<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class QcScraperService
{
    /**
     * Endpoint del agregador público de QCs.
     * Acepta la URL del producto como query param ?url=...
     */
    const SCRAPER_ENDPOINT = 'https://qc.photos/qc';

    /**
     * Busca fotos QC para un producto si no las tiene.
     *
     * @param Product $product
     * @return \Illuminate\Support\Collection Devuelve las imágenes (existentes o nuevas)
     */
    public function fetchQCImages(Product $product)
    {
        // 1. Verificación rápida: Si ya tenemos QCs, retornar las existentes.
        $existingQcs = $product->images()->where('type', 'qc')->get();
        if ($existingQcs->count() > 0) {
            return $existingQcs;
        }

        if (!$product->original_link) {
            return collect([]);
        }

        $targetUrl = $product->original_link;

        try {
            Log::info("QcScraper: Iniciando búsqueda para producto #{$product->id} - URL: {$targetUrl}");

            // 2. Componer la URL de búsqueda en qc.photos
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                ])
                ->get(self::SCRAPER_ENDPOINT, [
                    'url' => $targetUrl
                ]);

            if ($response->failed()) {
                Log::warning("QcScraper: Falló la petición a qc.photos. Status: " . $response->status());
                return collect([]);
            }

            $html = $response->body();

            // 3. Parsear el HTML para encontrar imágenes usando DomCrawler
            $imageUrls = $this->extractImagesFromHtml($html);

            if (empty($imageUrls)) {
                Log::info("QcScraper: No se encontraron imágenes QC para el producto #{$product->id}");
                return collect([]);
            }

            // 4. Persistir las imágenes encontradas
            $savedImages = [];
            foreach ($imageUrls as $url) {
                // Verificar duplicados por URL para este producto
                $exists = ProductImage::where('product_id', $product->id)
                    ->where('url', $url)
                    ->exists();

                if (!$exists) {
                    $savedImages[] = ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $url,
                        'type' => 'qc',
                        'source' => 'qc.photos'
                    ]);
                }
            }

            Log::info("QcScraper: Guardadas " . count($savedImages) . " imágenes nuevas.");

            // Retornar colección fresca
            return $product->images()->where('type', 'qc')->get();

        } catch (\Exception $e) {
            Log::error("QcScraper Error: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Extrae URLs de imágenes del HTML de qc.photos usando DomCrawler.
     *
     * @param string $html
     * @return array
     */
    private function extractImagesFromHtml($html)
    {
        $urls = [];
        $crawler = new Crawler($html);

        // Buscar todas las imágenes
        // Ajustamos la lógica para filtrar mejor
        $crawler->filter('img')->each(function (Crawler $node) use (&$urls) {
            $src = $node->attr('src');

            if (empty($src))
                return;

            // Filtros básicos explicítos
            if (str_starts_with($src, 'data:image'))
                return;
            if (str_contains($src, 'logo'))
                return;
            if (str_contains($src, 'icon'))
                return;
            if (str_contains($src, '.svg'))
                return;

            // Normalización de URL
            if (!str_contains($src, 'http')) {
                $src = 'https://qc.photos' . (str_starts_with($src, '/') ? '' : '/') . $src;
            }

            $urls[] = $src;
        });

        // Limpieza y únicos (max 10)
        return array_slice(array_unique($urls), 0, 10);
    }
}
