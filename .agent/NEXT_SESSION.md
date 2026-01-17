# QCFit - Planificación de Próxima Sesión

**Última Actualización:** 2026-01-17 19:30
**Rama Actual:** `develop`
**Estado:** ✅ Layers Panel implementado y mergeado

---

## ✅ Completado en esta sesión:

### Studio Design Overhaul - Layers Panel
- ✅ Nuevo componente `CanvasLayersPanel.vue`
- ✅ Panel lateral derecho con lista de capas ordenadas por z-index
- ✅ Drag & drop para reordenar capas
- ✅ Thumbnails, botones de acción, sincronización con store
- ✅ Build exitoso y verificación en navegador

---

## 🎯 PRÓXIMAS TAREAS (Design Alignment)

### 1. Floating Toolbar Contextual (Prioridad Alta)
Según la Imagen 4 del diseño, la toolbar actual debe convertirse en un menú flotante que aparece cerca del item seleccionado.

**Elementos:**
- Remove BG (con ícono de varita mágica)
- Bring Front
- Send Back
- Flip
- Trash (en rojo)

**Implementación sugerida:**
- Crear componente `CanvasFloatingToolbar.vue`
- Posicionar relativo al `selectedItem` usando sus coordenadas x,y
- Usar transiciones CSS para aparecer/desaparecer

### 2. Tab "UPLOADS" en Sidebar
Añadir tercera pestaña para subir imágenes propias.
- Zona de drag & drop
- Preview de imágenes subidas
- Conexión con endpoint (mock o real)

### 3. Undo/Redo
- Implementar historial de acciones en `canvasStore`
- Máximo 10-20 estados
- Botones ya están en el header (solo necesitan lógica)

---

## 📋 Verificaciones Pendientes

Las siguientes páginas fueron verificadas como funcionales:
- ✅ Home
- ✅ Search
- ✅ Product Detail
- ✅ Studio (con Layers Panel)
- ✅ Profile

---

## 💡 Notas Técnicas

- **Build:** Usar `cmd /c "npm run build"` para evitar problemas con PowerShell
- **Server:** `C:\xampp\php\php.exe artisan serve`
- **MySQL:** Funcionando en puerto 3306 via XAMPP
- **Git Flow:** Crear rama por feature, verificar en browser, merge a develop

---

## 🔗 Referencias de Diseño

Las 5 imágenes de diseño están en:
- **Imagen 0:** User Profile + Auth Modal
- **Imagen 1:** Home Page (Hero, Live Feed, Products)
- **Imagen 2:** Product Detail
- **Imagen 3:** Search Results
- **Imagen 4:** Studio (Floating Toolbar, Layers Panel)
