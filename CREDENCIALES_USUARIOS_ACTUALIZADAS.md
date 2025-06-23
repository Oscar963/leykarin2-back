# 🔐 Credenciales de Usuarios para Pruebas - Sistema de Planes de Compra Municipal

## 📋 Información General

- **Contraseña común para todos los usuarios**: `password123`
- **Todos los usuarios están activos** (status = 1)
- **RUTs son válidos** según formato chileno con dígito verificador

---

## 👥 Usuarios por Rol

### 🛡️ **1. Administrador del Sistema**
```
👤 Usuario: Juan Carlos Administrador
📧 Email: admin.sistema@demo.com
🆔 RUT: 12345678-9
🔑 Contraseña: password123
🎭 Rol: Administrador del Sistema
📊 Permisos: TOTAL (todos los permisos del sistema)
```

### 🏛️ **2. Administrador Municipal**
```
👤 Usuario: María González Municipal
📧 Email: admin.municipal@demo.com
🆔 RUT: 23456789-0
🔑 Contraseña: password123
🎭 Rol: Administrador Municipal
📊 Permisos: Gestión administrativa municipal completa
```

### 👁️ **3. Visador o de Administrador Municipal**
```
👤 Usuario: Pedro Visador Municipal
📧 Email: visador.admin@demo.com
🆔 RUT: 34567890-1
🔑 Contraseña: password123
🎭 Rol: Visador o de Administrador Municipal
📊 Permisos: Mismos que Administrador Municipal
```

### 🎯 **4. Encargado de Presupuestos** *(Antes: Secretaría Comunal de Planificación)*
```
👤 Usuario: Ana Presupuestos
📧 Email: encargado.presupuestos@demo.com
🆔 RUT: 45678901-2
🔑 Contraseña: password123
🎭 Rol: Encargado de Presupuestos
📊 Permisos: Gestión de planes de compra y presupuestos
```

### 🔄 **5. Subrogante de Encargado de Presupuestos** *(Antes: Subrogante de Secretaría Comunal de Planificación)*
```
👤 Usuario: Carlos Subrogante Presupuestos
�� Email: subrogante.encargado@demo.com
🆔 RUT: 56789012-3
🔑 Contraseña: password123
🎭 Rol: Subrogante de Encargado de Presupuestos
📊 Permisos: Mismos que Encargado de Presupuestos
```

### 🏢 **6. Director**
```
👤 Usuario: Roberto Director
📧 Email: director.daf@demo.com
🆔 RUT: 11223344-5
🔑 Contraseña: password123
🎭 Rol: Director
📊 Permisos: Gestión completa de su dirección
📤 Envío de planes: ✅ SÍ (puede enviar planes para aprobación)
```

### 🔄 **7. Subrogante de Director**
```
👤 Usuario: Laura Subrogante Director
📧 Email: director.dimao@demo.com
🆔 RUT: 22334455-6
🔑 Contraseña: password123
🎭 Rol: Subrogante de Director
📊 Permisos: Mismos que Director
📤 Envío de planes: ❌ NO (no puede enviar planes para aprobación)
```

### 📋 **8. Jefatura**
```
👤 Usuario: Miguel Jefatura
📧 Email: jefatura@demo.com
🆔 RUT: 33445566-7
🔑 Contraseña: password123
🎭 Rol: Jefatura
📊 Permisos: Gestión operativa de proyectos e items
📤 Envío de planes: ❌ NO (no puede enviar planes para aprobación)
```

### 🔄 **9. Subrogante de Jefatura**
```
👤 Usuario: Patricia Subrogante Jefatura
📧 Email: subrogante.jefatura@demo.com
�� RUT: 44556677-8
🔑 Contraseña: password123
🎭 Rol: Subrogante de Jefatura
📊 Permisos: Mismos que Jefatura
📤 Envío de planes: ❌ NO (no puede enviar planes para aprobación)
```

---

## 📊 Matriz de Permisos por Rol

| Rol | Envío Planes | Direcciones | Planes | Proyectos | Items | Configuración |
|-----|--------------|-------------|--------|-----------|-------|---------------|
| **Administrador del Sistema** | ✅ SÍ | ✅ Múltiples | ✅ Total | ✅ Total | ✅ Total | ✅ Total |
| **Administrador Municipal** | ✅ SÍ | ✅ Múltiples | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | 🔒 Solo lectura |
| **Visador Admin** | ✅ SÍ | ✅ Múltiples | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | 🔒 Solo lectura |
| **Encargado de Presupuestos** | ❌ NO | ✅ Múltiples | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | 🔒 Solo lectura |
| **Subrogante Encargado** | ❌ NO | ✅ Múltiples | ✅ Total | 🔒 Solo lectura | 🔒 Solo lectura | 🔒 Solo lectura |
| **Director** | ✅ SÍ | 🔒 Una sola | 🔒 Su dirección | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura |
| **Subrogante Director** | ❌ NO | 🔒 Una sola | 🔒 Su dirección | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura |
| **Jefatura** | ❌ NO | 🔒 Una sola | 🔒 Solo lectura | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura |
| **Subrogante Jefatura** | ❌ NO | 🔒 Una sola | 🔒 Solo lectura | ✅ Su dirección | ✅ Su dirección | 🔒 Solo lectura |

---

## 🔍 Reglas de Negocio Importantes

### **📤 Envío de Planes de Compra**
**Solo estos roles pueden enviar planes para aprobación:**
- ✅ Administrador del Sistema
- ✅ Administrador Municipal  
- ✅ Director

**Roles que NO pueden enviar planes:**
- ❌ Visador o de Administrador Municipal
- ❌ Encargado de Presupuestos
- ❌ Subrogante de Encargado de Presupuestos
- ❌ Subrogante de Director
- ❌ Jefatura
- ❌ Subrogante de Jefatura

### **🏢 Direcciones**
**Usuarios Multi-Dirección (pueden pertenecer a múltiples direcciones):**
- Administrador del Sistema
- Administrador Municipal
- Encargado de Presupuestos
- Subrogante de Encargado de Presupuestos

**Usuarios Jerárquicos (solo una dirección):**
- Director
- Subrogante de Director
- Jefatura
- Subrogante de Jefatura

---

## 🧪 Comandos de Prueba Disponibles

### **Verificar permisos de usuario específico:**
```bash
php artisan check:user-permissions admin.sistema@demo.com
```

### **Probar permisos de envío de planes:**
```bash
php artisan test:purchase-plan-send-permission
```

### **Migrar roles (si es necesario):**
```bash
php artisan migrate:role-names
```

### **Limpiar cache de permisos:**
```bash
php artisan permission:cache-reset
```

---

## 📝 Notas Importantes

1. **Contraseña**: Todos los usuarios usan `password123`
2. **RUTs**: Son válidos según formato chileno con dígito verificador
3. **Emails**: Siguen el patrón `rol@municipalidadarica.cl`
4. **Roles actualizados**: "Secretaría Comunal de Planificación" → "Encargado de Presupuestos"
5. **Envío de planes**: Solo 3 roles pueden enviar planes para aprobación
6. **Direcciones**: Los usuarios jerárquicos solo pueden pertenecer a una dirección

---

## 🚀 Casos de Prueba Recomendados

### **1. Prueba de Envío de Planes**
- ✅ Usar Director: Debe poder enviar planes
- ❌ Usar Jefatura: No debe poder enviar planes
- ❌ Usar Encargado de Presupuestos: No debe poder enviar planes

### **2. Prueba de Acceso a Direcciones**
- ✅ Usar Administrador: Debe poder acceder a múltiples direcciones
- ✅ Usar Director: Debe estar limitado a su dirección asignada

### **3. Prueba de Configuración**
- ✅ Usar cualquier usuario: Debe poder acceder a type-projects, unit-purchasings, etc.
- ❌ Usar usuarios no administradores: No deben poder acceder a status-purchase-plans, directions

### **4. Prueba de Validación Jerárquica**
- ❌ Intentar asignar Director a múltiples direcciones: Debe fallar
- ✅ Asignar Administrador a múltiples direcciones: Debe funcionar

---

## 📋 Lista Rápida de Credenciales

**Administrador del Sistema**
- Email: admin.sistema@demo.com
- RUT: 12345678-9
- Contraseña: password123

**Administrador Municipal**
- Email: admin.municipal@demo.com
- RUT: 23456789-0
- Contraseña: password123

**Visador o de Administrador Municipal**
- Email: visador.admin@demo.com
- RUT: 34567890-1
- Contraseña: password123

**Encargado de Presupuestos**
- Email: encargado.presupuestos@demo.com
- RUT: 45678901-2
- Contraseña: password123

**Subrogante de Encargado de Presupuestos**
- Email: subrogante.encargado@demo.com
- RUT: 56789012-3
- Contraseña: password123

**Director**
- Email: director.daf@demo.com
- RUT: 11223344-5
- Contraseña: password123

**Subrogante de Director**
- Email: director.dimao@demo.com
- RUT: 22334455-6
- Contraseña: password123

**Jefatura**
- Email: jefatura@demo.com
- RUT: 33445566-7
- Contraseña: password123

**Subrogante de Jefatura**
- Email: subrogante.jefatura@demo.com
- RUT: 44556677-8
- Contraseña: password123

**Usuarios de Ejemplo**
- Email: usuario.daf1@demo.com
- RUT: 50607080-9
- Contraseña: password123

- Email: usuario.dimao1@demo.com
- RUT: 70809010-1
- Contraseña: password123

- Email: usuario.dom1@demo.com
- RUT: 80901020-2
- Contraseña: password123 