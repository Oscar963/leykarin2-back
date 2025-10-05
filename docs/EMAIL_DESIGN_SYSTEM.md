# Sistema de Diseño de Emails Corporativo

## Descripción General

Todas las plantillas de email del sistema Leykarin siguen un diseño **corporativo tipo bancario** profesional, inspirado en instituciones financieras como Santander, BBVA, etc.

## Principios de Diseño

### 1. **Minimalismo Corporativo**
- Diseño limpio y sin distracciones
- Espaciado generoso
- Tipografía clara y legible
- Sin gradientes llamativos ni efectos excesivos

### 2. **Jerarquía Visual Clara**
- Header con logo y título del documento
- Contenido estructurado en secciones
- Footer con información legal

### 3. **Colores Institucionales**
- **Verde** (#27ae60): Confirmaciones y éxitos (denuncias)
- **Azul** (#3498db): Información y seguridad (passwords, 2FA)
- **Rojo** (#e74c3c): Alertas y advertencias (seguridad)
- **Grises neutros**: Texto y fondos (#2c3e50, #7f8c8d, #ecf0f1)

## Estructura HTML Estándar

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>[Título] - {{ config('app.name') }}</title>
    <style>...</style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="..." alt="Logo" class="logo">
            </div>
            <p class="header-title">[Subtítulo]</p>
            <h1 class="document-type">[Tipo de Documento]</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="intro-text">[Texto introductorio]</p>
            
            <!-- Secciones específicas -->
            
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-brand">Sistema Leykarin</p>
            <p class="footer-text">Ilustre Municipalidad de Arica</p>
            <p class="footer-text">Este es un correo automático...</p>
            <div class="footer-legal">...</div>
        </div>
    </div>
</body>
</html>
```

## Componentes CSS Comunes

### **Container Principal**
```css
.email-container {
    max-width: 600px;
    margin: 0 auto;
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
```

### **Header**
```css
.header {
    background-color: #ffffff;
    padding: 30px 40px 20px;
    border-bottom: 3px solid [COLOR]; /* Verde/Azul/Rojo según tipo */
}

.header-title {
    font-size: 13px;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.document-type {
    font-size: 24px;
    color: #2c3e50;
    font-weight: 600;
    letter-spacing: -0.3px;
}
```

### **Content**
```css
.content {
    padding: 40px;
    background-color: #ffffff;
}

.intro-text {
    font-size: 15px;
    color: #34495e;
    margin-bottom: 30px;
    line-height: 1.7;
}
```

### **Secciones de Información**
```css
.info-section {
    margin: 30px 0;
}

.section-title {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #ecf0f1;
}

.info-row {
    display: table;
    width: 100%;
    padding: 12px 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-label {
    display: table-cell;
    font-size: 14px;
    color: #7f8c8d;
    width: 35-40%;
}

.info-value {
    display: table-cell;
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
}
```

### **Cajas de Aviso**
```css
/* Verde - Éxito/Información */
.notice-box {
    background-color: #e8f5e9;
    border: 1px solid #c8e6c9;
    border-radius: 4px;
    padding: 20px;
}

/* Amarillo - Advertencia */
.alert-box {
    background-color: #fff8e1;
    border: 1px solid #ffecb3;
    border-left: 4px solid #ffa726;
}

/* Rojo - Alerta crítica */
.alert-box {
    background-color: #ffebee;
    border: 1px solid #ffcdd2;
    border-left: 4px solid #e74c3c;
}
```

### **Footer**
```css
.footer {
    background-color: #f8f9fa;
    padding: 30px 40px;
    border-top: 1px solid #ecf0f1;
}

.footer-brand {
    font-size: 13px;
    color: #2c3e50;
    font-weight: 600;
}

.footer-legal {
    font-size: 11px;
    color: #95a5a6;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
}
```

## Plantillas Disponibles

### 1. **complaint.blade.php** - Comprobante de Denuncia
- **Color**: Verde (#27ae60)
- **Elementos**: Folio destacado, información del denunciante
- **Tono**: Formal y confirmatorio

### 2. **reset-password.blade.php** - Restablecer Contraseña
- **Color**: Azul (#3498db)
- **Elementos**: Instrucciones paso a paso, botón CTA, enlace alternativo
- **Tono**: Instructivo y seguro

### 3. **two-factor-code.blade.php** - Código de Verificación
- **Color**: Azul (#3498db)
- **Elementos**: Código destacado, tiempo de expiración, consejos de seguridad
- **Tono**: Seguro y urgente

### 4. **security-alert.blade.php** - Alerta de Seguridad
- **Color**: Rojo (#e74c3c)
- **Elementos**: Resumen del evento, detalles técnicos, acciones requeridas
- **Tono**: Urgente y técnico

## Características del Diseño Bancario

### ✅ **Profesionalismo**
- Sin emojis excesivos
- Lenguaje formal (usted en lugar de tú)
- Tipografía system fonts (-apple-system, Segoe UI)
- Sin animaciones ni efectos hover

### ✅ **Claridad**
- Estructura de tabla para información
- Bordes sutiles (#ecf0f1)
- Espaciado consistente (30px, 40px)
- Jerarquía tipográfica clara

### ✅ **Confianza**
- Logo prominente pero discreto
- Colores institucionales sobrios
- Footer con información legal
- Texto de confidencialidad

### ✅ **Accesibilidad**
- Alto contraste de texto
- Tamaños de fuente legibles (14px-15px)
- Responsive design para móviles
- Estructura semántica

## Responsive Design

Todas las plantillas incluyen breakpoint a 600px:
- Padding reducido: 25px 20px
- Tablas cambian a display: block
- Labels y valores en columna
- Fuentes ligeramente más pequeñas

## Compatibilidad

- ✅ Gmail
- ✅ Outlook
- ✅ Apple Mail
- ✅ Thunderbird
- ✅ Clientes móviles (iOS, Android)

## Guía de Uso

### Para crear una nueva plantilla:

1. Copie la estructura base de cualquier plantilla existente
2. Cambie el color del border-bottom del header según el tipo:
   - Verde: Confirmaciones
   - Azul: Información/Seguridad
   - Rojo: Alertas
3. Mantenga la estructura de clases CSS
4. Use lenguaje formal y profesional
5. Incluya siempre el footer legal

### Variables requeridas:

Todas las plantillas deben recibir:
- `$logoUrl` o usar `asset('assets/img/logos/logo-blanco.png')`
- Variables específicas según el tipo de email

## Mantenimiento

Al modificar plantillas:
- ✅ Mantenga la estructura HTML consistente
- ✅ No agregue fuentes externas (usar system fonts)
- ✅ Pruebe en múltiples clientes de correo
- ✅ Verifique responsive en móviles
- ✅ Mantenga el tono formal y profesional
