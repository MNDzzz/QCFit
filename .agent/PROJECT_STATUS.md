# QCFit - Estado del Proyecto y Roadmap

**Última Actualización**: 2026-01-17  
**Rama Actual**: `feat/canvas-editor-component`  
**Rama Base**: `develop`

---

## 📋 Contexto del Proyecto

**QCFit** es una plataforma híbrida que combina:
1. **FindQC**: Buscador de productos de importación con fotos reales (QC)
2. **ShopLook Clone**: Editor visual (Canvas) para crear outfits
3. **Affiliate Hijacking**: Monetización mediante CNFans, Mulebuy, etc.

### Stack Tecnológico
- **Backend**: Laravel 10+, MySQL
- **Frontend**: Vue 3 (Composition API), Tailwind CSS
- **State Management**: Pinia
- **Canvas**: vue-konva + Konva.js
- **Metodología**: Repository Pattern, FormRequests, JsonResources

---

## ✅ IMPLEMENTADO (100% Funcional)

### FASE 0: Configuración Inicial
- ✅ Limpieza de migraciones plantilla
- ✅ Configuración Tailwind (colores: violet, slate, zinc)
- ✅ Fuentes personalizadas (Inter, Space Grotesk)

### FASE 1: Arquitectura de Datos (Backend Core) ✅

#### Migraciones
- ✅ `users` - Modificada (bio, avatar)
- ✅ `products` - Tabla principal (source_id, marketplace, brand, original_link, category_id)
- ✅ `product_images` - Imágenes (url, type: 'qc'/'original'/'user')
- ✅ `outfits` - Outfits creados (user_id, title, thumbnail_url)

#### Las 3 Relaciones N:M Obligatorias (Requisito Académico)
1. ✅ **Canvas Logic** (`outfit_product`):
   - Columnas pivote: `pos_x`, `pos_y`, `rotation`, `scale_x`, `scale_y`, `z_index`, `is_flipped`, `selected_image_id`
   - Modelo `Outfit.php` con `->withPivot(...)`

2. ✅ **Wishlist** (`product_user`):
   - Relación simple para productos guardados/favoritos
   - Método `favorites()` en `User.php`

3. ✅ **Social** (`followers`):
   - Tabla pivote: `follower_id`, `following_id`
   - Métodos `followers()` y `following()` en `User.php`

#### Modelos Eloquent
- ✅ `Product.php` - Con relaciones `images`, `category`
- ✅ `ProductImage.php` - Relación `product`
- ✅ `Outfit.php` - Relación `products()` con `withPivot`
- ✅ `User.php` - Relaciones `outfits`, `favorites`, `followers`, `following`

#### Seeders
- ✅ **ProductSeeder.php** - Importación masiva desde CSV
- ✅ **CSV Seed Data** (`database/seeders/data/products_seed.csv`) - 20 productos reales

### FASE 2: Lógica de Negocio & API (Backend Services) ✅

#### Repository Pattern
- ✅ `ProductSearchRepository` (Interface)
- ✅ `EloquentProductSearchRepository` (Implementación)
  - Búsqueda por texto (LIKE en nombre/marca)
  - Filtros dinámicos (category, brand, marketplace)
  - Método `getLatestQCImages()` para Live Feed

#### Servicios
- ✅ **ScrapingService** (`app/Services/ScrapingService.php`)
  - Detecta marketplace y extrae ID de URLs
  - Mock inteligente que prioriza productos en DB
  - Fallback a datos simulados si no existe

- ✅ **AffiliateService** (`app/Services/AffiliateService.php`)
  - Genera links de CNFans, Mulebuy, Hoobuy, Pandabuy
  - Inyección de código de referido `QCFIT_ACADEMIC`

#### API Endpoints (Laravel Resources)
- ✅ `GET /api/search` - Smart Search (URL o Texto)
  - Detecta URLs de Weidian/Taobao/1688
  - Si es URL → llama ScrapingService
  - Si es texto → búsqueda DB
  
- ✅ `GET /api/feed/live` - Live Feed de QC Photos (últimas 15)
- ✅ `GET /api/products/{id}` - Detalle de producto
- ✅ `GET /api/categories` - Listado de categorías

#### Resources (JSON Transformers)
- ✅ `ProductResource.php` - Transformador de productos
- ✅ `ProductImageResource.php` - Transformador de imágenes
  - **Fix aplicado**: Eliminada recursión infinita (solo devuelve datos básicos del producto)

### FASE 3: Frontend - Discovery (FindQC) ✅

#### Componentes Reutilizables
- ✅ **SmartSearch.vue** (`resources/js/components/SmartSearch.vue`)
  - Regex para detectar URLs de marketplaces
  - Llamada a API `/api/search`
  - Redirección automática según tipo de resultado

- ✅ **LiveFeed.vue** (`resources/js/components/LiveFeed.vue`)
  - Carrusel horizontal de QC photos
  - Animaciones premium (gradientes, hover effects)
  - Click para ir a producto

- ✅ **ProductCard.vue** (`resources/js/components/ui/ProductCard.vue`)
  - Efecto hover para cambiar imagen QC ↔ Original
  - Botón "Add to Studio"

#### Vistas Principales
- ✅ **Home View** (`resources/js/views/public/home/index.vue`)
  - Hero section con SmartSearch
  - LiveFeed integrado
  - Brands ticker

- ✅ **Search Results** (`resources/js/views/public/search/Results.vue`)
  - Sidebar con filtros (Marketplace, Category, Brands, Price Range)
  - Grid de productos usando ProductCard
  - Conectado a `/api/search`

- ✅ **Product Detail** (`resources/js/views/public/product/Show.vue`)
  - Toggle QC/Original photos
  - Galería con thumbnails
  - Meta información (peso, volumen, seller)
  - Agent Preference Widget
  - Botón "Buy via [Agent]" con affiliate links

#### Stores (Pinia)
- ✅ **usePreferenceStore** (`resources/js/store/preference.js`)
  - Agente preferido (localStorage)
  - Método `getAffiliateLink(product)` - Genera URLs de agentes
  - Soporta: CNFans, Mulebuy, Hoobuy, Pandabuy

- ✅ **useCanvasStore** (`resources/js/store/canvas.js`)
  - Gestión de items del canvas
  - Métodos: `addItem`, `removeItem`, `updateItem`, `selectItem`
  - Layer control: `bringToFront`, `sendToBack`
  - Transformaciones: `flipItem`

---

## 🚧 PENDIENTE (Próxima Sesión)

### FASE 4: Frontend - The Studio (Canvas Editor) 🔴 PRIORITARIO

Esta es la **feature más importante y compleja** del proyecto. Es el diferenciador clave.

#### 1. Canvas Editor Component (Alto Impacto)
**Archivo**: `resources/js/components/canvas/CanvasEditor.vue`

```vue
<template>
  <v-stage :config="stageConfig" @mousedown="handleStageClick">
    <v-layer>
      <v-image 
        v-for="item in sortedItems" 
        :key="item.id"
        :config="getImageConfig(item)"
        @click="selectItem(item.id)"
        @dragend="handleDragEnd(item.id, $event)"
      />
      <v-transformer 
        v-if="selectedItem"
        ref="transformerRef"
        :config="transformerConfig"
      />
    </v-layer>
  </v-stage>
</template>
```

**Responsabilidades**:
- Setup de vue-konva (Stage, Layer)
- Renderizar items desde `useCanvasStore`
- Implementar v-transformer para rotación/escala
- Detectar drag & drop de imágenes
- Actualizar store en tiempo real

**Complejidad**: Alta (vue-konva + state sync)

#### 2. Canvas Sidebar (Medio Impacto)
**Archivo**: `resources/js/components/canvas/CanvasSidebar.vue`

**Funcionalidades**:
- **Tab "Search"**: Buscador de productos integrado
- **Tab "Wardrobe"**: Lista de productos favoritos
- **Tab "Upload"**: Subir fotos propias (Future)
- **Drag & Drop**: Arrastrar productos al canvas

**Complejidad**: Media (integración con ProductCard)

#### 3. Canvas Toolbar (Bajo Impacto)
**Archivo**: `resources/js/components/canvas/CanvasToolbar.vue`

**Botones**:
- 🔼 Bring to Front
- 🔽 Send to Back
- 🔄 Flip Horizontal
- 🗑️ Remove Selected
- 🧹 Clear Canvas
- 💾 **Save Outfit** (más importante)

**Complejidad**: Baja (solo llama métodos del store)

#### 4. Backend API - Outfit Controller (Alto Impacto)
**Archivo**: `app/Http/Controllers/Api/OutfitController.php`

```php
class OutfitController extends Controller
{
    public function store(StoreOutfitRequest $request)
    {
        $outfit = Outfit::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'thumbnail_url' => $request->thumbnail_url, // Canvas screenshot
        ]);

        foreach ($request->items as $item) {
            $outfit->products()->attach($item['product_id'], [
                'pos_x' => $item['x'],
                'pos_y' => $item['y'],
                'rotation' => $item['rotation'],
                'scale_x' => $item['scaleX'],
                'scale_y' => $item['scaleY'],
                'z_index' => $item['zIndex'],
                'is_flipped' => $item['isFlipped'],
                'selected_image_id' => $item['imageId'],
            ]);
        }

        return new OutfitResource($outfit);
    }

    public function show($id)
    {
        $outfit = Outfit::with('products')->findOrFail($id);
        return new OutfitResource($outfit);
    }
}
```

**Complejidad**: Media (uso correcto de pivote con `->withPivot`)

#### 5. Form Request Validation
**Archivo**: `app/Http/Requests/StoreOutfitRequest.php`

```php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'thumbnail_url' => 'nullable|url',
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.x' => 'required|numeric',
        'items.*.y' => 'required|numeric',
        'items.*.rotation' => 'required|numeric',
        'items.*.scaleX' => 'required|numeric',
        'items.*.scaleY' => 'required|numeric',
        'items.*.zIndex' => 'required|integer',
        'items.*.isFlipped' => 'required|boolean',
        'items.*.imageId' => 'nullable|exists:product_images,id',
    ];
}
```

#### 6. Outfit Resource
**Archivo**: `app/Http/Resources/OutfitResource.php`

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'thumbnail_url' => $this->thumbnail_url,
        'user' => new UserResource($this->whenLoaded('user')),
        'items' => $this->products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product' => new ProductResource($product),
                'x' => $product->pivot->pos_x,
                'y' => $product->pivot->pos_y,
                'rotation' => $product->pivot->rotation,
                'scaleX' => $product->pivot->scale_x,
                'scaleY' => $product->pivot->scale_y,
                'zIndex' => $product->pivot->z_index,
                'isFlipped' => $product->pivot->is_flipped,
                'imageId' => $product->pivot->selected_image_id,
            ];
        }),
    ];
}
```

#### 7. Rutas API
**Archivo**: `routes/api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/outfits', [OutfitController::class, 'store']);
    Route::put('/outfits/{id}', [OutfitController::class, 'update']);
    Route::delete('/outfits/{id}', [OutfitController::class, 'destroy']);
});

Route::get('/outfits', [OutfitController::class, 'index']); // Public
Route::get('/outfits/{id}', [OutfitController::class, 'show']); // Public (para Remix)
```

#### 8. Vista Studio
**Archivo**: `resources/js/views/public/studio/Index.vue`

Integra:
- `CanvasEditor` (centro)
- `CanvasSidebar` (izquierda)
- `CanvasToolbar` (arriba)

### FASE 5: Social & User System (Opcional para demo)

- [ ] Perfil de Usuario (`/u/:alias`)
- [ ] Grid de Outfits creados
- [ ] Botón "Follow" funcional
- [ ] Feed de outfits de gente que sigues

### FASE 6: Calidad y Entrega (Pre-Presentación)

- [ ] PHPUnit Tests (Outfit creation, Product search)
- [ ] Swagger/OpenAPI documentation
- [ ] README.md completo
- [ ] Memoria del proyecto (PDF)
- [ ] Roles y Permisos (Admin panel)

---

## 🎯 ORDEN DE IMPLEMENTACIÓN RECOMENDADO

### Sesión 1: Canvas Core (4-5 horas)
1. **CanvasEditor.vue** - Setup vue-konva básico
2. **Drag & Drop** - Arrastrar desde sidebar
3. **Transformadores** - Rotar y escalar
4. **Layer Control** - Z-index management
5. **Test Manual** - Verificar que se puede crear outfit visualmente

### Sesión 2: Persistencia (2-3 horas)
1. **OutfitController** - Métodos `store()` y `show()`
2. **StoreOutfitRequest** - Validación
3. **OutfitResource** - Serialización con pivote
4. **Save Button** - Frontend que serialice canvas y llame API
5. **Load/Remix** - Cargar outfit existente en canvas

### Sesión 3: Polish & Testing (2 horas)
1. **CanvasSidebar** - Integración completa
2. **CanvasToolbar** - Todos los botones
3. **Thumbnail Generation** - Screenshot del canvas
4. **Browser Testing** - Verificar todo funciona
5. **Fixes** - Corregir bugs encontrados

---

## 📁 Estructura de Archivos Clave

### Backend
```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── SearchController.php ✅
│   │   ├── ProductController.php ✅
│   │   └── OutfitController.php ❌ TODO
│   ├── Requests/
│   │   └── StoreOutfitRequest.php ❌ TODO
│   └── Resources/
│       ├── ProductResource.php ✅
│       ├── ProductImageResource.php ✅
│       └── OutfitResource.php ❌ TODO
├── Models/
│   ├── Product.php ✅
│   ├── ProductImage.php ✅
│   ├── Outfit.php ✅
│   └── User.php ✅
├── Repositories/
│   ├── ProductSearchRepository.php ✅
│   └── EloquentProductSearchRepository.php ✅
└── Services/
    ├── ScrapingService.php ✅
    └── AffiliateService.php ✅
```

### Frontend
```
resources/js/
├── components/
│   ├── SmartSearch.vue ✅
│   ├── LiveFeed.vue ✅
│   ├── ui/
│   │   └── ProductCard.vue ✅
│   └── canvas/
│       ├── CanvasEditor.vue ❌ TODO (PRIORITARIO)
│       ├── CanvasSidebar.vue ❌ TODO
│       └── CanvasToolbar.vue ❌ TODO
├── views/public/
│   ├── home/index.vue ✅
│   ├── search/Results.vue ✅
│   ├── product/Show.vue ✅
│   └── studio/Index.vue ❌ TODO
└── store/
    ├── preference.js ✅
    └── canvas.js ✅
```

---

## ⚠️ Issues Conocidos y Soluciones

### ✅ RESUELTO: Recursión Infinita en Resources
**Problema**: `ProductImageResource` llamaba a `ProductResource` que cargaba `images` → loop infinito.

**Solución**: Línea 26 de `ProductImageResource.php` ahora solo devuelve array simple en lugar de Resource completo.

### ✅ RESUELTO: Home View con referencias a datos inexistentes
**Problema**: Template referenciaba `feedImages` que ya no existía en el script.

**Solución**: Limpiado el template para solo usar componente `<LiveFeed />`.

---

## 🔧 Comandos Útiles

### Git Flow
```bash
# Crear nueva feature
git checkout develop
git checkout -b feat/nombre-feature

# Después de implementar y verificar
git add .
git commit -m "feat: descripcion en español"
git checkout develop
git merge feat/nombre-feature

# Preparar siguiente feature
git checkout -b feat/siguiente-feature
```

### Laravel
```bash
# Ejecutar seeder
php artisan db:seed --class=ProductSeeder

# Crear controlador con resource
php artisan make:controller Api/OutfitController --api

# Crear FormRequest
php artisan make:request StoreOutfitRequest

# Crear Resource
php artisan make:resource OutfitResource
```

### Frontend
```bash
# Build para producción
npm run build

# Dev mode con hot reload
npm run dev
```

---

## 📊 Métricas del Proyecto

- **Commits hasta ahora**: 7 (en esta sesión)
- **Ramas mergeadas**: 5
- **APIs funcionando**: 4 endpoints
- **Componentes Vue**: 7
- **Stores Pinia**: 2
- **Backend Services**: 2
- **Productos seed**: 20

---

## 🎓 Requisitos Académicos Cumplidos

✅ **3 Relaciones N:M complejas** con atributos pivote  
✅ **Repository Pattern** implementado  
✅ **FormRequests para validación** (parcial, falta OutfitRequest)  
✅ **JsonResources para serialización**  
✅ **Migrations + Seeders**  
✅ **Eloquent Relationships con withPivot**  
✅ **SPA con Vue 3 + Composition API**  
✅ **State Management con Pinia**  
⏳ **Tests PHPUnit** (pendiente)  
⏳ **Documentación API** (pendiente)

---

## 📞 Notas para la Próxima Sesión

1. **Empezar desde**: Rama `feat/canvas-editor-component`
2. **Prioridad 1**: `CanvasEditor.vue` - Sin esto no hay proyecto
3. **Prioridad 2**: `OutfitController` - Para persistir los outfits
4. **Prioridad 3**: Botón Save que serialice y llame API
5. **Verificar siempre**: Build (`npm run build`) antes de merge

**Tiempo estimado para MVP funcional**: 6-8 horas de trabajo enfocado

---

## 🚀 Estado de Desarrollo

| Fase | Estado | Porcentaje |
|------|--------|------------|
| Fase 0: Config | ✅ Completo | 100% |
| Fase 1: Backend Core | ✅ Completo | 100% |
| Fase 2: Backend Services | ✅ Completo | 100% |
| Fase 3: Frontend Discovery | ✅ Completo | 100% |
| Fase 4: Canvas Studio | 🚧 En progreso | 10% |
| Fase 5: Social | ⏳ Pendiente | 0% |
| Fase 6: Testing/Docs | ⏳ Pendiente | 0% |

**Progreso Global del Proyecto: 65%**
