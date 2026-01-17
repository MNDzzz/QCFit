# QCFit - The Ultimate Weidian & Taobao QC Finder
> *Proyecto Académico: Desarrollo de Aplicaciones Web Híbridas*

**QCFit** es una plataforma integral diseñada para resolver el problema de la "compra a ciegas" en marketplaces asiáticos (Weidian, Taobao, 1688). Combina un buscador inteligente de fotos reales (QC Photos) con un estudio de diseño de outfits (Canvas), permitiendo a los usuarios visualizar sus compras antes de realizarlas.

![QCFit Banner](/public/images/og-default.jpg)

## 🚀 Funcionalidades Clave

### 🔍 1. FindQC (Discovery)
- **Smart Search**: Detecta automáticamente enlaces de marketplaces (Weidian/Taobao) y extrae su ID.
- **Live Feed**: Carrusel en tiempo real con las últimas QC Photos encontradas.
- **Toggle View**: Comparación instantánea entre foto original (marketing) y foto QC (realidad).
- **Affiliate Integration**: Generación de enlaces para agentes populares (CNFans, Mulebuy, Hoobuy, Pandabuy).

### 🎨 2. The Studio (Outfit Builder)
- **Canvas Interactivo**: Editor visual Drag & Drop potenciado por `vue-konva`.
- **Layer System**: Control total de capas (z-index), rotación, escalado y volteo.
- **Remix Mode**: Posibilidad de abrir cualquier outfit público y editarlo/mejorarlo.
- **Wardrobe**: Panel lateral con favoritos y buscador integrado.
- **Export**: Descarga de outfits en alta resolución (PNG/JPG).

### 🌐 3. Social & Monetización
- **Perfiles Públicos**: Portfolio de outfits creado por cada usuario.
- **Sistema de Seguidores**: Follow/Unfollow con contadores en tiempo real.
- **Affiliate Hijacking**: Inyección automática de códigos de referido en enlaces de salida.

---

## 🛠️ Stack Tecnológico

### Backend (Laravel 10 API)
- **Arquitectura**: Repository Pattern Service-Oriented.
- **Base de Datos**: MySQL con relaciones N:M complejas (Pivots con atributos).
- **Recursos**: API Resources para transformación de datos JSON.
- **Validación**: FormRequests robustos con reglas personalizadas.
- **Autenticación**: Laravel Sanctum (SPA Authentication).

### Frontend (Vue 3 + Vite)
- **Core**: Composition API + `<script setup>`.
- **Estado**: Pinia con persistencia (localStorage).
- **UI Framework**: Tailwind CSS v3 + PrimeVue 4 (Aura Theme).
- **Gráficos**: Konva.js (Canvas 2D).
- **SEO**: `@vueuse/head` para meta tags dinámicos.

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
php artisan migrate --seed # Importante: Carga productos de prueba
```
*Asegúrate de configurar DB_DATABASE, DB_USERNAME, etc. en el .env*

### 3. Configuración Frontend
```bash
npm install
npm run build # Para producción
# o
npm run dev # Para desarrollo
```

### 4. Ejecución
```bash
php artisan serve
```
Acceder a: `http://127.0.0.1:8000`

---

## 📚 Estructura del Proyecto

### Relaciones N:M (Requisito Académico)
El proyecto implementa 3 relaciones Many-to-Many clave:
1. **Outfits <-> Products**: Tabla `outfit_product`.
   - Atributos Pivot: `pos_x`, `pos_y`, `rotation`, `scale_x`, `scale_y`, `z_index`, `is_flipped`.
2. **Users <-> Products** (Wishlist): Tabla `product_user`.
3. **Users <-> Users** (Followers): Tabla `followers`.

---

## 👥 Créditos
Desarrollado como Proyecto Final de Desarrollo Web.
- **Autor**: [Tu Nombre]
- **Asignatura**: Desarrollo Web Entorno Servidor / Cliente

---

*Nota: Este proyecto es educativo y no está afiliado a Weidian, Taobao ni a los agentes mencionados.*
