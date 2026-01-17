# QCFit - Planificación de Próxima Sesión

**Estado Actual:**
- ✅ Fase 4 (Studio) COMPLETADA.
- ✅ Fase 5 (Social) COMPLETADA (Perfiles, Follow).
- ✅ Fase 6 (Monetización) COMPLETADA:
    - Generación de links de afiliados robusta.
    - Botones de compra integrados en todas las vistas clave.
    - Soporte multi-agente (CNFans, Mulebuy, etc.).

---

## 🎯 OBJETIVO PRINCIPAL: Refinamiento de UX/UI y Social++ (Fase 7)

Para que el proyecto parezca un SaaS real y premium, necesitamos mejorar la experiencia de usuario y el "engagement".

### 1. Feed de Actividad (Social++)
- [ ] Implementar feed en Home para usuarios logueados: "Outfits de gente que sigues".
- [ ] Endpoint backend `getFollowedUsersOutfits()`.

### 2. Feedback de Usuario (Toasts)
- [ ] Reemplazar `alert()` y `console.error` por un sistema de notificaciones Toast (ej: PrimeVue Toast).
- [ ] Feedback visual al guardar outfit, login error, etc.

## Objetivos para la próxima sesión (Design Polish)
1. **Studio Polish (Priority High):**
   - **Floating Toolbar:** Replace the top static styling with a contextual floating menu near selected items (Remove BG, Flip, Layer Up/Down).
   - **Right Sidebar (Layers):** Create `CanvasLayersPanel.vue` (drag-to-reorder, visibility toggle).
   - **Undo/Redo:** Implement history stack in Pinia store.
2. **Home Redesign (Hero):**
   - **Dark Background:** Update `Home.vue` background to `bg-slate-950`.
   - **3D Cards:** Implement the floating "Nike Dunk/Jordan" cards effect (CSS 3D transforms).
   - **Search Bar:** Round pill shape with internal purple button (`rounded-full`).
3. **Profile Refinement:**
   - **Auth Modal:** Replace standard modal with Glassmorphism (blur) dark modal.

## Notas Técnicas para el Agente
- **Estado Actual:** El `Studio` ya tiene la base Dark Mode (`bg-slate-950`) y el header actualizado.
- **Limitación Browser:** `browser_subagent` devuelve error 429. **CONFÍA EN LA VERIFICACIÓN DE CÓDIGO (Static Analysis)** y pide al usuario confirmación visual si es crítico.
- **Fuente de Verdad:** `DESIGN_ALIGNMENT_PLAN.md` contiene la lista completa de tareas de diseño.
- **Git:** Usa ramas por feature (`feat/studio-layers`, `feat/home-redesign`). Mensajes en Español.
