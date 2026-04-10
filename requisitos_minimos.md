# Proyecto 2: Desarrollo de Proyecto Libre
**Departament d'Informàtica - INS Bernat el Ferrer**

## 🚀 Descripción del Proyecto
El objetivo es desarrollar una aplicación web de temática libre utilizando un stack tecnológico moderno. El desarrollo se realiza en parejas y requiere la participación de ambos miembros en todas las capas del proyecto (frontend y backend).

## 🛠️ Stack Tecnológico
* **Backend:** Laravel (Framework PHP).
* **Frontend:** Vue 3 (Composition API) y Bootstrap para el diseño.
* **Gestión de Datos:** Axios y JavaScript para la obtención y edición de datos.
* **Base de Datos:** MySQL con integración mediante Eloquent.
* **Control de Versiones:** GIT (uso obligatorio durante todo el proyecto).

---

## 📋 Requisitos Mínimos de Desarrollo

### 1. Base de Datos y Modelado
* Esquema de tablas bien estructurado y eficiente.
* **Relaciones:** Debe contar con al menos tres relaciones **N:M** (muchos a muchos).
    * Como mínimo, una de estas relaciones debe tener campos adicionales en la tabla pivote (que no sean solo las claves primarias), gestionados correctamente con las herramientas de Eloquent.

### 2. Funcionalidades del Sistema
* **Autenticación:** Sistema completo de registro y login de usuarios.
* **Permisos y Roles:** Implementación de un gestor de permisos para restringir el acceso a zonas específicas de la web.
* **Búsqueda y Filtrado:** Implementación de dos tipos de filtrado de datos:
    1.  Filtrado mediante **Eloquent** (servidor).
    2.  Filtrado mediante **Vue** (cliente).
* **Peticiones HTTP:** Uso de rutas y controladores para manejar flujos de trabajo y Axios para manipular datos en tiempo real.

### 3. Interfaz y Experiencia de Usuario (UI/UX)
* Diseño atractivo y fácil de usar basado en **Bootstrap**.
* Aplicación estricta de normas de diseño web.
* El diseño debe ser totalmente **responsive** (adaptado a dispositivos móviles).

### 4. Calidad y Estabilidad
* **Pruebas:** Realización de pruebas unitarias y de integración para asegurar el correcto funcionamiento.
* **Buenas Prácticas:** Código uniforme, siguiendo una metodología de programación coherente y adopción de estándares de desarrollo.

---

## 📂 Entregables Técnicos en GIT
Al finalizar, el repositorio debe incluir:
* **Código Fuente:** Siguiendo una estructura limpia.
* **Carpeta `Documentación`:** * Memoria del proyecto (especificaciones y prototipo UI).
    * Manual de usuario.
* **Archivo `README.md`:** Instrucciones detalladas para el despliegue e instalación de la aplicación.

---

## ⚠️ Reglas Importantes
* **Participación:** Si un miembro no participa en alguna de las partes evaluables, su nota será 0.
* **Modificaciones:** No se permitirán cambios en el código desde el día anterior a la primera presentación.
* **Orden:** No se puede iniciar el desarrollo sin haber entregado y aprobado previamente la fase de documentación.