# Rutas de Importación Excel - API

## 📋 Endpoints Disponibles

### **1. Descargar Plantilla de Importación**

```http
GET /api/item-purchases/template
```

#### **Descripción**
Descarga una plantilla Excel con ejemplos de datos y referencias de valores válidos para la importación.

#### **Autenticación**
- **Requerida:** ✅ Sí
- **Middleware:** `auth:sanctum`
- **Permisos:** `item_purchases.list`

#### **Respuesta**
- **Tipo:** Archivo Excel (.xlsx)
- **Nombre:** `plantilla-items-compra.xlsx`
- **Contenido:** 4 hojas con ejemplos y referencias

#### **Ejemplo de Uso**
```bash
curl -X GET \
  'http://localhost:8000/api/item-purchases/template' \
  -H 'Authorization: Bearer {token}' \
  -H 'Accept: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' \
  --output plantilla-items-compra.xlsx
```

#### **Respuesta Exitosa**
```
HTTP/1.1 200 OK
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="plantilla-items-compra.xlsx"
```

---

### **2. Importar Archivo Excel**

```http
POST /api/item-purchases/import/{projectId}
```

#### **Descripción**
Importa múltiples ítems de compra desde un archivo Excel al proyecto especificado.

#### **Autenticación**
- **Requerida:** ✅ Sí
- **Middleware:** `auth:sanctum`
- **Permisos:** `item_purchases.create`

#### **Parámetros de URL**
| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `projectId` | Integer | ✅ Sí | ID del proyecto donde importar los ítems |

#### **Parámetros del Body (multipart/form-data)**
| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `file` | File | ✅ Sí | Archivo Excel (.xlsx, .xls) - Máximo 10MB |

#### **Ejemplo de Uso**
```bash
curl -X POST \
  'http://localhost:8000/api/item-purchases/import/123' \
  -H 'Authorization: Bearer {token}' \
  -H 'Accept: application/json' \
  -F 'file=@/path/to/items-compra.xlsx'
```

#### **Respuesta Exitosa (200)**
```json
{
    "message": "Importación completada exitosamente",
    "stats": {
        "imported": 150,
        "skipped": 5,
        "errors": 3,
        "total_processed": 158
    },
    "errors": [
        {
            "row": 15,
            "error": "Asignación presupuestaria no encontrada",
            "data": {
                "producto_o_servicio": "Laptop HP",
                "cantidad": 5,
                "monto": 500000,
                "asignacion_presupuestaria": "999999"
            }
        },
        {
            "row": 23,
            "error": "Tipo de compra no encontrado",
            "data": {
                "producto_o_servicio": "Servicio de mantenimiento",
                "cantidad": 12,
                "monto": 25000,
                "tipo_de_compra": "Mantenimiento"
            }
        }
    ],
    "success": true
}
```

#### **Respuesta con Errores de Validación (422)**
```json
{
    "message": "Errores de validación en el archivo",
    "errors": [
        {
            "row": 10,
            "attribute": "cantidad",
            "errors": ["El campo Cantidad debe ser mayor a 0"],
            "values": {
                "linea": 10,
                "producto_o_servicio": "Impresora",
                "cantidad": 0,
                "monto": 150000
            }
        },
        {
            "row": 12,
            "attribute": "producto_o_servicio",
            "errors": ["El campo Producto o Servicio es obligatorio"],
            "values": {
                "linea": 12,
                "producto_o_servicio": "",
                "cantidad": 5,
                "monto": 75000
            }
        }
    ],
    "success": false
}
```

#### **Respuesta de Error del Sistema (500)**
```json
{
    "message": "Error al importar el archivo: El archivo no es válido",
    "success": false
}
```

---

## 🔐 Configuración de Permisos

### **Permisos Requeridos**

#### **Para Descargar Plantilla**
```php
'permission:item_purchases.list'
```

#### **Para Importar Archivo**
```php
'permission:item_purchases.create'
```

### **Configuración en Base de Datos**

Asegúrate de que los siguientes permisos existan en tu sistema:

```sql
-- Permiso para listar ítems de compra (incluye descarga de plantilla)
INSERT INTO permissions (name, guard_name) VALUES ('item_purchases.list', 'web');

-- Permiso para crear ítems de compra (incluye importación)
INSERT INTO permissions (name, guard_name) VALUES ('item_purchases.create', 'web');
```

---

## 📊 Códigos de Estado HTTP

| Código | Descripción | Cuándo Ocurre |
|--------|-------------|---------------|
| **200** | OK | Importación exitosa o descarga de plantilla |
| **401** | Unauthorized | Token de autenticación inválido o faltante |
| **403** | Forbidden | Usuario sin permisos suficientes |
| **422** | Unprocessable Entity | Errores de validación en el archivo |
| **500** | Internal Server Error | Error del sistema durante la importación |

---

## 🛡️ Validaciones de Seguridad

### **Validación de Archivos**
```php
'file' => 'required|file|mimes:xlsx,xls|max:10240' // 10MB máximo
```

### **Validación de Proyecto**
- El proyecto debe existir en la base de datos
- El usuario debe tener acceso al proyecto
- El proyecto debe estar activo

### **Validación de Permisos**
- Usuario autenticado
- Permisos específicos para cada operación
- Validación de roles si es necesario

---

## 📝 Ejemplos de Uso por Tecnología

### **JavaScript (Fetch API)**
```javascript
// Descargar plantilla
async function downloadTemplate() {
    const response = await fetch('/api/item-purchases/template', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }
    });
    
    if (response.ok) {
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'plantilla-items-compra.xlsx';
        a.click();
    }
}

// Importar archivo
async function importFile(projectId, file) {
    const formData = new FormData();
    formData.append('file', file);
    
    const response = await fetch(`/api/item-purchases/import/${projectId}`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        },
        body: formData
    });
    
    const result = await response.json();
    
    if (response.ok) {
        console.log('Importación exitosa:', result.stats);
        if (result.errors.length > 0) {
            console.log('Errores encontrados:', result.errors);
        }
    } else {
        console.error('Error en importación:', result.message);
    }
}
```

### **Angular (HttpClient)**
```typescript
// Descargar plantilla
downloadTemplate(): Observable<Blob> {
    return this.http.get('/api/item-purchases/template', {
        headers: {
            'Authorization': `Bearer ${this.token}`,
            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        },
        responseType: 'blob'
    });
}

// Importar archivo
importFile(projectId: number, file: File): Observable<any> {
    const formData = new FormData();
    formData.append('file', file);
    
    return this.http.post(`/api/item-purchases/import/${projectId}`, formData, {
        headers: {
            'Authorization': `Bearer ${this.token}`
        }
    });
}
```

### **React (Axios)**
```javascript
// Descargar plantilla
const downloadTemplate = async () => {
    try {
        const response = await axios.get('/api/item-purchases/template', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            },
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'plantilla-items-compra.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error descargando plantilla:', error);
    }
};

// Importar archivo
const importFile = async (projectId, file) => {
    try {
        const formData = new FormData();
        formData.append('file', file);
        
        const response = await axios.post(`/api/item-purchases/import/${projectId}`, formData, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'multipart/form-data'
            }
        });
        
        console.log('Importación exitosa:', response.data);
    } catch (error) {
        console.error('Error en importación:', error.response?.data);
    }
};
```

---

## 🔍 Monitoreo y Logging

### **Actividades Registradas**
- Descarga de plantillas
- Importaciones exitosas
- Errores de importación
- Estadísticas de procesamiento

### **Logs Generados**
```php
// Descarga de plantilla
$this->logActivity('download_template', 'Usuario descargó la plantilla de ítems de compra');

// Importación exitosa
$this->logActivity('import_file', "Usuario importó {$stats['imported']} ítems de compra para el proyecto {$projectId}");

// Errores de importación
Log::error('Error importing item purchase row: ' . $e->getMessage(), $row);
```

---

## 🚀 Próximas Mejoras

### **Funcionalidades Planificadas**
- [ ] Importación con actualización de registros existentes
- [ ] Validación previa sin importación
- [ ] Procesamiento asíncrono con colas
- [ ] Soporte para archivos CSV
- [ ] Importación desde múltiples hojas
- [ ] Reporte de importación en PDF

### **Optimizaciones Técnicas**
- [ ] Cache de relaciones para mejor rendimiento
- [ ] Validación en tiempo real
- [ ] Importación incremental
- [ ] Compresión de archivos grandes 