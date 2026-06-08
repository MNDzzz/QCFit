<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSourceRequest;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use Illuminate\Http\Request;

/**
 * Controlador para gestionar los Marketplaces (Sources)
 */
class SourceController extends Controller
{
    /**
     * Listar marketplaces con filtrado y paginación
     */
    public function index()
    {
        // HERENCIA: Usamos los métodos del Controlador Base
        $orderColumn = $this->getOrderColumn(['id', 'name', 'created_at']);
        $orderDirection = $this->getOrderDirection();

        $sources = Source::when(request('search_global'), function ($query) {
                $query->where(function($q) {
                    $q->where('id', request('search_global'))
                        ->orWhere('name', 'like', '%'.request('search_global').'%');
                });
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(50);

        return SourceResource::collection($sources);
    }

    /**
     * Crear un nuevo marketplace
     */
    public function store(StoreSourceRequest $request)
    {
        $this->authorize('source-create');
        $source = Source::create($request->validated());

        return new SourceResource($source);
    }

    /**
     * Mostrar detalle de un marketplace
     */
    public function show(Source $source)
    {
        $this->authorize('source-edit');
        return new SourceResource($source);
    }

    /**
     * Actualizar un marketplace
     */
    public function update(Source $source, StoreSourceRequest $request)
    {
        $this->authorize('source-edit');
        $source->update($request->validated());

        return new SourceResource($source);
    }

    /**
     * Eliminar un marketplace
     */
    public function destroy(Source $source)
    {
        $this->authorize('source-delete');
        $source->delete();

        return response()->noContent();
    }

    /**
     * Obtener lista simple de marketplaces para selects
     */
    public function getList()
    {
        return SourceResource::collection(Source::all());
    }
}
