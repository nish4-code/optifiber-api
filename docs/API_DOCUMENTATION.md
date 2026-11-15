# Guía de Referencia y Documentación de la API REST - SmartOLT (Optifiber API)

Esta documentación técnica contiene la especificación oficial, exhaustiva y lista para producción de los endpoints de la API de SmartOLT implementados en Optifiber API. Cubre todas las operaciones de la colección de Postman ([SmartOLT.postman_collection.json](file:///home/r2d2/Documents/optifiber/optifiber-api/SmartOLT.postman_collection.json)), optimizaciones GIS para mapas, trazado de fibra óptica y tipos TypeScript para el equipo frontend.

---

## Índice General

1. [Configuración Global y Seguridad](#1-configuración-global-y-seguridad)
2. [Estructura Universal de Respuestas](#2-estructura-universal-de-respuestas)
3. [Módulo de OLTs y Telemetría de Cabecera](#3-módulo-de-olts-y-telemetría-de-cabecera)
4. [Módulo de Topología Física (Zonas, NAPs y Armarios)](#4-módulo-de-topología-física-zonas-naps-y-armarios)
5. [Módulo de Catálogos (Perfiles y Modelos de ONT)](#5-módulo-de-catálogos-perfiles-y-modelos-de-ont)
6. [Módulo de Abonados / ONUs](#6-módulo-de-abonados--onus)
   - [6.1 Listado Paginado con Filtros](#61-listado-paginado-con-filtros)
   - [6.2 Visor GIS Ultraligero para Mapas (Bounding Box)](#62-visor-gis-ultraligero-para-mapas-bounding-box)
   - [6.3 Trazado Óptico en Vivo (Trace Polilínea)](#63-trazado-óptico-en-vivo-trace-polilínea)
   - [6.4 Ficha Técnica y Telemetría en Vivo](#64-ficha-técnica-y-telemetría-en-vivo)
   - [6.5 Aprovisionamiento y Modificaciones](#65-aprovisionamiento-y-modificaciones)
   - [6.6 Operaciones Remotas y Control de Tráfico](#66-operaciones-remotas-y-control-de-tráfico)
   - [6.7 Tickets de Incidencia y Soporte](#67-tickets-de-incidencia-y-soporte)
7. [Módulo de Webhook (Eventos en Tiempo Real)](#7-módulo-de-webhook-eventos-en-tiempo-real)
8. [Definiciones de Tipos en TypeScript para Frontend](#8-definiciones-de-tipos-en-typescript-para-frontend)
9. [Guía de Integración Rápida (Axios + Leaflet)](#9-guía-de-integración-rápida-axios--leaflet)

---

## 1. Configuración Global y Seguridad

- **Base URL (Local):** `http://localhost:8000/api`
- **Base URL (Producción):** Configurada según `APP_URL` en variables de entorno.
- **Headers Obligatorios en cada petición:**
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Autenticación (Laravel Sanctum):**
  Para endpoints protegidos, enviar el Bearer Token en la cabecera `Authorization`:
  ```http
  Authorization: Bearer <SANCTUM_PERSONAL_ACCESS_TOKEN>
  ```
- **Control de Rate Limiting (HTTP 429):**
  Cuando SmartOLT o la API excede la tasa de peticiones, responde inmediatamente con código HTTP `429` e incluye la cabecera `Retry-After: <segundos>`.

---

## 2. Estructura Universal de Respuestas

Todas las respuestas están estandarizadas a través de `App\Helper\ApiResponse`:

### 2.1 Éxito Genérico (HTTP 200 / 201)
```json
{
    "status": true,
    "response": {}
}
```

### 2.2 Éxito para Colección Paginada (ONUs)
```json
{
    "status": true,
    "onus": [],
    "pagination": {
        "total": 1000,
        "page": 1,
        "page_size": 20,
        "total_pages": 50,
        "has_more": true
    }
}
```

### 2.3 Telemetría Óptica
```json
{
    "status": true,
    "onu_signal": "Very good",
    "onu_signal_value": "-18.50 dBm / 2.20 dBm",
    "onu_signal_1310": "2.20 dBm",
    "onu_signal_1490": "-18.50 dBm"
}
```

### 2.4 Error Estandarizado (HTTP 400, 404, 422, 502)
```json
{
    "status": false,
    "error": "Descripción clara del motivo del fallo",
    "details": null
}
```

---

## 3. Módulo de OLTs y Telemetría de Cabecera

Prefijo de rutas: `/api/smartolt/olts`

| Método | Endpoint | Query / Body | Descripción |
| :--- | :--- | :--- | :--- |
| `GET` | `/smartolt/olts` | Ninguno | Listado de OLTs con métricas de rendimiento en PostgreSQL. |
| `GET` | `/smartolt/olts/uptime-temperature` | Ninguno | Uptime y temperatura ambiental de todas las OLTs. |
| `GET` | `/smartolt/olts/{id}` | Ninguno | Ficha técnica completa de la OLT indicada (por UUID o ID externo). |
| `GET` | `/smartolt/olts/{id}/uptime-temperature` | Ninguno | Uptime y temperatura de la OLT específica. |
| `GET` | `/smartolt/olts/{id}/cards` | Ninguno | Estado y tipo de tarjetas instaladas en cada slot (MPLA, GPFD). |
| `GET` | `/smartolt/olts/{id}/pon-ports` | Ninguno | Puertos PON, estado operacional (Up/Down) y cantidad de ONUs. |
| `GET` | `/smartolt/olts/{id}/outage-pons` | Ninguno | Lista de puertos PON con caída activa de servicio. |
| `GET` | `/smartolt/olts/{id}/uplink-ports` | Ninguno | Puertos de subida / uplink (10GE, GE). |
| `GET` | `/smartolt/olts/{id}/vlans` | Ninguno | VLANs de servicio creadas en la OLT. |
| `POST` | `/smartolt/olts/{id}/vlans` | `vlan_id`, `description` | Registrar nueva VLAN en la OLT. |
| `POST` | `/smartolt/sync` | Ninguno | Dispara sincronización masiva desde SmartOLT hacia PostgreSQL. |

#### Ejemplo de Respuesta: `GET /api/smartolt/olts`
```json
{
    "status": true,
    "response": [
        {
            "id": "1",
            "name": "OLT-Chiclayo-Norte",
            "olt_hardware_version": "Huawei-MA5800-X7",
            "ip": "192.168.10.2",
            "telnet_port": "2323",
            "snmp_port": "2161",
            "status": "online",
            "latitude": -6.762,
            "longitude": -79.835,
            "metrics": {
                "cpu_usage": 16,
                "memory_usage": 32,
                "temperature": 40.2,
                "pon_ports_total": 16,
                "pon_ports_active": 14
            }
        }
    ]
}
```

---

## 4. Módulo de Topología Física (Zonas, NAPs y Armarios)

### 4.1 Zonas de Red
- `GET /api/smartolt/zones`: Listar todas las zonas.
- `POST /api/smartolt/zones`: Crear nueva zona.
  - **Body:** `{"name": "Urbanizacion Santa Victoria"}`

### 4.2 Cajas de Distribución Óptica (ODBs / NAPs / Splitters)
- `GET /api/smartolt/zones/{zoneId}/odbs`: Cajas NAP de la zona indicada.
- `POST /api/smartolt/odbs`: Registrar nueva caja NAP.
  - **Body:**
    ```json
    {
        "name": "NAP-09",
        "zone_id": "uuid-de-zona",
        "smartolt_olt_id": "uuid-de-olt",
        "pon_port": "0/0/2",
        "total_ports": 16
    }
    ```
- `POST /api/smartolt/odbs/{odbId}/edit`: Modificar caja NAP.
- `DELETE /api/smartolt/odbs/{odbId}`: Eliminar caja NAP.

### 4.3 Armarios de Distribución
- `GET /api/smartolt/zones/{zoneId}/cabinets`: Listar armarios de la zona.
- `POST /api/smartolt/cabinets`: Crear armario (`name`, `zone_id`).
- `POST /api/smartolt/cabinets/{cabinetId}/edit`: Modificar armario.
- `DELETE /api/smartolt/cabinets/{cabinetId}`: Eliminar armario.

---

## 5. Módulo de Catálogos (Perfiles y Modelos de ONT)

- `GET /api/smartolt/speed-profiles`: Perfiles de velocidad en Kbps.
- `GET /api/smartolt/onu-types?pon_type=gpon`: Catálogo de hardware homologado en la OLT.

#### Ejemplo de Respuesta: `GET /api/smartolt/speed-profiles`
```json
{
    "status": true,
    "response": [
        {"id": "1", "name": "Plan Hogar 50M", "download_speed": "51200", "upload_speed": "25600"},
        {"id": "2", "name": "Plan Duo 100M", "download_speed": "102400", "upload_speed": "51200"},
        {"id": "3", "name": "Plan Gamer 200M", "download_speed": "204800", "upload_speed": "102400"},
        {"id": "6", "name": "Plan Dedicado 1Gbps", "download_speed": "1024000", "upload_speed": "1024000"}
    ]
}
```

---

## 6. Módulo de Abonados / ONUs

Prefijo de rutas: `/api/smartolt/onus`

### 6.1 Listado Paginado con Filtros

- **Método:** `GET`
- **Ruta:** `/api/smartolt/onus`
- **Parámetros de Query:**
  - `page` *(int, opcional, defecto 1)*: Página a consultar.
  - `page_size` / `per_page` *(int, opcional, defecto 20, máx 100)*: Cantidad de registros.
  - `status` *(string, opcional)*: `Online`, `LOS`, `PowerFail`, `Disabled`.
  - `olt_id` *(string, opcional)*: Filtrar por ID de la OLT.
  - `zone_id` *(string, opcional)*: Filtrar por ID de la zona.
  - `search` *(string, opcional)*: Búsqueda parcial (`ilike`) por serial `sn`, nombre de abonado o dirección.

#### Ejemplo de Respuesta:
```json
{
    "status": true,
    "onus": [
        {
            "unique_external_id": "onu_HWTC_0001",
            "sn": "HWTC4A5B6C7D",
            "name": "Juan Perez - Clie 0001",
            "olt_id": "1",
            "olt_name": "OLT-Chiclayo-Norte",
            "board": "0/0",
            "port": "1",
            "onu": 1,
            "onu_type_id": "308",
            "onu_type_name": "HG8546M",
            "zone": "Centro Historico",
            "odb": "NAP-01",
            "status": "Online",
            "administrative_status": "Enabled",
            "signal_value": "-18.50 dBm / 2.20 dBm",
            "signal_1310": "2.20 dBm",
            "signal_1490": "-18.50 dBm",
            "distance": 410,
            "address": "Av. Balta 245, Chiclayo",
            "latitude": -6.75124,
            "longitude": -79.82145,
            "vlan": 100
        }
    ],
    "pagination": {
        "total": 1000,
        "page": 1,
        "page_size": 20,
        "total_pages": 50,
        "has_more": true
    }
}
```

---

### 6.2 Visor GIS Ultraligero para Mapas (Bounding Box)

Permite consultar exclusivamente las ONUs que caben en el marco visible de la pantalla, evitando la saturación del servidor y del navegador.

- **Método:** `GET`
- **Ruta:** `/api/smartolt/onus/map`
- **Parámetros de Query:**
  - `north` *(float, obligatorio para BBox)*: Límite norte de latitud.
  - `south` *(float, obligatorio para BBox)*: Límite sur de latitud.
  - `east` *(float, obligatorio para BBox)*: Límite este de longitud.
  - `west` *(float, obligatorio para BBox)*: Límite oeste de longitud.
  - `zone_id` *(string, opcional)*: Filtro por zona.
  - `status` *(string, opcional)*: Filtro por estado operativo.
  - `limit` *(int, opcional, defecto 1000, máx 2000)*: Límite de seguridad.

#### Payload de Respuesta Ultraligero (~45 KB para 1,000 puntos):
```json
{
    "status": true,
    "response": [
        {
            "id": "7a8b3c4d-5e6f-4a1b-8c2d-9e0f1a2b3c4d",
            "unique_external_id": "onu_HWTC_0001",
            "sn": "HWTC4A5B6C7D",
            "name": "Juan Perez - Clie 0001",
            "latitude": -6.75124,
            "longitude": -79.82145,
            "status": "Online",
            "signal_rx": -18.5,
            "odb_id": "2b3c4d5e-6f7a-8b9c-0d1e-2f3a4b5c6d7e",
            "zone_id": "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d"
        }
    ]
}
```

---

### 6.3 Trazado Óptico en Vivo (Trace Polilínea)

Al hacer clic sobre una ONU en el mapa, este endpoint entrega la ruta física completa de la fibra óptica conectada:
$$\textbf{Cliente (ONU)} \longrightarrow \textbf{Caja NAP (ODB / Splitter)} \longrightarrow \textbf{Puerto PON en OLT}$$

- **Método:** `GET`
- **Ruta:** `/api/smartolt/onus/{id}/trace` (donde `{id}` puede ser el UUID o el `unique_external_id`).

#### Ejemplo de Respuesta:
```json
{
    "status": true,
    "response": {
        "onu_id": "onu_HWTC_0001",
        "sn": "HWTC4A5B6C7D",
        "status": "Online",
        "distance_m": 410,
        "hops": [
            {
                "type": "onu",
                "id": "7a8b3c4d-5e6f-4a1b-8c2d-9e0f1a2b3c4d",
                "unique_external_id": "onu_HWTC_0001",
                "label": "Juan Perez - Clie 0001",
                "status": "Online",
                "signal_rx": -18.5,
                "address": "Av. Balta 245, Chiclayo",
                "latitude": -6.75124,
                "longitude": -79.82145
            },
            {
                "type": "odb",
                "id": "2b3c4d5e-6f7a-8b9c-0d1e-2f3a4b5c6d7e",
                "external_id": "odb_01",
                "label": "NAP-01",
                "pon_port": "0/0/1",
                "total_ports": 16,
                "used_ports": 12,
                "zone": "Centro Historico",
                "latitude": -6.752,
                "longitude": -79.825
            },
            {
                "type": "olt",
                "id": "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d",
                "external_id": "1",
                "label": "OLT-Chiclayo-Norte",
                "ip": "192.168.10.2",
                "hardware": "Huawei-MA5800-X7",
                "board": "0/0",
                "port": "1",
                "onu_number": 1,
                "latitude": -6.762,
                "longitude": -79.835
            }
        ],
        "polyline": [
            [-6.75124, -79.82145],
            [-6.752, -79.825],
            [-6.762, -79.835]
        ]
    }
}
```

---

### 6.4 Ficha Técnica y Telemetría en Vivo

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/smartolt/onus/{id}` | Ficha completa del abonado por ID o UUID. |
| `GET` | `/smartolt/onus/sn/{sn}` | Ficha por número de serie exacto de 12 caracteres. |
| `GET` | `/smartolt/onus/unconfigured?olt_id=1` | ONUs descubiertas pendientes de aprovisionar. |
| `GET` | `/smartolt/onus/search-mac?mac_address=...` | Búsqueda inversa por dirección MAC conectada a la ONT. |
| `GET` | `/smartolt/onus/presets?olt_id=1` | Presets de autorización configurados. |
| `GET` | `/smartolt/onus/{id}/signal` | Potencia óptica RX/TX en vivo (1310 nm / 1490 nm). |
| `GET` | `/smartolt/onus/{id}/status` | Estado operativo (`Online`, `LOS`, `PowerFail`). |
| `GET` | `/smartolt/onus/{id}/status-history` | Historial cronológico de desconexiones. |
| `GET` | `/smartolt/onus/{id}/full-status` | Voltaje, temperatura interna y corriente de polarización. |
| `GET` | `/smartolt/onus/{id}/running-config` | Script de configuración activa en la OLT. |
| `GET` | `/smartolt/onus/{id}/signal-graph/{type}` | Historial gráfico de potencia (`daily`, `weekly`, `monthly`). |
| `GET` | `/smartolt/onus/{id}/traffic-graph/{type}` | Historial gráfico de consumo de ancho de banda. |
| `GET` | `/smartolt/onus/{id}/macs` | Direcciones MAC conectadas a los puertos LAN/WiFi. |
| `GET` | `/smartolt/onus/{id}/router-hosts` | Tabla ARP de dispositivos locales (IP, MAC, hostname). |

---

### 6.5 Aprovisionamiento y Modificaciones

#### Autorizar Nueva ONU
- **Método:** `POST`
- **Ruta:** `/api/smartolt/onus/authorize`
- **Payload:**
```json
{
    "olt_id": "1",
    "board": "0/0",
    "port": "2",
    "sn": "ZTEGC7E230E4",
    "onu_type_id": "75",
    "name": "Carlos Torres - Sucursal Central",
    "zone_id": "uuid-de-zona",
    "odb_id": "uuid-de-nap",
    "vlan": 120,
    "onu_mode": "Routing"
}
```

#### Modificaciones de Servicio
- `POST /api/smartolt/onus/{id}/move`: Migrar ONU a otro puerto PON (`board`, `port`).
- `POST /api/smartolt/onus/{id}/update-sn`: Cambiar número de serie por reemplazo físico de módem (`sn`).
- `POST /api/smartolt/onus/{id}/change-type`: Cambiar modelo de hardware asignado (`onu_type`).
- `POST /api/smartolt/onus/{id}/update-location`: Actualizar ubicación (`address`, `latitude`, `longitude`).
- `POST /api/smartolt/onus/{id}/update-vlan`: Modificar VLAN de tráfico (`vlan`).
- `POST /api/smartolt/onus/{id}/update-mode`: Alternar modo entre `Routing` y `Bridging` (`mode`).
- `POST /api/smartolt/onus/{id}/update-speed-profiles`: Asignar nuevos planes (`download_speed_profile_name`, `upload_speed_profile_name`).

---

### 6.6 Operaciones Remotas y Control de Tráfico

- `POST /api/smartolt/onus/{id}/reboot`: Reinicio remoto de la ONT.
- `POST /api/smartolt/onus/{id}/enable`: Rehabilitar servicio tras suspensión.
- `POST /api/smartolt/onus/{id}/disable`: Suspender / cortar tráfico de la ONT.
- `DELETE /api/smartolt/onus/{id}`: Desaprovisionar y borrar de la OLT y de la base de datos.
- `POST /api/smartolt/onus/bulk-enable`: Reactivación masiva (`{"onus": ["onu_1", "onu_2"]}`).
- `POST /api/smartolt/onus/bulk-disable`: Suspensión masiva (`{"onus": ["onu_1", "onu_2"]}`).
- `POST /api/smartolt/onus/{id}/restore-defaults`: Restablecer a valores de fábrica por OMCI.
- `POST /api/smartolt/onus/{id}/resync-config`: Forzar reescritura de perfiles desde la OLT hacia la ONT.

---

### 6.7 Tickets de Incidencia y Soporte

- `GET /api/smartolt/onus/{id}/tickets?limit=50&offset=0`: Listar tickets de soporte del cliente.
- `POST /api/smartolt/onus/{id}/tickets`: Abrir nuevo ticket técnico (`subject`, `description`).
- `POST /api/smartolt/onus/{id}/tickets/update`: Agregar notas de seguimiento (`ticket_id`, `notes`).
- `POST /api/smartolt/onus/{id}/tickets/resolve`: Cerrar ticket (`ticket_id`, `resolution`).

---

## 7. Módulo de Webhook (Eventos en Tiempo Real)

SmartOLT envía eventos HTTP POST automáticos hacia tu servidor cuando detecta incidencias en la fibra.

- **Ruta:** `POST /api/smartolt/webhook`
- **Tipos de Eventos Recibidos:**
  - `onu.status_changed`: La ONU cambió a `Online`, `LOS`, `PowerFail` o `DyingGasp`.
  - `pon.outage_started`: Corte físico detectado en un puerto PON o troncal.
  - `pon.outage_ended`: Restablecimiento de la señal en el puerto PON.
- **Acción Automática:** Registra el evento en `smartolt_events` y actualiza inmediatamente el estado del abonado en `smartolt_onus`.

#### Payload de Ejemplo Recibido de SmartOLT:
```json
{
    "event": "onu.status_changed",
    "sn": "HWTC4A5B6C7D",
    "olt_id": "1",
    "status": "LOS",
    "signal": null,
    "timestamp": "2026-11-15T03:00:00Z"
}
```

---

## 8. Definiciones de Tipos en TypeScript para Frontend

Puedes copiar estas interfaces directamente en tu proyecto frontend (`types/smartolt.ts`):

```typescript
export type OnuStatus = 'Online' | 'LOS' | 'PowerFail' | 'DyingGasp' | 'Disabled' | 'Unconfigured';

export interface PaginationMeta {
    total: number;
    page: number;
    page_size: number;
    total_pages: number;
    has_more: boolean;
}

export interface ApiResponse<T> {
    status: boolean;
    response?: T;
    onus?: T;
    pagination?: PaginationMeta;
    error?: string;
    details?: unknown;
}

export interface Onu {
    unique_external_id: string;
    sn: string;
    name: string | null;
    olt_id: string | null;
    olt_name: string | null;
    board: string | null;
    port: string | null;
    onu: number | null;
    onu_type_id: string | null;
    onu_type_name: string | null;
    zone: string | null;
    odb: string | null;
    status: OnuStatus;
    administrative_status: string;
    signal_value: string | null;
    signal_1310: string | null;
    signal_1490: string | null;
    distance: number | null;
    address: string | null;
    latitude: number | null;
    longitude: number | null;
    vlan: number | null;
}

export interface OnuMapPoint {
    id: string;
    unique_external_id: string;
    sn: string;
    name: string | null;
    latitude: number;
    longitude: number;
    status: OnuStatus;
    signal_rx: number | null;
    odb_id: string | null;
    zone_id: string | null;
}

export interface TraceHop {
    type: 'onu' | 'odb' | 'olt';
    id: string;
    label: string;
    latitude: number | null;
    longitude: number | null;
    status?: OnuStatus;
    signal_rx?: number | null;
    address?: string | null;
    pon_port?: string | null;
    total_ports?: number;
    used_ports?: number;
    ip?: string | null;
    hardware?: string | null;
}

export interface TraceResponse {
    onu_id: string;
    sn: string;
    status: OnuStatus;
    distance_m: number | null;
    hops: TraceHop[];
    polyline: [number, number][];
}
```

---

## 9. Guía de Integración Rápida (Axios + Leaflet)

### 9.1 Consumo GIS con Bounding Box y Debounce

```typescript
import axios from 'axios';
import { OnuMapPoint, ApiResponse } from './types/smartolt';

let debounceTimer: ReturnType<typeof setTimeout>;

export function fetchMapPointsInBounds(
    bounds: { north: number; south: number; east: number; west: number },
    onSuccess: (points: OnuMapPoint[]) => void
) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
        const { data } = await axios.get<ApiResponse<OnuMapPoint[]>>('/api/smartolt/onus/map', {
            params: bounds,
        });

        if (data.status && data.response) {
            onSuccess(data.response);
        }
    }, 300);
}
```

### 9.2 Dibujar Trazado Óptico al Hacer Clic en un Cliente

```typescript
import L from 'leaflet';
import axios from 'axios';
import { TraceResponse, ApiResponse } from './types/smartolt';

let activeTracePolyline: L.Polyline | null = null;

export async function drawCustomerOpticalTrace(onuId: string, map: L.Map) {
    const { data } = await axios.get<ApiResponse<TraceResponse>>(`/api/smartolt/onus/${onuId}/trace`);

    if (!data.status || !data.response) return;

    // 1. Limpiar trazo anterior si existía
    if (activeTracePolyline) {
        map.removeLayer(activeTracePolyline);
    }

    // 2. Dibujar polilínea continua: Cliente -> NAP -> OLT
    activeTracePolyline = L.polyline(data.response.polyline, {
        color: '#00e5ff',
        weight: 4,
        dashArray: '8, 8',
        opacity: 0.9,
    }).addTo(map);

    // 3. Enfocar el mapa automáticamente sobre la traza
    map.fitBounds(activeTracePolyline.getBounds(), { padding: [60, 60] });
}
```
