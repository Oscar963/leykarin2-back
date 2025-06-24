# Comandos de Generación de Plantillas Excel

## 📋 Comandos Disponibles

### **1. Generar Plantilla de Importación**

```bash
php artisan import:generate-template
```

#### **Descripción**
Genera una plantilla Excel oficial con ejemplos de datos y referencias de valores válidos para la importación de ítems de compra.

#### **Opciones Disponibles**
| Opción | Descripción | Valor por Defecto |
|--------|-------------|-------------------|
| `--output` | Ruta de salida para el archivo | `storage/app/templates/plantilla-items-compra.xlsx` |
| `--format` | Formato del archivo (xlsx, xls) | `xlsx` |

#### **Ejemplos de Uso**

**Generar plantilla con configuración por defecto:**
```bash
php artisan import:generate-template
```

**Especificar ruta de salida:**
```bash
php artisan import:generate-template --output=/path/to/plantilla.xlsx
```

**Generar en formato XLS:**
```bash
php artisan import:generate-template --format=xls
```

**Combinar opciones:**
```bash
php artisan import:generate-template --output=plantilla-personalizada.xlsx --format=xlsx
```

#### **Salida del Comando**
```
🚀 Generando plantilla de importación para ítems de compra...
📁 Directorio creado: /path/to/storage/app/templates
📊 Generando plantilla con datos de ejemplo...
✅ Plantilla generada exitosamente!
📁 Ubicación: /path/to/storage/app/templates/plantilla-items-compra.xlsx
📏 Tamaño: 45.2 KB

📋 Contenido de la plantilla:
+------------------------+-------------+------------------------+
| Hoja                   | Descripción | Contenido             |
+------------------------+-------------+------------------------+
| Plantilla Ítems de     | Datos de    | 2 filas con ejemplos  |
| Compra                 | ejemplo     | completos             |
| Asignaciones           | Referencias | Códigos y             |
| Presupuestarias        |             | descripciones         |
| Tipos de Compra        | Referencias | Tipos de compra       |
| Meses de Publicación   | Referencias | Meses disponibles     |
+------------------------+-------------+------------------------+

📖 Instrucciones de uso:
1. Abre el archivo Excel generado
2. Ve a la hoja "Plantilla Ítems de Compra"
3. Copia las filas de ejemplo y pégalas en tu archivo de trabajo
4. Completa con tus datos siguiendo el formato de los ejemplos
5. Usa las hojas de referencia para valores válidos
6. Guarda tu archivo como .xlsx
7. Importa usando el endpoint: POST /api/item-purchases/import/{projectId}

⚠️  Campos obligatorios:
   • Producto o Servicio
   • Cantidad (mínimo 1)
   • Monto (mínimo 0)

⚠️  Formatos importantes:
   • Mes de publicación: "Dic 2025"
   • Asignación presupuestaria: "123456 - Descripción"
   • Montos: Solo números (sin símbolos de moneda)
```

---

### **2. Generar Archivo de Ejemplo**

```bash
php artisan import:generate-sample
```

#### **Descripción**
Genera un archivo Excel de ejemplo con datos completos y realistas para que los usuarios vean exactamente cómo debe ser el archivo de importación.

#### **Opciones Disponibles**
| Opción | Descripción | Valor por Defecto |
|--------|-------------|-------------------|
| `--output` | Ruta de salida para el archivo | `storage/app/templates/ejemplo-items-compra-{rows}-filas.xlsx` |
| `--rows` | Número de filas de ejemplo a generar | `10` |
| `--format` | Formato del archivo (xlsx, xls) | `xlsx` |

#### **Ejemplos de Uso**

**Generar archivo con 10 filas (por defecto):**
```bash
php artisan import:generate-sample
```

**Generar archivo con 25 filas:**
```bash
php artisan import:generate-sample --rows=25
```

**Especificar ruta y formato:**
```bash
php artisan import:generate-sample --output=ejemplo-completo.xlsx --format=xlsx
```

**Generar archivo grande para pruebas:**
```bash
php artisan import:generate-sample --rows=50 --output=archivo-prueba.xlsx
```

#### **Salida del Comando**
```
🚀 Generando archivo de ejemplo para importación de ítems de compra...
📁 Directorio creado: /path/to/storage/app/templates
📊 Generando archivo con 25 filas de ejemplo...
✅ Archivo de ejemplo generado exitosamente!
📁 Ubicación: /path/to/storage/app/templates/ejemplo-items-compra-25-filas.xlsx
📏 Tamaño: 78.5 KB
📊 Filas generadas: 25

📋 Contenido del archivo de ejemplo:
+------------------+-------------+------------------------+
| Hoja             | Descripción | Contenido             |
+------------------+-------------+------------------------+
| Ítems de Compra  | Datos de    | 25 filas con datos    |
|                  | ejemplo     | completos             |
| Meses de         | Referencias | Meses disponibles     |
| Publicación      |             | para publicación      |
+------------------+-------------+------------------------+

📊 Tipos de datos incluidos:
   • Equipos informáticos (laptops, impresoras, etc.)
   • Servicios de mantenimiento
   • Mobiliario de oficina
   • Software y licencias
   • Materiales de oficina
   • Servicios profesionales

📖 Instrucciones de uso:
1. Abre el archivo Excel generado
2. Revisa los datos de ejemplo en la primera hoja
3. Modifica o reemplaza los datos con tu información real
4. Asegúrate de mantener el formato de los encabezados
5. Verifica que los valores de referencia sean válidos
6. Guarda tu archivo como .xlsx
7. Importa usando el endpoint: POST /api/item-purchases/import/{projectId}

⚠️  Campos obligatorios:
   • Producto o Servicio (no puede estar vacío)
   • Cantidad (debe ser mayor a 0)
   • Monto (debe ser mayor o igual a 0)

⚠️  Formatos importantes:
   • Mes de publicación: "Dic 2025", "Ene 2026"
   • Asignación presupuestaria: "123456 - Descripción"
   • Montos: Solo números (sin símbolos de moneda)
   • Cantidades: Solo números enteros

💡 Consejos:
   • Usa la plantilla oficial para obtener referencias actualizadas
   • Verifica que los tipos de compra existan en el sistema
   • Los meses de publicación deben estar en el formato correcto
   • Puedes dejar campos opcionales vacíos
```

---

## 📁 Ubicación de Archivos Generados

### **Ruta por Defecto**
```
storage/app/templates/
```

### **Estructura de Archivos**
```
storage/app/templates/
├── plantilla-items-compra.xlsx
├── ejemplo-items-compra-10-filas.xlsx
├── ejemplo-items-compra-25-filas.xlsx
└── archivos-personalizados.xlsx
```

---

## 📊 Contenido de las Plantillas

### **Plantilla Oficial (4 hojas)**

#### **1. Plantilla Ítems de Compra**
- **Contenido:** 2 filas con ejemplos completos
- **Propósito:** Mostrar el formato correcto
- **Estilo:** Fondo amarillo claro para ejemplos

#### **2. Asignaciones Presupuestarias**
- **Contenido:** Códigos y descripciones disponibles
- **Propósito:** Referencia para valores válidos
- **Estilo:** Fondo verde, encabezados blancos

#### **3. Tipos de Compra**
- **Contenido:** Tipos de compra válidos
- **Propósito:** Referencia para valores válidos
- **Estilo:** Fondo naranja, encabezados blancos

#### **4. Meses de Publicación**
- **Contenido:** Meses disponibles para publicación
- **Propósito:** Referencia para valores válidos
- **Estilo:** Fondo verde, encabezados blancos

### **Archivo de Ejemplo (2 hojas)**

#### **1. Ejemplo Ítems de Compra**
- **Contenido:** 10-100 filas con datos realistas
- **Propósito:** Mostrar datos completos y variados
- **Estilo:** Fondo verde claro para datos

#### **2. Meses de Publicación**
- **Contenido:** Meses disponibles para publicación
- **Propósito:** Referencia para valores válidos
- **Estilo:** Fondo verde, encabezados blancos

---

## 🔧 Configuración Avanzada

### **Personalizar Datos de Ejemplo**

Para modificar los datos de ejemplo, edita el archivo:
```php
app/Exports/ItemsPurchaseSampleExport.php
```

En el método `generateSampleData()`, puedes:
- Cambiar los productos y servicios
- Modificar los precios y cantidades
- Agregar nuevas categorías
- Personalizar las regiones

### **Personalizar Estilos**

Para modificar los estilos, edita:
```php
app/Exports/ItemsPurchaseTemplateExport.php
app/Exports/ItemsPurchaseSampleExport.php
```

Puedes cambiar:
- Colores de encabezados
- Colores de datos
- Tipos de bordes
- Formato de números

---

## 🚀 Casos de Uso

### **1. Desarrollo y Pruebas**
```bash
# Generar archivo pequeño para pruebas rápidas
php artisan import:generate-sample --rows=5

# Generar archivo grande para pruebas de rendimiento
php artisan import:generate-sample --rows=100
```

### **2. Capacitación de Usuarios**
```bash
# Generar plantilla para capacitación
php artisan import:generate-template --output=plantilla-capacitacion.xlsx

# Generar ejemplos variados
php artisan import:generate-sample --rows=20 --output=ejemplos-capacitacion.xlsx
```

### **3. Migración de Datos**
```bash
# Generar plantilla para migración
php artisan import:generate-template --output=plantilla-migracion.xlsx

# Generar archivo de prueba con muchos datos
php artisan import:generate-sample --rows=50 --output=prueba-migracion.xlsx
```

### **4. Documentación**
```bash
# Generar archivos para documentación
php artisan import:generate-template --output=docs/plantilla-oficial.xlsx
php artisan import:generate-sample --rows=15 --output=docs/ejemplo-completo.xlsx
```

---

## 🔍 Monitoreo y Logging

### **Logs Generados**
Los comandos registran automáticamente:
- Generación exitosa de archivos
- Errores durante la generación
- Estadísticas de archivos creados

### **Verificar Archivos Generados**
```bash
# Listar archivos en el directorio de plantillas
ls -la storage/app/templates/

# Verificar tamaño de archivos
du -h storage/app/templates/*.xlsx
```

---

## 🛠️ Troubleshooting

### **Errores Comunes**

#### **"No se pudo generar el archivo"**
- Verificar permisos de escritura en `storage/app/templates/`
- Verificar espacio disponible en disco
- Verificar que Laravel Excel esté instalado correctamente

#### **"Formato no válido"**
- Usar solo `xlsx` o `xls` como formato
- Verificar que el parámetro esté en minúsculas

#### **"Número de filas debe estar entre 1 y 100"**
- Usar un número entre 1 y 100 para el parámetro `--rows`
- Para archivos más grandes, usar el comando múltiples veces

### **Solución de Problemas**

#### **Permisos de Directorio**
```bash
# Crear directorio si no existe
mkdir -p storage/app/templates

# Asignar permisos correctos
chmod 755 storage/app/templates
```

#### **Verificar Instalación de Laravel Excel**
```bash
# Verificar que el paquete esté instalado
composer show maatwebsite/excel

# Reinstalar si es necesario
composer require maatwebsite/excel
```

---

## 📈 Próximas Mejoras

### **Funcionalidades Planificadas**
- [ ] Generación de plantillas con datos específicos por región
- [ ] Plantillas personalizadas por tipo de proyecto
- [ ] Validación previa de datos en plantillas
- [ ] Generación de plantillas con fórmulas Excel
- [ ] Plantillas con macros y validaciones automáticas

### **Optimizaciones Técnicas**
- [ ] Cache de datos de referencia
- [ ] Generación asíncrona para archivos grandes
- [ ] Compresión automática de archivos
- [ ] Integración con sistema de plantillas 