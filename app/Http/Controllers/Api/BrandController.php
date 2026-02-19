<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

/**
 * Controlador para gestionar las Marcas (Brands)
 */
class BrandController extends Controller
{
    /**
     * Listar marcas con filtrado y paginación
     */
    public function index()
    {
        $orderColumn = request('order_column', 'created_at');
        if (!in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'created_at';
        }
        $orderDirection = request('order_direction', 'desc');
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $brands = Brand::when(request('search_global'), function ($query) {
                $query->where(function($q) {
                    $q->where('id', request('search_global'))
                        ->orWhere('name', 'like', '%'.request('search_global').'%');
                });
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(50);

        return BrandResource::collection($brands);
    }

    /**
     * Crear una nueva marca
     */
    public function store(StoreBrandRequest $request)
    {
        $this->authorize('brand-create');
        $brand = Brand::create($request->validated());

        return new BrandResource($brand);
    }

    /**
     * Mostrar detalle de una marca
     */
    public function show(Brand $brand)
    {
        $this->authorize('brand-edit');
        return new BrandResource($brand);
    }

    /**
     * Actualizar una marca
     */
    public function update(Brand $brand, StoreBrandRequest $request)
    {
        $this->authorize('brand-edit');
        $brand->update($request->validated());

        return new BrandResource($brand);
    }

    /**
     * Eliminar una marca
     */
    public function destroy(Brand $brand)
    {
        $this->authorize('brand-delete');
        $brand->delete();

        return response()->noContent();
    }

    /**
     * Obtener lista simple de marcas para selects
     */
    public function getList()
    {
        return BrandResource::collection(Brand::all());
    }
}
