# Reservas de alojamiento — Prueba técnica MisterPlan

Pequeña aplicación de gestión de reservas: una **API REST en PHP nativo** (sin framework), **vistas renderizadas con Twig** y una **interfaz con JavaScript vanilla** que consume la API para filtrar y cancelar reservas sin recargar la página.

- **Demo:** https://raulvaliturbide.alwaysdata.net/
- **API:** https://raulvaliturbide.alwaysdata.net/api/reservas

**Stack:** PHP 8.1+ · MySQL/MariaDB · PDO · Twig 3 · JavaScript (vanilla) · Composer (autoload PSR-4, phpdotenv)

---

## Cómo levantarlo en local

**Requisitos:** PHP 8.1 o superior con las extensiones `pdo_mysql` y `mbstring`, Composer y MySQL/MariaDB (por ejemplo, con WAMP o MAMP).

1. **Clonar e instalar dependencias**
   ```bash
   git clone https://github.com/RaulValIturbide/reservas-misterplan.git
   cd reservas-misterplan
   composer install
   ```

2. **Crear la base de datos.** Importar `sql/schema.sql` (desde phpMyAdmin o por consola). El fichero crea la base de datos `reservas_misterplan`, las tablas y los datos de ejemplo (15 reservas en distintos estados y su historial):
   ```bash
   mysql -u root -p < sql/schema.sql
   ```

3. **Configurar la conexión.** Copiar `.env.ejemplo` a `.env` y ajustar los valores:
   ```
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=reservas_misterplan
   DB_USER=root
   DB_PASS=
   ```

4. **Arrancar el servidor de desarrollo**, con `public/` como raíz web:
   ```bash
   php -S localhost:8000 -t public
   ```
   Abrir http://localhost:8000/

**Probar la API:** el fichero `peticiones.http` contiene casos de prueba (creación, validaciones, filtros, cambios de estado, 404/405/409…) para el cliente HTTP de PhpStorm/VS Code. `http-client.env.json` define los entornos `local` y `produccion`.

---

## API

| Método y ruta | Descripción | Respuestas |
|---|---|---|
| `GET /api/reservas` | Listado. Filtros combinables: `estado`, `desde`, `hasta` (sobre la fecha de entrada, formato `AAAA-MM-DD`) y `nombre` (búsqueda parcial) | 200 (array, `[]` si no hay resultados) · 400 |
| `GET /api/reservas/{id}` | Detalle con su historial de eventos | 200 · 404 |
| `POST /api/reservas` | Crear una reserva (JSON) | 201 con la reserva creada · 400 |
| `PATCH /api/reservas/{id}` | Cambiar el estado: `{"estado": "confirmada" \| "cancelada" \| "pendiente"}` | 200 · 400 · 404 · 409 |

- Los errores de validación devuelven `400` con los errores por campo: `{"errores": {"email_huesped": "..."}}`.
- Una ruta existente con un método no permitido devuelve `405` con la cabecera `Allow`.
- Los errores inesperados devuelven `500` con un mensaje genérico, sin detalles internos; el detalle se registra en el log del servidor.

Ejemplo:
```
GET /api/reservas?estado=pendiente&desde=2026-12-01&hasta=2026-12-31&nombre=gil
```

**Vistas HTML:** `/` (listado con filtros) y `/reservas/{id}` (detalle con el historial).

---

## Estructura

```
public/
  index.php          Front controller: compone las dependencias, enruta y captura los errores inesperados
  .htaccess          Envía las rutas al front controller en Apache
  css/, js/          Estilos y JavaScript del frontend
src/
  Controller/        ReservaController (API, JSON) y VistaController (HTML con Twig)
  Service/           Lógica de negocio y transacciones
  Repository/        Acceso a datos con PDO (una clase por tabla)
  Validation/        Validación de la entrada; devuelve DTOs tipados o lanza ValidacionException
  Dto/               Objetos inmutables que viajan entre capas
  Enum/              EstadoReserva (pendiente | confirmada | cancelada)
  Exception/         Excepciones de dominio, traducidas a códigos HTTP en el controlador
  Http/              Respuesta JSON
  Database/          Conexión PDO a partir del .env
templates/           Plantillas Twig (base + listado + detalle + 404)
sql/schema.sql       DDL y datos de ejemplo
```

---

## Decisiones

### Organización del código
- **Capas con una responsabilidad cada una.** El controlador traduce HTTP ↔ llamadas al servicio; el servicio aplica las reglas de negocio y no sabe nada de HTTP; el repositorio es la única capa que escribe SQL. Así la API (JSON) y las vistas (HTML) reutilizan exactamente el mismo servicio.
- **Inyección de dependencias manual** en `public/index.php`: cada clase recibe por constructor lo que necesita, sin contenedor. Facilita las pruebas y deja claro de qué depende cada clase.
- **Validación → DTO tipado.** Los datos de entrada (`$_GET`, cuerpo JSON) se validan y se convierten en DTOs `readonly` con tipos (`DateTimeImmutable`, enum `EstadoReserva`). Las capas inferiores trabajan con datos ya validados.
- **Router sencillo con un mapa de rutas y métodos permitidos.** Es la única fuente de verdad: el 404 y el 405 (con cabecera `Allow`) se deducen de él.
- **Errores centralizados.** Cada controlador captura solo las excepciones que sabe tratar (validación → 400, no encontrada → 404, transición no válida → 409). Todo lo inesperado sube a un único `catch` global que registra el error y responde 500 genérico.

### Base de datos
- **MySQL**, la base de datos que pide el enunciado y la que se usa en el core de MisterPlan.
- Dos tablas: `reserva` y `reserva_evento` (historial), con clave foránea `ON DELETE CASCADE`.
- `estado` como `ENUM` en la base de datos y como enum en PHP: no se pueden guardar valores fuera de los tres permitidos.
- `importe` como `DECIMAL(10,2)` y tratado como string en PHP, para no perder precisión con `float`.
- `utf8mb4` con collation `utf8mb4_unicode_ci`: soporta cualquier carácter, y la búsqueda por nombre no distingue mayúsculas ni tildes ("garcia" encuentra "García").
- **Transacciones en el servicio:** crear una reserva o cambiar su estado inserta también el evento del historial; o se guardan las dos cosas o ninguna.

### Seguridad
- **Inyección SQL:** todas las consultas son preparadas, con `PDO::ATTR_EMULATE_PREPARES = false`. En el filtro del listado, el `WHERE` se construye dinámicamente solo con fragmentos escritos en el código; los valores del usuario van siempre como parámetros (incluido el `%` del `LIKE`).
- **XSS:** Twig escapa automáticamente toda la salida (`autoescape` activado de forma explícita); no se usa `|raw` en ninguna plantilla. En JavaScript, las filas se construyen con `createElement` y `textContent`, nunca con `innerHTML`.
- **Validación en servidor** de todos los campos: obligatorios, longitud máxima según el esquema, email válido, fechas reales en formato `AAAA-MM-DD` (se rechaza, por ejemplo, `2026-02-31`), salida posterior a la entrada e importe positivo.
- **Tipos inesperados:** si un parámetro llega como array (`?estado[]=x`) la API responde 400, no 500.
- **Credenciales fuera del repositorio:** `.env` está en `.gitignore`. En producción la raíz web es `public/`, así que `.env`, `src/`, `vendor/` y las plantillas no son accesibles desde el navegador.
- **Errores sin detalles internos** para el cliente; `display_errors` desactivado en producción.

### Otras decisiones de comportamiento
- El filtro de fechas se aplica sobre la **fecha de entrada** (llegadas del periodo), de forma inclusiva.
- Un filtro sin resultados devuelve **200 con `[]`**; el 404 se reserva para un recurso concreto que no existe.
- **Cambios de estado:** se permite cualquier transición excepto al mismo estado (409), ya que todas quedan registradas en el historial. En un caso real, reactivar una reserva cancelada debería comprobar la disponibilidad.

---

## Despliegue

Desplegado en **alwaysdata** (plan gratuito: PHP 8.4, MySQL y acceso SSH), con `public/` como raíz web.

Se descartó InfinityFree porque su sistema de seguridad bloquea las peticiones a la API que no vienen de un navegador (Postman, curl…), lo que impediría probarla directamente.

Pasos seguidos:
1. Crear la base de datos y el usuario desde el panel e importar `sql/schema.sql` **a partir de los `DROP TABLE`** (en este hosting la base de datos se crea desde el panel, no por SQL).
2. Por SSH: `git clone` del repositorio y `composer install --no-dev --optimize-autoloader`.
3. Crear el `.env` en el servidor (con permisos `600`).
4. Configurar el sitio con la raíz web en `reservas-misterplan/public/` y `display_errors = Off`.

Para actualizar: `git pull` y `composer install --no-dev --optimize-autoloader`.

---

## Qué haría diferente con más tiempo

- **Formulario de creación en la interfaz**, con validación en cliente y mostrando los errores por campo que ya devuelve la API (el endpoint `POST` está completo y probado).
- **Tests automáticos con PHPUnit**, empezando por los validadores (casos límite de fechas, importes y filtros) y por el servicio con una base de datos de pruebas.
- **Bloqueo en el cambio de estado:** `cambiarEstado` lee la reserva y después la actualiza; dos peticiones simultáneas podrían cruzarse. Se resolvería con `SELECT ... FOR UPDATE` dentro de la transacción.
- **Reglas de transición explícitas** (por ejemplo, no reactivar una cancelada sin comprobar disponibilidad).
- **Extraer la validación de fechas** a una clase común: ahora la lógica está repetida en los dos validadores.
- **Paginación** del listado.
- **Autenticación** de la API para las operaciones que modifican datos.

---

## Escalabilidad: 1000 reservas/hora

1000 reservas por hora son unas 0,3 por segundo: una carga baja para PHP y MySQL bien configurados. Aun así, cambiaría:

1. **Índices** en las columnas que usan los filtros y el orden: `estado`, `fecha_entrada` (o un índice compuesto `estado, fecha_entrada`) y `reserva_evento.reserva_id` (ya cubierta por la clave foránea). La tabla crece unas 24.000 filas al día; sin índices, cada listado recorrería la tabla entera.
2. **Paginación** obligatoria en el listado (`LIMIT`/`OFFSET` o paginación por cursor), para no devolver miles de filas en cada petición.
3. **Concurrencia:** `SELECT ... FOR UPDATE` en los cambios de estado y, si se gestionara la disponibilidad, una restricción o bloqueo que impida reservar dos veces el mismo alojamiento en las mismas fechas.
4. **Infraestructura:** PHP-FPM con OPcache activado, conexiones persistentes y, si las lecturas crecieran mucho, réplicas de lectura de MySQL o caché (por ejemplo, Redis) para los listados más consultados.
5. **Tareas asíncronas:** si crear una reserva implicara enviar emails o sincronizar con portales, hacerlo con una cola de trabajos para no alargar la respuesta de la API.
6. **Observabilidad:** logs estructurados y métricas de tiempos de respuesta para detectar cuellos de botella antes de que afecten a los usuarios.
