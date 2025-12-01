# Guía de Deployment en Railway

## Servicios Necesarios

Para este proyecto necesitarás crear **3 servicios** en Railway:

### 1. Laravel Application (API Backend)
### 2. MongoDB Database
### 3. MQTT Broker (Mosquitto)

---

## Configuración Paso a Paso

### 1️⃣ Crear Proyecto en Railway

1. Ve a [railway.app](https://railway.app)
2. Click en "New Project"
3. Selecciona "Deploy from GitHub repo"
4. Selecciona tu repositorio: `mqtt-environment-quality`

---

### 2️⃣ Servicio 1: Laravel Application

Railway detectará automáticamente el `Dockerfile` y comenzará el deployment.

**IMPORTANTE:**
- El proyecto incluye `railway.toml` que fuerza el uso del Dockerfile
- El proyecto incluye `.nixpacksignore` para deshabilitar la autodetección de Nixpacks
- El Dockerfile usa PHP 8.4-cli con extensión MongoDB 2.1.0
- El puerto expuesto es `8080`

**Si Railway aún usa Nixpacks en lugar del Dockerfile:**
1. Ve a Settings del servicio en Railway
2. En la sección "Build" selecciona "Builder: Dockerfile"
3. Guarda y redeploy

#### Variables de Entorno Requeridas:

```env
# App Settings
APP_NAME="MQTT Environment Monitor"
APP_ENV=production
APP_KEY=base64:TU_KEY_AQUI
APP_DEBUG=false
APP_URL=https://tu-app.railway.app
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

# Database MongoDB
DB_CONNECTION=mongodb
DB_HOST=mongodb.railway.internal
DB_PORT=27017
DB_DATABASE=monitoreo_ambiental
DB_USERNAME=tu_usuario_mongodb
DB_PASSWORD=tu_password_mongodb

# MQTT Broker
MQTT_BROKER_HOST=mqtt-broker.railway.internal
MQTT_BROKER_PORT=1883
MQTT_BROKER_USERNAME=
MQTT_BROKER_PASSWORD=
MQTT_CLIENT_ID=laravel_mqtt_production

# Sessions y Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=info
```

**IMPORTANTE:**
- Genera un nuevo `APP_KEY` ejecutando: `php artisan key:generate --show`
- Copia el resultado y pégalo en la variable `APP_KEY`

---

### 3️⃣ Servicio 2: MongoDB Database

Tienes 2 opciones:

#### Opción A: MongoDB en Railway (Recomendado para desarrollo)

1. En tu proyecto de Railway, click en "+ New Service"
2. Selecciona "Database" → "Add MongoDB"
3. Railway creará automáticamente la base de datos
4. Copia las credenciales generadas

Las variables que Railway genera automáticamente:
```env
MONGO_URL=mongodb://usuario:password@host:port
```

Convierte esto a tus variables:
```env
DB_HOST=mongodb.railway.internal
DB_PORT=27017
DB_DATABASE=railway
DB_USERNAME=mongo
DB_PASSWORD=password_generado
```

#### Opción B: MongoDB Atlas (Recomendado para producción)

Ya tienes credenciales comentadas en tu `.env`:
```env
DB_USERNAME=devfsand_db_user
DB_PASSWORD=O1VuXIhoucBs8Eac
MONGODB_URI=mongodb+srv://devfsand_db_user:O1VuXIhoucBs8Eac@mqtt-cluster.iedzp9e.mongodb.net/?appName=mqtt-cluster
```

Si usas MongoDB Atlas, configura:
```env
DB_CONNECTION=mongodb
DB_HOST=mqtt-cluster.iedzp9e.mongodb.net
DB_PORT=27017
DB_DATABASE=monitoreo_ambiental
DB_USERNAME=devfsand_db_user
DB_PASSWORD=O1VuXIhoucBs8Eac
```

O simplemente usa la URI completa:
```env
MONGODB_URI=mongodb+srv://devfsand_db_user:O1VuXIhoucBs8Eac@mqtt-cluster.iedzp9e.mongodb.net/monitoreo_ambiental?appName=mqtt-cluster
```

---

### 4️⃣ Servicio 3: MQTT Broker (Eclipse Mosquitto)

1. En tu proyecto de Railway, click en "+ New Service"
2. Selecciona "Empty Service"
3. Click en "Settings"
4. En "Source" selecciona "Docker Image"
5. Ingresa la imagen: `eclipse-mosquitto:latest`

#### Configuración del MQTT Broker:

**Port:** Exponer puerto `1883` (TCP)

**Variables de entorno:**
No necesita variables especiales si usas autenticación anónima.

**Volumen (opcional):**
Si quieres persistencia, puedes montar un volumen en `/mosquitto/data`

**Networking:**
Railway automáticamente asignará un hostname interno: `mqtt-broker.railway.internal`

---

### 5️⃣ Networking entre Servicios

Railway usa networking privado. Los servicios se comunican usando:
- `servicio.railway.internal:puerto`

**Para Laravel:**
```env
MQTT_BROKER_HOST=mqtt-broker.railway.internal
MQTT_BROKER_PORT=1883
```

**Para MongoDB:**
```env
DB_HOST=mongodb.railway.internal
DB_PORT=27017
```

---

### 6️⃣ Iniciar el MQTT Listener

El MQTT listener debe ejecutarse como un proceso separado. Tienes 2 opciones:

#### Opción A: Crear un servicio Worker separado

1. Crea un nuevo servicio desde el mismo repositorio
2. En "Settings" → "Deploy Command" configura:
   ```bash
   php artisan mqtt:listen
   ```
3. Usa las mismas variables de entorno que el servicio principal

#### Opción B: Usar Supervisor (incluido en Dockerfile)

Modifica el `Dockerfile` para incluir supervisor que ejecute ambos procesos.

---

### 7️⃣ Configurar CORS (para tu frontend Vue)

En tu servicio de Laravel, agrega estas variables:

```env
FRONTEND_URL=https://tu-frontend.vercel.app
SANCTUM_STATEFUL_DOMAINS=tu-frontend.vercel.app
SESSION_DOMAIN=.railway.app
```

---

### 8️⃣ Public Domain

1. Railway asignará automáticamente un dominio: `tu-app.railway.app`
2. Copia ese dominio y actualiza `APP_URL` con él
3. Este será el endpoint de tu API

---

## Checklist de Deployment

- [ ] Servicio Laravel creado y desplegado
- [ ] MongoDB configurado (Railway o Atlas)
- [ ] MQTT Broker desplegado
- [ ] Variables de entorno configuradas
- [ ] APP_KEY generada
- [ ] Networking configurado entre servicios
- [ ] MQTT Listener ejecutándose
- [ ] Dominio público asignado
- [ ] CORS configurado

---

## Endpoints de la API

Una vez desplegado, tu API estará disponible en:

```
https://tu-app.railway.app/api/v1/
```

Endpoints disponibles:
- `GET /api/v1/sensors` - Listar todos los datos
- `GET /api/v1/sensors/latest` - Último dato recibido
- `GET /api/v1/sensors/{id}` - Dato específico
- `GET /api/v1/sensors/date-range` - Filtrar por fechas
- `GET /api/v1/alerts` - Listar alertas
- `GET /api/v1/metrics/temperature` - Estadísticas temperatura
- `GET /api/v1/metrics/humidity` - Estadísticas humedad
- `GET /api/v1/metrics/air-quality` - Estadísticas calidad aire
- `GET /api/v1/metrics/hourly` - Promedios por hora
- `GET /api/v1/metrics/daily` - Promedios por día

---

## Conectar Arduino al MQTT Broker

Una vez desplegado el MQTT Broker en Railway:

1. Railway te dará un hostname público: `mqtt-broker-production.up.railway.app`
2. El puerto será el que expongas (por defecto 1883)

En tu código Arduino, actualiza:

```cpp
const char* mqtt_server = "mqtt-broker-production.up.railway.app";
const int mqtt_port = 1883;
```

**IMPORTANTE:** Railway solo expone el puerto `1883` vía TCP, no WebSocket (puerto 9001).
Si tu Arduino usa WiFi, funcionará perfectamente con el puerto 1883.

---

## Troubleshooting

### Error: "Class 'MongoDB\Laravel\MongoDBServiceProvider' not found"
Asegúrate de que `composer.json` incluya:
```json
"mongodb/laravel-mongodb": "^5.1"
```

### Error: "MQTT connection refused"
- Verifica que el servicio MQTT esté corriendo
- Verifica el hostname: `mqtt-broker.railway.internal`
- Verifica el puerto: `1883`

### Error: "Database connection failed"
- Verifica las credenciales de MongoDB
- Verifica el hostname de MongoDB
- Si usas Atlas, verifica que la IP de Railway esté en la whitelist (o permite 0.0.0.0/0)

---

## Monitoreo

Para ver los logs:
```bash
# En Railway Dashboard
Click en el servicio → Deployments → View Logs
```

Para debugging:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

**IMPORTANTE:** En producción, siempre usa `APP_DEBUG=false`


mongodb.railway.internal

dyuIbLgyKyiBcJNjOBuSBMxWOrtkfZCu password
mongo username

mongo public URl mongodb://mongo:dyuIbLgyKyiBcJNjOBuSBMxWOrtkfZCu@gondola.proxy.rlwy.net:41610

URL mongodb://mongo:dyuIbLgyKyiBcJNjOBuSBMxWOrtkfZCu@gondola.proxy.rlwy.net:41610

host mongodb.railway.internal

password dyuIbLgyKyiBcJNjOBuSBMxWOrtkfZCu

27017

mongo
