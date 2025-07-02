# 📋 Análisis del Módulo de Subir Decreto

## 🔍 **Estado Actual del Sistema**

### ❌ **Problema Identificado**
El sistema **NO** cambiaba automáticamente el estado del plan de compra a "Decretado" cuando se subía un decreto.

### ✅ **Solución Implementada**
Se modificó el sistema para que **automáticamente** maneje los cambios de estado de forma bidireccional:

- **Subir decreto** → Estado cambia a "Decretado" ✅
- **Eliminar decreto** → Estado revierte a "Aprobado para decretar" ✅

---

## 📊 **Flujo del Sistema**

### **1. Estados de Planes de Compra**
```php
// database/seeders/StatusPurchasePlanSeeder.php
1. 'Borrador'
2. 'Para aprobación' 
3. 'Visado'
4. 'Aprobado para decretar'
5. 'Rechazado'
6. 'Decretado'  // ← Estado objetivo (ID: 6)
7. 'Publicado'
```

### **2. Subida de Decreto (ANTES)**
```php
// app/Services/PurchasePlanService.php - uploadFileDecreto()
public function uploadFileDecreto(array $data)
{
    $decreto = $this->createDecreto($data);
    
    $purchasePlan = $this->getPurchasePlanByToken($data['token_purchase_plan']);
    $purchasePlan->decreto_id = $decreto->id;  // ✅ Asocia el decreto
    $purchasePlan->save();
    
    // ❌ NO cambiaba el estado automáticamente
    
    // Solo registraba en historial
    HistoryPurchaseHistory::logAction(
        $purchasePlan->id,
        'file_upload',
        'Decreto subido',
        [...]
    );
    
    return $purchasePlan;
}
```

### **3. Subida de Decreto (DESPUÉS)**
```php
// app/Services/PurchasePlanService.php - uploadFileDecreto()
public function uploadFileDecreto(array $data)
{
    $decreto = $this->createDecreto($data);

    $purchasePlan = $this->getPurchasePlanByToken($data['token_purchase_plan']);
    $purchasePlan->decreto_id = $decreto->id;
    $purchasePlan->save();

    // ✅ CAMBIO AUTOMÁTICO DE ESTADO A "DECRETADO"
    $this->updatePurchasePlanStatus($purchasePlan->id, [
        'status_purchase_plan_id' => 6, // ID del estado "Decretado"
        'sending_comment' => 'Estado cambiado automáticamente a Decretado al subir el decreto'
    ]);

    // Registrar en el historial
    HistoryPurchaseHistory::logAction(
        $purchasePlan->id,
        'file_upload',
        'Decreto subido',
        [
            'file_name' => $decreto->name,
            'file_size' => $decreto->size,
            'file_type' => $decreto->type,
            'file_url' => $decreto->url
        ]
    );

    return $purchasePlan;
}
```

### **4. Creación de Decreto (DESPUÉS)**
```php
// app/Services/DecretoService.php - createDecreto()
public function createDecreto(array $data)
{
    $purchasePlan = PurchasePlan::findOrFail($data['purchase_plan_id']);
    // ... crear decreto ...
    
    $purchasePlan->decreto_id = $decreto->id;
    $purchasePlan->save();

    // ✅ CAMBIO AUTOMÁTICO DE ESTADO A "DECRETADO"
    $this->updatePurchasePlanStatusToDecretado($purchasePlan);

    return $decreto;
}

private function updatePurchasePlanStatusToDecretado(PurchasePlan $purchasePlan)
{
    $currentStatus = $purchasePlan->getCurrentStatus();
    
    // Solo cambiar si NO está ya en estado "Decretado" o "Publicado"
    if ($currentStatus && !in_array($currentStatus->status_purchase_plan_id, [6, 7])) {
        // Crear nuevo estado "Decretado" (ID: 6)
        $purchasePlanStatus = new PurchasePlanStatus();
        $purchasePlanStatus->purchase_plan_id = $purchasePlan->id;
        $purchasePlanStatus->status_purchase_plan_id = 6;
        $purchasePlanStatus->sending_comment = 'Estado cambiado automáticamente a "Decretado" al crear el decreto';
        $purchasePlanStatus->created_by = auth()->id();
        $purchasePlanStatus->save();

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'status_change',
            "Estado cambiado de '{$currentStatusName}' a 'Decretado' al crear decreto",
            [...]
        );
    }
}
```

### **5. Eliminación de Decreto (DESPUÉS)**
```php
// app/Services/DecretoService.php - deleteDecreto()
public function deleteDecreto($id)
{
    $decreto = $this->getDecretoById($id);
    $purchasePlan = PurchasePlan::where('decreto_id', $id)->first();
    
    if ($purchasePlan) {
        // ✅ CAMBIO AUTOMÁTICO DE ESTADO A "APROBADO PARA DECRETAR"
        $this->revertPurchasePlanStatusToApproved($purchasePlan);
        
        $purchasePlan->decreto_id = null;
        $purchasePlan->save();
    }
    
    $decreto->delete();
}

private function revertPurchasePlanStatusToApproved(PurchasePlan $purchasePlan)
{
    $currentStatus = $purchasePlan->getCurrentStatus();
    
    // Solo cambiar si está en estado "Decretado" o "Publicado"
    if ($currentStatus && in_array($currentStatus->status_purchase_plan_id, [6, 7])) {
        // Crear nuevo estado "Aprobado para decretar" (ID: 4)
        $purchasePlanStatus = new PurchasePlanStatus();
        $purchasePlanStatus->purchase_plan_id = $purchasePlan->id;
        $purchasePlanStatus->status_purchase_plan_id = 4;
        $purchasePlanStatus->sending_comment = 'Estado revertido automáticamente a "Aprobado para decretar" al eliminar el decreto';
        $purchasePlanStatus->created_by = auth()->id();
        $purchasePlanStatus->save();

        // Registrar en el historial
        HistoryPurchaseHistory::logAction(
            $purchasePlan->id,
            'status_change',
            "Estado revertido de '{$currentStatusName}' a 'Aprobado para decretar' al eliminar decreto",
            [...]
        );
    }
}
```

### **6. Notificación por Email (DESPUÉS)**
```php
// app/Http/Controllers/PurchasePlanController.php - uploadDecreto()
public function uploadDecreto(UploadDecretoRequest $request): JsonResponse
{
    try {
        $upload = $this->purchasePlanService->uploadFileDecreto($request->validated());
        
        // ✅ ENVIAR EMAIL DE NOTIFICACIÓN DE DECRETADO
        $this->sendPurchasePlanDecretadoEmail($upload, 'Estado cambiado automáticamente a Decretado al subir el decreto');
        
        $this->logActivity('upload_decreto', 'Usuario subió un decreto con ID: ' . $upload->id);

        return response()->json([
            'message' => 'Decreto subido exitosamente y plan de compra marcado como Decretado',
            'data' => new PurchasePlanResource($upload)
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Error al subir el decreto. ' . $e->getMessage()
        ], 500);
    }
}

// app/Http/Controllers/DecretoController.php - destroy()
public function destroy(int $id): JsonResponse
{
    try {
        // Obtener el decreto antes de eliminarlo para enviar notificación
        $decreto = $this->decretoService->getDecretoById($id);
        $purchasePlan = $decreto->purchasePlan;
        
        $this->decretoService->deleteDecreto($id);
        
        // ✅ ENVIAR NOTIFICACIÓN SI HAY PLAN DE COMPRA ASOCIADO
        if ($purchasePlan) {
            $this->sendDecretoRemovedNotification($purchasePlan);
        }
        
        $this->logActivity('delete_decreto', 'Usuario eliminó el decreto con ID: ' . $id);

        return response()->json([
            'message' => 'Decreto ha sido eliminado exitosamente y plan de compra revertido a "Aprobado para decretar"'
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Error al eliminar el decreto. ' . $e->getMessage()
        ], 500);
    }
}
```

---

## 🔄 **Flujo Completo del Proceso**

### **1. Usuario Sube/Crea Decreto**
```mermaid
graph TD
    A[Usuario autorizado] --> B[POST /api/purchase-plans/upload/decreto o POST /api/decretos]
    B --> C[Validar archivo PDF]
    C --> D[Crear registro Decreto]
    D --> E[Asociar al Plan de Compra]
    E --> F[✅ CAMBIO AUTOMÁTICO DE ESTADO A "DECRETADO"]
    F --> G[Registrar en historial]
    G --> H[✅ ENVIAR EMAIL DE NOTIFICACIÓN]
    H --> I[Respuesta exitosa]
```

### **2. Usuario Elimina Decreto**
```mermaid
graph TD
    A[Usuario autorizado] --> B[DELETE /api/decretos/{id}]
    B --> C[Obtener decreto y plan asociado]
    C --> D[✅ CAMBIO AUTOMÁTICO DE ESTADO A "APROBADO PARA DECRETAR"]
    D --> E[Eliminar asociación decreto-plan]
    E --> F[Eliminar archivo físico]
    F --> G[Registrar en historial]
    G --> H[✅ ENVIAR EMAIL DE NOTIFICACIÓN]
    H --> I[Respuesta exitosa]
```

### **3. Cambios Automáticos Realizados**

#### **Al Subir/Crear Decreto:**
1. **Estado del Plan**: Cambia automáticamente a "Decretado" (ID: 6)
2. **Historial**: Se registra el cambio de estado
3. **Email**: Se envía notificación automática
4. **Logs**: Se registra la actividad
5. **Validación**: Solo cambia si NO está ya en "Decretado" o "Publicado"

#### **Al Eliminar Decreto:**
1. **Estado del Plan**: Revierte automáticamente a "Aprobado para decretar" (ID: 4)
2. **Historial**: Se registra el cambio de estado
3. **Email**: Se envía notificación automática
4. **Logs**: Se registra la actividad
5. **Validación**: Solo revierte si estaba en "Decretado" o "Publicado"

---

## 📁 **Estructura del Sistema de Decretos**

### **Modelo Decreto**
```php
// app/Models/Decreto.php
- id (Primary Key)
- name (nombre descriptivo)
- description (descripción)
- url (URL del archivo)
- type (tipo MIME)
- size (tamaño en bytes)
- extension (extensión del archivo)
- created_by/updated_by (usuarios responsables)
```

### **Relación con Planes de Compra**
```php
// app/Models/PurchasePlan.php
- decreto_id (foreign key hacia decretos)
- Relación 1:1 con Decreto
```

### **Validaciones**
```php
// app/Http/Requests/UploadDecretoRequest.php
- 'file' => 'required|file|mimes:pdf|max:5120'  // Máximo 5MB
- 'token_purchase_plan' => 'required|exists:purchase_plans,token'
```

---

## 🔐 **Sistema de Permisos**

### **Roles Autorizados para Subir Decretos**
- **Administrador del Sistema**: Acceso completo
- **Administrador Municipal**: Acceso completo  
- **Director**: Puede subir decretos
- **Subrogante de Director**: Puede subir decretos

### **Roles SIN Acceso para Subir Decretos**
- **Secretaría Comunal de Planificación**: Solo visualización
- **Visador**: Sin acceso
- **Jefatura**: Sin acceso
- **Subrogante de Jefatura**: Sin acceso
- **Encargado de Presupuestos**: Sin acceso
- **Subrogante Encargado de Presupuestos**: Sin acceso

---

## 📧 **Sistema de Notificaciones**

### **Email de Decretado**
```php
// app/Mail/PurchasePlanDecretado.php
Subject: "Plan de Compra Decretado"
Template: resources/views/emails/purchase-plan-decretado.blade.php
Destinatario: oscar.apata@municipalidadarica.cl
```

### **Email de Decreto Eliminado**
```php
// app/Mail/DecretoRemoved.php
Subject: "Decreto Eliminado - Plan de Compra Revertido"
Template: resources/views/emails/decreto-removed.blade.php
Destinatario: oscar.apata@municipalidadarica.cl
```

### **Contenido de los Emails**
- ✅ Confirmación de decretado/eliminación
- 📋 Detalles del plan de compra
- 📄 Información del decreto
- 🔄 Próximo paso: Publicación (decretado) o Subir nuevo decreto (eliminado)

---

## 🎯 **Beneficios de la Implementación**

### **1. Automatización**
- ✅ Cambio automático de estado
- ✅ Notificación automática por email
- ✅ Registro automático en historial

### **2. Consistencia**
- ✅ Garantiza que todo plan con decreto esté en estado "Decretado"
- ✅ Evita inconsistencias manuales
- ✅ Flujo de trabajo estandarizado

### **3. Trazabilidad**
- ✅ Historial completo de cambios
- ✅ Logs de actividad
- ✅ Notificaciones automáticas

### **4. Validaciones**
- ✅ Solo archivos PDF
- ✅ Tamaño máximo 5MB
- ✅ Permisos por roles
- ✅ Asociación obligatoria con plan de compra

---

## 🚀 **Endpoints Disponibles**

### **Upload/Crear Decreto**
```http
# Via Plan de Compra
POST /api/purchase-plans/upload/decreto
Content-Type: multipart/form-data

{
    "file": "archivo.pdf",
    "token_purchase_plan": "abc123xyz789"
}

# Via CRUD de Decretos
POST /api/decretos
Content-Type: multipart/form-data

{
    "file": "archivo.pdf",
    "purchase_plan_id": 1
}
```

### **Eliminar Decreto**
```http
DELETE /api/decretos/{id}
```

### **Respuestas Exitosas**

#### **Al Subir/Crear Decreto:**
```json
{
    "message": "Decreto subido exitosamente y plan de compra marcado como Decretado",
    "data": {
        "id": 1,
        "name": "Plan de Compra 2024 - Alcaldía",
        "current_status": {
            "id": 6,
            "name": "Decretado"
        },
        "decreto": {
            "id": 1,
            "name": "2024-01-15 12:30 - Alcaldía - Decreto",
            "url": "http://localhost/storage/uploads/decretos/decreto-abc123.pdf"
        }
    }
}
```

#### **Al Eliminar Decreto:**
```json
{
    "message": "Decreto ha sido eliminado exitosamente y plan de compra revertido a \"Aprobado para decretar\""
}
```

---

## 📝 **Consideraciones Importantes**

### **1. Estados Válidos para Cambios de Ítems**
```php
// app/Services/ItemPurchaseService.php
// Solo permite cambios de estado cuando el plan está:
- Decretado (ID: 6) ✅
- Publicado (ID: 7) ✅
```

### **2. Flujo de Trabajo Recomendado**
1. **Borrador** → **Para aprobación** → **Visado** → **Aprobado para decretar**
2. **Subir Decreto** → **Decretado** (automático) ✅
3. **Publicado** (manual)

### **3. Flujo de Reversión**
1. **Decretado/Publicado** → **Eliminar Decreto** → **Aprobado para decretar** (automático) ✅
2. **Subir Nuevo Decreto** → **Decretado** (automático) ✅

### **4. Validaciones del Sistema**
- ✅ Un plan de compra solo puede tener un decreto
- ✅ El decreto debe ser un archivo PDF
- ✅ Tamaño máximo de 5MB
- ✅ Solo roles autorizados pueden subir decretos
- ✅ Solo revierte estado si estaba en "Decretado" o "Publicado"

---

## 🎉 **Resumen de Mejoras Implementadas**

### **✅ Antes**
- Subir decreto solo asociaba el archivo
- Estado debía cambiarse manualmente
- No había notificación automática
- Eliminar decreto no afectaba el estado
- **PROBLEMA**: Solo funcionaba con un endpoint, no con ambos

### **✅ Después**
- Subir/Crear decreto cambia automáticamente el estado a "Decretado" (ambos endpoints)
- Eliminar decreto revierte automáticamente el estado a "Aprobado para decretar"
- Notificación automática por email en ambos casos
- Historial completo de cambios
- Flujo de trabajo automatizado y consistente
- Validaciones inteligentes para evitar cambios innecesarios
- **SOLUCIÓN**: Comando de corrección para casos existentes

**El sistema ahora cumple completamente con los requerimientos:**
- ✅ "Si se sube un decreto, el plan de compras debe pasar a estado Decretado"
- ✅ "Si se elimina un decreto, el plan de compras debe volver a estado Aprobado para decretar"

### **🔧 Comando de Corrección**
```bash
# Corregir estado de un plan de compra específico
php artisan purchase-plan:fix-status {purchase_plan_id}

# Ejemplo para el plan que mencionaste:
php artisan purchase-plan:fix-status 1
``` 