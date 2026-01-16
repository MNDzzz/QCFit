MASTER PROMPT: PROYECTO "QCFit" - ULTIMATE EDITION
ROL: Actúa como un Arquitecto de Software Senior y Desarrollador Fullstack experto en el stack TALL/VALL (Vue 3 Composition API, Axios, Laravel 10/11, Tailwind CSS).
1. CONTEXTO Y MISIÓN
Estamos desarrollando un proyecto académico final (TFG) de alto nivel que fusiona dos plataformas de éxito:
FindQC (Buscador): Indexa productos de marketplaces chinos (Taobao, Weidian, 1688) permitiendo ver fotos reales de control de calidad (QC) antes de comprar.
ShopLook (Creatividad): Un editor visual tipo "Canvas" donde los usuarios crean outfits combinando estas fotos.
Objetivo de Negocio: Crear una plataforma "viva" con feed en tiempo real y monetización mediante un sistema de "Monopolio de Afiliados Inteligente": el usuario elige su agente favorito (CNFans, Mulebuy, etc.) y la plataforma reescribe todos los enlaces de salida con mis códigos de referido para ese agente específico.

2. REQUISITOS TÉCNICOS & RESOLUCIÓN DE CONFLICTOS
IMPORTANTE: El enunciado académico menciona "Bootstrap", pero el repositorio base (mndzzz/QCFit) está pre-configurado con Tailwind CSS.
DECISIÓN TÉCNICA: Ignorar Bootstrap. Usaremos EXCLUSIVAMENTE Tailwind CSS.
Justificación: Necesario para el posicionamiento granular (absolute, coordenadas exactas) del Canvas y coherencia con la configuración actual de Vite.
Stack Estricto:
Backend: Laravel 10/11 (API REST con Eloquent).
Frontend: Vue 3 (Composition API <script setup> obligatorio) + Pinia (State Management) + Vue Router + Axios.
Base de Datos: MySQL.
Seguridad: spatie/laravel-permission para Roles (Admin/User).

3. ARQUITECTURA DE DATOS (MySQL Schema)
El diseño debe cumplir el requisito académico de 3 Relaciones N:M, una de ellas con atributos pivote complejos.
Tablas Principales
users: id, name, alias (único), email, password, bio, avatar.
products:
id, category_id, name.
source_id (String, Index): ID único del marketplace (ej: 6824563 de Taobao).
marketplace (Enum): taobao, weidian, 1688.
brand (String, Index, Nullable): [REQ UX] Para filtrar por marca (ej: "Nike", "Stussy").
original_link (Text).
product_images:
id, product_id, url.
type (Enum): qc (fotos almacén), original (marketing), user_upload.
embedding (JSON, Nullable): Vector numérico para futura búsqueda por IA.
outfits: id, user_id, title, description, thumbnail_url.
Las 3 Relaciones N:M Obligatorias
RELACIÓN CRÍTICA (Canvas Logic): outfits <-> products
Tabla Pivote: outfit_product.
Atributos Pivote (Cumple requisito académico): Guardan el estado visual del item en el lienzo.
pos_x, pos_y (Decimal): Coordenadas.
rotation, scale_x, scale_y (Decimal): Transformación.
z_index (Integer): Orden de capas.
is_flipped (Boolean).
selected_image_id (FK -> product_images): Qué variante de foto específica se usó.
Wishlist: users <-> products (Tabla product_user).
Social: users <-> users (Tabla followers - Self referencing).

4. FUNCIONALIDADES CORE Y LÓGICA DE NEGOCIO
A. Home: "Smart Search" & "Live Feed"
Smart Search Input (Frontend):
Detectar mediante Regex si el input es URL o Texto.
Caso URL: Ignorar DB local -> Scraping Service (Mock o Real) -> Redirigir a Producto.
Caso Texto: Usar ProductSearchRepository (filtrar por name O brand).
Live QC Feed (Engagement):
Mostrar en la Home un carrusel con las últimas imágenes type='qc' ingresadas en el sistema para dar sensación de actividad viva.
B. El Editor (Canvas + Remix)
Tecnología: Librería vue-konva.
Modal de Selección: Al arrastrar prenda, permitir elegir fuente: QC, Oficial o Upload (con binding obligatorio al ID del producto).
Microservicio IA: Botón "Quitar Fondo" conecta a endpoint (Python rembg).
Función "Remix": Botón en outfits públicos que clona los items y posiciones en el canvas del usuario actual para editar sobre la base de otro.
C. Monetización (Agent Preference System)
Pinia Store: usePreferenceStore guarda el agente favorito del usuario (CNFans, Mulebuy, Hoobuy) en localStorage.
Affiliate Hijacking: Al hacer clic en "W2C" (Comprar), el sistema genera la URL de destino al vuelo inyectando MI código de afiliado correspondiente al agente seleccionado por el usuario.
D. Escalabilidad (Repository Pattern)
Implementar interfaz ProductSearchRepository.
Implementación actual: MySQL (LIKE y lógica PHP para vectores JSON).
Futuro: Preparado para inyectar Qdrant/Meilisearch sin cambiar controladores.



1. Primera Etapa: Investigación de referencias y definición de la idea
Para el desarrollo del proyecto "OutfitBuilder" (QCFit), se ha realizado un estudio de mercado analizando aplicaciones existentes que resuelven partes del problema (búsqueda de productos de importación y creación de estilismos), pero que funcionan de manera aislada.
A continuación, se presentan las tres referencias seleccionadas, su análisis y la definición de nuestra propuesta de valor única.
1.1. Análisis de Referencias
Referencia 1: FindQC.com (QC Finder)
¿En qué consiste? Es un motor de búsqueda vertical especializado en marketplaces chinos (Taobao, Weidian, 1688). Su función principal es indexar las fotos de "Control de Calidad" (QC Photos) que los agentes de compras toman en los almacenes. Permite a los usuarios ver el estado real de un producto antes de importarlo, evitando las fotos de marketing engañosas de los vendedores.
Análisis y características clave:
Búsqueda inversa: Permite pegar un enlace de producto y encontrar sus fotos almacenadas.
Historial visual: Muestra múltiples fotos de diferentes compras del mismo artículo.
Conversión de enlaces: Transforma enlaces nativos chinos en enlaces de plataformas de agentes.
Ideas extraídas para el proyecto:
La necesidad crítica de "Verdad Visual": El usuario no confía en la foto del vendedor, necesita la foto del almacén (QC).
La lógica de extracción de ID: El sistema debe reconocer que un link de Taobao y uno de Weidian son fuentes distintas pero tratables bajo una misma lógica de búsqueda.

Referencia 2: ShopLook.io
¿En qué consiste? Es una plataforma social y creativa que permite a los usuarios diseñar "Outfits" o collages de moda. Funciona como un lienzo interactivo donde se pueden arrastrar prendas, eliminar sus fondos automáticamente y superponerlas para visualizar cómo combinan entre sí antes de comprar.
Análisis y características clave:
Canvas Interactivo: Herramienta de edición "Drag & Drop" (arrastrar y soltar) muy fluida.
Eliminación de fondos (IA): Característica vital para que los collages se vean profesionales y estéticos.
Comercio social: Cada prenda en el collage es un enlace de compra. Si a un usuario le gusta el outfit de otro, puede comprar los ítems individuales.
Ideas extraídas para el proyecto:
La Gamificación de la moda: Crear outfits es divertido y retiene al usuario mucho más tiempo que una simple búsqueda.
El Editor visual: La implementación de un canvas (lienzo) es el diferenciador clave que transforma una herramienta utilitaria en una red social.

Referencia 3: Doppel.fit (o PandaBuy Leads)
¿En qué consiste? Es una plataforma de descubrimiento basada en tendencias ("Trends"). A diferencia de un buscador donde tú sabes lo que quieres, aquí se muestra un "Feed" en vivo de lo que otros usuarios están comprando o buscando en tiempo real dentro del nicho de streetwear y réplicas.
Análisis y características clave:
Prueba Social (Live Feed): Muestra actividad reciente, lo que valida que la plataforma está viva y reduce la ansiedad de compra ("si otros lo compran, será bueno").
Filtrado por Marcas: Organización clara por marcas populares (Nike, Stussy, etc.) para facilitar la navegación sin rumbo fijo.
Interfaz visual tipo Grid: Prioriza la imagen sobre el texto.
Ideas extraídas para el proyecto:
La importancia de una Home dinámica: No mostrar solo una barra de búsqueda vacía, sino inspirar al usuario con contenido reciente ("Live QC Feed").
La estructura de categorización por Marcas: Vital para el SEO y la usabilidad en el nicho de moda urbana.

1.2. Patrones Comunes Detectados
Tras analizar las tres referencias, identificamos los siguientes patrones que aplicaremos:
Prioridad Visual: En los tres casos, la imagen es el elemento principal de la interfaz.
Contenido Generado por el Usuario (UGC): Ya sean fotos de QC o Outfits creados, el valor de la plataforma reside en los datos que aporta la comunidad.
Monetización por Afiliación: Ninguna de estas webs vende ropa directamente; todas actúan como intermediarios que redirigen el tráfico a cambio de comisiones.

1.3. Definición de la Idea Propia: "OutfitBuilder" (QCFit)
Basándonos en la investigación, definimos OutfitBuilder como una Plataforma Híbrida de Moda y Herramienta de Compras que fusiona la utilidad técnica de la verificación de productos con la creatividad social del diseño de moda.
Propósito de la Aplicación
El objetivo es resolver las dos mayores fricciones de la compra de moda de importación (Reps/Streetwear):
La Incertidumbre de Calidad: "¿Se verá bien esta prenda en la realidad?" (Solucionado mediante el buscador de QC).
La Incertidumbre de Estilo: "¿Cómo combino esta prenda y cómo quedará el conjunto?" (Solucionado mediante el Canvas de Outfits).
Funcionalidades Principales
Omni-Buscador Inteligente ("Smart Search"):
Capacidad de detectar si el usuario introduce texto (búsqueda por nombre/marca) o una URL de un marketplace chino (Taobao/Weidian/1688).
Extracción automática de imágenes reales (QC) mediante scraping o APIs de terceros.
Editor de Outfits (Canvas):
Lienzo interactivo para crear combinaciones de ropa.
Selector de Activos: Permite al usuario elegir entre usar la foto de marketing, la foto real (QC) o subir su propia imagen.
Herramientas de edición: Recorte, rotación y eliminación de fondos mediante IA.
Sistema de Preferencia de Agentes (Monetización):
El usuario configura su agente de envíos favorito (ej. CNFans, Mulebuy).
La plataforma reescribe automáticamente todos los enlaces de compra para usar ese agente, inyectando nuestro código de afiliado ("Affiliate Hijacking").
Feed Social y "Remix":
Visualización en tiempo real de productos verificados.
Capacidad de copiar y editar ("Remix") los outfits creados por otros usuarios.
Propuesta de Valor
A diferencia de FindQC, que es solo una herramienta de consulta, y de ShopLook, que usa fotos genéricas de stock, OutfitBuilder ofrece una experiencia integral para el comprador de nicho:
"La única plataforma donde puedes encontrar ropa exclusiva a bajo coste, verificar su calidad real con fotos de almacén y probar virtualmente cómo te queda antes de gastar un solo euro, todo mientras simplificamos el complejo proceso de los agentes de compra."
🗺️ SITEMAP 2.0: ARQUITECTURA "QCFIT"
Este sitemap está diseñado bajo el principio de "Interconnected Discovery". No hay callejones sin salida; cada página de contenido lleva al editor (Canvas) o a la compra (Afiliado).
🟢 MÓDULO 1: DISCOVERY & BRANDING (Público)
Donde capturamos al usuario y mostramos "vida" en la plataforma.
HOME (/)
Hero Smart Search: Input inteligente (Detecta URL vs Texto).
Live QC Feed: Carrusel en tiempo real ("Just Checked").
Brand Spotlight: Logos de marcas populares (Nike, Stussy, etc.) -> Lleva a /brand/{name}.
Remix Teasers: Outfits destacados con botón directo "Edit in Studio".
SEARCH CENTER (/search)
Resultados Mixtos: Productos + Outfits.
Filtros Avanzados: Marcas, Marketplace, Precio, Categoría.
BRAND INDEX (/brands) NUEVO
Directorio A-Z de marcas (Vital para SEO: "QC Photos Nike", "QC Photos Yeezy").
Brand Page (/brand/{slug}): Feed filtrado solo de esa marca.
EXPLORE FEED (/explore)
Grid infinito de Outfits de la comunidad.
🔵 MÓDULO 2: PRODUCT & CONVERSION (El Core)
Donde ocurre la magia de la monetización y la decisión.
PRODUCT DETAIL (/product/{id})
Visor QC: Toggle Foto Original / QC.
Brand Link: Clic en la marca lleva a /brand/{slug}.
Acciones:
❤️ Save to Wardrobe.
🎨 Add to Studio: Envía la prenda al Canvas.
🛒 Smart W2C: Botón de compra que usa la Preferencia de Agente del usuario.
OUTFIT DETAIL (/outfit/{id})
Vista estática del fit.
Botón "Remix This": Clona el outfit entero en el /studio del usuario actual.
Shop List: Lista de items usados (Links afiliados).
🟣 MÓDULO 3: THE STUDIO (Herramienta Privada)
Requiere Auth.
STUDIO / BUILDER (/studio)
Canvas: Área de trabajo (Konva.js).
Sidebar Assets:
Search: Buscador mini integrado.
Wardrobe: Mis guardados.
Uploads: Mis fotos subidas.
Toolbar: Remove BG, Layers, Filters.
⚪ MÓDULO 4: USER & SYSTEM (Gestión)
USER PROFILE (/u/{alias})
My Fits: Galería pública.
My Wardrobe: Lista de deseos pública/privada.
SETTINGS (/settings)
Agent Preference: Selector global (CNFans, Mulebuy, etc.) -> Define a dónde van los links de salida.
Appearance: Dark/Light Mode.
GLOBAL OVERLAYS (Componentes omnipresentes)
Agent Selector Modal: Accesible desde el Navbar si no está configurado.
Auth Modal: Login/Register rápido.


















📘 QCFIT MASTER WIREFLOW (Text-Based Description)
Leyenda de símbolos:
[UI]: Elemento visual en pantalla.
[CLICK]: Acción del usuario.
➔: Resultado/Navegación inmediata.
ℹ️ NOTE: Anotación de funcionalidad global (sin cambio de pantalla).

🟢 CAPA GLOBAL (Navbar & Footer)
Presente en todas las pantallas excepto en el Studio (que tiene su propia UI).
NAVBAR
[UI] Logo QCFit: Izquierda.
[CLICK] ➔ Ir a Home Screen.
[UI] Enlaces Navegación: "Explore", "Brands", "Studio".
[CLICK] ➔ Ir a sus respectivas pantallas.
[UI] Search Icon:
[CLICK] ➔ Despliega barra de búsqueda flotante o lleva a /search.
[UI] Ajustes Globales (Iconos):
ℹ️ Sol/Luna: Al hacer clic, cambia toda la UI de Claro a Oscuro (CSS Class Toggle).
ℹ️ Bandera/Moneda: Al hacer clic, despliegue pequeño (Dropdown) para cambiar entre USD/EUR/CNY.
[UI] Auth Area:
Si es invitado: Botón "Login". [CLICK] ➔ Abre Auth Modal.
Si está logueado: Avatar de usuario. [CLICK] ➔ Dropdown Menú (Profile, Settings, Logout).

1. HOME SCREEN (/)
El Hub central.
A. HERO SECTION
[UI] Smart Search Bar (Input Gigante):
[INPUT TEXTO] (Ej: "Yeezy Gap") + Enter ➔ Ir a Search Results.
[PASTE URL] (Ej: weidian.com/item...) + Enter ➔ Activa Scraper ➔ Ir a Product Detail Page (Producto nuevo o existente).
B. LIVE FEED SECTION ("Just Checked")
[UI] Carrusel de tarjetas de producto: Muestra foto, nombre y hora ("Hace 2 min").
[CLICK Card] ➔ Ir a Product Detail Page de ese ítem.
C. BRAND SPOTLIGHT
[UI] Grid de Logos (Nike, Stussy, Balenciaga):
[CLICK Logo] ➔ Ir a Brand Page (Ej: /brand/nike).
[CLICK "View All"] ➔ Ir a Brand Index.
D. REMIX TEASERS
[UI] Tarjetas de Outfit: Muestra el fit completo.
[CLICK Imagen] ➔ Ir a Outfit Detail Page.
[CLICK Botón "Edit in Studio"] ➔ Ir a The Studio (Con los ítems precargados).

2. SEARCH & DISCOVERY ECOSYSTEM
PANTALLA: SEARCH RESULTS (/search)
[UI] Sidebar de Filtros: Checkboxes (Marcas, Categoría, Precio, Marketplace).
[CLICK Filtro] ➔ Recarga el Grid de resultados (AJAX).
[UI] Tabs Superiores: [Productos] | [Outfits].
[UI] Grid de Resultados:
[CLICK Producto] ➔ Ir a Product Detail Page.
[CLICK Outfit] ➔ Ir a Outfit Detail Page.
PANTALLA: BRAND INDEX (/brands)
[UI] Lista Alfabética A-Z:
[UI] Grid de Logos:
[CLICK Logo] ➔ Ir a Brand Page.
PANTALLA: BRAND PAGE (/brand/{slug})
[UI] Header de Marca: Logo grande + Descripción.
[UI] Botón "Follow Brand": (Añade a preferencias de usuario).
[UI] Brand Feed: Grid de productos filtrado solo por esa marca.
[CLICK Producto] ➔ Ir a Product Detail Page.
PANTALLA: EXPLORE FEED (/explore)
[UI] Masonry Grid (Estilo Pinterest): Outfits infinitos.
[CLICK Outfit Card] ➔ Ir a Outfit Detail Page.

3. PRODUCT & CONVERSION (El Dinero)
PANTALLA: PRODUCT DETAIL (/product/{id})
[UI] Visor de Imágenes:
[CLICK Toggle "Show QC"] ➔ Cambia la imagen principal por la foto real de almacén.
[CLICK Toggle "Original"] ➔ Muestra la foto de marketing.
[UI] Info Panel: Título, Precio, Marketplace de origen.
[UI] Botón "Save" (Corazón):
[CLICK] ➔ Guarda en la tabla product_user (Wardrobe). Cambia icono a relleno.
[UI] Botón "Add to Studio" (Percha):
[CLICK] ➔ Ir a The Studio (El producto se añade automáticamente al lienzo).
[UI] Botón "W2C / BUY" (Principal - Violeta):
[CLICK] ➔ SISTEMA: Verifica "Agent Preference" del usuario (en LocalStorage/DB).
Caso A: Usuario prefiere CNFans ➔ Abre pestaña nueva en CNFans con tu ID de afiliado.
Caso B: Sin preferencia ➔ Muestra pequeño Popover/Tooltip ahí mismo: "Choose Agent: [CNFans] [Mulebuy] [Hoobuy]". Al elegir, abre pestaña y guarda preferencia.

4. THE CREATOR (Studio & Outfits)
PANTALLA: OUTFIT DETAIL (/outfit/{id})
[UI] Imagen Principal: El outfit renderizado.
[UI] Botón "Remix This Fit":
[CLICK] ➔ Ir a The Studio (Clona todos los ítems de este fit al lienzo del usuario).
[UI] Botón "Share":
[CLICK] ➔ Copia URL al portapapeles / Abre Modal Redes Sociales.
[UI] "Shop the Look" List: Lista lateral con las prendas usadas.
[CLICK Botón "Buy" en prenda] ➔ Mismo comportamiento que el botón W2C (Abre agente externo).
PANTALLA: THE STUDIO (/studio) Requiere Login
(Si no está logueado ➔ Redirige a Login)
[UI] Sidebar Izquierda (Assets):
Tabs: [Search] [Wardrobe] [Uploads].
[DRAG & DROP] Arrancar prenda hacia el centro ➔ Añade imagen al Canvas.
[UI] Centro (Canvas):
[CLICK Item en Canvas] ➔ Selecciona el ítem.
[UI] Menú Contextual Flotante: Aparece al seleccionar.
[CLICK "Remove BG"] ➔ Llama API IA ➔ Recorta fondo.
[CLICK Capas Arriba/Abajo] ➔ Cambia z-index.
[CLICK Flip/Rotate] ➔ Transforma la imagen.
[UI] Botón "Publish Outfit":
[CLICK] ➔ Abre Modal de Publicación.
[INPUT] Título, Descripción, Tags.
[CLICK "Confirm"] ➔ Guarda en DB ➔ Redirige a Outfit Detail (del nuevo fit creado).

5. USER & SYSTEM
MODAL: AUTH (Login/Register)
[UI] Formulario: Email/Pass.
[CLICK "Login"] ➔ Cierra modal, actualiza UI (muestra avatar).
[CLICK "Register"] ➔ Crea cuenta ➔ Loguea ➔ Cierra modal.
PANTALLA: USER PROFILE (/u/{alias})
[UI] Info Usuario: Avatar, Bio, Botón "Edit Profile".
[UI] Tabs: [Fits Created] | [Wardrobe (Saved)].
[CLICK Fit Card] ➔ Ir a Outfit Detail.
[CLICK Product Card] ➔ Ir a Product Detail.
PANTALLA: SETTINGS (/settings)
[UI] Sección "Shopping Preferences":
[UI] Dropdown "Preferred Agent": Selecciona (CNFans, Hoobuy, etc.).
ℹ️ Explicación visual: Este ajuste controla a dónde te llevan los botones de compra.
[UI] Sección "Profile": Inputs para cambiar nombre, bio, avatar.
[CLICK "Save Changes"] ➔ Actualiza DB ➔ Toast de confirmación "Saved".

✅ ANOTACIONES PARA EL DESARROLLO
Estados Vacíos (Empty States):
Si entras a /studio y no tienes nada guardado en Wardrobe, mostrar botón "Go to Search".
Si buscas algo en /search y no hay resultados, mostrar "No results found" y sugerir marcas populares.
Persistencia:
El "Agent Preference" debe guardarse en Cookie/LocalStorage para usuarios no registrados, y en DB para registrados.
Loading States:
Al hacer "Remove BG" en el Studio, mostrar spinner sobre la imagen específica.



















📘 QCFIT: MASTER HOME SPEC (v4.0 - Product Centric)
Cambio Principal: Se añade la sección "Trending Warehouse Finds" como bloque central de contenido.
1. NAVBAR (Sticky Global)
[IZQ] Logo: "QCFit." (Space Grotesk Bold, Blanco + Punto Violeta).
[CEN] Menú: Explore | Brands | Studio.
[DER] Utility: Agent Selector (CNFans ▾) | Log in | Join Free (Btn Violeta).
2. HERO SECTION (Dark Mode - The Hook)
Fondo: Slate-950 con Glow Violeta central.
Texto:
H1: "The Ultimate Weidian & Taobao QC Finder".
H2: "Search millions of real photos & build outfits."
[CENTRO] SMART SEARCH BAR:
Input gigante con detección de links. Botón de búsqueda violeta integrado.
Trust Signals: 2 Tarjetas flotantes ("QC Verified") a los lados.
3. LIVE FEED TICKER (Social Proof)
Barra estrecha: "JUST CHECKED LIVE: [Foto Mini] Yeezy Slide [Foto Mini] Stussy Tee..." (Scroll infinito).

4. 🚨 SECCIÓN NUEVA: TRENDING WAREHOUSE FINDS (The Marketplace)
Aquí es donde atacamos tu requisito. El catálogo visual inmediato.
Fondo: Transición a Modo Claro (Stone-50) para que las fotos de producto resalten limpias.
Header de Sección:
Título (H3): "POPULAR RIGHT NOW" (Texto oscuro Slate-900).
[UI] QUICK FILTERS (Pills): Una fila de botones con forma de píldora.
[Active] All (Fondo Negro, Texto Blanco).
[Inactive] 👟 Shoes (Fondo Blanco, Borde Gris, Hover Violeta).
[Inactive] 👕 Tops (Hoodies, Tees).
[Inactive] 👖 Bottoms (Pants, Shorts).
[Inactive] 🧢 Accessories.
PRODUCT GRID (La Chicha):
Layout: Grid de 4 Columnas x 2 Filas (8 Productos destacados).
Card Design (Tarjeta de Producto):
Imagen: Foto cuadrada grande. Hover: Alterna entre Foto QC (Real) y Foto Marketing.
Info:
Título: "Nike Dunk Low 'Grey Fog'".
Precio: ¥ 180 (Negrita).
Badge: Icono pequeño del Marketplace (Weidian/Taobao).
Action (Hover): Botón flotante "+ Studio" (Para enviar directo al canvas).
Footer de Sección: Botón "View All Trending ->" (Texto simple).

5. BRAND SPOTLIGHT (Authority)
Título: "BROWSE BY BRAND".
Grid: Logos minimalistas (Nike, Arc'teryx, Balenciaga...).
6. REMIX TEASERS (Viral Loop)
Título: "TRENDING OUTFITS".
Grid: 3 Tarjetas de Outfits completos creados por usuarios.
CTA: Botón "⚡ Remix This Fit" sobre la imagen.
7. SEO KNOWLEDGE BLOCK (Ranking)
Fondo: Slate-900 (Footer Area).
Texto: 3 Columnas densas con keywords ("How to find QC", "Best Agents", "Outfit Builder").
8. FOOTER
Links legales y Copyright.

📘 QCFIT: SEARCH & DISCOVERY SPECIFICATION (High Fidelity)
Objetivo: Crear una experiencia de búsqueda tipo "E-commerce Pro" (como SSENSE o Farfetch) pero optimizada para fotos QC y contenido generado por usuarios (Outfits).

1. SISTEMA DE DISEÑO (Extensión para Búsqueda)
Mantenemos la base de la Home, pero adaptamos para densidad de información.
Background Strategy:
Canvas Principal: Stone-50 (#FAFAF9). Fondo claro para que las fotos de los productos (que pueden tener iluminaciones variadas) sean las protagonistas absolutas.
Sidebar (Filtros): White (#FFFFFF) con borde derecho Slate-200. Limpieza total.
Componentes UI Específicos:
Filtros (Checkbox): Custom style. Violet-600 al estar activo.
Tags/Pills: Slate-100 texto Slate-600. Hover: Slate-200.
Product Card: bg-white, borde slate-100, sombra hover shadow-lg shadow-violet-500/10.

2. PANTALLA A: SEARCH RESULTS (/search)
La vista principal cuando buscas "Nike" o "Hoodie".
A. SEARCH HEADER (Sticky)
Ubicación: Justo debajo del Navbar global.
Fondo: White con borde inferior Slate-200.
Contenido (Layout Flex):
[IZQ] Breadcrumbs: Home > Search > "Jordan 4" (Texto Inter gris pequeño).
[IZQ - Abajo] Título H1: "Results for 'Jordan 4'" (24px Space Grotesk Bold).
Contador: Pequeño badge gris "1,240 items".
[CENTRO] TABS DE CONTEXTO (Switch):
Un componente "Pill" segmentado:
[Activo] PRODUCTS (Fondo Violeta-600, Texto Blanco).
[Inactivo] OUTFITS (Fondo Transparente, Texto Gris).
[DER] Sort Dropdown: "Sort by: Relevance ▾" (Minimalista).
B. SIDEBAR DE FILTROS (Izquierda - Desktop)
Ancho: 280px fijo. Scroll independiente si es muy largo.
Estilo: Limpio, tipografía técnica (Inter 13px).
Secciones (Acordeones desplegables):
Marketplace: Checkboxes con Iconos (Weidian, Taobao, 1688).
Category: Árbol de categorías (Shoes > Sneakers > High Top).
Price Range: Slider dual (Min - Max) + Inputs numéricos manuales.
Brands: Input de búsqueda mini ("Search brand...") + Lista de checkboxes scrollable.
QC Availability: Toggle Switch "Has QC Photos Only" (Activado por defecto).
C. THE GRID (Resultados)
Layout: Grid responsivo (4 columnas en Desktop ancho, 3 en Laptop).
Gap: 24px (Espaciado generoso).
COMPONENTE: PRODUCT CARD (La Célula)
Contenedor: Rounded-xl, overflow-hidden, borde sutil.
Imagen (Ratio 1:1):
Default: Muestra la foto QC (Fondo de almacén verde/gris).
Hover: Transición suave a la foto "Original" (Marketing).
Badges: Etiqueta pequeña en esquina superior izq: Logo del Agente/Marketplace.
Info (Abajo):
Marca: "NIKE" (Texto mini, uppercase, gris).
Título: "Air Jordan 4 Retro 'Black Cat'" (Truncado a 1 línea).
Precio: ¥ 280 (Texto grande, Space Grotesk).
Acciones Rápidas (Hover):
Aparece botón flotante circular "+" (Violeta) en la esquina inferior derecha de la foto ➔ Tooltip: "Add to Studio".

3. PANTALLA B: BRAND INDEX (/brands)
El directorio A-Z para SEO y exploración.
A. HERO DE MARCAS
Fondo: Slate-900 (Dark Mode momentáneo para impacto).
Texto: "Brand Directory".
Search Input: Barra larga centrada "Find a brand...".
B. ALPHABET STICKY BAR
Ubicación: Debajo del Hero. Sticky.
Diseño: Barra horizontal con letras A - Z.
Interacción: Al hacer clic en "N", scroll suave hasta la sección Nike/New Balance.
C. BRAND GRID
Layout: Grid denso de tarjetas pequeñas (Logos).
Card: Cuadrado blanco simple con el logo centrado en negro.
Hover: Borde violeta + Ligera elevación.

4. PANTALLA C: BRAND PAGE (/brand/nike)
La "Home" de cada marca dentro de QCFit.
A. BRAND HEADER
Estilo: Banner visual ancho completo (Gris oscuro o imagen de campaña de la marca con superposición oscura).
Contenido:
Logo: Grande, circular, blanco, superpuesto al banner.
Info: Título "NIKE" (H1 Gigante) + "15k Products indexed".
Botón CTA: "Follow Brand" (Borde blanco, icono campana). Feedback: Al hacer clic cambia a "Following" (Relleno blanco).
B. BRAND FEED (El contenido)
Estructura: Similar a Search Results, pero sin el filtro de "Marca" en el sidebar (obvio).
Filtros específicos: "Collabs" (Ej: x Travis Scott, x Off-White) aparecen destacados como pills al inicio.

5. ADAPTACIÓN MOBILE (Responsive)
A. SEARCH MOBILE
Header Compacto:
Input de búsqueda arriba.
Fila inferior: Botón "FILTERS (3)" (Outline) + Scroll horizontal de Categorías.
Modal de Filtros:
Al tocar "Filters", se abre un Drawer (Panel deslizante) desde abajo con todas las opciones del Sidebar de desktop. Botón flotante grande "APPLY FILTERS".
Grid Móvil: 2 Columnas (Estilo Instagram Shop).
Info reducida: Solo Foto + Precio + Título corto.
B. BRAND MOBILE
Alphabet: Se convierte en un Dropdown o scroll horizontal para no ocupar pantalla.

6. GUÍA DE INTERACCIÓN (Micro-Animations)
QC Toggle (Card Hover):
La transición entre la foto QC y la Original no debe ser brusca. Usar opacity-0 a opacity-100 con duration-300 ease-in-out.
Filter Selection:
Al clicar un filtro (ej: "Sneakers"), el Grid de productos se vuelve semitransparente (opacity-50) mostrando un Spinner violeta central durante 0.5s (simulación de carga AJAX) y luego aparece nítido con los nuevos resultados.
Tabs (Products vs Outfits):
Animación deslizante del fondo ("Sliding Pill") cuando cambias de Product a Outfit.

Nota para el desarrollador: Esta especificación busca el equilibrio entre densidad de datos (necesaria para comparar productos) y limpieza visual (necesaria para la estética). El uso del espacio en blanco (Stone-50) es la clave.



📘 QCFIT: PRODUCT DETAIL SPECIFICATION (High Fidelity)
Objetivo: Maximizar la conversión (clic en afiliado) y la retención (uso en Canvas). La interfaz debe resolver la duda principal: "¿La calidad real coincide con la foto de marketing?".

1. SISTEMA DE DISEÑO (Product Centric)
🎨 Paleta de Colores
Background: Stone-50 (#FAFAF9). Blanco roto, estilo museo.
Panel Surface: White (#FFFFFF) con borde Slate-200.
Primary Action (Buy): Violet-600 (#7C3AED) ➔ Gradiente sutil al Violet-500 en Hover.
Secondary Action (Studio): Slate-900 (#0F172A). Negro sólido, estilo moda.
Trust Badge: Emerald-500 (#10B981) para "QC Verified".
Marketplace Badges:
Weidian: Rojo Alizarina (#E11D48).
Taobao: Naranja (#F97316).
1688: Naranja/Amarillo (#F59E0B).
✒️ Tipografía
Price Tag: Space Grotesk Bold (Tamaño 32px).
Product Title: Inter SemiBold (Tamaño 24px, Tracking apretado).
Labels/Specs: JetBrains Mono (Tamaño 12px) ➔ Para datos técnicos como Peso (g) y Dimensiones.

2. PANTALLA: PRODUCT DETAIL PAGE (Desktop View - 1440px)
A. GLOBAL HEADER (Sticky)
Mantiene el Navbar Global con fondo Glassmorphism (Light Mode: bg-white/80 backdrop-blur-md).
B. LAYOUT PRINCIPAL (2 Columnas)
Distribución:
Columna Izquierda (Visual): 60% del ancho. Galería inmersiva.
Columna Derecha (Control): 40% del ancho. Panel de compra "Sticky" (se queda fijo al hacer scroll).
C. COLUMNA IZQUIERDA: THE DUAL GALLERY
Contenedor: Amplio, fondo blanco, esquinas rounded-2xl.
Visor Principal (Viewport):
Imagen de gran formato (Ratio 4:3).
[CRÍTICO] THE QC TOGGLE SWITCH:
Ubicación: Flotando en la parte superior central de la imagen.
Diseño: Cápsula (Pill) con fondo Slate-900/90 y texto blanco.
Segmentos:
[Activo] 📸 QC PHOTOS: Fondo Gris Oscuro. Muestra fotos reales del almacén (fondo verde/reglas).
[Inactivo] ✨ ORIGINAL: Fondo Transparente. Muestra la foto pulida del vendedor.
Thumbnails Strip (Tira de Miniaturas):
Debajo de la imagen principal.
Lista horizontal de 5-6 fotos. La seleccionada tiene un borde Violet-600 de 2px.
D. COLUMNA DERECHA: THE CONVERSION PANEL
Padding: 40px.
1. Header Info:
Breadcrumbs: Home / Shoes / Jordan 4.
Brand Link: "NIKE" (Texto pequeño, negrita, subrayado al hover).
Título: "Air Jordan 4 Retro 'Military Black'".
2. Price Block:
Precio Principal: ¥ 260 (Grande, Space Grotesk).
Conversión: Aprox. $36.50 USD (Gris claro, texto pequeño).
Badge Origen: Icono pequeño "Weidian" + ID del producto.
3. Specs Grid (Datos Técnicos):
Grid de 2x2 datos con iconos lineales finos:
⚖️ Avg. Weight: 1250g
📦 Volume: 35x25x15cm
🏭 Seller: WTG
✅ QC Check: Passed
4. ACTION CLUSTER (Botones):
Espaciado generoso (Gap 16px).
Botón A: "W2C / BUY VIA AGENT" (Primary):
Estilo: Ancho total, Alto 56px, rounded-xl. Fondo Violet-600. Sombra shadow-lg shadow-violet-500/30.
Contenido: Texto "Buy via CNFans" (Si el usuario tiene preferencia) + Icono de salida externo ↗.
Micro-copy: Debajo del botón, texto mini: "Links to partner agent. Affiliate supported."
Botón B: "ADD TO STUDIO" (Secondary):
Estilo: Ancho total, Alto 48px, rounded-xl. Borde negro 2px (border-slate-900), texto negro, fondo transparente. Hover: Fondo negro, texto blanco.
Icono: Percha o símbolo "+".
Botón C: "SAVE" (Tertiary):
Botón cuadrado pequeño al lado. Icono Corazón.
5. Agent Preference Widget (Si no hay preferencia):
Pequeño recuadro informativo con fondo amarillo pálido (amber-50) borde amber-200.
Texto: "Select your preferred agent for auto-links:"
Pills clicables: [Pandabuy] [CNFans] [Mulebuy] [Hoobuy].
E. CROSS-SELL SECTION (Debajo del Fold)
Título: "SEEN IN THESE OUTFITS".
Grid: Fila horizontal de 4 "Outfit Cards". Muestra cómo otros usuarios han combinado ESTA zapatilla específica.
Objetivo: Inspiración -> Clic -> Studio.

3. PANTALLA: MOBILE VIEW (390px - Responsive)
Adaptación "E-commerce App".
A. MOBILE GALLERY (Carrusel)
Ocupa el 100% del ancho superior.
Indicadores de puntos (Dots) abajo.
El QC Toggle flota en la esquina inferior derecha de la imagen (más accesible al pulgar).
B. BOTTOM ACTION BAR (Sticky Footer)
En móvil, los botones de compra NO están en el flujo de texto, sino fijos abajo (fixed bottom-0).
Layout:
Botón Izq (Pequeño): Icono "Studio" (Fondo negro).
Botón Der (Grande): "Buy for ¥260" (Fondo Violeta).
Efecto: Fondo blanco con sombra superior (shadow-top) para separarlo del contenido.

4. GUÍA DE ESTILO VISUAL & FEEDBACK
Hover en "W2C Button":
El botón se expande ligeramente (scale-105).
Si es "Smart Link" (detecta agente), aparece un Tooltip rápido: "Redirecting to CNFans...".
Toggle Switch Animation:
Al cambiar de "Original" a "QC", la imagen no cambia de golpe. Hace un efecto "Cross-fade" rápido (0.3s) para que se note que es el mismo producto, solo distinta realidad.
Skeleton Loading:
Mientras cargan las imágenes pesadas de QC, mostrar un esqueleto pulsante gris claro con el icono de "Imagen" en el centro.

Nota Técnica para la Imagen: Esta especificación describe una interfaz limpia, profesional y orientada a la conversión, eliminando el "ruido" visual típico de las webs de reps chinas y elevándolo al estándar de una tienda de moda occidental.


📘 QCFIT: STUDIO & OUTFIT SPECIFICATION (High Fidelity)
Objetivo: Crear un entorno de trabajo fluido ("Flow State") donde la fricción técnica desaparece. El usuario debe sentir que está jugando a vestir muñecas, pero con ropa real de gama alta.

1. SISTEMA DE DISEÑO (Editor Mode)
El Studio tiene su propio "Micro-Universo" visual para maximizar el espacio de trabajo.
🎨 Paleta de Interfaz (Dark UI Chrome)
Workspace Shell (Paneles): Slate-950 (#020617). Interfaz oscura para reducir fatiga visual y destacar el lienzo.
Canvas Background: Checkered Pattern (Cuadrícula gris muy suave sobre blanco) o Stone-100. Simula un fondo transparente de Photoshop.
Active Tool: Violet-600 (#7C3AED).
Selection Bound: Borde azul cian (Cyan-400) con manejadores de esquina circulares blancos.
✒️ Tipografía
UI Labels: Inter (Tamaño 11px y 12px). Muy pequeña para ahorrar espacio.
Títulos de Herramientas: Space Grotesk Medium.

2. PANTALLA A: THE STUDIO (/studio)
La mesa de trabajo. Desktop View (1440px).
A. STUDIO HEADER (Toolbar Superior)
Altura: 56px. Fondo Slate-900 borde inferior Slate-800.
[IZQ] Navegación:
Icono "Home" (Casa) + Separador.
Input de Título: Texto editable "Untitled Fit #24" (Inter, Blanco).
[CEN] Herramientas de Canvas:
Iconos simples: ↩️ Undo | ↪️ Redo | 🔍 Zoom In/Out | 🗑️ Clear All.
[DER] Acciones Finales:
Botón "Save Draft" (Texto Gris).
Botón "PUBLISH" (Primary): Pill Violet-600 con brillo.
B. PANEL IZQUIERDO: ASSETS LIBRARY (El Armario)
Ancho: 300px fijo. Fondo Slate-950. Borde derecho Slate-800.
1. Tabs Superiores (Segmented Control):
[🔍 SEARCH] | [❤️ WARDROBE] | [☁️ UPLOADS]
Estado Activo: Fondo gris oscuro, texto blanco.
2. Buscador Interno:
Input pequeño: "Find items..." (Búsqueda rápida sin salir del editor).
3. Grid de Activos (Scrollable):
Grid de 2 columnas de miniaturas.
Comportamiento:
Los ítems tienen fondo transparente (si ya están procesados) o fondo sólido.
Interacción: El cursor cambia a "Mano" (Grab) al pasar por encima.
4. Drop Zone (Upload):
Área inferior fija: "Drop images here to upload".
C. CENTER STAGE: THE CANVAS (El Lienzo)
Espacio: Ocupa todo el resto de la pantalla.
Visual: Fondo Stone-50 con una cuadrícula de puntos (dot-pattern) muy sutil color gris para ayudar a alinear.
Elementos en el Lienzo (Composición Ejemplo):
Una camiseta Stussy (Centro).
Unos pantalones Cargo (Abajo).
Unas Jordan 4 (Pies).
[UI] ELEMENTO SELECCIONADO (Active State):
La camiseta Stussy tiene un Bounding Box (Marco azul fino).
Manejadores: 4 Puntos en las esquinas (Escalar) + 1 Palito arriba (Rotar).
[UI] CONTEXT MENU FLOTANTE (La Magia):
Aparece justo encima del elemento seleccionado. Cápsula negra rounded-full.
Botones:
[🪄 Remove BG]: Icono varita mágica + Texto degradado (Indica IA).
[Bring Front]: Icono Capas Arriba.
[Send Back]: Icono Capas Abajo.
[Flip]: Icono espejo.
[Trash]: Icono papelera rojo.
D. PANEL DERECHO: PROPERTIES & LAYERS (Opcional/Colapsado)
Para mantener la limpieza, mostramos solo una barra flotante vertical o un panel muy fino a la derecha con el orden de las capas.

3. PANTALLA B: OUTFIT DETAIL PAGE (/outfit/{id})
El resultado público. La página que se comparte en redes.
A. LAYOUT (Split Screen)
Fondo General: Stone-50 (Light Mode).
B. COLUMNA IZQUIERDA: THE MASTERPIECE
Contenido: La imagen final generada por el Studio, renderizada en alta calidad sobre fondo liso o con un color de fondo artístico elegido por el usuario.
Overlay (Hover):
Al pasar el ratón por encima de la imagen, aparecen Tags (Puntos) sobre cada prenda.
Tooltip: "Nike Dunk Low - ¥280".
C. COLUMNA DERECHA: SHOP & REMIX
Padding: 40px. Estilo limpio editorial.
1. Header:
Título: "Summer Techwear Vibes".
Autor: Avatar pequeño + "Created by @AlexReps".
Fecha: "2 hours ago".
2. Action Bar (Viral Loop):
Botón Gigante: "⚡ REMIX THIS FIT" (Violet-600, ancho total).
Subtexto: "Open in Studio to edit items".
Social: Iconos Share (Link, Twitter, Reddit).
3. SHOP THE LOOK (La Lista de Compra - Monetización):
Título: "ITEMS IN THIS FIT".
Lista Vertical (Scrollable):
Item Row:
[Foto Miniatura]
[Texto] "Stussy 8-Ball Fleece"
[Precio] ¥ 320
[Botón]: "BUY" (Negro, pequeño, a la derecha). Lleva al Agente Preferido.
4. Comentarios/Tags:
Sección inferior con hashtags: #Streetwear #Budget #Green.

4. ADAPTACIÓN MOBILE (Studio Lite)
A. CANVAS MÓVIL
Ocupa el 100% de la pantalla.
Barra Inferior: Botón grande central "+" que abre un Bottom Sheet (Panel deslizante) con el armario/búsqueda.
Gestos: Pellizcar para zoom, rotar con dos dedos.

5. GUÍA DE INTERACCIONES (Studio FX)
Remove BG Magic:
Al pulsar "Remove BG", el ítem muestra un efecto de "escaneo" (línea de luz bajando).
Al finalizar (1-2s), el fondo blanco desaparece y el ítem hace un pequeño "pop" (escala up/down).
Drag & Drop:
Al arrastrar una prenda del panel izquierdo al canvas, la miniatura se vuelve semitransparente y sigue al cursor. Al soltar, cae con un efecto de gravedad suave.
Snapping:
Guías inteligentes (líneas magentas) aparecen cuando el centro de una prenda se alinea con otra.

Nota Técnica para la Imagen: Generaremos una vista compuesta que muestre principalmente la interfaz del STUDIO (Desktop), ya que es la más compleja visualmente, pero quizás insinuando el resultado final al lado. La imagen debe transmitir "Potencia Creativa".


¡Entendido! Aquí Aura, Lead Designer. 👩‍💻🔐
Cerramos el ciclo de diseño con el Sistema de Usuario. Aunque parezca "administrativo", estas pantallas son vitales:
El Login (Auth): Es la puerta de entrada. Si es feo o lento, perdemos usuarios.
El Perfil: Es el ego del usuario. Debe verse tan bien como un perfil de Instagram para que quieran compartirlo.
Los Ajustes: Es el cerebro de la monetización (donde el usuario elige su Agente).
Aquí tienes la Especificación Técnica Visual (Design Spec v1.0) para el ecosistema User & System.

📘 QCFIT: USER & SYSTEM SPECIFICATION (High Fidelity)
Objetivo: Gestión de identidad sin fricción. Estética limpia, confianza y claridad en la configuración de preferencias de compra.

1. SISTEMA DE DISEÑO (User Interface)
🎨 Paleta de Colores
Background: Stone-50 (#FAFAF9). Limpieza clínica.
Card Surface: White (#FFFFFF) con borde Slate-200.
Text Primary: Slate-900 (#0F172A).
Text Secondary: Slate-500 (#64748B).
Avatar Placeholder: Gradiente lineal Violet-400 a Fuchsia-400.
Active Tab: Black (Subrayado).
✒️ Tipografía
Nombres de Usuario/Títulos: Space Grotesk Bold.
Etiquetas de Formulario: Inter Medium (Tamaño 13px).
Stats (Números): Space Grotesk (Tamaño 24px).

2. COMPONENTE A: AUTH MODAL (Login/Register)
Este modal se superpone a cualquier pantalla (Home, Studio, Product).
A. CONTENEDOR (Overlay)
Backdrop: bg-slate-900/60 (Oscuro translúcido) con backdrop-blur-sm.
Modal Card: Centrado. Ancho 420px. bg-white, rounded-2xl, shadow-2xl.
Header:
Logo "QCFit." centrado arriba.
Botón "X" (Cerrar) discreto en la esquina superior derecha.
B. CONTENIDO INTERIOR
Tabs Superiores:
[Login] (Texto Negro, Borde inferior Negro).
[Register] (Texto Gris, Sin borde).
Formulario:
Input 1: "Email Address" (Estilo: Borde gris claro, Focus Violeta).
Input 2: "Password" (Icono de "ojo" a la derecha para revelar).
Link: "Forgot password?" (Texto mini, alineado derecha, color Violeta).
Botón Principal (CTA):
Botón ancho total. "Sign In". Fondo Slate-900 (Negro). Texto Blanco. Hover: Slate-800.
Divider: Línea gris con texto "OR" en el centro.
Social Login (Opcional):
Botón "Continue with Google" (Borde gris, Logo G color).

3. PANTALLA B: USER PROFILE (/u/{alias})
La carta de presentación del creador.
A. PROFILE HEADER (Hero)
Padding: 60px arriba/abajo. Fondo Stone-50.
Layout: Centrado verticalmente.
Avatar: Circular grande (120px). Borde blanco grueso.
Si es mi perfil: Icono pequeño de "Cámara" superpuesto (Editar).
Info:
Nombre: "AlexStreetwear" (H1, Space Grotesk Bold).
Bio: "Just sharing my budget finds. 🇪🇸" (Inter, Gris).
Stats Row (Fila de métricas):
12 Fits Created
45 Items in Wardrobe
1.2k Profile Views
Action Button: Botón "Edit Profile" (Outline Negro) o "Share Profile" (Icono).
B. PROFILE CONTENT (Tabs)
Sticky Bar: Barra de pestañas que se pega al hacer scroll.
[CREATED FITS] (Activo).
[WARDROBE] (Inactivo).
Grid Content (Created):
Masonry Grid de Outfits (igual que en Explore).
Grid Content (Wardrobe):
Grid de Productos guardados (igual que en Search).
Diferencia visual: Cada producto tiene un botón pequeño "Remove" (X) en la esquina.

4. PANTALLA C: SETTINGS (/settings)
El panel de control. Crítico para el "Agent Preference".
A. LAYOUT (2 Columnas - Desktop)
Contenedor: Máximo 1000px ancho, centrado.
Columna Izq (Nav): 250px. Lista vertical de enlaces.
General (Activo - Barra lateral violeta).
Shopping Preferences (Icono carrito).
Account & Security.
Columna Der (Panel): Tarjeta blanca rounded-xl, borde Slate-200. Padding 40px.
B. PANEL DE CONTENIDO: SHOPPING PREFERENCES (El Core)
Título: "Affiliate & Agent Settings".
Subtítulo: "Choose where you want to buy items. We will auto-convert links for you."
COMPONENT: AGENT SELECTOR CARD
Un Input Select grande y estilizado.
Label: "Preferred Agent Service".
Dropdown Visual: Muestra el Logo del agente + Nombre.
[Logo] CNFans (Recomendado).
[Logo] Mulebuy.
[Logo] Hoobuy.
[Logo] CSSBuy.
Nota informativa: Recuadro azul claro con icono (i): "Links generated on QCFit will automatically redirect to this service."
C. PANEL DE CONTENIDO: GENERAL (Profile Edit)
Inputs:
Display Name: Input estándar.
Bio: Textarea.
Avatar Upload: Zona de arrastrar imagen.
Botón Guardar: "Save Changes" (Violeta, esquina inferior derecha).

5. ADAPTACIÓN MOBILE (Responsive)
A. AUTH MOBILE
El Modal ocupa el 100% de la pantalla (Full Screen) en móviles, deslizándose desde abajo (Sheet Modal).
B. PROFILE MOBILE
Avatar: Más pequeño (80px).
Stats: Se mueven debajo del nombre en una fila horizontal con separadores.
Tabs: Ocupan el 50% del ancho cada una.
C. SETTINGS MOBILE
No hay 2 columnas. Es una lista vertical de opciones con iconos de flecha >.
Al tocar "Shopping Preferences", navega a una sub-pantalla.

6. GUÍA DE INTERACCIONES
Tab Switching (Profile):
Animación deslizante suave ("Slide") de la línea negra debajo de las pestañas al cambiar de "Fits" a "Wardrobe".
Agent Selection (Settings):
Al seleccionar un agente, aparece un Toast Notification (mensaje flotante verde) en la esquina superior derecha: "Preference saved: CNFans selected."
Avatar Hover:
En el perfil propio, al pasar el ratón sobre el avatar, aparece un overlay oscuro con el texto "Change Photo".

Nota Técnica para la Imagen: La imagen debe mostrar una composición limpia. Sugiero mostrar el Perfil de Usuario como elemento central, con el Modal de Auth flotando sutilmente a un lado (o semi-transparente) y una vista del panel de Settings asomando, para entender que son parte del mismo sistema de gestión. Estilo minimalista, mucho espacio en blanco (Stone-50).


📘 QCFIT: ADMIN DASHBOARD SPECIFICATION (High Fidelity)
Objetivo: Control total. Monitorización en tiempo real de ingresos (clics de afiliados), salud del sistema (scrapers) y gestión de catálogo.

1. SISTEMA DE DISEÑO (Admin Variant)
Una versión más técnica y "oscura" del sistema QCFit, optimizada para largas sesiones de trabajo.
🎨 Paleta de Colores (Data-Dense Theme)
Sidebar Background: Slate-900 (#0F172A). Oscuro profundo para separar la navegación del contenido.
Main Canvas: Slate-50 (#F8FAFC). Fondo muy claro para máximo contraste con las tablas de datos.
Cards/Panels: White (#FFFFFF) con sombra suave shadow-sm.
Data Colors:
🟣 Violet: Ingresos/Clics.
🟢 Emerald: Estado "Healthy" / Éxito.
🟠 Amber: Advertencias / Pendiente.
🔴 Rose: Errores Críticos / Buns.
✒️ Tipografía
Headings: Inter Tight (Más compacto que Space Grotesk, más serio).
Data & Logs: JetBrains Mono (Vital para leer IDs, Logs de errores y JSON).
Body: Inter.

2. ESTRUCTURA GLOBAL (Shell)
A. SIDEBAR NAVEGACIÓN (Izquierda - Fijo 260px)
Header: Logo "QCFit Admin" (Blanco, "Admin" en Badge violeta pequeño).
Menú Principal (Lista Vertical):
Items con iconos lineales (Stroke 1.5px):
📊 Dashboard (Activo - Fondo White/10, Texto Blanco).
📦 Products (Catálogo Indexado).
🤖 Scrapers (Gestión de Bots/Queue).
👥 Users (Gestión de cuentas).
🎨 Featured (Control de Home).
💸 Affiliates (Stats de Agentes).
Footer Sidebar:
Estado del Servidor: 🟢 "System Operational" (Texto mini).
Versión: "v1.0.4-beta".
B. TOP BAR (Header Global)
Ubicación: Arriba del Canvas. Fondo Blanco. Borde inferior.
[IZQ] Breadcrumbs: Dashboard / Overview.
[CEN] Global Search: Input "Search by UUID, Product Name or User..." (Atajo Cmd+K).
[DER] Acciones:
Botón "Flush Cache" (Icono Rayo).
Notificaciones (Campana con punto rojo).
Avatar Admin (Pequeño).

3. PANTALLA PRINCIPAL: DASHBOARD OVERVIEW
La vista por defecto al entrar.
A. KPI CARDS (Fila Superior)
Layout: Grid de 4 tarjetas.
Card 1: Affiliate Outbound (El Dinero)
Título: "Total Clicks (24h)".
Dato: 12,450 ↗️ 12% (Verde).
Visual: Pequeño sparkline (gráfico de línea) violeta de fondo.
Card 2: Scraper Queue (La Salud)
Título: "Pending Jobs".
Dato: 45 (Amber) / Processed: 1.2k.
Visual: Barra de progreso (Loading).
Card 3: Indexed Products (El Activo)
Título: "Total Catalog".
Dato: 854,021.
Subtexto: "+320 added today".
Card 4: Error Rate (La Alerta)
Título: "Failed Scrapes".
Dato: 0.4% (Verde - Bajo control).
Visual: Icono de servidor.
B. MAIN CHART SECTION (Tráfico)
Contenedor: Tarjeta grande blanca.
Header: "Traffic vs. Affiliate Conversions" | Select: [Last 7 Days] ▾.
Gráfico:
Eje X: Días.
Eje Y: Volumen.
Línea 1 (Azul): Page Views.
Línea 2 (Violeta Relleno): Clicks a Agentes (W2C).
Insight Visual: Ver claramente la correlación entre visitas y dinero.
C. SPLIT VIEW (Mitad Inferior)
COLUMNA IZQ: LIVE SCRAPER CONSOLE (Terminal)
Título: "Live Ingestion Log".
Estilo: Fondo Negro (Slate-950). Fuente JetBrains Mono 11px.
Contenido (Scrolling Text):
[10:42:01] INFO: Weidian_Worker_01 scraping Item #49281... (Blanco)
[10:42:03] SUCCESS: Images found (5). QC Photos found (3). (Verde)
[10:42:05] ERROR: Taobao Link timeout. Retrying... (Rojo)
[10:42:06] DB: Saved Product ID 99281 to 'products'. (Azul)
COLUMNA DER: TOP TRENDING PRODUCTS (Gestión Rápida)
Título: "Most Viewed Today".
Tabla Simplificada:
1. [Img] Nike Dunk Low | 👁️ 1.2k Views | 🔗 400 Clicks.
2. [Img] Stussy 8-Ball | 👁️ 900 Views | 🔗 250 Clicks.
3. [Img] Yeezy Slide | 👁️ 850 Views | 🔗 210 Clicks.
Acción: Botón "Manage Featured" al pie.

4. SECCIÓN SECUNDARIA: PRODUCTS TABLE (Vista Previa)
Aunque el prompt pide la Home del Admin, insinuaremos cómo se ve la lista de productos.
Tabla de Datos (DataGrid):
Cabeceras: [Image] [Name] [Origin] [Price] [QC Status] [Actions].
Fila Ejemplo:
[Miniatura]
"Jordan 4 Black Cat"
Badge Rojo "Weidian"
¥ 280
Badge Verde "Has QC"
[✏️ Edit] [🗑️ Delete].

5. ADAPTACIÓN RESPONSIVE (Admin on the Go)
Porque el servidor siempre se cae cuando estás cenando fuera.
Sidebar: Se convierte en menú hamburguesa.
KPI Cards: Se apilan en vertical (1 columna).
Console: Se oculta por defecto (Botón "Show Logs").
Chart: Versión simplificada (solo línea de tendencia).

6. GUÍA DE ESTILO VISUAL (Admin Specifics)
Status Dots:
Usar puntos de color pulsantes (animate-pulse) al lado de "Scraper Status" para indicar que está trabajando en tiempo real.
Mono Font Usage:
Cualquier ID (UUID), Precio o Fecha debe ir en JetBrains Mono para alineación tabular perfecta.
Density:
Reducir el padding de las celdas de tabla (py-2) comparado con la web pública. Aquí queremos ver muchos datos sin hacer scroll.

Nota Técnica para la Imagen: La imagen debe transmitir Control. No es una web bonita para comprar, es un panel de instrumentos. El contraste entre el fondo oscuro del Sidebar y el blanco del Dashboard es clave. La terminal negra con letras de colores le da el toque "Tech/Developer" necesario.


