# 📊 TABLA COMPLETA DE ROLES Y PERMISOS - SISTEMA DE PLANES DE COMPRA

## 👥 ROLES DEL SISTEMA

| **ROL** | **DESCRIPCIÓN** | **NIVEL JERÁRQUICO** |
|---------|-----------------|---------------------|
| **Administrador del Sistema** | Control total del sistema | Máximo |
| **Administrador Municipal** | Gestión municipal completa | Alto |
| **Visador o de Administrador Municipal** | Aprobación y visación | Medio-Alto |
| **Encargado de Presupuestos** | Gestión de Formularios F1 | Especializado |
| **Subrogante de Encargado de Presupuestos** | Suplencia de presupuestos | Especializado |
| **Director** | Gestión de su dirección | Medio |
| **Subrogante de Director** | Suplencia de director | Medio |
| **Jefatura** | Gestión de su área | Bajo-Medio |
| **Subrogante de Jefatura** | Suplencia de jefatura | Bajo-Medio |

---

## 🔐 PERMISOS POR MÓDULO

### 📋 DASHBOARD
| **ROL** | **VER** | **EXPORTAR** | **CONFIGURAR** |
|---------|---------|--------------|----------------|
| Administrador del Sistema | ✅ | ✅ | ✅ |
| Administrador Municipal | ✅ | ✅ | ❌ |
| Visador | ✅ | ✅ | ❌ |
| Encargado de Presupuestos | ❌ | ❌ | ❌ |
| Subrogante de Encargado | ❌ | ❌ | ❌ |
| Director | ✅ | ✅ | ❌ |
| Subrogante de Director | ✅ | ✅ | ❌ |
| Jefatura | ✅ | ✅ | ❌ |
| Subrogante de Jefatura | ✅ | ✅ | ❌ |

### 📝 PLANES DE COMPRA
| **ROL** | **CREAR** | **EDITAR** | **ELIMINAR** | **VER** | **VISAR** | **APROBAR** | **RECHAZAR** | **ENVIAR** | **EXPORTAR** |
|---------|-----------|------------|--------------|---------|-----------|-------------|--------------|------------|--------------|
| Administrador del Sistema | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Administrador Municipal | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Visador | ❌ | ❌ | ❌ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Encargado de Presupuestos | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Encargado | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Director | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Subrogante de Director | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Jefatura | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Jefatura | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

### 🏗️ PROYECTOS
| **ROL** | **CREAR** | **EDITAR** | **ELIMINAR** | **VER** | **VERIFICAR** |
|---------|-----------|------------|--------------|---------|---------------|
| Administrador del Sistema | ✅ | ✅ | ✅ | ✅ | ✅ |
| Administrador Municipal | ✅ | ✅ | ✅ | ✅ | ❌ |
| Visador | ✅ | ✅ | ✅ | ✅ | ❌ |
| Encargado de Presupuestos | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Encargado | ❌ | ❌ | ❌ | ❌ | ❌ |
| Director | ✅ | ✅ | ✅ | ✅ | ✅ |
| Subrogante de Director | ✅ | ✅ | ❌ | ✅ | ✅ |
| Jefatura | ✅ | ✅ | ❌ | ✅ | ✅ |
| Subrogante de Jefatura | ✅ | ✅ | ❌ | ✅ | ✅ |

### 📄 FORMULARIO F1
| **ROL** | **CREAR** | **EDITAR** | **ELIMINAR** | **VER** | **CARGAR** | **DESCARGAR** | **REMOVER** |
|---------|-----------|------------|--------------|---------|------------|---------------|-------------|
| Administrador del Sistema | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Administrador Municipal | ❌ | ❌ | ❌ | ✅ | ❌ | ✅ | ❌ |
| Visador | ❌ | ❌ | ❌ | ✅ | ❌ | ✅ | ❌ |
| Encargado de Presupuestos | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Subrogante de Encargado | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Director | ❌ | ❌ | ❌ | ✅ | ❌ | ✅ | ❌ |
| Subrogante de Director | ❌ | ❌ | ❌ | ✅ | ❌ | ✅ | ❌ |
| Jefatura | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Jefatura | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

### 👤 USUARIOS
| **ROL** | **CREAR** | **EDITAR** | **ELIMINAR** | **VER** | **RESET PASSWORD** |
|---------|-----------|------------|--------------|---------|--------------------|
| Administrador del Sistema | ✅ | ✅ | ✅ | ✅ | ✅ |
| Administrador Municipal | ❌ | ❌ | ❌ | ❌ | ❌ |
| Visador | ❌ | ❌ | ❌ | ❌ | ❌ |
| Encargado de Presupuestos | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Encargado | ❌ | ❌ | ❌ | ❌ | ❌ |
| Director | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Director | ❌ | ❌ | ❌ | ❌ | ❌ |
| Jefatura | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Jefatura | ❌ | ❌ | ❌ | ❌ | ❌ |

### 📄 ARCHIVOS
| **ROL** | **CREAR** | **EDITAR** | **ELIMINAR** | **VER** | **CARGAR** | **DESCARGAR** |
|---------|-----------|------------|--------------|---------|------------|---------------|
| Administrador del Sistema | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Administrador Municipal | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Visador | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Encargado de Presupuestos | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Encargado | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Director | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Director | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Jefatura | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Jefatura | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

### 🏢 DIRECCIONES
| **ROL** | **CREAR** | **EDITAR** | **ELIMINAR** | **VER** |
|---------|-----------|------------|--------------|---------|
| Administrador del Sistema | ✅ | ✅ | ✅ | ✅ |
| Administrador Municipal | ✅ | ✅ | ✅ | ✅ |
| Visador | ❌ | ❌ | ❌ | ❌ |
| Encargado de Presupuestos | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Encargado | ❌ | ❌ | ❌ | ❌ |
| Director | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Director | ❌ | ❌ | ❌ | ❌ |
| Jefatura | ❌ | ❌ | ❌ | ❌ |
| Subrogante de Jefatura | ❌ | ❌ | ❌ | ❌ |

---

## 📊 RESUMEN DE ACCESOS POR ROL

### 🔴 **Administrador del Sistema**
- **Acceso completo** a todos los módulos
- **Permisos totales** en todas las funcionalidades
- **Gestión de usuarios** y configuración del sistema
- **Auditoría** y logs del sistema

### 🟠 **Administrador Municipal**
- **Planes de Compra**: CRUD completo + Visar/Aprobar/Rechazar/Enviar
- **Proyectos**: CRUD completo
- **Usuarios**: CRUD completo
- **Direcciones**: CRUD completo
- **Formulario F1**: Solo ver y descargar
- **Dashboard**: Ver y exportar

### 🟡 **Visador**
- **Planes de Compra**: Solo ver, visar, rechazar y exportar
- **Proyectos**: CRUD completo
- **Formulario F1**: Solo ver y descargar
- **Dashboard**: Ver y exportar
- **No puede crear, editar, eliminar o aprobar planes**

### 🟢 **Encargado de Presupuestos**
- **Formulario F1**: CRUD completo + Cargar/Descargar
- **Acceso exclusivo** al módulo de presupuestos
- **Sin acceso** a otros módulos

### 🔵 **Subrogante de Encargado de Presupuestos**
- **Mismos permisos** que Encargado de Presupuestos
- **Formulario F1**: CRUD completo + Cargar/Descargar

### 🟣 **Director**
- **Planes de Compra**: CRUD (excepto eliminar) + Enviar
- **Proyectos**: CRUD completo + Verificar
- **Formulario F1**: CRUD completo + Cargar/Descargar
- **Archivos**: CRUD (excepto eliminar) + Cargar/Descargar
- **Dashboard**: Ver y exportar
- **Restricción**: Solo su dirección

### 🟤 **Subrogante de Director**
- **Mismos permisos** que Director
- **Restricción**: Solo su dirección

### ⚪ **Jefatura**
- **Planes de Compra**: Solo ver
- **Proyectos**: CRUD (excepto eliminar) + Verificar
- **Formulario F1**: Solo ver y descargar
- **Archivos**: Crear, ver, cargar y descargar
- **Dashboard**: Ver y exportar
- **Restricción**: Solo su dirección

### ⚫ **Subrogante de Jefatura**
- **Mismos permisos** que Jefatura
- **Restricción**: Solo su dirección

---

## 🔒 RESTRICCIONES ESPECIALES

### **Envío y Cambio de Estado de Planes de Compra**
- **Solo Administradores y Directores** pueden enviar planes
- **Visadores NO pueden enviar** planes (solo visar/rechazar)
- **Visadores solo pueden cambiar a estado 3 (Visado) o 5 (Rechazado)**
- **Administradores Municipales pueden cambiar a cualquier estado excepto 6 (Decretado) y 7 (Publicado)**
- **Administradores del Sistema pueden cambiar a cualquier estado**

### **Acceso por Dirección**
- **Directores, Jefaturas y sus subrogantes** solo ven datos de su dirección
- **Administradores** ven todas las direcciones

### **Formulario F1**
- **Acceso exclusivo** para Encargado de Presupuestos y su subrogante (gestión completa)
- **Administradores del Sistema** tienen acceso completo
- **Administrador Municipal y Visador** pueden ver y descargar
- **Directores** también pueden ver y descargar
- **Jefaturas** no tienen acceso

### **Gestión de Usuarios**
- **Solo Administradores** pueden gestionar usuarios
- **Administrador Municipal** puede crear/editar usuarios pero no resetear passwords

---

## 📋 LEGENDA

| **SÍMBOLO** | **SIGNIFICADO** |
|-------------|-----------------|
| ✅ | **Permitido** |
| ❌ | **No permitido** |
| 🔒 | **Restringido por dirección** |
| ⚠️ | **Con restricciones especiales** |

---

## 🎯 NOTAS IMPORTANTES

1. **Jerarquía de roles**: Los roles superiores tienen más permisos que los inferiores
2. **Restricciones por dirección**: Algunos roles solo ven datos de su dirección asignada
3. **Permisos granulares**: Cada acción está controlada individualmente
4. **Seguridad**: El sistema valida permisos tanto en frontend como backend
5. **Auditoría**: Todas las acciones quedan registradas en logs

---

**📅 Fecha de actualización**: Diciembre 2024  
**🔄 Versión**: 1.0  
**📝 Documento**: Tabla de Roles y Permisos - Sistema de Planes de Compra Municipal 