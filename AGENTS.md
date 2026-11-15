# Reglas de Arquitectura y Estándares de Desarrollo - Optifiber API

## 1. Estilo de Código y Formato
- Indentación estricta de 4 espacios en todos los archivos PHP, JSON y YAML.
- Modo estricto activado en cada archivo PHP (`declare(strict_types=1);`).
- Tipado estricto en parámetros, propiedades de clase y retornos de métodos.
- Código limpio y autoexplicativo: Prohibido agregar comentarios redundantes o triviales en el código.
- Cero código espagueti: Métodos pequeños y especializados con una sola responsabilidad.

## 2. Separación de Responsabilidades y Arquitectura
- **Controllers:** Delgados (Thin Controllers). Solo capturan la solicitud, delegan al Servicio correspondiente y retornan un API Resource.
- **Form Requests:** Toda validación de datos entrantes debe residir en Form Requests dedicados con reglas explícitas.
- **Services y Drivers (Patrón Strategy):**
    - `SmartOltClientInterface`: Define el contrato para interactuar con SmartOLT.
    - `SmartOltHttpClient`: Implementa la conexión HTTP real contra la API de SmartOLT (modo `production`).
    - `SmartOltMockClient`: Implementa la simulación de datos estructurados con Faker (modo `development`).
    - Los controladores y servicios jamás deben acoplarse a la implementación concreta.
- **DTOs (Data Transfer Objects):** Objetos inmutables tipados para transferir datos limpios entre clientes HTTP, servicios y modelos.
- **API Resources:** Toda respuesta JSON debe formatearse mediante `JsonResource` para garantizar una estructura uniforme idéntica a la colección de Postman.

## 3. Base de Datos (PostgreSQL)
- Motor de base de datos obligatorio: PostgreSQL (`pgsql`).
- Toda tabla debe crearse mediante migraciones de Laravel con tipos nativos de PostgreSQL (`jsonb`, `uuid` o `bigIncrements`, `timestamp with time zone`).
- Indexación obligatoria de columnas de búsqueda frecuente (`external_id`, `sn`, claves foráneas y campos de filtrado como `status`, `zone`).

## 4. Respuestas API y Manejo de Errores
- Respuestas de éxito estructuradas con formato consistente:
    ```json
    {
        "status": true,
        "response": {}
    }
    ```
- Respuestas de error con código HTTP semántico y detalle del error:
    ```json
    {
        "status": false,
        "error": "Mensaje descriptivo del error"
    }
    ```
- Manejo centralizado de excepciones en `bootstrap/app.php`.
- Control y reintento con backoff exponencial para Rate Limiting (código HTTP 429) y tolerancia a fallos.

## 5. Entorno y Configuración
- Alternancia de modo controlada por `API_MODE` (`development` / `production`).
- Credenciales y endpoints sensibles centralizados en `.env` y mapeados en `config/services.php`:
    - `API_MODE`
    - `SMARTOLT_BASE_URL`
    - `SMARTOLT_API_TOKEN`
