# Documentación Completa de la API REST - SmartOLT (Frontend Reference)

Esta documentación técnica detalla exhaustivamente todos los módulos y endpoints de la API de SmartOLT implementados en Optifiber API, reflejando la colección completa de Postman ([SmartOLT.postman_collection.json](file:///home/r2d2/Documents/optifiber/optifiber-api/SmartOLT.postman_collection.json)).

---

## 1. Parámetros Globales

- **Base URL:** `http://localhost:8000/api`
- **Headers Requeridos en cada petición:**
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Autenticación:**
  Para rutas protegidas por Sanctum:
  ```http
  Authorization: Bearer <SANCTUM_TOKEN>
  ```

---

## 2. Convención de Respuestas JSON

### Éxito Estándar
```json
{
    "status": true,
    "response": {}
}
```

### Éxito para Colección de ONUs con Paginación
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

### Error de Negocio o Validación
```json
{
    "status": false,
    "error": "Descripción del error"
}
```

### Control de Rate Limiting (HTTP 429)
```json
{
    "status": false,
    "error": "Rate limit de SmartOLT alcanzado",
    "retry_after": 60
}
```

---

## 3. Módulo de OLTs y Hardware

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/smartolt/olts` | Listado de OLTs registradas con métricas generales. |
| `GET` | `/smartolt/olts/uptime-temperature` | Uptime y temperatura ambiental de todas las OLTs. |
| `GET` | `/smartolt/olts/{id}` | Ficha técnica y métricas de una OLT específica. |
| `GET` | `/smartolt/olts/{id}/uptime-temperature` | Uptime y temperatura de una OLT específica. |
| `GET` | `/smartolt/olts/{id}/cards` | Listado y estado operativo de tarjetas instaladas (slots). |
| `GET` | `/smartolt/olts/{id}/pon-ports` | Detalle de puertos PON, ONUs conectadas y estado administrativo. |
| `GET` | `/smartolt/olts/{id}/outage-pons` | Puertos PON que se encuentran actualmente en corte/outage. |
| `GET` | `/smartolt/olts/{id}/uplink-ports` | Puertos Uplink de fibra óptica (10GE, GE). |
| `GET` | `/smartolt/olts/{id}/vlans` | VLANs configuradas en la OLT. |
| `POST` | `/smartolt/olts/{id}/vlans` | Crear VLAN en la OLT (`vlan_id`, `description`). |
| `POST` | `/smartolt/sync` | Sincronización masiva de inventario OLT/Zonas/ONUs hacia PostgreSQL. |

#### Ejemplo: Detalle de Tarjetas de OLT
- **Petición:** `GET /smartolt/olts/1/cards`
- **Respuesta (200 OK):**
```json
{
    "status": true,
    "response": [
        {"slot": 0, "card_type": "MPLA", "status": "Normal"},
        {"slot": 1, "card_type": "GPFD", "status": "Normal", "ports": 16}
    ]
}
```

---

## 4. Módulo de Zonas, ODBs (Splitters / NAPs) y Armarios

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/smartolt/zones` | Listado general de zonas de cobertura. |
| `POST` | `/smartolt/zones` | Crear zona (`name`). |
| `GET` | `/smartolt/zones/{zoneId}/odbs` | Listar cajas NAP / Splitters asociados a la zona. |
| `POST` | `/smartolt/odbs` | Registrar nueva caja NAP / ODB (`name`, `zone_id`, `total_ports`). |
| `POST` | `/smartolt/odbs/{odbId}/edit` | Editar caja NAP / Splitter. |
| `DELETE` | `/smartolt/odbs/{odbId}` | Eliminar caja NAP / Splitter. |
| `GET` | `/smartolt/zones/{zoneId}/cabinets` | Listar armarios de distribución de la zona. |
| `POST` | `/smartolt/cabinets` | Crear armario de distribución. |
| `POST` | `/smartolt/cabinets/{cabinetId}/edit` | Editar armario. |
| `DELETE` | `/smartolt/cabinets/{cabinetId}` | Eliminar armario. |

---

## 5. Módulo de Catálogos (Perfiles y Modelos de ONU)

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/smartolt/speed-profiles` | Perfiles de velocidad disponibles (download/upload en Kbps). |
| `GET` | `/smartolt/onu-types?pon_type=gpon` | Modelos de ONU homologados (ZTE, Huawei, etc.). |

---

## 6. Módulo de ONUs (Abonados GPON)

### 6.1 Consultas e Inspección de Red

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/smartolt/onus?page=1&page_size=100` | Listado paginado de ONUs registradas. |
| `GET` | `/smartolt/onus/map?north=...&south=...&east=...&west=...` | Carga ultraligera por Bounding Box (GIS/Mapa) para evitar saturación. |
| `GET` | `/smartolt/onus/unconfigured?olt_id=1` | ONUs autodescubiertas sin aprovisionar. |
| `GET` | `/smartolt/onus/search-mac?mac_address=...` | Búsqueda inversa de ONU por dirección MAC. |
| `GET` | `/smartolt/onus/presets?olt_id=1` | Presets de autorización preconfigurados. |
| `GET` | `/smartolt/onus/sn/{sn}` | Consulta directa de ONU por número de serie (12 car.). |
| `GET` | `/smartolt/onus/{id}` | Ficha completa del abonado por ID externo. |
| `GET` | `/smartolt/onus/{id}/signal` | Potencia óptica RX/TX en vivo (1310/1490 nm). |
| `GET` | `/smartolt/onus/{id}/status` | Estado operativo (Online, LOS, PowerFail). |
| `GET` | `/smartolt/onus/{id}/status-history` | Historial de caídas y reconexiones de la ONU. |
| `GET` | `/smartolt/onus/{id}/full-status` | Diagnóstico de voltaje, temperatura y corriente de bias. |
| `GET` | `/smartolt/onus/{id}/running-config` | Script de configuración activa en la OLT. |
| `GET` | `/smartolt/onus/{id}/signal-graph/{graphType}` | Datos para gráfica de potencia óptica (`daily`, `weekly`, `monthly`). |
| `GET` | `/smartolt/onus/{id}/traffic-graph/{graphType}` | Datos para gráfica de consumo de ancho de banda. |
| `GET` | `/smartolt/onus/{id}/macs` | Direcciones MAC aprendidas por los puertos de la ONT. |
| `GET` | `/smartolt/onus/{id}/router-hosts` | Dispositivos conectados al router WiFi (IP, MAC, hostname). |

#### Ejemplo: Telemetría Óptica en Vivo
- **Petición:** `GET /smartolt/onus/onu_hw_001/signal`
- **Respuesta (200 OK):**
```json
{
    "status": true,
    "onu_signal": "Very good",
    "onu_signal_value": "-18.50 dBm / 2.25 dBm",
    "onu_signal_1310": "2.25 dBm",
    "onu_signal_1490": "-18.50 dBm"
}
```

---

### 6.2 Aprovisionamiento y Modificación de Configuración

| Método | Endpoint | Payload Clave | Descripción |
| :--- | :--- | :--- | :--- |
| `POST` | `/smartolt/onus/authorize` | `olt_id, board, port, sn, onu_type_id, name, vlan, zone_id, odb_id` | Aprovisiona y activa una nueva ONU. |
| `POST` | `/smartolt/onus/{id}/move` | `board, port` | Traslada la ONU a otro puerto PON en la OLT. |
| `POST` | `/smartolt/onus/{id}/update-sn` | `sn` | Reemplaza el número de serie (ej. cambio de módem por avería). |
| `POST` | `/smartolt/onus/{id}/change-type` | `onu_type` | Modifica el modelo de hardware asignado a la ONU. |
| `POST` | `/smartolt/onus/{id}/update-location` | `address, latitude, longitude, zone, odb` | Actualiza la geolocalización y dirección del abonado. |
| `POST` | `/smartolt/onus/{id}/update-vlan` | `vlan` | Modifica la VLAN de servicio del cliente. |
| `POST` | `/smartolt/onus/{id}/update-mode` | `mode` (`Routing` o `Bridging`) | Cambia el modo de operación de la ONT. |
| `POST` | `/smartolt/onus/{id}/update-speed-profiles` | `upload_speed_profile_name, download_speed_profile_name` | Actualiza los planes de ancho de banda en la OLT. |

---

### 6.3 Acciones Operativas y Control Remoto

| Método | Endpoint | Payload | Descripción |
| :--- | :--- | :--- | :--- |
| `POST` | `/smartolt/onus/{id}/reboot` | Ninguno | Reinicia remotamente la ONT del cliente. |
| `POST` | `/smartolt/onus/{id}/enable` | Ninguno | Rehabilita el servicio de la ONT tras suspensión. |
| `POST` | `/smartolt/onus/{id}/disable` | Ninguno | Suspende/corta el tráfico de la ONT. |
| `DELETE` | `/smartolt/onus/{id}` | Ninguno | Desaprovisiona y elimina la ONT de la OLT y de PostgreSQL. |
| `POST` | `/smartolt/onus/bulk-enable` | `{"onus": ["onu_1", "onu_2"]}` | Habilitación masiva de múltiples ONUs. |
| `POST` | `/smartolt/onus/bulk-disable` | `{"onus": ["onu_1", "onu_2"]}` | Suspensión masiva de múltiples ONUs. |
| `POST` | `/smartolt/onus/{id}/restore-defaults` | Ninguno | Restablece la ONT a sus valores de fábrica de forma remota. |
| `POST` | `/smartolt/onus/{id}/resync-config` | Ninguno | Fuerza la reescritura de perfiles desde la OLT hacia la ONT. |

---

### 6.4 Módulo de Tickets de Soporte en SmartOLT

| Método | Endpoint | Payload | Descripción |
| :--- | :--- | :--- | :--- |
| `GET` | `/smartolt/onus/{id}/tickets?limit=50` | Ninguno | Lista tickets de incidencia vinculados al abonado. |
| `POST` | `/smartolt/onus/{id}/tickets` | `subject, description` | Abre ticket técnico de atención. |
| `POST` | `/smartolt/onus/{id}/tickets/update` | `ticket_id, notes` | Añade notas de seguimiento al ticket. |
| `POST` | `/smartolt/onus/{id}/tickets/resolve` | `ticket_id, resolution` | Marca el ticket como solucionado. |

---

## 7. Webhook para Eventos en Vivo

- **Ruta:** `POST /api/smartolt/webhook`
- **Recepción de eventos enviados automáticamente por SmartOLT:**
  - `onu.status_changed`: Cambios de estado en ONUs (`Online`, `LOS`, `PowerFail`).
  - `pon.outage_started`: Caídas de fibra en troncal o puerto PON.
  - `pon.outage_ended`: Restablecimiento del servicio en el puerto PON.
- **Acción:** Registra el evento en la tabla `smartolt_events` de PostgreSQL y actualiza de inmediato el estado del abonado en `smartolt_onus`.
