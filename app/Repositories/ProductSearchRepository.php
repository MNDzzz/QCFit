<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductSearchRepository
{
    /**
     * Busca productos basándose en una query de texto y filtros opcionales.
     *
     * @param string|null $query Texto de búsqueda (nombre, marca).
     * @param array $filters Filtros adicionales (category_id, brand, marketplace, etc.)
     * @param int $perPage Cantidad de resultados por página.
     * @return LengthAwarePaginator
     */
    public function search(?string $query, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Obtiene productos por búsqueda por ID (útil para scraping/import directos).
     *
     * @param string $sourceId
     * @return \App\Models\Product|null
     */
    public function findBySourceId(string $sourceId);

    /**
     * Obtiene las últimas imágenes QC subidas al sistema.
     *
     * @param int $limit
     * @return Collection
     */
    public function getLatestQCImages(int $limit = 15);
}
