<?php

namespace App\Repositories;

interface ProductSearchRepository
{
    /**
     * Search products by text query (name or brand).
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $query, int $limit = 20);

    /**
     * Search products similar to a vector (future implementation).
     *
     * @param array $vector
     * @return mixed
     */
    public function vectorSearch(array $vector);

    /**
     * Get latest QC images for the live feed.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestQCImages(int $limit = 10);
}
