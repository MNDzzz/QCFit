<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutfitRequest;
use App\Http\Resources\OutfitResource;
use App\Http\Resources\OutfitSimpleResource;
use App\Models\Outfit;
use Illuminate\Http\Request;

/**
 * OutfitController
 * 
 * Controlador para gestionar los outfits creados en el Studio.
 * Implementa operaciones CRUD con soporte para datos pivote del canvas.
 */
class OutfitController extends Controller
{
    /**
     * Listar outfits públicos (feed principal).
     * Devuelve los outfits más recientes con sus productos.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $outfits = Outfit::with(['user', 'products.images'])
            ->latest()
            ->limit(20)
            ->get();

        return OutfitResource::collection($outfits);
    }

    /**
     * Mostrar un outfit específico con todos sus productos y posiciones.
     * Usado para la función "Remix" que carga el outfit en el canvas.
     *
     * @param int $id
     * @return OutfitResource
     */
    public function show($id)
    {
        $outfit = Outfit::with(['user', 'products.images'])->findOrFail($id);

        return new OutfitResource($outfit);
    }

    /**
     * Crear un nuevo outfit con los productos y sus posiciones.
     * Guarda los datos del canvas en la tabla pivote outfit_product.
     *
     * @param StoreOutfitRequest $request
     * @return OutfitResource
     */
    public function store(StoreOutfitRequest $request)
    {
        // Obtener datos validados
        $validated = $request->validated();

        // Crear el outfit
        $outfit = Outfit::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
        ]);

        // Preparar datos pivote para cada producto
        $productsData = [];
        foreach ($validated['items'] as $item) {
            $productsData[$item['product_id']] = [
                'pos_x' => $item['x'],
                'pos_y' => $item['y'],
                'rotation' => $item['rotation'],
                'scale_x' => $item['scaleX'],
                'scale_y' => $item['scaleY'],
                'z_index' => $item['zIndex'],
                'is_flipped' => $item['isFlipped'],
                'selected_image_id' => $item['imageId'] ?? null,
            ];
        }

        // Asociar productos con datos pivote usando sync
        $outfit->products()->sync($productsData);

        // Cargar relaciones para la respuesta
        $outfit->load(['user', 'products.images']);

        return new OutfitResource($outfit);
    }

    /**
     * Actualizar un outfit existente.
     * Permite editar título, descripción y las posiciones de los productos.
     *
     * @param StoreOutfitRequest $request
     * @param int $id
     * @return OutfitResource
     */
    public function update(StoreOutfitRequest $request, $id)
    {
        // Buscar outfit y verificar propiedad
        $outfit = Outfit::findOrFail($id);

        // Verificar que el usuario sea el dueño
        if ($outfit->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar este outfit.');
        }

        // Obtener datos validados
        $validated = $request->validated();

        // Actualizar datos básicos
        $outfit->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? $outfit->description,
            'thumbnail_url' => $validated['thumbnail_url'] ?? $outfit->thumbnail_url,
        ]);

        // Preparar datos pivote actualizados
        $productsData = [];
        foreach ($validated['items'] as $item) {
            $productsData[$item['product_id']] = [
                'pos_x' => $item['x'],
                'pos_y' => $item['y'],
                'rotation' => $item['rotation'],
                'scale_x' => $item['scaleX'],
                'scale_y' => $item['scaleY'],
                'z_index' => $item['zIndex'],
                'is_flipped' => $item['isFlipped'],
                'selected_image_id' => $item['imageId'] ?? null,
            ];
        }

        // Sincronizar productos (elimina los anteriores y añade los nuevos)
        $outfit->products()->sync($productsData);

        // Cargar relaciones para la respuesta
        $outfit->load(['user', 'products.images']);

        return new OutfitResource($outfit);
    }

    /**
     * Eliminar un outfit.
     * Solo el dueño puede eliminar su propio outfit.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Buscar outfit
        $outfit = Outfit::findOrFail($id);

        // Verificar propiedad
        if ($outfit->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar este outfit.');
        }

        // Eliminar relaciones pivote y el outfit
        $outfit->products()->detach();
        $outfit->delete();

        return response()->json([
            'message' => 'Outfit eliminado correctamente.',
            'id' => $id,
        ]);
    }

    /**
     * Listar outfits del usuario autenticado.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function myOutfits()
    {
        $outfits = Outfit::with(['products.images'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return OutfitResource::collection($outfits);
    }

    /**
     * Feed de outfits de usuarios seguidos.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function feed()
    {
        // Obtener IDs de usuarios seguidos
        $followingIds = auth()->user()->following()->pluck('users.id');

        // Obtener outfits de esos usuarios
        $outfits = Outfit::with(['user', 'products.images'])
            ->withCount('products')
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->paginate(10);

        return OutfitSimpleResource::collection($outfits);
    }
}

