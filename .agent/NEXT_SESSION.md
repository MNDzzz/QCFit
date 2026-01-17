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

## Objetivos para la próxima sesión
1. **Verificación UX/UI:** Comprobar visualmente los Toasts y Skeletons en el navegador.
2. **Animaciones:** Añadir animaciones de entrada (`animate-fade-in-up`) en listas de productos.
3. **Limpieza y Optimización:** Revisar código muerto y optimizar imports.

## Notas Técnicas
- PrimeVue Toast ya está configurado globalmente.
- Revisar si hay más `alert()` olvidados.
- La rama actual es `feat/ux-refinement`. features.
