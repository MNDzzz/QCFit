<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

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

        if (!$product->source_link && !$product->original_link) {
            return collect([]);
        }

        $targetUrl = $product->original_link ?? $product->source_link;

        try {
            Log::info("QcScraper: Iniciando búsqueda para producto #{$product->id} - URL: {$targetUrl}");

            // 2. Componer la URL de búsqueda en qc.photos
            // Ejemplo: https://qc.photos/qc?url=https://weidian.com/item.html?itemID=4477393523
            $response = Http::timeout(10)
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

            // 3. Parsear el HTML para encontrar imágenes
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
                        'type' => 'qc', // Marcaremos estas como QC
                        'source' => 'qc.photos' // Opcional, para saber el origen
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
     * Extrae URLs de imágenes del HTML de qc.photos.
     * Analiza el DOM buscando patrones comunes de galerías.
     *
     * @param string $html
     * @return array
     */
    private function extractImagesFromHtml($html)
    {
        $urls = [];

        // Suprimir warnings de HTML malformado
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // NOTA: La estructura de qc.photos puede variar. 
        // Generalmente muestran grid de imágenes. Buscaremos todas las img que parezcan ser de QC.
        // Las fotos de QC suelen estar alojadas en pandabuy, cssbuy, o dominios propios de agentes.
        // Ojo: qc.photos retorna a veces thumbnails y links a full size.

        // Estrategia: Buscar todas las etiquetas <img> dentro de contenedores de resultados
        // Ajustar el selector según lo que veamos en qc.photos real. 
        // Por ahora, capturamos todas las imágenes que NO sean logos o iconos.

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            if (!($img instanceof \DOMElement)) {
                continue;
            }
            $src = $img->getAttribute('src');

            // Filtros básicos para descartar basura
            if (empty($src))
                continue;
            if (strpos($src, 'data:image') === 0)
                continue; // Base64 thumbs a veces no sirven
            if (strpos($src, 'logo') !== false)
                continue;
            if (strpos($src, 'icon') !== false)
                continue;
            if (strpos($src, '.svg') !== false)
                continue;

            // Fix rutas relativas si las hay (asumiendo base https://qc.photos)
            if (strpos($src, 'http') === false) {
                $src = 'https://qc.photos' . (str_starts_with($src, '/') ? '' : '/') . $src;
            }

            // qc.photos suele mostrar imágenes de almacén con fondo verde/gris.
            // Si pudiéramos detectar el source 'pandabuy' o similar sería ideal.
            // Por ahora aceptamos cualquier imagen válida que parezca contenido.
            $urls[] = $src;
        }

        // Limpieza y únicos (solo tomar las primeras 10 para no saturar)
        return array_slice(array_unique($urls), 0, 10);
    }
}
