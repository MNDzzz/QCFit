# QCFit - Planificación de Próxima Sesión

**Estado Actual:**
- ✅ Fase 4 (Studio) COMPLETADA.
- ✅ Fase 5 (Social) COMPLETADA:
    - Perfiles Públicos (`/u/:id`) funcionales con grid de outfits.
    - Sistema Follow/Unfollow operativo (Backend y Frontend).
- ✅ Testing E2E: Validado guardado de outfits y funcionalidad social básica.

---

## 🎯 OBJETIVO PRINCIPAL: Monetización (Fase 6)

Ahora que tenemos tráfico potencial (Social) y contenido (Outfits), es hora de implementar el modelo de negocio "Affiliate Hijacking".

### 1. Inyección de Enlaces de Afiliados
- [ ] Modificar `ProductCard` y `OutfitDetail` para que los botones de compra generen enlaces de afiliados.
- [ ] Implementar soporte para múltiples agentes (CNFans, Mulebuy, etc.) en el Frontend (actualmente lógica solo en Store de Pinia).
- [ ] Lógica backend para generar enlaces profundos (Deep Links) a los agentes.

### 2. Feed de Actividad (Social++)
- [ ] Mejorar el Dashboard de usuario para mostrar un feed de los usuarios seguidos.
- [ ] Endpoint backend para `getFollowedUsersOutfits()`.

### 3. Refinamiento Final (UX/UI Polishing)
- [ ] Implementar Toast Notifications (PrimeVue Toast) para reemplazar todos los `alert()`.
- [ ] Añadir Skeleton Loaders en Perfil y Buscador.
- [ ] Revisión de móviles.

---

## 📝 Notas Técnicas
- **Perfil Público**: La paginación devuelve `{ data: [...], meta: {...} }`. El frontend espera esta estructura.
- **Seguidores**: La relación es `user.followers()` y `user.following()`.
