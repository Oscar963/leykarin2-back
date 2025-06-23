# Reglas de Dirección del Sistema

## 📋 Resumen de Reglas

El sistema implementa reglas específicas para controlar qué usuarios pueden pertenecer a múltiples direcciones y cuáles deben pertenecer únicamente a una.

### 🔒 Roles con Dirección Única

Los siguientes roles **SOLO** pueden pertenecer a **UNA** dirección:

- **Director**
- **Subrogante de Director**
- **Jefatura**
- **Subrogante de Jefatura**

### 🔓 Roles con Múltiples Direcciones

Los siguientes roles pueden pertenecer a **MÚLTIPLES** direcciones:

- **Administrador del Sistema**
- **Administrador Municipal**
- **Secretaría Comunal de Planificación**
- **Subrogante de Secretaría Comunal de Planificación**
- **Visador o de Administrador Municipal** (y otros roles no jerárquicos)

## 🛠️ Implementación Técnica

### Modelo User

```php
// Roles que deben pertenecer únicamente a una dirección
const HIERARCHICAL_ROLES = [
    'Director',
    'Subrogante de Director',
    'Jefatura',
    'Subrogante de Jefatura'
];

// Roles que pueden tener múltiples direcciones
const MULTI_DIRECTION_ROLES = [
    'Administrador del Sistema',
    'Administrador Municipal',
    'Secretaría Comunal de Planificación',
    'Subrogante de Secretaría Comunal de Planificación'
];
```

### Métodos del Modelo

- `hasHierarchicalRole()`: Verifica si el usuario tiene roles jerárquicos
- `canBelongToMultipleDirections()`: Verifica si puede tener múltiples direcciones
- `validateDirectionAssignment()`: Valida las reglas de asignación
- `assignDirection()`: Asigna una dirección con validación
- `assignDirections()`: Asigna múltiples direcciones con validación

### Middleware de Validación

El middleware `ValidateHierarchicalUserDirection` se aplica automáticamente a:

- Creación/edición de usuarios (`users.store`, `users.update`)
- Asignación de usuarios a direcciones (`directions.assign-users`)

### Validaciones Implementadas

1. **Al crear/editar usuarios**: Verifica que usuarios con roles jerárquicos no se asignen a múltiples direcciones
2. **Al asignar usuarios a direcciones**: Verifica que usuarios jerárquicos no pertenezcan ya a otra dirección
3. **Excepciones**: Los administradores y secretaría comunal pueden tener múltiples direcciones

## 🔧 Comandos de Utilidad

### Validar Usuarios Jerárquicos

```bash
# Solo mostrar violaciones
php artisan users:validate-hierarchical-directions

# Modo dry-run (sin corregir)
php artisan users:validate-hierarchical-directions --dry-run

# Corregir automáticamente
php artisan users:validate-hierarchical-directions --fix
```

### Mostrar Reglas del Sistema

```bash
php artisan directions:show-rules
```

### Verificar Relaciones Director-Dirección

```bash
php artisan directors:show-relations
```

### Verificar Permisos de Usuario

```bash
php artisan user:check-permissions {email}
```

## 📊 Ejemplos de Validación

### ✅ Casos Válidos

1. **Director con una dirección**:
   - Usuario: Juan Pérez
   - Rol: Director
   - Dirección: Alcaldía
   - ✅ **VÁLIDO**

2. **Administrador Municipal con múltiples direcciones**:
   - Usuario: María González
   - Rol: Administrador Municipal
   - Direcciones: Alcaldía, SECPLAN, DAF
   - ✅ **VÁLIDO**

3. **Secretaría Comunal con múltiples direcciones**:
   - Usuario: Carlos Silva
   - Rol: Secretaría Comunal de Planificación
   - Direcciones: SECPLAN, DAF, DOM
   - ✅ **VÁLIDO**

### ❌ Casos Inválidos

1. **Director con múltiples direcciones**:
   - Usuario: Ana López
   - Rol: Director
   - Direcciones: Alcaldía, SECPLAN
   - ❌ **INVÁLIDO** - Se corregirá automáticamente

2. **Jefatura con múltiples direcciones**:
   - Usuario: Pedro Ramírez
   - Rol: Jefatura
   - Direcciones: DAF, DOM
   - ❌ **INVÁLIDO** - Se corregirá automáticamente

## 🔄 Corrección Automática

El sistema incluye un seeder (`FixHierarchicalUserDirectionsSeeder`) que:

1. Identifica usuarios jerárquicos con múltiples direcciones
2. Mantiene la primera dirección asignada
3. Remueve las direcciones adicionales
4. Registra las correcciones realizadas

### Ejecutar Corrección

```bash
# Ejecutar seeder específico
php artisan db:seed --class=FixHierarchicalUserDirectionsSeeder

# Ejecutar todos los seeders (incluye corrección)
php artisan db:seed
```

## 🚨 Mensajes de Error

### Al Crear/Editar Usuario

```
Los usuarios con roles jerárquicos (Director, Subrogante de Director, Jefatura, Subrogante de Jefatura) solo pueden pertenecer a una dirección. Los administradores y secretaría comunal de planificación pueden tener múltiples direcciones.
```

### Al Asignar Usuario a Dirección

```
El usuario Juan Pérez tiene roles jerárquicos y ya pertenece a otra dirección. Los usuarios con roles jerárquicos (Director, Subrogante de Director, Jefatura, Subrogante de Jefatura) solo pueden pertenecer a una dirección. Los administradores y secretaría comunal de planificación pueden tener múltiples direcciones.
```

## 📈 Beneficios de la Implementación

1. **Integridad Organizacional**: Mantiene la jerarquía correcta
2. **Prevención de Errores**: Valida automáticamente las asignaciones
3. **Flexibilidad**: Permite múltiples direcciones para roles administrativos
4. **Auditoría**: Comandos para verificar el estado del sistema
5. **Corrección Automática**: Herramientas para corregir violaciones

## 🔮 Consideraciones Futuras

- Monitoreo automático de violaciones
- Notificaciones cuando se detecten violaciones
- Dashboard para visualizar relaciones director-dirección
- Reportes de cumplimiento de reglas 