🟢 FASE 0: LIMPIEZA Y CONFIGURACIÓN INICIAL (Día 1)
El objetivo es preparar el repositorio plantilla para tu lógica.

[ ] Limpieza de Base de Datos:
  [ ] Borrar migraciones basura del repo plantilla (tasks, courses, exercises, grades...). Revisar carpeta database/migrations.
  [ ] Borrar Seeders basura (TaskSeeder, CourseSeeder...).

[ ] Configuración de Tailwind:
  [ ] Verificar que tailwind.config.js tiene los colores de marca (violet, slate, zinc).
  [ ] Definir fuentes en CSS (Inter, Space Grotesk).

[ ] Stores de Pinia:
  [ ] Crear usePreferenceStore (para guardar el Agente favorito en localStorage).
  [ ] Crear useCanvasStore (para gestionar el estado del editor).

🔵 FASE 1: ARQUITECTURA DE DATOS (Backend Core)
Cumplimiento estricto de requisitos académicos (MySQL + Eloquent).

[x] Migraciones Principales:
  [x] users (Modificar): Añadir bio, avatar.
  [x] products (Crear): source_id (index), marketplace, brand (index), original_link, category_id.
  [x] product_images (Crear): url, type ('qc', 'original', 'user'), embedding (json).
  [x] outfits (Crear): user_id, title, thumbnail_url.

[x] Las 3 Relaciones N:M (OBLIGATORIAS):
  [x] 1. Canvas Logic (outfit_product): Crear tabla pivote con columnas: pos_x, pos_y, rotation, scale_x, scale_y, z_index, is_flipped, selected_image_id.
  [x] 2. Wishlist (product_user): Tabla pivote simple para "Guardados".
  [x] 3. Social (followers): Tabla pivote follower_id, following_id.

[x] Modelos Eloquent:
  [x] Product.php, Outfit.php, ProductImage.php.
  [x] Definir relaciones belongsToMany con ->withPivot(...) en Outfit.php.

[x] Seeder Maestro:
  [x] Crear script para importar 50 productos de prueba (Nike, Jordan, Stussy) desde un CSV/Array para no tener la web vacía.

🟣 FASE 2: LÓGICA DE NEGOCIO & API (Backend Services)
[x] Repository Pattern (Escalabilidad):
  [x] Crear interfaz ProductSearchRepository.
  [x] Implementar EloquentProductSearchRepository (Búsqueda por texto LIKE + Búsqueda por Marca).

[ ] Servicios:
  [ ] ScrapingService (Mock o Real): Que reciba una URL de Taobao y devuelva título + fotos.
  [ ] AffiliateService: Lógica para generar links de CNFans/Mulebuy inyectando tu código.

[ ] API Endpoints (Routes):
  [ ] GET /api/search: El "Smart Search" (detecta URL o Texto).
  [ ] GET /api/feed/live: Devuelve las últimas 10 imágenes QC (Live Feed).
  [ ] GET /api/outfits/{id}: Para ver y hacer "Remix".
  [ ] POST /api/outfits: Guardar el canvas (recibe array de items y posiciones).
  [ ] POST /api/products/upload: Para subir fotos de usuario.

🟠 FASE 3: FRONTEND - DISCOVERY (FindQC)
La experiencia de búsqueda y exploración.

[ ] Componente SmartSearchBar:
  [ ] Input principal con detección Regex (¿Es URL o Texto?).
  [ ] Redirección automática si es URL.

[ ] Home View (/):
  [ ] Hero Section con el buscador.
  [ ] Componente LiveFeed (Carrusel horizontal de QCs recientes).
  [ ] Listado "Trending Outfits" (Masonry Grid).

[ ] Search Results View (/search):
  [ ] Sidebar con filtros (Marca, Categoría).
  [ ] Grid de Productos.
  [ ] Product Card: Hover effect (cambia de foto QC a Oficial).

[ ] Product Detail View (/product/:id):
  [ ] Galería de fotos (Tabs: QC / Oficial).
  [ ] Botón "W2C" (Where to Cop) con lógica de Agentes.
  [ ] Botón "Añadir al Studio".

🔴 FASE 4: FRONTEND - THE STUDIO (ShopLook)
El corazón del proyecto (Canvas Interactivo).

[ ] Setup del Canvas:
  [ ] Instalar y configurar vue-konva.
  [ ] Crear lienzo reactivo que lea del useCanvasStore.

[ ] Sidebar del Editor:
  [ ] Pestaña "Buscar Ropa" (Drag & drop al lienzo).
  [ ] Pestaña "Armario" (Items guardados).
  [ ] Pestaña "Subir" (Upload propio).

[ ] Modal de "Asset Selection":
  [ ] Al soltar una prenda, preguntar: "¿Qué foto quieres usar? (QC, Oficial, Upload)".

[ ] Herramientas de Edición:
  [ ] Transformadores (Rotar, Escalar).
  [ ] Botón "Quitar Fondo" (conectar a API o mock).
  [ ] Control de Capas (Traer al frente / Enviar al fondo).

[ ] Funcionalidad "Remix":
  [ ] Cargar un outfit existente en el store para editarlo.

🟡 FASE 5: SOCIAL & USER SYSTEM
[ ] Perfil de Usuario (/u/:alias):
  [ ] Grid de Outfits creados.
  [ ] Botón "Seguir".

[ ] Ajustes de Usuario:
  [ ] Selector de "Agente Preferido": Guardar si prefiere CNFans, Mulebuy, etc.

[ ] Auth:
  [ ] Registro/Login (ya viene en la plantilla, revisar estilos).

⚪ FASE 6: CALIDAD Y ENTREGA (Academic Polish)
[ ] Testing:
  [ ] PHPUnit: Test unitario de creación de Outfit.
  [ ] PHPUnit: Test de búsqueda de productos.

[ ] Documentación:
  [ ] Generar Swagger/OpenAPI para la API.
  [ ] README.md con instrucciones de despliegue.
  [ ] Memoria del proyecto (PDF) con capturas.

[ ] Roles y Permisos:
  [ ] Proteger rutas de Admin (/admin).
  [ ] Asegurar que solo el dueño puede editar su outfit.
