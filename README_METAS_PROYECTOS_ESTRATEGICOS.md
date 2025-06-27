# Sistema de Metas para Proyectos Estratégicos

## 📋 Resumen

Se ha implementado un sistema completo de **metas** que funciona exclusivamente con **proyectos de tipo estratégico**. Las metas permiten definir objetivos específicos, monitorear el progreso y obtener estadísticas detalladas de cumplimiento.

---

## 🎯 Características Principales

### **Restricción por Tipo de Proyecto**
- ✅ Las metas **SOLO** se pueden crear en proyectos de tipo **"Estratégico"**
- ❌ Los proyectos de tipo **"Operativo"** **NO** pueden tener metas
- ✅ Validación automática en todas las operaciones

### **Campos de Meta**
- **Nombre**: Título descriptivo de la meta
- **Descripción**: Detalle opcional de la meta
- **Valor Meta**: Cantidad objetivo a alcanzar (numérico)
- **Unidad de Medida**: Unidad (ej: "unidades", "porcentaje", "kilómetros")
- **Valor Actual**: Progreso actual alcanzado
- **Fecha Meta**: Fecha límite para cumplir la meta
- **Estado**: Pendiente, En Progreso, Completada, Cancelada
- **Notas**: Observaciones y comentarios

### **Estados Automáticos**
- **Pendiente**: Estado inicial
- **En Progreso**: Cuando se actualiza el progreso
- **Completada**: Cuando se alcanza el valor meta
- **Cancelada**: Meta cancelada manualmente

---

## 🔐 Permisos por Rol

### **Administradores (Sistema y Municipal)**
- ✅ Crear, editar, eliminar metas
- ✅ Ver todas las metas
- ✅ Actualizar progreso
- ✅ Ver estadísticas completas

### **Directores y Subrogantes**
- ✅ Gestión completa de metas de sus proyectos
- ✅ Crear, editar, eliminar metas
- ✅ Actualizar progreso
- ✅ Ver estadísticas

### **Jefaturas y Subrogantes**
- ✅ Ver metas de sus proyectos
- ✅ Actualizar progreso
- ✅ Ver estadísticas básicas
- ❌ No pueden crear o eliminar metas

---

## 📡 API Endpoints

### **Gestión de Metas**

#### **Listar Metas**
```http
GET /api/goals?project_id={id}&status={status}&query={search}
```

**Respuesta:**
```json
{
  "message": "Metas obtenidas exitosamente",
  "data": [
    {
      "id": 1,
      "name": "Incrementar eficiencia energética",
      "description": "Reducir consumo de energía en 20%",
      "target_value": 20.00,
      "unit_measure": "porcentaje",
      "current_value": 15.50,
      "target_date": "2024-12-31",
      "status": "en_progreso",
      "status_label": "En Progreso",
      "progress_percentage": 77.50,
      "is_completed": false,
      "is_overdue": false,
      "days_remaining": 45,
      "project": {
        "id": 5,
        "name": "Modernización de Edificios Municipales",
        "type": "Estratégico"
      }
    }
  ],
  "pagination": {...}
}
```

#### **Crear Meta**
```http
POST /api/goals
Content-Type: application/json

{
  "name": "Incrementar eficiencia energética",
  "description": "Reducir consumo de energía en 20%",
  "target_value": 20.00,
  "unit_measure": "porcentaje",
  "target_date": "2024-12-31",
  "project_id": 5
}
```

#### **Actualizar Meta**
```http
PUT /api/goals/{id}
Content-Type: application/json

{
  "name": "Incrementar eficiencia energética",
  "description": "Reducir consumo de energía en 25%",
  "target_value": 25.00,
  "status": "en_progreso"
}
```

#### **Actualizar Progreso**
```http
PUT /api/goals/{id}/progress
Content-Type: application/json

{
  "current_value": 18.50,
  "notes": "Se instalaron nuevos paneles solares"
}
```

#### **Eliminar Meta**
```http
DELETE /api/goals/{id}
```

### **Estadísticas y Reportes**

#### **Estadísticas por Proyecto**
```http
GET /api/goals/project/{projectId}/statistics
```

**Respuesta:**
```json
{
  "message": "Estadísticas obtenidas exitosamente",
  "data": {
    "project_id": 5,
    "project_name": "Modernización de Edificios",
    "project_type": "Estratégico",
    "total_goals": 4,
    "completed_goals": 2,
    "in_progress_goals": 2,
    "pending_goals": 0,
    "cancelled_goals": 0,
    "overdue_goals": 1,
    "average_progress": 65.25,
    "completion_percentage": 50.00
  }
}
```

#### **Metas Vencidas**
```http
GET /api/goals/overdue?project_id={id}
```

---

## 📊 Integración con Proyectos

### **ProjectResource Actualizado**

Los proyectos estratégicos ahora incluyen información de metas:

```json
{
  "id": 5,
  "name": "Modernización de Edificios Municipales",
  "is_strategic": true,
  "goals": [
    {
      "id": 1,
      "name": "Incrementar eficiencia energética",
      "progress_percentage": 77.50,
      "status": "en_progreso"
    }
  ],
  "goal_statistics": {
    "total_goals": 4,
    "completed_goals": 2,
    "average_progress": 65.25
  }
}
```

### **Métodos Nuevos en Project Model**

```php
// Verificar si es estratégico
$project->isStrategic(); // true/false

// Obtener metas (solo si es estratégico)
$project->getGoals(); // Collection de metas

// Progreso promedio de metas
$project->getAverageGoalProgress(); // 65.25

// Número de metas completadas
$project->getCompletedGoalsCount(); // 2
```

---

## 🚀 Comandos de Instalación

### **1. Ejecutar Migración**
```bash
php artisan migrate
```

### **2. Ejecutar Seeder de Permisos**
```bash
php artisan db:seed --class=GoalPermissionSeeder
```

### **3. Limpiar Cache de Permisos**
```bash
php artisan permission:cache-reset
```

---

## 📝 Validaciones Implementadas

### **Middleware `ValidateStrategicProject`**
- ✅ Valida que solo proyectos estratégicos puedan tener metas
- ✅ Bloquea creación de metas en proyectos operativos
- ✅ Devuelve mensajes de error específicos

### **Validaciones de Formulario**
- **Nombre**: Requerido, máximo 255 caracteres
- **Valor Meta**: Numérico, mayor a 0
- **Fecha Meta**: Fecha válida, posterior a hoy (en creación)
- **Proyecto**: Debe existir y ser estratégico

---

## 🎨 Ejemplos de Uso Prácticos

### **Ejemplo 1: Meta de Eficiencia**
```json
{
  "name": "Reducir tiempo de trámites",
  "description": "Disminuir tiempo promedio de atención al público",
  "target_value": 15.00,
  "unit_measure": "minutos",
  "target_date": "2024-06-30",
  "project_id": 5
}
```

### **Ejemplo 2: Meta de Capacitación**
```json
{
  "name": "Capacitar personal administrativo",
  "description": "Capacitar al 100% del personal en nuevos procedimientos",
  "target_value": 100.00,
  "unit_measure": "porcentaje",
  "target_date": "2024-08-31",
  "project_id": 7
}
```

### **Ejemplo 3: Meta de Infraestructura**
```json
{
  "name": "Reparar luminarias públicas",
  "description": "Reparar todas las luminarias dañadas del sector norte",
  "target_value": 45.00,
  "unit_measure": "unidades",
  "target_date": "2024-12-15",
  "project_id": 9
}
```

---

## 📈 Métricas Automáticas

### **Cálculos Automáticos**
- **Porcentaje de Progreso**: `(valor_actual / valor_meta) * 100`
- **Estado de Vencimiento**: Automático según fecha meta
- **Días Restantes**: Calculado desde la fecha actual
- **Estado de Completitud**: Automático al alcanzar el valor meta

### **Estadísticas por Proyecto**
- Total de metas
- Metas por estado
- Promedio de progreso
- Porcentaje de completitud
- Metas vencidas

---

## ⚠️ Consideraciones Importantes

### **Solo Proyectos Estratégicos**
- ❌ **NO** se pueden crear metas en proyectos operativos
- ✅ El sistema valida automáticamente el tipo de proyecto
- ✅ Mensajes de error claros al intentar operaciones inválidas

### **Permisos por Dirección**
- Los usuarios solo pueden gestionar metas de proyectos de su dirección
- Se mantienen las mismas reglas de acceso que para proyectos

### **Estados Automáticos**
- El estado se actualiza automáticamente según el progreso
- No es necesario cambiar manualmente el estado a "Completada"

---

## 🔮 Posibles Extensiones Futuras

### **Dashboard de Metas**
- Gráficos de progreso en tiempo real
- Alertas para metas próximas a vencer
- Comparativas entre proyectos

### **Notificaciones**
- Emails automáticos para metas vencidas
- Recordatorios de fechas próximas
- Notificaciones de completitud

### **Reportes Avanzados**
- Exportación de metas en Excel/PDF
- Reportes comparativos por período
- Análisis de tendencias de cumplimiento

---

## ✅ Resumen de Implementación

1. ✅ **Modelo Goal** con todas las relaciones y métodos
2. ✅ **Migración** de base de datos con campos completos
3. ✅ **Controlador** con CRUD completo y estadísticas
4. ✅ **Servicio** con lógica de negocio específica
5. ✅ **Resource** para serialización JSON
6. ✅ **Middleware** de validación de proyectos estratégicos
7. ✅ **Rutas API** protegidas con permisos
8. ✅ **Permisos** granulares por rol
9. ✅ **Integración** con modelo Project existente
10. ✅ **Documentación** completa de uso

El sistema está **listo para uso inmediato** y se integra perfectamente con la arquitectura existente del proyecto. 🚀 