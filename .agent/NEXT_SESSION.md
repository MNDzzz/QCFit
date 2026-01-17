# Próxima Sesión: Canvas Editor Component

## Objetivo
Implementar el componente principal del Studio Canvas con vue-konva.

## Tareas Pendientes

### 1. Crear `CanvasEditor.vue` Component
- Setup de vue-konva (Stage, Layer)
- Renderizar items desde canvasStore
- Implementar transformadores (v-transformer)

### 2. Sidebar de Productos
- Lista de productos guardados/buscados
- Drag & Drop hacia el canvas
- Preview de imagen antes de soltar

### 3. Toolbar de Herramientas
- **Layer Controls**: Bring to Front, Send to Back
- **Transform**: Flip Horizontal
- **Actions**: Remove Item, Clear Canvas

### 4. API Backend
- Crear `OutfitController.php`
- Método `store()` que guarde en `outfits` + pivote `outfit_product`
- Método `show()` para cargar outfits existentes (Remix)

### 5. Guardar/Cargar Outfit
- Botón "Save Outfit" que serialice el canvas
- Guardar posiciones (`x, y`), transformaciones (`rotation, scale`), z-index
- Cargar outfit existente en el canvas (parámetro de ruta)

## Checklist Técnico

- [ ] Instalar vue-konva (✅ Ya instalado)
- [ ] Crear CanvasStore (✅ Ya creado en `resources/js/store/canvas.js`)
- [ ] Crear `resources/js/components/canvas/CanvasEditor.vue`
- [ ] Crear `resources/js/components/canvas/CanvasSidebar.vue`
- [ ] Crear `resources/js/components/canvas/CanvasToolbar.vue`
- [ ] Crear `app/Http/Controllers/Api/OutfitController.php`
- [ ] Crear `app/Http/Requests/StoreOutfitRequest.php`
- [ ] Crear rutas API: `POST /api/outfits`, `GET /api/outfits/{id}`
- [ ] Actualizar vista `/studio` o `/canvas` en rutas web
- [ ] Probar drag & drop
- [ ] Probar guardar outfit
- [ ] Probar cargar outfit (Remix)

## Notas de Implementación

### VueKonva Ejemplo Mínimo
```vue
<template>
  <v-stage :config="stageConfig">
    <v-layer>
      <v-image 
        v-for="item in canvasItems" 
        :key="item.id"
        :config="item"
        @click="selectItem(item.id)"
      />
      <v-transformer ref="transformer" />
    </v-layer>
  </v-stage>
</template>
```

### Pivot Table Save
```php
$outfit->products()->attach($productId, [
    'pos_x' => $item['x'],
    'pos_y' => $item['y'],
    'rotation' => $item['rotation'],
    'scale_x' => $item['scaleX'],
    'scale_y' => $item['scaleY'],
    'z_index' => $item['zIndex'],
    'is_flipped' => $item['isFlipped'],
    'selected_image_id' => $imageId
]);
```

## Git Flow
1. Commit cada componente por separado
2. Merge a `develop` cuando funcione (sin errores)
3. NO acumular múltiples features en una rama

## Referencia Académica
Esta implementación cumple con:
- **Relación N:M Compleja**: `outfit_product` con atributos pivote
- **Frontend Avanzado**: vue-konva + Pinia state management
- **Backend Laravel**: Resource Controllers + Form Requests
