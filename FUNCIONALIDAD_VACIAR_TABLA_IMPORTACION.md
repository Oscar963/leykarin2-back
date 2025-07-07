# 🗑️ Funcionalidad: Vaciar Tabla Automáticamente al Importar

## 📋 Descripción

Se ha implementado una funcionalidad que **automáticamente** vacía completamente la tabla de inmuebles antes de realizar cada importación. Esto asegura que siempre se reemplacen todos los datos existentes con los nuevos datos del archivo Excel, evitando duplicados y manteniendo la consistencia de los datos.

## 🔧 Implementación

### **Comportamiento Automático**

El servidor **automáticamente** vacía la tabla de inmuebles antes de cada importación. No es necesario enviar parámetros adicionales ni hacer configuraciones especiales.

```javascript
// Ejemplo de uso en el frontend
const formData = new FormData();
formData.append('excel_file', file);

fetch('/api/v1/inmuebles/import', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    },
    body: formData
});
```

**Proceso automático:**
1. ✅ Se valida el archivo
2. 🗑️ Se vacía automáticamente la tabla de inmuebles
3. 📥 Se importan los nuevos datos
4. 📊 Se retorna el resultado con estadísticas

## 📊 Respuesta de Importación

La respuesta de importación siempre incluye información sobre la tabla vaciada automáticamente:

```json
{
    "success": true,
    "message": "🗑️ Tabla de inmuebles vaciada automáticamente, ✅ 200 inmuebles importados exitosamente.",
    "data": {
        "file_name": "inmuebles_nuevos.xlsx",
        "statistics": {
            "imported": 200,
            "skipped": 0,
            "duplicates": 0,
            "errors": 0
        },
        "has_errors": false,
        "error_count": 0,
        "table_cleared_before_import": true
    },
    "timestamp": "2024-01-15T10:30:00.000000Z"
}
```

## 🔒 Seguridad y Logging

### **Logs de Actividad**

Todas las acciones de vaciar tabla se registran en los logs:

- **Log de seguridad**: Se registra como warning con detalles del usuario
- **Log de actividad**: Se registra la acción en el sistema de auditoría
- **Información registrada**:
  - ID del usuario
  - Número de registros eliminados
  - Timestamp
  - IP del usuario
  - User Agent

### **Validaciones**

- ✅ Usuario debe estar autenticado
- ✅ Se valida que el parámetro sea booleano
- ✅ Se manejan errores de base de datos
- ✅ Se registra toda la actividad

## 🚀 Casos de Uso

### **Caso 1: Importación Estándar**
```javascript
// Importación que automáticamente vacía la tabla antes de importar
const importInmuebles = async (file) => {
    const formData = new FormData();
    formData.append('excel_file', file);
    
    const response = await fetch('/api/v1/inmuebles/import', {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
        body: formData
    });
    
    return response.json();
};
```

### **Caso 2: Importación con Manejo de Errores**
```javascript
// Importación con manejo completo de errores
const importInmueblesWithErrorHandling = async (file) => {
    try {
        const formData = new FormData();
        formData.append('excel_file', file);
        
        const response = await fetch('/api/v1/inmuebles/import', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ Importación exitosa:', result.message);
            console.log('📊 Estadísticas:', result.data.statistics);
        } else {
            console.error('❌ Error en importación:', result.error);
        }
        
        return result;
    } catch (error) {
        console.error('❌ Error de red:', error);
        throw error;
    }
};
```

## ⚠️ Consideraciones Importantes

### **⚠️ Advertencias**

1. **Pérdida de Datos**: **Cada importación elimina TODOS los registros existentes**
2. **Irreversible**: La acción no se puede deshacer automáticamente
3. **Backup**: Se recomienda hacer backup antes de cada importación
4. **Permisos**: Solo usuarios autenticados pueden usar esta funcionalidad

### **🔍 Recomendaciones**

1. **Notificación**: Informar al usuario que cada importación reemplaza todos los datos
2. **Backup**: Implementar sistema de backup automático antes de importar
3. **Validación**: Verificar que el archivo contenga todos los datos necesarios
4. **Confirmación**: Pedir confirmación al usuario antes de importar

## 📝 Ejemplo de Implementación Frontend

```javascript
class InmuebleImportService {
    constructor(token) {
        this.token = token;
        this.baseUrl = '/api/v1/inmuebles/import';
    }
    
    async importInmuebles(file, showConfirmation = true) {
        if (showConfirmation) {
            const confirmed = await this.showImportConfirmation();
            if (!confirmed) return null;
        }
        
        const formData = new FormData();
        formData.append('excel_file', file);
        
        const response = await fetch(this.baseUrl, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${this.token}` },
            body: formData
        });
        
        return response.json();
    }
    
    async showImportConfirmation() {
        return new Promise((resolve) => {
            const confirmed = confirm(
                '⚠️ ADVERTENCIA: Esta importación eliminará TODOS los inmuebles existentes.\n\n' +
                '¿Está seguro de que desea continuar?\n\n' +
                'Esta acción no se puede deshacer.'
            );
            resolve(confirmed);
        });
    }
    
    async getImportStatistics() {
        const response = await fetch(`${this.baseUrl}/statistics`, {
            headers: { 'Authorization': `Bearer ${this.token}` }
        });
        
        return response.json();
    }
}

// Uso
const importService = new InmuebleImportService(userToken);

// Importar inmuebles (automáticamente vacía la tabla)
const result = await importService.importInmuebles(fileInput.files[0]);

// Obtener estadísticas de importación
const stats = await importService.getImportStatistics();
```

## 🔄 Historial de Cambios

- **v1.0.0**: Implementación inicial de la funcionalidad
- **v1.1.0**: Comportamiento automático de vaciar tabla
- Eliminado parámetro `clear_table_before_import` (ahora es automático)
- Eliminado endpoint independiente para vaciar tabla
- La tabla se vacía automáticamente en cada importación
- Implementado logging completo de la actividad
- Agregadas validaciones de seguridad 