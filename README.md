# AstroSport · Plataforma de fotografía deportiva

Sistema de venta y entrega de fotografías deportivas desarrollado en PHP 8.1+, arquitectura MVC y MySQL. La portada dinámica replica el diseño del prototipo `index.html` incluido en la raíz y obtiene sus galerías desde MySQL.

## Funciones incluidas

- Portada dinámica con hero, búsqueda, eventos y galerías destacadas.
- Catálogo por eventos, sets, dorsales y fotografías individuales.
- Compra individual, packs configurables o set completo.
- Carrito lateral, checkout e integración con Flow Sandbox/Producción.
- Registro opcional en checkout y portal de clientes con pedidos y cambio de contraseña.
- Descarga protegida de originales y ZIP, habilitada solo tras confirmar el pago.
- Marca de agua de texto o imagen y bloqueo de originales.
- Panel MVC para dashboard, pedidos, eventos, lotes, usuarios, roles, CTA, hero, correo SMTP y Flow.
- Diseño responsive, con dos columnas de productos en celulares.

## Instalación nueva

1. Copia el proyecto dentro de `htdocs/astrosport` o `public_html`.
2. Crea una base MySQL e importa el único instalador `database/astrosport_complete.sql`. Incluye el esquema completo y todos los datos demo; no hace falta ejecutar actualizaciones adicionales.
3. Configura las credenciales en `config/config.php` o mediante `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` y `APP_URL`.
4. Usa PHP 8.1 o superior con PDO MySQL, GD, cURL, OpenSSL, fileinfo, ZipArchive y mod_rewrite.
5. Da permisos de escritura al usuario de Apache sobre `storage/originals`, `storage/previews` y `uploads/events`.
6. Crea el administrador desde la terminal con `php scripts/create_admin.php`. El script solicitará una contraseña segura y generará un hash compatible con la versión de PHP instalada.

## Rutas principales

- `/`: portada y tienda AstroSport.
- `/eventos`: eventos publicados.
- `/evento?slug=...`: sets del evento.
- `/foto?id=...`: selección y compra de fotografías.
- `/carrito` y `/checkout`: compra y pago.
- `/mi-cuenta`: portal del cliente.
- `/admin`: panel administrativo.

## Flow

Configura primero `APP_URL` con la URL HTTPS pública. En el panel abre **Pasarela Flow**, selecciona Sandbox o Producción y registra la API Key y Secret Key. Flow no puede confirmar pagos contra `localhost`.

El archivo `index.html` se conserva como referencia visual. Apache usa `index.php` como portada mediante `DirectoryIndex`, para que el contenido provenga de MySQL y del panel administrativo.
