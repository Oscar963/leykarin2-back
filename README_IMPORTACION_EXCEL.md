# Sistema de Importación Excel - Ítems de Compra

## Descripción General

El sistema de importación Excel permite cargar múltiples ítems de compra desde un archivo Excel (.xlsx, .xls) de manera eficiente y con validación completa.

## Características Principales

### ✅ **Funcionalidades Implementadas**

- **Importación masiva** de ítems de compra
- **Validación completa** de datos
- **Mapeo automático** de relaciones (asignaciones presupuestarias, tipos de compra, meses de publicación)
- **Manejo de errores** robusto con reporte detallado
- **Plantilla descargable** con ejemplos y referencias
- **Procesamiento por lotes** para archivos grandes
- **Logging** de actividades
- **Estadísticas** de importación

### 📊 **Datos Soportados**

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| Línea | Número | No | Número de línea del ítem |
| Producto o Servicio | Texto | **Sí** | Descripción del producto o servicio |
| Cantidad | Número | **Sí** | Cantidad de unidades |
| Monto | Número | **Sí** | Precio por unidad |
| Cantidad OC | Número | No | Cantidad de órdenes de compra |
| Meses envio OC | Texto | No | Meses de envío de OC |
| Dist. Regional | Texto | No | Distribución regional |
| Cod. Gasto Presupuestario | Texto | No | Código de gasto presupuestario |
| Tipo de Compra | Texto | No | Tipo de compra (se mapea automáticamente) |
| Mes de publicación | Texto | No | Mes de publicación (formato: "Dic 2025") |
| Comentario | Texto | No | Comentarios adicionales |

## Endpoints Disponibles

### 1. **Descargar Plantilla**
```http
GET /api/item-purchases/template
```
**Descripción:** Descarga una plantilla Excel con ejemplos y referencias de datos válidos.

**Respuesta:** Archivo Excel con 4 hojas:
- **Plantilla Ítems de Compra:** Ejemplos de datos
- **Asignaciones Presupuestarias:** Códigos y descripciones disponibles
- **Tipos de Compra:** Tipos de compra válidos
- **Meses de Publicación:** Meses disponibles

### 2. **Importar Archivo**
```http
POST /api/item-purchases/import/{projectId}
Content-Type: multipart/form-data
```
**Parámetros:**
- `file`: Archivo Excel (.xlsx, .xls) - Máximo 10MB
- `projectId`: ID del proyecto donde importar los ítems

**Respuesta Exitosa:**
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
            "data": {...}
        }
    ],
    "success": true
}
```

**Respuesta con Errores de Validación:**
```json
{
    "message": "Errores de validación en el archivo",
    "errors": [
        {
            "row": 10,
            "attribute": "cantidad",
            "errors": ["El campo Cantidad debe ser mayor a 0"],
            "values": {...}
        }
    ],
    "success": false
}
```

## Formato del Archivo Excel

### **Encabezados Requeridos**
El archivo debe tener exactamente estos encabezados en la primera fila:

```
Línea | Producto o Servicio | Cantidad | Monto | Cantidad OC | Meses envio OC | Dist. Regional | Cod. Gasto Presupuestario | Tipo de Compra | Mes de publicación | Comentario
```

### **Ejemplo de Datos**
```
1 | Laptop HP ProBook 450 G8 | 5 | 500000 | 2 | Ene, Feb | Región Metropolitana | 123456 | Bienes | Dic 2025 | Equipos informáticos
2 | Servicio de mantenimiento | 12 | 25000 | 1 | Mar | Valparaíso | 789012 | Servicios | Ene 2026 | Mantenimiento anual
```

## Mapeo Automático de Relaciones

### **1. Asignaciones Presupuestarias**
El sistema busca automáticamente las asignaciones presupuestarias por:
- Código exacto
- Descripción que contenga el código
- Formato "código - descripción"

### **2. Tipos de Compra**
Busca por:
- Nombre del tipo de compra
- Código del tipo de compra

### **3. Meses de Publicación**
Acepta formatos:
- "Dic 2025"
- "Diciembre 2025"
- Busca por nombre corto y año

### **4. Estado por Defecto**
Asigna automáticamente un estado "pendiente" o "borrador" al importar.

## Validaciones Implementadas

### **Validaciones de Campos**
- **Producto o Servicio:** Requerido, máximo 255 caracteres
- **Cantidad:** Requerido, numérico, mínimo 1
- **Monto:** Requerido, numérico, mínimo 0
- **Línea:** Opcional, numérico, mínimo 1
- **Cantidad OC:** Opcional, numérico, mínimo 0
- **Meses envio OC:** Opcional, máximo 100 caracteres
- **Dist. Regional:** Opcional, máximo 255 caracteres
- **Cod. Gasto Presupuestario:** Opcional, máximo 100 caracteres
- **Tipo de Compra:** Opcional, máximo 255 caracteres
- **Mes de publicación:** Opcional, máximo 100 caracteres
- **Comentario:** Opcional, máximo 500 caracteres

### **Validaciones de Negocio**
- Verificación de existencia de relaciones
- Validación de formatos de fecha
- Limpieza automática de datos numéricos

## Manejo de Errores

### **Tipos de Errores**
1. **Errores de Validación:** Datos que no cumplen las reglas
2. **Errores de Relación:** Referencias a datos inexistentes
3. **Errores de Formato:** Datos mal formateados
4. **Errores de Sistema:** Problemas técnicos

### **Estrategia de Recuperación**
- **Filas con errores:** Se omiten y continúa la importación
- **Errores de validación:** Se reportan con detalles específicos
- **Logging:** Todos los errores se registran para auditoría

## Optimizaciones de Rendimiento

### **Procesamiento por Lotes**
- **Tamaño de lote:** 100 registros
- **Tamaño de chunk:** 100 registros
- **Memoria optimizada** para archivos grandes

### **Validaciones Eficientes**
- **Validación temprana** de relaciones
- **Cache de búsquedas** para evitar consultas repetidas
- **Procesamiento asíncrono** para archivos grandes

## Logging y Auditoría

### **Actividades Registradas**
- Descarga de plantillas
- Importaciones exitosas
- Errores de importación
- Estadísticas de procesamiento

### **Información de Auditoría**
- Usuario que realiza la importación
- Timestamp de la operación
- Número de registros procesados
- Detalles de errores

## Casos de Uso

### **1. Importación Inicial**
1. Descargar plantilla
2. Llenar con datos
3. Importar archivo
4. Revisar reporte de errores

### **2. Actualización Masiva**
1. Exportar datos actuales
2. Modificar en Excel
3. Reimportar con cambios

### **3. Migración de Datos**
1. Preparar archivo con formato correcto
2. Validar relaciones antes de importar
3. Procesar en lotes si es necesario

## Mejores Prácticas

### **Para Usuarios**
1. **Usar la plantilla** como base
2. **Validar datos** antes de importar
3. **Revisar errores** después de la importación
4. **Hacer respaldos** antes de importaciones masivas

### **Para Desarrolladores**
1. **Mantener validaciones** actualizadas
2. **Monitorear logs** de errores
3. **Optimizar consultas** de relaciones
4. **Documentar cambios** en el formato

## Troubleshooting

### **Errores Comunes**

#### **"Asignación presupuestaria no encontrada"**
- Verificar que el código existe en la base de datos
- Usar el formato "código - descripción"
- Revisar la hoja de referencias en la plantilla

#### **"Tipo de compra no encontrado"**
- Verificar el nombre exacto del tipo
- Usar el código del tipo de compra
- Revisar la hoja de tipos de compra en la plantilla

#### **"Mes de publicación inválido"**
- Usar formato "Dic 2025"
- Verificar que el mes existe en el sistema
- Revisar la hoja de meses en la plantilla

#### **"Archivo demasiado grande"**
- Dividir el archivo en lotes menores a 10MB
- Usar procesamiento por lotes
- Optimizar el archivo eliminando datos innecesarios

## Archivos del Sistema

### **Importación**
- `app/Imports/ItemsPurchaseImport.php` - Lógica principal de importación
- `app/Http/Controllers/ItemPurchaseController.php` - Endpoints de importación

### **Plantilla**
- `app/Exports/ItemsPurchaseTemplateExport.php` - Generación de plantilla
- `app/Http/Controllers/ItemPurchaseController.php` - Endpoint de descarga

### **Modelos Relacionados**
- `app/Models/ItemPurchase.php` - Modelo principal
- `app/Models/BudgetAllocation.php` - Asignaciones presupuestarias
- `app/Models/TypePurchase.php` - Tipos de compra
- `app/Models/PublicationMonth.php` - Meses de publicación

## Configuración

### **Límites de Archivo**
```php
'file' => 'required|file|mimes:xlsx,xls|max:10240' // 10MB máximo
```

### **Tamaños de Lote**
```php
public function batchSize(): int
{
    return 100; // Registros por lote
}

public function chunkSize(): int
{
    return 100; // Registros por chunk
}
```

### **Logging**
```php
Log::error('Error importing item purchase row: ' . $e->getMessage(), $row);
```

## Próximas Mejoras

### **Funcionalidades Planificadas**
- [ ] Importación con actualización de registros existentes
- [ ] Validación previa sin importación
- [ ] Reporte de importación en PDF
- [ ] Importación desde múltiples hojas
- [ ] Validación de totales y sumas
- [ ] Importación con imágenes adjuntas

### **Optimizaciones Técnicas**
- [ ] Procesamiento asíncrono con colas
- [ ] Cache de relaciones para mejor rendimiento
- [ ] Validación en tiempo real
- [ ] Soporte para archivos CSV
- [ ] Importación incremental 