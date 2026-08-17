# AstroSport · Plataforma de fotografía deportiva

Sistema de venta y entrega de fotografías deportivas desarrollado en PHP 8.1+, arquitectura MVC y MySQL. La portada dinámica replica el diseño del prototipo `index.html` incluido en la raíz y obtiene sus galerías desde MySQL.

## Funciones incluidas

- Portada dinámica con hero, búsqueda, eventos y galerías destacadas.
- Catálogo por eventos, sets, dorsales y fotografías individuales.
- Compra individual, tres combos configurables por cantidad o set completo.
- Carrito lateral y checkout con selección entre Flow y Transbank Webpay Plus.
- Registro opcional en checkout y portal de clientes con pedidos y cambio de contraseña.
- Descarga protegida de originales y ZIP, habilitada solo tras confirmar el pago.
- Marca de agua de texto o imagen y bloqueo de originales.
- Panel MVC para dashboard, pedidos, eventos, lotes, usuarios, roles, CTA, hero, correo SMTP, Flow y Transbank.
- Diseño responsive, con dos columnas de productos en celulares.

## Instalación nueva

1. Copia el proyecto dentro de `htdocs/astrosport` o `public_html`.
2. Crea una base MySQL e importa el único instalador `database/astrosport_complete.sql`. Incluye el esquema completo y todos los datos demo; no hace falta ejecutar actualizaciones adicionales.
3. Configura las credenciales en `config/config.php` o mediante `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` y `APP_URL`.
4. Usa PHP 8.1 o superior con PDO MySQL, GD, cURL, OpenSSL, fileinfo, ZipArchive y mod_rewrite.
5. Da permisos de escritura al usuario de Apache sobre `storage/originals`, `storage/previews` y `uploads/events`.
6. El instalador incluye un administrador local listo para ingresar. Si necesitas cambiar su clave, ejecuta `php scripts/create_admin.php`; el script solicitará una nueva contraseña y generará un hash compatible con la versión de PHP instalada.

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

## Transbank Webpay Plus

En el panel abre **Transbank Webpay**, selecciona Integración o Producción e ingresa `Tbk-Api-Key-Id` y `Tbk-Api-Key-Secret`. La llave se almacena cifrada y no se incluye en el repositorio. Para Producción, `APP_URL` debe apuntar al dominio público HTTPS; el retorno se procesa en `/pago/transbank/retorno` mediante la API REST 1.2.

El archivo `index.html` se conserva como referencia visual. Apache usa `index.php` como portada mediante `DirectoryIndex`, para que el contenido provenga de MySQL y del panel administrativo.
