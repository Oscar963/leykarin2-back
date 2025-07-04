# Sistema de Modificaciones - Documentación Actualizada

## 📋 Descripción General

El sistema de modificaciones permite gestionar cambios en los planes de compra de forma organizada y controlada. Las modificaciones ahora incluyen generación automática de versiones correlativas y fechas automáticas.

## 🔄 Funcionalidades Principales

### ✅ Generación Automática de Versiones
- **Versión correlativa**: Se genera automáticamente basándose en la última versión del mismo tipo de modificación y plan de compra
- **Formato**: X.Y (ejemplo: 1.0, 1.1, 1.2, 2.0, etc.)
- **Lógica**: Incrementa la versión menor, cuando llega a 10 incrementa la mayor

### ✅ Fecha Automática
- **Fecha de creación**: Se establece automáticamente como la fecha actual
- **Formato**: YYYY-MM-DD

## 📡 Endpoints de la API

### **Listar modificaciones**
```
GET /api/modifications
```

### **Obtener modificación específica**
```
GET /api/modifications/{id}
```

### **Obtener modificación por token**
```
GET /api/modifications/token/{token}
```

### **Crear modificación**
```
POST /api/modifications
```

### **Actualizar modificación**
```
PUT /api/modifications/{id}
```

### **Actualizar modificación por token**
```
PUT /api/modifications/token/{token}
```

### **Cambiar estado**
```
PATCH /api/modifications/{id}/status
```

### **Eliminar modificación**
```
DELETE /api/modifications/{id}
```

## 📤 Datos Requeridos para Crear una Modificación

### **Campos Obligatorios:**
```typescript
{
  "name": "string",                    // Nombre de la modificación (máx. 255 caracteres)
  "description": "string",             // Descripción de la modificación (máx. 2000 caracteres)
  "modification_type_id": "number",    // ID del tipo de modificación
  "purchase_plan_id": "number"         // ID del plan de compra
}
```

### **Campos Opcionales:**
```typescript
{
  "status": "string"                   // Estado: "pending", "active", "inactive", "approved", "rejected"
}
```

### **Campos Generados Automáticamente:**
```typescript
{
  "version": "string",                 // Generado automáticamente (ej: "1.0", "1.1", "2.0")
  "date": "string",                    // Fecha actual en formato YYYY-MM-DD
  "token": "string",                   // Token único de 32 caracteres
  "created_by": "number",              // ID del usuario autenticado
  "created_at": "datetime"             // Timestamp de creación
}
```

## 📥 Ejemplo de Datos para Envío

### **Crear Nueva Modificación:**
```typescript
const modificationData = {
  name: "Modificación de Especificaciones Técnicas",
  description: "Actualización de las especificaciones técnicas del proyecto para mejorar la calidad del servicio",
  modification_type_id: 2,
  purchase_plan_id: 1,
  status: "pending"  // opcional
};
```

### **Respuesta del Servidor:**
```typescript
{
  "message": "Modificación ha sido guardada exitosamente",
  "data": {
    "id": 1,
    "name": "Modificación de Especificaciones Técnicas",
    "description": "Actualización de las especificaciones técnicas...",
    "version": "1.0",                    // Generado automáticamente
    "date": "2024-01-15",                // Fecha actual
    "status": "pending",
    "token": "abc123def456...",          // Token único
    "modification_type_id": 2,
    "purchase_plan_id": 1,
    "created_by": 1,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

## 🔄 Lógica de Generación de Versiones

### **Primera Modificación:**
- Si no existe ninguna modificación del mismo tipo y plan de compra → **Versión 1.0**

### **Modificaciones Subsecuentes:**
- Última versión: 1.0 → Nueva versión: **1.1**
- Última versión: 1.1 → Nueva versión: **1.2**
- Última versión: 1.9 → Nueva versión: **2.0** (incrementa mayor)
- Última versión: 2.0 → Nueva versión: **2.1**

### **Cambio de Tipo o Plan de Compra:**
- Si se actualiza el tipo de modificación o plan de compra → Se genera nueva versión correlativa para la nueva combinación

## 📊 Estados de Modificación

| Estado | Descripción | Color |
|--------|-------------|-------|
| `pending` | Pendiente de revisión | 🟡 Amarillo |
| `active` | Activa | 🟢 Verde |
| `inactive` | Inactiva | ⚫ Gris |
| `approved` | Aprobada | 🔵 Azul |
| `rejected` | Rechazada | 🔴 Rojo |

## 🔍 Filtros Disponibles

### **Parámetros de Query:**
```typescript
{
  "q": "string",                    // Búsqueda por nombre, descripción, versión o tipo
  "show": "number",                 // Elementos por página (default: 15)
  "status": "string",               // Filtro por estado
  "modification_type_id": "number", // Filtro por tipo de modificación
  "start_date": "string",           // Fecha de inicio (YYYY-MM-DD)
  "end_date": "string"              // Fecha de fin (YYYY-MM-DD)
}
```

## 📈 Estadísticas Disponibles

### **Estadísticas Básicas:**
```typescript
GET /api/modifications/statistics
```

### **Estadísticas por Usuario:**
```typescript
GET /api/modifications/statistics/user?user_id=1
```

### **Estadísticas por Plan de Compra:**
```typescript
GET /api/modifications/statistics/purchase-plan?purchase_plan_id=1
```

## 🛡️ Validaciones y Restricciones

### **Creación:**
- ✅ Nombre y descripción obligatorios
- ✅ Tipo de modificación y plan de compra deben existir
- ✅ Versión y fecha se generan automáticamente
- ✅ Estado por defecto: "pending"

### **Actualización:**
- ✅ Solo se pueden editar modificaciones en estado "pending" o "inactive"
- ✅ Si se cambia tipo o plan de compra, se genera nueva versión
- ✅ Se registra automáticamente quién actualizó

### **Eliminación:**
- ❌ No se pueden eliminar modificaciones aprobadas
- ✅ Solo se pueden eliminar modificaciones en otros estados

## 🔧 Configuración del Frontend

### **Formulario de Creación:**
```typescript
const modificationForm = {
  name: '',                    // Obligatorio
  description: '',             // Obligatorio
  modification_type_id: '',    // Obligatorio
  purchase_plan_id: '',        // Obligatorio
  status: 'pending'            // Opcional
};

// NO incluir version ni date - se generan automáticamente
```

### **Formulario de Actualización:**
```typescript
const modificationForm = {
  name: '',                    // Opcional
  description: '',             // Opcional
  modification_type_id: '',    // Opcional (genera nueva versión)
  purchase_plan_id: '',        // Opcional (genera nueva versión)
  status: ''                   // Opcional
};

// La versión se actualiza automáticamente si cambia tipo o plan
```

## 📝 Notas Importantes

1. **Versiones Automáticas**: No enviar el campo `version` desde el frontend
2. **Fechas Automáticas**: No enviar el campo `date` desde el frontend
3. **Tokens Únicos**: Cada modificación tiene un token único para acceso directo
4. **Auditoría**: Se registra automáticamente quién creó y actualizó cada modificación
5. **Validaciones**: El sistema valida la existencia de entidades relacionadas antes de crear/actualizar

## 🚀 Migración de Base de Datos

Para habilitar las nuevas funcionalidades, ejecutar:

```bash
php artisan migrate
```

Esto agregará los campos:
- `token` (string, único, nullable)
- `updated_by` (foreign key a users, nullable) 