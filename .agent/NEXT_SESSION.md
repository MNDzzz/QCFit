# Próxima Sesión: Backend - Outfit Controller

## Objetivo
Implementar la persistencia de outfits en la base de datos con el backend de Laravel.

## Estado Actual ✅

### ✅ COMPLETADO en Sesión 2026-01-17:

1. **CanvasEditor.vue Component** ✅
   - Setup de vue-konva (Stage, Layer, Image, Transformer)
   - Renderizar items desde canvasStore
   - Implementar transformadores (v-transformer)
   - Drag & drop funcional
   - Integración completa con Pinia

2. **CanvasSidebar.vue** ✅
   - Lista de productos guardados/buscados
   - Drag & Drop hacia el canvas
   - Tabs funcionales (Buscar / Armario)
   - Búsqueda integrada con API

3. **CanvasToolbar.vue** ✅
   - **Layer Controls**: Bring to Front, Send to Back
   - **Transform**: Flip Horizontal
   - **Actions**: Remove Item, Clear Canvas
   - **Save Button**: Modal de guardado implementado

4. **Vista Studio (Index.vue)** ✅
   - Integración completa de los 3 componentes
   - Header de navegación
   - Modal de guardado
   - Ruta '/studio' añadida

## Tareas Pendientes (Próxima Sesión)
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
