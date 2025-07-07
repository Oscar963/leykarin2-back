# Roles y Permisos Simplificados

## Resumen de Cambios

Se ha simplificado el sistema de roles y permisos de 9 roles complejos a solo 2 roles principales:

### Roles Anteriores (Eliminados)
- Administrador Municipal
- Visador o de Administrador Municipal
- Director
- Subrogante de Director
- Jefatura
- Subrogante de Jefatura
- Encargado de Presupuestos
- Subrogante de Encargado de Presupuestos

### Nuevos Roles

## 1. Administrador del Sistema

**Descripción:** Tiene acceso completo a todas las funcionalidades del sistema.

**Permisos:**
- **Acceso Total:** Todos los permisos del sistema
- **Gestión de Usuarios:** Crear, editar, eliminar, ver usuarios
- **Gestión de Roles y Permisos:** Asignar roles, gestionar permisos
- **Configuraciones:** Acceso completo a todas las configuraciones
- **Auditoría:** Acceso completo a logs y historial
- **Reportes:** Todos los reportes disponibles

**Usuarios de Prueba:**
- Email: `admin.sistema@demo.com`
- Contraseña: `password123`

## 2. Gestor de Contenido

**Descripción:** Encargado de gestionar el contenido del sistema con permisos limitados.

**Permisos:**

### Autenticación y Perfil
- `auth.login` - Iniciar sesión
- `auth.logout` - Cerrar sesión
- `users.update_profile` - Actualizar perfil propio
- `users.profile` - Ver perfil propio
- `users.update_password` - Cambiar contraseña propia

### Planes de Compra (Gestión Completa)
- `purchase_plans.list` - Listar planes de compra
- `purchase_plans.create` - Crear planes de compra
- `purchase_plans.edit` - Editar planes de compra
- `purchase_plans.view` - Ver planes de compra
- `purchase_plans.send` - Enviar planes de compra
- `purchase_plans.export` - Exportar planes de compra
- `purchase_plans.by_year` - Filtrar por año
- `purchase_plans.upload_decreto` - Subir decretos

### Estados de Planes de Compra
- `purchase_plan_statuses.list` - Listar estados
- `purchase_plan_statuses.view` - Ver estados
- `purchase_plan_statuses.current` - Ver estado actual

### Proyectos (Gestión Completa)
- `projects.list` - Listar proyectos
- `projects.create` - Crear proyectos
- `projects.edit` - Editar proyectos
- `projects.view` - Ver proyectos
- `projects.by_purchase_plan` - Filtrar por plan de compra

### Items de Compra (Gestión Completa)
- `item_purchases.list` - Listar items
- `item_purchases.create` - Crear items
- `item_purchases.edit` - Editar items
- `item_purchases.view` - Ver items
- `item_purchases.update_status` - Actualizar estado
- `item_purchases.export` - Exportar items

### Configuraciones (Solo Lectura)
- `budget_allocations.list` - Listar asignaciones presupuestarias
- `budget_allocations.view` - Ver asignaciones presupuestarias
- `type_purchases.list` - Listar tipos de compra
- `type_purchases.view` - Ver tipos de compra
- `type_projects.list` - Listar tipos de proyecto
- `type_projects.view` - Ver tipos de proyecto
- `unit_purchasings.list` - Listar unidades de compra
- `unit_purchasings.view` - Ver unidades de compra
- `status_item_purchases.list` - Listar estados de items
- `status_item_purchases.view` - Ver estados de items
- `status_purchase_plans.list` - Listar estados de planes
- `status_purchase_plans.view` - Ver estados de planes

### Historial (Solo Lectura)
- `history_purchase_histories.list` - Listar historial
- `history_purchase_histories.view` - Ver historial
- `history_purchase_histories.statistics` - Ver estadísticas

### Reportes Básicos
- `reports.view` - Ver reportes
- `reports.purchase_plans` - Reportes de planes de compra
- `reports.projects` - Reportes de proyectos
- `reports.item_purchases` - Reportes de items de compra

**Usuarios de Prueba:**
- Email: `gestor.contenido@demo.com`
- Contraseña: `password123`
- Email: `gestor.contenido2@demo.com`
- Contraseña: `password123`

## Permisos NO Asignados al Gestor de Contenido

El Gestor de Contenido **NO** tiene acceso a:

### Gestión de Usuarios
- Crear, editar, eliminar otros usuarios
- Resetear contraseñas de otros usuarios
- Asignar roles a usuarios

### Gestión de Roles y Permisos
- Crear, editar, eliminar roles
- Asignar permisos a roles
- Gestionar permisos del sistema

### Configuraciones Avanzadas
- Crear, editar, eliminar configuraciones del sistema
- Modificar asignaciones presupuestarias
- Modificar tipos de compra, proyecto, etc.

### Auditoría Completa
- Logs de auditoría completos
- Historial de auditoría completo

### Reportes Avanzados
- Reportes de análisis presupuestario
- Exportación de reportes avanzados

### Funciones de Aprobación
- Visar planes de compra
- Aprobar planes de compra
- Rechazar planes de compra

## Implementación

Para aplicar estos cambios:

1. **Ejecutar migraciones:**
   ```bash
   php artisan migrate:fresh
   ```

2. **Ejecutar seeders:**
   ```bash
   php artisan db:seed --class=RoleSeeder
   php artisan db:seed --class=PermissionSeeder
   php artisan db:seed --class=UserSeeder
   ```

3. **O ejecutar todos los seeders:**
   ```bash
   php artisan db:seed
   ```

## Ventajas de la Simplificación

1. **Mayor Claridad:** Solo 2 roles fáciles de entender
2. **Mantenimiento Simplificado:** Menos complejidad en la gestión de permisos
3. **Seguridad Mejorada:** Menos puntos de confusión en la asignación de roles
4. **Escalabilidad:** Fácil agregar nuevos permisos específicos según necesidades
5. **Testing Simplificado:** Menos casos de prueba para roles y permisos

## Consideraciones de Seguridad

- El **Administrador del Sistema** debe ser asignado solo a usuarios de confianza
- El **Gestor de Contenido** puede ser asignado a múltiples usuarios según necesidades
- Todos los cambios en roles y permisos quedan registrados en el log de auditoría
- Se recomienda revisar periódicamente las asignaciones de roles 