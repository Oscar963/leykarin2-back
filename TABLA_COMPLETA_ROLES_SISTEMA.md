# 📊 TABLA COMPLETA DE ROLES DEL SISTEMA - PLANES DE COMPRA

## 👥 ROLES Y PERMISOS PRINCIPALES

| **ROL** | **DESCRIPCIÓN** | **PLANES DE COMPRA** | **PROYECTOS** | **FORMULARIO F1** | **USUARIOS** | **DIRECCIONES** | **ARCHIVOS** | **DASHBOARD** |
|---------|-----------------|---------------------|---------------|-------------------|--------------|-----------------|--------------|---------------|
| **Administrador del Sistema** | Control total del sistema | ✅ CRUD completo + Todos los estados | ✅ CRUD completo + Verificar | ✅ CRUD completo | ✅ CRUD completo | ✅ CRUD completo | ✅ CRUD completo | ✅ Completo |
| **Administrador Municipal** | Gestión municipal completa | ✅ CRUD + Visar/Aprobar/Rechazar | ✅ CRUD completo | ✅ Ver/Descargar | ✅ CRUD completo | ✅ CRUD completo | ❌ Sin acceso | ✅ Ver/Exportar |
| **Visador** | Aprobación y visación | ❌ Solo ver/visar/rechazar | ✅ CRUD completo | ✅ Ver/Descargar | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ✅ Ver/Exportar |
| **Encargado de Presupuestos** | Gestión de Formularios F1 | ❌ Sin acceso | ❌ Sin acceso | ✅ CRUD completo | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso |
| **Subrogante de Encargado** | Suplencia de presupuestos | ❌ Sin acceso | ❌ Sin acceso | ✅ CRUD completo | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso |
| **Director** | Gestión de su dirección | ✅ CRUD (sin eliminar) + Enviar | ✅ CRUD completo + Verificar | ✅ Ver/Descargar | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ✅ Ver/Exportar |
| **Subrogante de Director** | Suplencia de director | ✅ CRUD (sin eliminar) + Enviar | ✅ CRUD (sin eliminar) + Verificar | ✅ Ver/Descargar | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ✅ Ver/Exportar |
| **Jefatura** | Gestión de su área | ❌ Solo ver | ✅ CRUD (sin eliminar) + Verificar | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ✅ Ver/Exportar |
| **Subrogante de Jefatura** | Suplencia de jefatura | ❌ Solo ver | ✅ CRUD (sin eliminar) + Verificar | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ❌ Sin acceso | ✅ Ver/Exportar |

---

## 🔐 PERMISOS DETALLADOS POR ROL

### 🔴 **Administrador del Sistema**
- **Nivel de Acceso**: Máximo
- **Planes de Compra**: Crear, editar, eliminar, ver, visar, aprobar, rechazar, enviar, exportar, subir decretos, subir F1
- **Proyectos**: Crear, editar, eliminar, ver, verificar
- **Formulario F1**: Crear, editar, eliminar, ver, subir, descargar, remover
- **Usuarios**: Crear, editar, eliminar, ver, resetear contraseñas
- **Direcciones**: Crear, editar, eliminar, ver
- **Archivos**: Crear, editar, eliminar, ver, subir, descargar
- **Dashboard**: Ver, exportar, configurar
- **Restricciones**: Ninguna

### 🟠 **Administrador Municipal**
- **Nivel de Acceso**: Alto
- **Planes de Compra**: Crear, editar, eliminar, ver, visar, aprobar, rechazar, enviar, exportar
- **Proyectos**: Crear, editar, eliminar, ver
- **Formulario F1**: Ver, descargar
- **Usuarios**: Crear, editar, eliminar, ver
- **Direcciones**: Crear, editar, eliminar, ver
- **Archivos**: Sin acceso
- **Dashboard**: Ver, exportar
- **Restricciones**: No puede decretar (estado 6) ni publicar (estado 7)

### 🟡 **Visador**
- **Nivel de Acceso**: Medio-Alto
- **Planes de Compra**: Ver, visar (estado 3), rechazar (estado 5), exportar
- **Proyectos**: Crear, editar, eliminar, ver
- **Formulario F1**: Ver, descargar
- **Usuarios**: Sin acceso
- **Direcciones**: Sin acceso
- **Archivos**: Sin acceso
- **Dashboard**: Ver, exportar
- **Restricciones**: Solo puede cambiar a estado 3 (Visado) o 5 (Rechazado)

### 🟢 **Encargado de Presupuestos**
- **Nivel de Acceso**: Especializado
- **Planes de Compra**: Sin acceso
- **Proyectos**: Sin acceso
- **Formulario F1**: Crear, editar, eliminar, ver, subir, descargar, remover
- **Usuarios**: Sin acceso
- **Direcciones**: Sin acceso
- **Archivos**: Sin acceso
- **Dashboard**: Sin acceso
- **Restricciones**: Solo acceso a Formulario F1

### 🔵 **Subrogante de Encargado de Presupuestos**
- **Nivel de Acceso**: Especializado
- **Planes de Compra**: Sin acceso
- **Proyectos**: Sin acceso
- **Formulario F1**: Crear, editar, eliminar, ver, subir, descargar, remover
- **Usuarios**: Sin acceso
- **Direcciones**: Sin acceso
- **Archivos**: Sin acceso
- **Dashboard**: Sin acceso
- **Restricciones**: Solo acceso a Formulario F1

### 🟣 **Director**
- **Nivel de Acceso**: Medio
- **Planes de Compra**: Crear, editar, ver, enviar, exportar, subir decretos, subir F1
- **Proyectos**: Crear, editar, eliminar, ver, verificar
- **Formulario F1**: Ver, descargar
- **Usuarios**: Sin acceso
- **Direcciones**: Sin acceso
- **Archivos**: Sin acceso
- **Dashboard**: Ver, exportar
- **Restricciones**: Solo su dirección, no puede eliminar planes

### 🟤 **Subrogante de Director**
- **Nivel de Acceso**: Medio
- **Planes de Compra**: Crear, editar, ver, enviar, exportar, subir decretos, subir F1
- **Proyectos**: Crear, editar, ver, verificar
- **Formulario F1**: Ver, descargar
- **Usuarios**: Sin acceso
- **Direcciones**: Sin acceso
- **Archivos**: Sin acceso
- **Dashboard**: Ver, exportar
- **Restricciones**: Solo su dirección, no puede eliminar planes ni proyectos

### ⚪ **Jefatura**
- **Nivel de Acceso**: Bajo-Medio
- **Planes de Compra**: Solo ver
- **Proyectos**: Crear, editar, ver, verificar
- **Formulario F1**: Sin acceso
- **Usuarios**: Sin acceso
- **Direcciones**: Sin acceso
- **Archivos**: Sin acceso
- **Dashboard**: Ver, exportar
- **Restricciones**: Solo su dirección, no puede eliminar proyectos

### ⚫ **Subrogante de Jefatura**
- **Nivel de Acceso**: Bajo-Medio
- **Planes de Compra**: Solo ver
- **Proyectos**: Crear, editar, ver, verificar
- **Formulario F1**: Sin acceso
- **Usuarios**: Sin acceso
- **Direcciones**: Sin acceso
- **Archivos**: Sin acceso
- **Dashboard**: Ver, exportar
- **Restricciones**: Solo su dirección, no puede eliminar proyectos

---

## 📋 ESTADOS DE PLANES DE COMPRA

| **ESTADO** | **ID** | **NOMBRE** | **DESCRIPCIÓN** |
|------------|--------|------------|-----------------|
| 1 | Borrador | Estado inicial del plan |
| 2 | Para aprobación | Enviado para revisión |
| 3 | Visado | Revisado técnicamente |
| 4 | Aprobado para decretar | Aprobado para convertirse en decreto |
| 5 | Rechazado | Plan rechazado |
| 6 | Decretado | Convertido en decreto municipal |
| 7 | Publicado | Publicado oficialmente |

---

## 🔒 RESTRICCIONES ESPECIALES POR ESTADO

| **ROL** | **ESTADOS PERMITIDOS** | **RESTRICCIONES** |
|---------|------------------------|-------------------|
| **Visador** | 3, 5 | Solo puede visar o rechazar |
| **Administrador Municipal** | 1-5 | No puede decretar ni publicar |
| **Administrador del Sistema** | 1-7 | Sin restricciones |
| **Director** | 1-2 | Solo puede enviar para aprobación |
| **Otros roles** | Ninguno | No pueden cambiar estados |

---

## 📊 RESUMEN DE ACCESOS

### **Gestión Completa**
- **Administrador del Sistema**: Todos los módulos
- **Administrador Municipal**: Todos excepto archivos

### **Gestión Especializada**
- **Encargado de Presupuestos**: Solo Formulario F1
- **Subrogante de Encargado**: Solo Formulario F1

### **Gestión por Dirección**
- **Director**: Su dirección
- **Subrogante de Director**: Su dirección
- **Jefatura**: Su dirección
- **Subrogante de Jefatura**: Su dirección

### **Gestión de Aprobación**
- **Visador**: Solo visar y rechazar
- **Administrador Municipal**: Visar, aprobar y rechazar

---

**📅 Fecha de actualización**: Diciembre 2024  
**🔄 Versión**: 2.0  
**📝 Documento**: Tabla Completa de Roles del Sistema - Planes de Compra Municipal 