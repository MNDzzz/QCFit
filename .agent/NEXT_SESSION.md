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

### 3. Polish Visual
- [ ] Skeleton Loaders en lugar de spinners simples donde sea posible.
- [ ] Revisión de responsive en móviles (Sidebar menu, Canvas controls).

---

## 📝 Comandos Críticos
- `npm run build`: Ejecutar siempre tras cambios en Stores o Config.
- `git checkout develop`: Rama base para nuevos features.
