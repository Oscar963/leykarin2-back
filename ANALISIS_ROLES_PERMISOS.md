# 📊 Análisis Completo de Roles y Permisos - Sistema de Planes de Compra Municipal

## 🎯 Resumen Ejecutivo

El sistema implementa una **arquitectura de seguridad robusta** basada en **Spatie Laravel Permission v6** con **9 roles jerárquicos** y **más de 100 permisos granulares**. La estructura garantiza **seguridad en capas** con validaciones específicas para usuarios jerárquicos y administradores.

---

## 🏗️ Arquitectura del Sistema

### **Tecnología Base**
- **Spatie Laravel Permission v6**: Sistema de roles y permisos
- **Laravel Sanctum**: Autenticación API
- **Middleware Personalizado**: Validaciones de negocio específicas
- **Cache de Permisos**: Optimización de rendimiento (24 horas)

### **Estructura de Base de Datos**
```sql
-- Tablas principales de Spatie
roles                    -- Roles del sistema
permissions              -- Permisos disponibles
model_has_roles          -- Relación usuarios-roles
model_has_permissions    -- Relación usuarios-permisos
role_has_permissions     -- Relación roles-permisos

-- Tablas personalizadas
directions               -- Direcciones municipales
direction_user           -- Relación muchos a muchos usuarios-direcciones
users                    -- Usuarios del sistema
```

---

## 👥 Roles del Sistema

### **1. 🛡️ Administrador del Sistema**
- **Descripción**: Acceso total y completo al sistema
- **Jerarquía**: Máximo nivel de autoridad
- **Direcciones**: Múltiples direcciones permitidas
- **Permisos**: Todos los permisos del sistema (100+)

### **2. 🏛️ Administrador Municipal**
- **Descripción**: Gestión administrativa municipal
- **Jerarquía**: Nivel ejecutivo municipal
- **Direcciones**: Múltiples direcciones permitidas
- **Permisos**: Gestión completa de planes, proyectos y reportes

### **3. 👁️ Visador o de Administrador Municipal**
- **Descripción**: Revisión y aprobación de planes
- **Jerarquía**: Nivel de supervisión municipal
- **Direcciones**: Múltiples direcciones permitidas
- **Permisos**: Mismos que Administrador Municipal

### **4. 🎯 Secretaría Comunal de Planificación (SECPLAN)**
- **Descripción**: Gestión de planes de compra municipal
- **Jerarquía**: Nivel de planificación comunal
- **Direcciones**: Múltiples direcciones permitidas
- **Permisos**: Gestión completa de planes y reportes

### **5. 🏢 Director**
- **Descripción**: Gestión de su dirección específica
- **Jerarquía**: Nivel directivo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Gestión completa de su dirección

### **6. 🔄 Subrogante de Director**
- **Descripción**: Funciones del director en ausencia
- **Jerarquía**: Nivel directivo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Mismos que Director

### **7. 📋 Jefatura**
- **Descripción**: Gestión operativa de proyectos
- **Jerarquía**: Nivel operativo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Gestión de proyectos e items

### **8. 🔄 Subrogante de Jefatura**
- **Descripción**: Funciones de jefatura en ausencia
- **Jerarquía**: Nivel operativo por dirección
- **Direcciones**: **UNA SOLA DIRECCIÓN** (regla jerárquica)
- **Permisos**: Mismos que Jefatura

### **9. 🔄 Subrogante de Secretaría Comunal de Planificación**
- **Descripción**: Funciones de SECPLAN en ausencia
- **Jerarquía**: Nivel de planificación comunal
- **Direcciones**: Múltiples direcciones permitidas
- **Permisos**: Mismos que SECPLAN

---

## 🔐 Permisos por Módulo

### **🔑 Autenticación y Usuarios**
```php
// Autenticación
'auth.login'              // Iniciar sesión
'auth.logout'             // Cerrar sesión
'auth.reset_password'     // Restablecer contraseña
'auth.forgot_password'    // Solicitar restablecimiento

// Gestión de usuarios
'users.list'              // Listar usuarios
'users.create'            // Crear usuarios
'users.edit'              // Editar usuarios
'users.delete'            // Eliminar usuarios
'users.view'              // Ver usuarios
'users.reset_password'    // Restablecer contraseña de usuario
'users.update_password'   // Actualizar contraseña propia
'users.update_profile'    // Actualizar perfil propio
'users.profile'           // Ver perfil propio
```

### **🏢 Direcciones**
```php
'directions.list'         // Listar direcciones
'directions.create'       // Crear direcciones
'directions.edit'         // Editar direcciones
'directions.delete'       // Eliminar direcciones
'directions.view'         // Ver direcciones
```

### **📊 Planes de Compra**
```php
'purchase_plans.list'           // Listar planes
'purchase_plans.create'         // Crear planes
'purchase_plans.edit'           // Editar planes
'purchase_plans.delete'         // Eliminar planes
'purchase_plans.view'           // Ver planes
'purchase_plans.approve'        // Aprobar planes
'purchase_plans.reject'         // Rechazar planes
'purchase_plans.send'           // Enviar planes
'purchase_plans.export'         // Exportar planes
'purchase_plans.upload_decreto' // Subir decretos
'purchase_plans.upload_form_f1' // Subir formularios F1
'purchase_plans.by_year'        // Ver planes por año
```

### **📋 Estados de Planes de Compra**
```php
'purchase_plan_statuses.list'    // Listar estados
'purchase_plan_statuses.create'  // Crear estados
'purchase_plan_statuses.edit'    // Editar estados
'purchase_plan_statuses.delete'  // Eliminar estados
'purchase_plan_statuses.view'    // Ver estados
'purchase_plan_statuses.history' // Ver historial
'purchase_plan_statuses.current' // Ver estado actual
```

### **🏗️ Proyectos**
```php
'projects.list'                  // Listar proyectos
'projects.create'                // Crear proyectos
'projects.edit'                  // Editar proyectos
'projects.delete'                // Eliminar proyectos
'projects.view'                  // Ver proyectos
'projects.by_purchase_plan'      // Ver por plan de compra
'projects.by_token'              // Ver por token
'projects.verification'          // Verificar proyectos
'projects.verification_files'    // Archivos de verificación
'projects.verification_download' // Descargar verificación
'projects.verification_delete'   // Eliminar verificación
```

### **📦 Items de Compra**
```php
'item_purchases.list'            // Listar items
'item_purchases.create'          // Crear items
'item_purchases.edit'            // Editar items
'item_purchases.delete'          // Eliminar items
'item_purchases.view'            // Ver items
'item_purchases.update_status'   // Actualizar estado
'item_purchases.export'          // Exportar items
```

### **💰 Configuraciones del Sistema**
```php
// Asignaciones presupuestarias
'budget_allocations.list'        // Listar asignaciones
'budget_allocations.create'      // Crear asignaciones
'budget_allocations.edit'        // Editar asignaciones
'budget_allocations.delete'      // Eliminar asignaciones
'budget_allocations.view'        // Ver asignaciones

// Tipos de compra
'type_purchases.list'            // Listar tipos
'type_purchases.create'          // Crear tipos
'type_purchases.edit'            // Editar tipos
'type_purchases.delete'          // Eliminar tipos
'type_purchases.view'            // Ver tipos

// Tipos de proyecto
'type_projects.list'             // Listar tipos
'type_projects.create'           // Crear tipos
'type_projects.edit'             // Editar tipos
'type_projects.delete'           // Eliminar tipos
'type_projects.view'             // Ver tipos

// Unidades de compra
'unit_purchasings.list'          // Listar unidades
'unit_purchasings.create'        // Crear unidades
'unit_purchasings.edit'          // Editar unidades
'unit_purchasings.delete'        // Eliminar unidades
'unit_purchasings.view'          // Ver unidades

// Estados de items
'status_item_purchases.list'     // Listar estados
'status_item_purchases.create'   // Crear estados
'status_item_purchases.edit'     // Editar estados
'status_item_purchases.delete'   // Eliminar estados
'status_item_purchases.view'     // Ver estados

// Estados de planes
'status_purchase_plans.list'     // Listar estados
'status_purchase_plans.create'   // Crear estados
'status_purchase_plans.edit'     // Editar estados
'status_purchase_plans.delete'   // Eliminar estados
'status_purchase_plans.view'     // Ver estados
```

### **📁 Archivos y Documentos**
```php
'files.list'                     // Listar archivos
'files.create'                   // Crear archivos
'files.edit'                     // Editar archivos
'files.delete'                   // Eliminar archivos
'files.view'                     // Ver archivos
'files.upload'                   // Subir archivos
'files.download'                 // Descargar archivos

'form_f1.list'                   // Listar formularios F1
'form_f1.create'                 // Crear formularios F1
'form_f1.edit'                   // Editar formularios F1
'form_f1.delete'                 // Eliminar formularios F1
'form_f1.view'                   // Ver formularios F1
'form_f1.download'               // Descargar formularios F1
```

### **📈 Historial y Auditoría**
```php
'history_purchase_histories.list'      // Listar historial
'history_purchase_histories.view'      // Ver historial
'history_purchase_histories.statistics' // Estadísticas
'history_purchase_histories.export'    // Exportar historial

'audit.logs'                           // Logs de auditoría
'audit.history'                        // Historial de auditoría
```

### **📊 Reportes**
```php
'reports.view'                         // Ver reportes
'reports.export'                       // Exportar reportes
'reports.purchase_plans'               // Reportes de planes
'reports.projects'                     // Reportes de proyectos
'reports.item_purchases'               // Reportes de items
'reports.budget_analysis'              // Análisis presupuestario
```

---

## 🛡️ Reglas de Negocio Específicas

### **1. Usuarios Jerárquicos (Una Dirección)**
```php
const HIERARCHICAL_ROLES = [
    'Director',
    'Subrogante de Director', 
    'Jefatura',
    'Subrogante de Jefatura'
];
```

**Regla**: Estos usuarios **SOLO pueden pertenecer a UNA dirección**

### **2. Usuarios Multi-Dirección**
```php
const MULTI_DIRECTION_ROLES = [
    'Administrador del Sistema',
    'Administrador Municipal',
    'Secretaría Comunal de Planificación',
    'Subrogante de Secretaría Comunal de Planificación'
];
```

**Regla**: Estos usuarios pueden pertenecer a **MÚLTIPLES direcciones**

### **3. Validaciones Automáticas**
- **Middleware `validate.hierarchical.user`**: Valida reglas al crear/editar usuarios
- **Middleware `CheckDirectionPermission`**: Verifica acceso a direcciones específicas
- **Validación en tiempo real**: Previene asignaciones incorrectas

---

## 📊 Matriz de Permisos por Rol

| Rol | Autenticación | Usuarios | Direcciones | Planes | Proyectos | Items | Configuración | Archivos | Reportes |
|-----|---------------|----------|-------------|--------|-----------|-------|---------------|----------|----------|
| **Administrador del Sistema** | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Administrador Municipal** | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | 🔒 Solo lectura | ✅ Total | ✅ Total |
| **Visador Admin** | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | 🔒 Solo lectura | ✅ Total | ✅ Total |
| **SECPLAN** | ✅ Total | ❌ Sin acceso | ❌ Sin acceso | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | 🔒 Solo lectura | ✅ Total | ✅ Total |
| **Director** | ✅ Total | 🔒 Solo perfil | 🔒 Solo su dirección | ✅ Su dirección | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura | ✅ Total | ✅ Básicos |
| **Subrogante Director** | ✅ Total | 🔒 Solo perfil | 🔒 Solo su dirección | ✅ Su dirección | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura | ✅ Total | ✅ Básicos |
| **Jefatura** | ✅ Total | 🔒 Solo perfil | 🔒 Solo su dirección | 🔒 Solo lectura | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura | ✅ Total | ✅ Básicos |
| **Subrogante Jefatura** | ✅ Total | 🔒 Solo perfil | 🔒 Solo su dirección | 🔒 Solo lectura | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura | ✅ Total | ✅ Básicos |

---

## 🔧 Middleware Implementados

### **1. ValidateHierarchicalUserDirection**
**Propósito**: Valida reglas de negocio para usuarios jerárquicos

**Funcionalidades**:
- ✅ Valida que usuarios jerárquicos solo pertenezcan a una dirección
- ✅ Permite múltiples direcciones solo a administradores y SECPLAN
- ✅ Previene asignaciones incorrectas en creación/edición de usuarios

**Rutas protegidas**:
```php
'directions.assign-users',
'directions.assign-director', 
'users.store',
'users.update'
```

### **2. CheckDirectionPermission**
**Propósito**: Verifica permisos específicos y acceso a direcciones

**Funcionalidades**:
- ✅ Permite todo a administradores del sistema
- ✅ Permite todo a administradores municipales
- ✅ Verifica permisos específicos para otros usuarios
- ✅ Valida acceso a direcciones específicas

### **3. Middleware de Spatie**
**Propósito**: Control de roles y permisos estándar

**Tipos**:
- `role`: Verifica roles específicos
- `permission`: Verifica permisos específicos
- `role_or_permission`: Verifica roles O permisos

---

## 🚀 Optimizaciones Implementadas

### **1. Cache de Permisos**
```php
'cache' => [
    'expiration_time' => \DateInterval::createFromDateString('24 hours'),
    'key' => 'spatie.permission.cache',
    'store' => 'default',
]
```

### **2. Middleware Registrado**
```php
'register_permission_check_method' => true
```

### **3. Configuración de Seguridad**
```php
'display_permission_in_exception' => false,
'display_role_in_exception' => false,
'enable_wildcard_permission' => false
```

---

## 📋 Comandos Artisan Disponibles

### **1. Verificar Permisos de Usuario**
```bash
php artisan check:user-permissions {email}
```

### **2. Validar Relaciones Director-Dirección**
```bash
php artisan validate:director-direction-relations
```

### **3. Validar Usuarios Jerárquicos**
```bash
php artisan validate:hierarchical-user-directions
```

### **4. Mostrar Reglas de Dirección**
```bash
php artisan show:direction-rules
```

---

## 🔍 Puntos de Atención

### **1. Seguridad Robusta**
- ✅ Autenticación obligatoria para todas las rutas protegidas
- ✅ Autorización granular por permisos
- ✅ Validación de reglas de negocio
- ✅ Protección de recursos por dirección

### **2. Flexibilidad**
- ✅ Roles jerárquicos con restricciones específicas
- ✅ Administradores con acceso total
- ✅ Permisos granulares por funcionalidad

### **3. Mantenibilidad**
- ✅ Middleware reutilizables
- ✅ Separación clara de responsabilidades
- ✅ Fácil agregar nuevos permisos

---

## 📈 Recomendaciones

### **1. Monitoreo**
- Implementar logging de accesos denegados
- Monitorear intentos de acceso no autorizado
- Registrar cambios en permisos y roles

### **2. Auditoría**
- Mantener historial de cambios de permisos
- Registrar acciones críticas (eliminaciones, aprobaciones)
- Implementar alertas para acciones sensibles

### **3. Testing**
- Crear tests para cada nivel de middleware
- Validar reglas de negocio en tests automatizados
- Probar casos edge de permisos

---

## ✅ Conclusión

El sistema implementa una **arquitectura de seguridad robusta y bien estructurada** que:

1. **Protege todos los recursos** con múltiples capas de seguridad
2. **Implementa reglas de negocio** específicas del dominio municipal
3. **Permite flexibilidad** en la asignación de permisos
4. **Facilita el mantenimiento** con middleware reutilizables
5. **Garantiza la integridad** de los datos y operaciones

La implementación está **lista para producción** y proporciona una base sólida para la seguridad del sistema de planes de compra municipal. 