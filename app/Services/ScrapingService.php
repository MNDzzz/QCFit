<?php

namespace App\Services;

use App\Repositories\ProductSearchRepository;
use Illuminate\Support\Str;

class ScrapingService
{
    protected $productRepo;

    public function __construct(ProductSearchRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    /**
     * Simula el scraping de una URL de producto.
     * Si el producto ya existe en nuestra DB (por el seeder), devuelve sus datos.
     * Si no, devuelve datos simulados.
     *
     * @param string $url
     * @return array
     */
    public function scrapeUrl(string $url): array
    {
        // 1. Detectar Marketplace y ID
        $data = $this->parseUrl($url);

        if (!$data) {
            throw new \Exception("URL no soportada o inválida.");
        }

        // 2. Intentar buscar en DB local para "simular" éxito real
        $existingProduct = $this->productRepo->findBySourceId($data['source_id']);

        if ($existingProduct) {
            return [
                'title' => $existingProduct->name,
                'price' => '¥' . rand(100, 900), // Precio simulado
                'images' => $existingProduct->images->pluck('url')->toArray(),
                'source_id' => $data['source_id'],
                'marketplace' => $data['marketplace'],
                'original_link' => $url,
                'is_cached' => true
            ];
        }

        // 3. Mock Fallback (Si no está en nuestra DB de seed)
        return [
            'title' => 'Producto Importado (Simulación)',
            'price' => '¥999',
            'images' => [
                'https://placehold.co/600x600?text=QC+Photo+1',
                'https://placehold.co/600x600?text=QC+Photo+2',
                'https://placehold.co/600x600?text=Original+Photo'
            ],
            'source_id' => $data['source_id'],
            'marketplace' => $data['marketplace'],
            'original_link' => $url,
            'is_cached' => false
        ];
    }

    /**
     * Extrae ID y Marketplace de la URL.
     * Soporta Weidian y Taobao básicos.
     */
    protected function parseUrl(string $url): ?array
    {
        $marketplace = null;
        $sourceId = null;

        if (Str::contains($url, 'weidian.com')) {
            $marketplace = 'weidian';
            parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
            $sourceId = $queryParams['itemID'] ?? null;
        } elseif (Str::contains($url, 'taobao.com')) {
            $marketplace = 'taobao';
            parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
            $sourceId = $queryParams['id'] ?? null;
        } elseif (Str::contains($url, '1688.com')) {
            $marketplace = '1688';
            // Lógica simple para extraer ID de 1688 (usualmente en el path)
            if (preg_match('/offer\/(\d+)\.html/', $url, $matches)) {
                $sourceId = $matches[1];
            }
        }

        if ($marketplace && $sourceId) {
            return ['marketplace' => $marketplace, 'source_id' => $sourceId];
        }

        return null;
    }
}
