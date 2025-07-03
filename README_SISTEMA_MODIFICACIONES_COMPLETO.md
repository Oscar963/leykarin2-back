# 🚀 Sistema Completo de Modificaciones - Planes de Compra

## 📋 Descripción General

El sistema de modificaciones permite a los funcionarios realizar modificaciones a los planes de compra una vez que han sido decretados. Cada modificación mantiene un historial completo de todas las acciones realizadas y puede incluir documentos de respaldo.

## ✨ Características Principales

### 🔄 **Gestión Completa de Modificaciones**
- ✅ **Numeración automática** por plan de compra
- ✅ **Tipos de modificación** predefinidos
- ✅ **Estados de flujo** (pendiente, aprobada, rechazada)
- ✅ **Impacto presupuestario** calculable
- ✅ **Justificación técnica** obligatoria
- ✅ **Documentos de respaldo** adjuntables

### 📊 **Tipos de Modificación Disponibles**

#### **Tipos Principales**
1. **Eliminar - Cualitativa** - Eliminación de características cualitativas, especificaciones o criterios
2. **Eliminar - Cuantitativa** - Eliminación de cantidades, montos o valores numéricos
3. **Agregar y/o Cambiar** - Adición de nuevos elementos o modificación de elementos existentes
4. **Eliminar y/o Agregar** - Eliminación de elementos existentes y adición de nuevos elementos
5. **Agregar** - Adición de nuevos elementos, características o especificaciones

#### **Tipos Específicos**
6. **Incremento de Presupuesto** - Aumento del monto asignado
7. **Decremento de Presupuesto** - Reducción del monto asignado
8. **Cambio de Especificaciones** - Modificación de características técnicas
9. **Cambio de Proveedor** - Cambio de empresa proveedora
10. **Cambio de Cantidad** - Modificación de cantidades
11. **Cambio de Fecha de Entrega** - Ajuste de plazos
12. **Otro** - Categoría general para otros cambios

### 🔐 **Estados del Flujo de Trabajo**
- **Pendiente** - Modificación creada, esperando aprobación
- **Activa** - Modificación en proceso
- **Aprobada** - Modificación autorizada
- **Rechazada** - Modificación denegada con motivo
- **Inactiva** - Modificación desactivada

## 🏗️ Estructura de Base de Datos

### Tabla `modification_types`
```sql
- id (bigint, primary key)
- name (string, unique) - Nombre del tipo de modificación
- description (text) - Descripción del tipo
- created_at (timestamp)
- updated_at (timestamp)
```

### Tabla `modifications`
```sql
- id (bigint, primary key)
- modification_number (integer) - Número secuencial por plan
- date (date) - Fecha de la modificación
- reason (text) - Motivo principal
- modification_type_id (bigint, foreign key) - Relación con tipo de modificación
- budget_impact (decimal) - Impacto presupuestario
- description (text) - Descripción detallada
- justification (text) - Justificación técnica
- status (string) - Estado actual
- purchase_plan_id (bigint, foreign key)
- created_by (bigint, foreign key)
- updated_by (bigint, foreign key)
- approved_by (bigint, foreign key)
- approved_at (timestamp)
- rejected_by (bigint, foreign key)
- rejected_at (timestamp)
- rejection_reason (text)
- created_at (timestamp)
- updated_at (timestamp)
```

### Tabla `modification_histories`
```sql
- id (bigint, primary key)
- modification_id (bigint, foreign key)
- action (string) - create, update, delete, status_change
- description (text) - Descripción de la acción
- details (json) - Detalles adicionales
- user_id (bigint, foreign key)
- date (timestamp)
- created_at (timestamp)
- updated_at (timestamp)
```

### Tabla `modification_files`
```sql
- id (bigint, primary key)
- modification_id (bigint, foreign key)
- file_id (bigint, foreign key)
- file_type (string) - Tipo de documento
- description (text) - Descripción del archivo
- uploaded_by (bigint, foreign key)
- created_at (timestamp)
- updated_at (timestamp)
```

## 🚀 Instalación

### Instalación Automática
```bash
# Instalación completa
php artisan modifications:install

# Reinstalación completa (elimina datos existentes)
php artisan modifications:install --fresh

# Instalación con datos de ejemplo
php artisan modifications:install --with-examples

# Actualizar solo los tipos de modificación
php artisan modifications:update-types

# Limpiar y reinstalar solo el sistema de modificaciones
php artisan modifications:clean

# Limpiar y reinstalar con datos de ejemplo
php artisan modifications:clean --with-examples
```

### Gestión de Tipos de Modificación
```bash
# Crear nuevo tipo de modificación (interactivo)
php artisan modifications:create-type

# Crear tipo con parámetros
php artisan modifications:create-type --name="Nuevo Tipo" --description="Descripción del tipo"

# Listar tipos de modificación
php artisan modifications:list-types

# Listar con información detallada
php artisan modifications:list-types --detailed

# Mostrar solo el conteo
php artisan modifications:list-types --count

# Editar tipo de modificación
php artisan modifications:edit-type {id}

# Editar con parámetros
php artisan modifications:edit-type {id} --name="Nuevo Nombre" --description="Nueva Descripción"

# Eliminar tipo de modificación
php artisan modifications:delete-type {id}

# Eliminar sin confirmación
php artisan modifications:delete-type {id} --force
```

### Instalación Manual
```bash
# 1. Ejecutar migraciones (incluye todas las tablas necesarias)
php artisan migrate

# 2. Instalar tipos de modificación
php artisan db:seed --class=ModificationTypeSeeder

# 3. Instalar permisos
php artisan db:seed --class=ModificationPermissionSeeder

# 4. Crear datos de ejemplo (opcional)
php artisan db:seed --class=ModificationExampleSeeder

# 5. Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Estructura de Migraciones
El sistema utiliza **2 migraciones principales**:

1. **`2025_05_12_123100_create_modifications_table.php`**
   - Tabla `modifications` con todos los campos necesarios
   - Incluye campos para tipos, impacto presupuestario, aprobación/rechazo
   - Índices optimizados para consultas frecuentes

2. **`2025_05_12_123200_create_modification_histories_table.php`**
   - Tabla `modification_histories` para el historial de acciones
   - Tabla `modification_files` para documentos adjuntos
   - Índices para optimizar consultas de historial y archivos

### Seeders del Sistema

#### **ModificationTypeSeeder**
- Crea los tipos de modificación predefinidos
- Incluye tipos principales y específicos
- Se ejecuta automáticamente en la instalación

#### **ModificationPermissionSeeder**
- Crea permisos específicos para modificaciones
- Asigna permisos a roles según jerarquía
- Incluye permisos para tipos de modificación

#### **ModificationExampleSeeder**
- Crea datos de ejemplo para pruebas
- Incluye modificaciones con diferentes estados
- Requiere datos base (usuarios, planes de compra)

## 📊 Tipos de Modificación Detallados

### **Tipos Principales (Recomendados)**

#### 1. **Eliminar - Cualitativa**
- **Uso**: Cuando se eliminan características cualitativas, especificaciones o criterios
- **Ejemplo**: Eliminar requisitos de certificación, cambiar especificaciones técnicas
- **Impacto**: Puede afectar la calidad o características del producto/servicio

#### 2. **Eliminar - Cuantitativa**
- **Uso**: Cuando se eliminan cantidades, montos o valores numéricos
- **Ejemplo**: Reducir cantidad de unidades, eliminar partidas presupuestarias
- **Impacto**: Afecta directamente el presupuesto o volumen

#### 3. **Agregar y/o Cambiar**
- **Uso**: Adición de nuevos elementos o modificación de elementos existentes
- **Ejemplo**: Agregar nuevas especificaciones, cambiar características existentes
- **Impacto**: Puede aumentar complejidad o mejorar especificaciones

#### 4. **Eliminar y/o Agregar**
- **Uso**: Eliminación de elementos existentes y adición de nuevos elementos
- **Ejemplo**: Reemplazar un tipo de material por otro, cambiar proveedor
- **Impacto**: Cambio significativo en la naturaleza del proyecto

#### 5. **Agregar**
- **Uso**: Adición de nuevos elementos, características o especificaciones
- **Ejemplo**: Agregar nuevos ítems, incluir servicios adicionales
- **Impacto**: Aumenta el alcance o complejidad del proyecto

### **Tipos Específicos (Complementarios)**

#### 6. **Incremento de Presupuesto**
- **Uso**: Aumento del monto asignado a un proyecto o ítem
- **Ejemplo**: Incrementar el presupuesto por inflación o cambios de especificaciones

#### 7. **Decremento de Presupuesto**
- **Uso**: Reducción del monto asignado a un proyecto o ítem
- **Ejemplo**: Optimización de costos, reducción de alcance

#### 8. **Cambio de Especificaciones**
- **Uso**: Modificación de características técnicas o especificaciones
- **Ejemplo**: Cambiar estándares de calidad, modificar especificaciones técnicas

#### 9. **Cambio de Proveedor**
- **Uso**: Cambio de empresa proveedora de un producto o servicio
- **Ejemplo**: Cambiar proveedor por mejor precio o calidad

#### 10. **Cambio de Cantidad**
- **Uso**: Modificación en la cantidad de unidades o volumen
- **Ejemplo**: Ajustar cantidades según necesidades reales

#### 11. **Cambio de Fecha de Entrega**
- **Uso**: Modificación en los plazos de entrega o ejecución
- **Ejemplo**: Extender o reducir plazos de entrega

#### 12. **Otro**
- **Uso**: Categoría general para otros cambios no clasificados
- **Ejemplo**: Modificaciones únicas o específicas del proyecto

## 🛠️ Comandos de Gestión

### **Comandos de Instalación y Mantenimiento**

#### **Instalación Completa**
```bash
php artisan modifications:install
```
- Instala el sistema completo de modificaciones
- Crea tablas, tipos, permisos y configuración

#### **Instalación con Datos de Ejemplo**
```bash
php artisan modifications:install --with-examples
```
- Instala el sistema y crea datos de ejemplo para pruebas

#### **Actualización de Tipos**
```bash
php artisan modifications:update-types
```
- Actualiza solo los tipos de modificación sin afectar otros datos

#### **Limpieza del Sistema**
```bash
php artisan modifications:clean
```
- Elimina todos los datos de modificaciones y reinstala el sistema

### **Comandos de Gestión de Tipos**

#### **Crear Tipo de Modificación**
```bash
# Interactivo
php artisan modifications:create-type

# Con parámetros
php artisan modifications:create-type --name="Nuevo Tipo" --description="Descripción"
```

#### **Listar Tipos**
```bash
# Lista básica
php artisan modifications:list-types

# Con información detallada
php artisan modifications:list-types --detailed

# Solo conteo
php artisan modifications:list-types --count
```

#### **Editar Tipo**
```bash
# Interactivo
php artisan modifications:edit-type {id}

# Con parámetros
php artisan modifications:edit-type {id} --name="Nuevo Nombre" --description="Nueva Descripción"
```

#### **Eliminar Tipo**
```bash
# Con confirmación
php artisan modifications:delete-type {id}

# Sin confirmación
php artisan modifications:delete-type {id} --force
```

## 📡 API Endpoints

### **Gestión Básica**
```http
GET    /api/modifications                    # Listar modificaciones
POST   /api/modifications                    # Crear modificación
GET    /api/modifications/{id}               # Ver modificación
PUT    /api/modifications/{id}               # Actualizar modificación
DELETE /api/modifications/{id}               # Eliminar modificación
```

### **Estados y Flujo**
```http
PUT    /api/modifications/{id}/status        # Cambiar estado
POST   /api/modifications/{id}/approve       # Aprobar modificación
POST   /api/modifications/{id}/reject        # Rechazar modificación
```

### **Archivos**
```http
POST   /api/modifications/{id}/attach-files  # Adjuntar archivos
POST   /api/modifications/{id}/detach-files  # Desadjuntar archivos
```

### **Consultas y Reportes**
```http
GET    /api/modifications/statuses           # Estados disponibles
GET    /api/modifications/types              # Tipos disponibles
GET    /api/modifications/statistics         # Estadísticas globales
GET    /api/modifications/pending-approval   # Pendientes de aprobación
GET    /api/purchase-plans/{id}/modifications # Por plan de compra
```

### **Tipos de Modificación**
```http
GET    /api/modification-types               # Listar tipos de modificación
POST   /api/modification-types               # Crear tipo de modificación
GET    /api/modification-types/{id}          # Ver tipo de modificación
PUT    /api/modification-types/{id}          # Actualizar tipo de modificación
DELETE /api/modification-types/{id}          # Eliminar tipo de modificación
GET    /api/modification-types/select        # Tipos para select
GET    /api/modification-types/{id}/statistics # Estadísticas del tipo
```

## 📝 Ejemplos de Uso

### Crear una Modificación
```json
POST /api/modifications
{
    "purchase_plan_id": 1,
    "date": "2024-01-15",
    "reason": "Cambio en especificaciones técnicas",
    "modification_type_id": 3,
    "budget_impact": 50000.00,
    "description": "Se requiere cambiar las especificaciones del equipo",
    "justification": "El proveedor actual no puede cumplir con las especificaciones originales",
    "status": "pending"
}
```

### Crear un Tipo de Modificación
```json
POST /api/modification-types
{
    "name": "Cambio de Especificaciones",
    "description": "Modificación de las características técnicas o especificaciones de un producto o servicio"
}
```

### Aprobar una Modificación
```json
POST /api/modifications/1/approve
{
    "comment": "Aprobada por el director técnico"
}
```

### Rechazar una Modificación
```json
POST /api/modifications/1/reject
{
    "rejection_reason": "No se justifica el incremento presupuestario",
    "comment": "Revisar con el área de presupuestos"
}
```

### Adjuntar Archivos
```json
POST /api/modifications/1/attach-files
{
    "files": [
        {
            "file_id": 123,
            "file_type": "justificacion",
            "description": "Justificación técnica del cambio"
        },
        {
            "file_id": 124,
            "file_type": "cotizacion",
            "description": "Nueva cotización del proveedor"
        }
    ]
}
```

## 🔐 Permisos y Roles

### Permisos Disponibles
- `modifications.list` - Ver lista de modificaciones
- `modifications.create` - Crear modificaciones
- `modifications.show` - Ver detalles de modificación
- `modifications.edit` - Editar modificaciones
- `modifications.delete` - Eliminar modificaciones
- `modifications.update_status` - Cambiar estado de modificaciones

### Asignación por Roles
- **Administrador del Sistema**: Todos los permisos
- **Administrador Municipal**: Todos los permisos
- **Director**: Listar, crear, ver, editar y cambiar estado
- **Subrogante de Director**: Listar, crear, ver, editar y cambiar estado
- **Visador**: Listar, ver y cambiar estado
- **Usuario**: Solo listar y ver

## 📊 Estadísticas y Reportes

### Estadísticas Globales
```json
GET /api/modifications/statistics
{
    "total": 25,
    "pending": 8,
    "approved": 12,
    "rejected": 3,
    "active": 2,
    "by_type": {
        "budget_increase": 10,
        "specification_change": 8,
        "supplier_change": 4,
        "other": 3
    },
    "total_budget_impact": 150000.00,
    "total_budget_impact_formatted": "150.000,00"
}
```

### Estadísticas por Plan de Compra
```json
GET /api/purchase-plans/1/modifications
{
    "data": [...],
    "stats": {
        "total": 5,
        "pending": 2,
        "approved": 2,
        "rejected": 1,
        "total_budget_impact": 25000.00,
        "total_budget_impact_formatted": "25.000,00"
    }
}
```

## 🔧 Uso en el Código

### Crear una Modificación
```php
use App\Services\ModificationService;

$modificationService = new ModificationService();

$modification = $modificationService->createModification([
    'purchase_plan_id' => 1,
    'date' => '2024-01-15',
    'reason' => 'Cambio en especificaciones',
    'type' => 'specification_change',
    'budget_impact' => 50000.00,
    'description' => 'Descripción detallada',
    'justification' => 'Justificación técnica',
    'status' => 'pending'
]);
```

### Obtener Modificaciones de un Plan
```php
use App\Models\PurchasePlan;

$purchasePlan = PurchasePlan::find(1);
$modifications = $purchasePlan->modifications()->with(['createdBy', 'approvedBy'])->get();
```

### Aprobar una Modificación
```php
$modificationService->approveModification($modificationId, 'Aprobada por el director');
```

### Obtener Historial
```php
use App\Models\Modification;

$modification = Modification::find(1);
$history = $modification->history()->with('user')->get();
```

## 🧪 Testing

### Factory para Testing
```php
use App\Models\Modification;

// Crear modificación de prueba
$modification = Modification::factory()->create([
    'purchase_plan_id' => 1,
    'type' => 'budget_increase',
    'status' => 'pending'
]);

// Crear múltiples modificaciones
$modifications = Modification::factory()->count(5)->create();
```

### Tests de Integración
```php
// Test de creación
public function test_can_create_modification()
{
    $response = $this->postJson('/api/modifications', [
        'purchase_plan_id' => 1,
        'date' => '2024-01-15',
        'reason' => 'Test reason',
        'type' => 'budget_increase',
        'justification' => 'Test justification'
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('modifications', [
        'reason' => 'Test reason'
    ]);
}
```

## 🔍 Validaciones

### Reglas de Validación
- **Fecha**: Obligatoria, formato válido
- **Motivo**: Obligatorio, máximo 1000 caracteres
- **Tipo**: Obligatorio, debe ser uno de los tipos predefinidos
- **Impacto presupuestario**: Opcional, numérico entre -999,999,999.99 y 999,999,999.99
- **Descripción**: Opcional, máximo 2000 caracteres
- **Justificación**: Obligatoria, máximo 2000 caracteres
- **Plan de compra**: Obligatorio, debe existir

### Validaciones de Negocio
- Solo se pueden crear modificaciones en planes de compra decretados
- El número de modificación se genera automáticamente
- Solo usuarios autorizados pueden aprobar/rechazar
- Se registra automáticamente el historial de cambios

## 🚨 Consideraciones Importantes

### Seguridad
- Todas las operaciones están protegidas por permisos
- Se valida la existencia de relaciones antes de operaciones
- Se registra automáticamente el usuario que realiza cada acción

### Performance
- Se utilizan índices en campos frecuentemente consultados
- Se implementa paginación en listados grandes
- Se optimizan las consultas con eager loading

### Mantenibilidad
- Código organizado en capas (Controller, Service, Model)
- Validaciones centralizadas en Request classes
- Recursos para transformación de datos
- Logging automático de todas las acciones

## 📞 Soporte

Para dudas o problemas con el sistema de modificaciones:

1. **Documentación**: Revisar este README
2. **Logs**: Verificar `storage/logs/laravel.log`
3. **Comandos**: Usar `php artisan modifications:install --help`
4. **API**: Consultar documentación Swagger en `/api/documentation`

---

**📅 Fecha de actualización**: Enero 2025  
**🔄 Versión**: 2.0  
**📝 Documento**: Sistema Completo de Modificaciones - Planes de Compra Municipal 