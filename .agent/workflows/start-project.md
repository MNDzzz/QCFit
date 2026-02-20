---
description: Cómo inicializar y arrancar el proyecto QCFit (Laravel + Vue 3)
---

# Inicializar el proyecto QCFit

El proyecto está en `C:\xampp\htdocs\QCFit\QCFit`. Todos los comandos deben ejecutarse desde ese directorio.

## Primera vez (instalación desde cero)

1. Instalar dependencias PHP:
```
php composer.phar install --no-interaction
```

2. Instalar dependencias Node:
```
npm install
```

3. Iniciar MySQL (XAMPP debe estar corriendo):
```
Start-Process -FilePath "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini" -WindowStyle Hidden
```
Esperar 5 segundos para que MySQL arranque.

4. Ejecutar migraciones:
```
php artisan migrate --force
```

5. Ejecutar seeders (datos de prueba):
```
php artisan db:seed --force
```

## Arranque diario (cuando ya está instalado)

1. Asegurarse de que MySQL (XAMPP) está corriendo.

2. Iniciar el servidor Laravel:
```
php artisan serve --host=127.0.0.1 --port=8000
```

3. En otra terminal, iniciar Vite (desarrollo con hot reload):
```
npm run dev
```

La app estará disponible en: http://127.0.0.1:8000

## Notas importantes

- **MySQL**: Usar XAMPP Control Panel para iniciar Apache y MySQL, o iniciar mysqld manualmente.
- **Vite dev vs build**: En desarrollo, `npm run dev` debe estar corriendo. Si solo quieres verificar que funciona sin hot reload, usa `npm run build` una vez.
- **Base de datos**: `DB_DATABASE=qcfit`, `DB_USERNAME=root`, `DB_PASSWORD=` (vacío), puerto 3306.
- **APP_URL**: http://localhost:8000
