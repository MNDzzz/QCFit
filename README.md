# QCFit - The Ultimate Weidian & Taobao QC Finder
> *Proyecto Académico: Desarrollo de Aplicaciones Web Híbridas (Senior Level)*

**QCFit** es una plataforma integral diseñada para resolver el problema de la "compra a ciegas" en marketplaces asiáticos (Weidian, Taobao, 1688). Combina un buscador inteligente de fotos reales (QC Photos) con un estudio de diseño de outfits (Canvas), permitiendo a los usuarios visualizar sus compras antes de realizarlas y compartir su estilo con la comunidad.

![QCFit Banner](/public/images/og-default.jpg)

## 🚀 Funcionalidades Clave

### 🔍 1. FindQC (Discovery)
- **Smart Search**: Detecta automáticamente enlaces de marketplaces (Weidian/Taobao) y extrae su ID.
- **Live Feed**: Carrusel en tiempo real con las últimas QC Photos encontradas en la comunidad.
- **Toggle View**: Comparación instantánea entre foto original (marketing) y foto QC (realidad).
- **Affiliate Integration**: Generación de enlaces para agentes populares (CNFans, Mulebuy, Hoobuy, Pandabuy).

### 🎨 2. The Studio (Outfit Builder)
- **Canvas Interactivo**: Editor visual Drag & Drop potenciado por `vue-konva` con soporte para múltiples capas.
- **Layer System**: Control total de capas (z-index), rotación, escalado, volteo y **eliminación de fondo**.
- **Remix Mode**: Posibilidad de abrir cualquier outfit público y editarlo/mejorarlo en el Studio.
- **Wardrobe**: Panel lateral con favoritos y buscador integrado para añadir productos rápidamente.
- **Export**: Descarga de outfits en alta resolución (PNG/JPG) listos para redes sociales.

### 🌐 3. Social & Perfil Público
- **Perfiles Sociales**: Portfolio dinámico con outfits y wishlist (favoritos) de cada usuario.
- **Filtrado Avanzado**: Buscador y ordenación local (Más recientes, A-Z) tanto en outfits como en favoritos.
- **Sistema de Seguidores**: Follow/Unfollow con contadores en tiempo real y modales de seguimiento.
- **Affiliate Hijacking**: Inyección automática de códigos de referido en todos los enlaces de salida.

---

## 🛠️ Stack Tecnológico & Arquitectura

### Backend (Laravel 10+ MVC)
- **Arquitectura**: Patrón **MVC estricto** con separación de responsabilidades.
- **Capa de Persistencia**: Repository Pattern para la lógica de búsqueda (`ProductSearchRepository`).
- **Base de Datos**: MySQL con relaciones N:M complejas (Pivots con atributos de diseño).
- **Vistas de Datos**: Uso extensivo de **Eloquent Resources** para una API JSON limpia y escalable.
- **Validación**: Lógica de validación desacoplada en **FormRequests**.
- **Autenticación**: Laravel Sanctum para sesiones seguras en la SPA.

### Frontend (Vue 3 + Vite)
- **Core**: Composition API con `<script setup>` y organización modular.
- **Estado**: Pinia con persistencia automática de preferencias (Agente, Tema).
- **UI & UX**: Tailwind CSS + PrimeVue (Aura) con diseño Dark Mode Premium y micro-animaciones.
- **Lienzo**: Konva.js para la manipulación avanzada del Canvas.

---

## ⚙️ Instalación y Despliegue

### Requisitos
- PHP >= 8.1
- Composer
- Node.js >= 18
- MySQL

### 1. Clonar Repositorio
```bash
git clone https://github.com/mndzzz/qcfit.git
cd qcfit
```

### 2. Configuración Backend
```bash
cp .env.example .env
composer install
php artisan key:generate
# Importante: Carga productos, outfits e interacciones sociales de prueba
php artisan migrate:fresh --seed 
```

### 3. Configuración Frontend
```bash
npm install
npm run dev
```

Acceder a: `http://127.0.0.1:8000`

---

## 📚 Estructura y Requisitos Académicos

### Relaciones Many-to-Many (N:M)
El proyecto implementa 3 relaciones complejas obligatorias:
1. **Outfits <-> Products**: Tabla `outfit_product`. Almacena coordenadas (X, Y), escala, rotación y capa.
2. **Users <-> Products** (Wishlist): Tabla `product_user`.
3. **Users <-> Users** (Followers): Tabla `followers`.

### Organización de Archivos
- **/app/Http/Controllers/Api**: Controladores del sistema.
- **/app/Http/Resources**: Transformación de modelos a JSON.
- **/resources/js/views**: Vistas principales de la SPA (The "V" in MVC).
- **/resources/js/components/layout**: Componentes estructurales (Navbar, Sidebar).
- **/docs**: Carpeta con manuales, wireframes y documentación técnica del proyecto.

---

## 👥 Créditos
Desarrollado como Proyecto Final de Desarrollo Web por el equipo de QCFit.
