# QCFit - Planificación de Próxima Sesión

**Estado Actual:**
- ✅ Fase 4 (Studio & Remix) COMPLETADA 100%.
- ✅ Testing Functional E2E COMPLETADO (Login, Create, Save, Remix).
- ✅ Bug crítico 405 (Method Not Allowed) solucionado.

---

## 🎯 OBJETIVOS DE LA SESIÓN (Fase 5: Social & Community)

### 1. Sistema de Seguidores (Social)
Implementar la lógica completa de "Followers/Following" para cumplir con el requisito académico N:M Users<->Users.
- [ ] Botón "Follow/Unfollow" en el componente `Show.vue` (Outfit Detail).
- [ ] Vista de Perfil Público (`/u/{username}`):
    - Avatar, Bio, Estadísticas (Seguidores/Seguidos).
    - Grid de sus outfits creados.
- [ ] API Endpoint para listar seguidores/seguidos (ya existen las relaciones en Modelo).

### 2. Feed de Actividad (Discovery)
- [ ] Añadir pestaña "Following" en el Feed principal (`/outfits`).
- [ ] Filtrar outfits para mostrar solo los de usuarios seguidos.

### 3. Refinamiento de UX (Studio)
- [ ] Implementar Toast Notifications (reemplazar `alert()` feos).
    - Al guardar outfit.
    - Al añadir items al canvas.
- [ ] Skeleton Loaders mientras cargan las imágenes del canvas o grids.

---

## 📝 Notas Técnicas Importantes

1. **Rutas API:**
   Recordar que las rutas protegidas (`POST /outfits`, etc.) requieren auth. Si fallan con 405, revisar `php artisan route:list` y limpiar chache.

2. **Relaciones:**
   La relación de followers ya está en el modelo `User.php`.
   ```php
   public function followers() { return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id'); }
   ```
   Usar esto para el controlador de Social.

3. **Componentes Reutilizables:**
   Considerar extraer el grid de outfits (`ProductCard` se usa para productos, necesitaremos `OutfitCard` si no existe ya o adaptar `ProductCard` para outfits).
