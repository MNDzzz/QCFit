# Cambios Implementados - Recuperación
Cambios más importantes:

## 1. Utilización de Funciones de JS Avanzadas
He actualizado la forma en la que la aplicación gestiona la asincronía y los posibles errores:

* **Migración a `async/await`**: He sustituido las antiguas cadenas de promesas (`.then().catch()`) por sintaxis `async/await` en combinación con bloques `try/catch`.
* **Seguridad en variables**: He implementado funciones avanzadas de JavaScript como el **Encadenamiento Opcional (`?.`)** para evitar errores cuando una propiedad de un objeto no existe (por ejemplo, `response.data?.data`), y el operador de **Fusión Nula (`??`)** para establecer valores por defecto seguros.

**Archivos afectados:** 
Principalmente los flujos de autenticación (`resources/js/composables/auth.js` y `resources/js/store/auth.js`) y las vistas del panel de administración.

## 2. Unificación de Lógica CRUD (Principio DRY)
Tenía mucho código repetido haciendo llamadas a la API mediante Axios para cada una de las entidades del sistema (Categorías, Marcas, Productos, etc.). 

Para solucionarlo, he he hecho lo siguiente:
* **Creación de `useCrud.js`**: He desarrollado un Composable Maestro genérico en `resources/js/composables/useCrud.js` que centraliza la lógica de carga (`getItems`), creación (`createItem`), actualización (`updateItem`) y borrado (`deleteItem`), así como la gestión del estado de carga (`isLoading`).
* **Refactorización de Composables**: He modificado todos los composables individuales para que dependan de este archivo maestro, eliminando líneas repetidas.

**Archivos afectados:**
`categories.js`, `brands.js`, `products.js`, `users.js`, `roles.js`, `permissions.js`, `sources.js` y `outfits.js`.

## 3. Optimización de Controladores mediante Herencia (Backend)
En el código PHP de Laravel, me di cuenta de que todos mis controladores de la API repetían el mismo bloque de código en sus métodos `index()` para gestionar la columna y dirección de ordenación de las tablas.

He optado por aplicar metodos de herencia de la Programación Orientada a Objetos.
* He añadido los métodos protegidos `getOrderColumn()` y `getOrderDirection()` al controlador base `app/Http/Controllers/Controller.php`.
* He refactorizado todos los controladores para eliminar esa lógica duplicada y hacer que la hereden automáticamente llamando a `$this->getOrderColumn()`.

**Archivos afectados:**
`Controller.php`, `CategoryController.php`, `BrandController.php`, `ProductController.php`, `UserController.php`, `RoleController.php`, `PermissionController.php` y `SourceController.php`.

## 4. Comentarios
He añadido comenatarios en el código que he añadido