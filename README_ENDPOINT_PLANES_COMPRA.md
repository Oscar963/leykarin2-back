# Endpoint de Planes de Compra por Año

## 📋 Descripción

El endpoint `GET /api/purchase-plans/year/{year}` ha sido modificado para manejar diferentes tipos de usuarios según sus roles y permisos de dirección.

## 🔐 Lógica de Acceso por Rol

### 👥 Usuarios Jerárquicos
**Roles:** Director, Subrogante de Director, Jefatura, Subrogante de Jefatura

- **Comportamiento:** Carga automáticamente los datos de su dirección asignada
- **Parámetros:** Solo requiere el año
- **Validación:** Verifica que el usuario tenga una dirección asignada

### 👑 Administradores
**Roles:** Administrador del Sistema, Administrador Municipal

- **Comportamiento:** Requiere especificar qué dirección cargar
- **Parámetros:** Año + `direction_id`
- **Validación:** Verifica que la dirección especificada exista

## 🛠️ Uso del Endpoint

### Para Usuarios Jerárquicos

```http
GET /api/purchase-plans/year/2024
Authorization: Bearer {token}
```

**Respuesta exitosa:**
```json
{
  "data": {
    "id": 1,
    "name": "Plan de Compra 2024 - Alcaldía",
    "year": 2024,
    "token": "abc123...",
    "direction": {
      "id": 1,
      "name": "Alcaldía",
      "alias": "ALCALDÍA"
    }
  },
  "direction_info": {
    "id": 1,
    "name": "Alcaldía",
    "alias": "ALCALDÍA"
  },
  "user_info": {
    "roles": ["Director"],
    "is_admin": false
  }
}
```

### Para Administradores

```http
GET /api/purchase-plans/year/2024?direction_id=1
Authorization: Bearer {token}
```

**Respuesta exitosa:**
```json
{
  "data": {
    "id": 1,
    "name": "Plan de Compra 2024 - Alcaldía",
    "year": 2024,
    "token": "abc123...",
    "direction": {
      "id": 1,
      "name": "Alcaldía",
      "alias": "ALCALDÍA"
    }
  },
  "direction_info": {
    "id": 1,
    "name": "Alcaldía",
    "alias": "ALCALDÍA"
  },
  "user_info": {
    "roles": ["Administrador Municipal"],
    "is_admin": true
  }
}
```

## 🚨 Casos de Error

### Administrador sin direction_id

```http
GET /api/purchase-plans/year/2024
Authorization: Bearer {token}
```

**Respuesta de error (400):**
```json
{
  "message": "Los administradores deben especificar el parámetro direction_id para cargar los datos de una dirección específica.",
  "required_parameter": "direction_id",
  "user_roles": ["Administrador Municipal"]
}
```

### Dirección inexistente

```http
GET /api/purchase-plans/year/2024?direction_id=999
Authorization: Bearer {token}
```

**Respuesta de error (404):**
```json
{
  "message": "La dirección especificada no existe.",
  "direction_id": 999
}
```

### Usuario jerárquico sin dirección asignada

```http
GET /api/purchase-plans/year/2024
Authorization: Bearer {token}
```

**Respuesta de error (403):**
```json
{
  "message": "No tienes una dirección asignada. Contacta al administrador del sistema.",
  "user_roles": ["Director"]
}
```

## 🔧 Endpoint para Obtener Direcciones Disponibles

### Para Administradores

```http
GET /api/purchase-plans/available-directions
Authorization: Bearer {token}
```

**Respuesta exitosa:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Alcaldía",
      "alias": "ALCALDÍA"
    },
    {
      "id": 2,
      "name": "Gabinete de Alcaldía",
      "alias": "GABINETE"
    },
    {
      "id": 3,
      "name": "Secretaría Comunal de Planificación",
      "alias": "SECPLAN"
    }
  ],
  "total": 20,
  "user_info": {
    "roles": ["Administrador Municipal"],
    "is_admin": true
  }
}
```

**Respuesta de error (403) para usuarios no administradores:**
```json
{
  "message": "Solo los administradores pueden acceder a esta funcionalidad.",
  "user_roles": ["Director"]
}
```

## 💡 Flujo Recomendado para Frontend

### 1. Verificar Rol del Usuario

```javascript
// Al cargar la aplicación, verificar el rol del usuario
const userResponse = await fetch('/api/user', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const userData = await userResponse.json();

const isAdmin = userData.data.roles.some(role => 
  ['Administrador del Sistema', 'Administrador Municipal'].includes(role)
);
```

### 2. Para Usuarios Jerárquicos

```javascript
// Cargar directamente el plan de compra
const response = await fetch(`/api/purchase-plans/year/2024`, {
  headers: { 'Authorization': `Bearer ${token}` }
});
const planData = await response.json();
```

### 3. Para Administradores

```javascript
// Primero obtener las direcciones disponibles
const directionsResponse = await fetch('/api/purchase-plans/available-directions', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const directionsData = await directionsResponse.json();

// Mostrar selector de dirección en el frontend
// Cuando el usuario seleccione una dirección:
const selectedDirectionId = 1; // ID seleccionado por el usuario

const response = await fetch(`/api/purchase-plans/year/2024?direction_id=${selectedDirectionId}`, {
  headers: { 'Authorization': `Bearer ${token}` }
});
const planData = await response.json();
```

## 🔄 Creación Automática

Si no existe un plan de compra para el año y dirección especificados, el sistema:

1. **Crea automáticamente** un plan de compra por defecto
2. **Asigna el estado inicial** (Borrador)
3. **Registra la actividad** en el historial
4. **Retorna el plan creado** en la respuesta

## 📊 Información Adicional en Respuesta

Todas las respuestas incluyen información adicional:

- `direction_info`: Información de la dirección cargada
- `user_info`: Roles del usuario y si es administrador
- `data`: Datos del plan de compra

## 🛡️ Seguridad

- **Autenticación requerida** en todos los endpoints
- **Validación de roles** para acceso a funcionalidades
- **Validación de dirección** para administradores
- **Verificación de asignación** para usuarios jerárquicos
- **Logs de actividad** para auditoría

## 🔮 Consideraciones Futuras

- Cache de direcciones disponibles para administradores
- Filtros adicionales por estado del plan
- Paginación para listas grandes de direcciones
- Notificaciones cuando se creen planes automáticamente 