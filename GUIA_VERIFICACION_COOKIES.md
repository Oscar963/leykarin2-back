# Guía de Verificación de Cookies en Chrome DevTools

## 🔍 Verificación en Chrome DevTools

### 1. Abrir DevTools
- Presiona `F12` o `Ctrl+Shift+I`
- Ve a la pestaña **Application**
- En el panel izquierdo, expande **Storage** → **Cookies**

### 2. Verificar Cookies por Dominio
1. Selecciona tu dominio (`https://dev.imaarica.cl`)
2. Verifica que solo haya **UNA** instancia de cada cookie:
   - `XSRF-TOKEN`
   - `gestin_de_plan_de_compras_session`

### 3. Verificar Propiedades de las Cookies
Para cada cookie, verifica:
- **Name**: Nombre correcto
- **Value**: Valor no duplicado
- **Domain**: `.imaarica.cl` (con punto al inicio)
- **Path**: `/`
- **Expires**: Fecha futura
- **HttpOnly**: `true` para session, `false` para XSRF-TOKEN
- **Secure**: `true` (para HTTPS)
- **SameSite**: `Lax`

## 🧹 Limpieza Completa del Estado

### Opción 1: Limpieza Manual en DevTools
1. En **Application** → **Storage** → **Cookies**
2. Selecciona tu dominio
3. Haz clic derecho → **Clear**
4. Repite para todos los dominios relacionados

### Opción 2: Limpieza por Consola
```javascript
// Ejecutar en la consola del navegador
function clearAllCookies() {
    const cookies = document.cookie.split(";");
    
    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
        
        // Eliminar cookie para todos los dominios posibles
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;";
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=.imaarica.cl;";
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=dev.imaarica.cl;";
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=imaarica.cl;";
    }
    
    console.log('✅ Todas las cookies han sido eliminadas');
}

// Ejecutar la limpieza
clearAllCookies();
```

### Opción 3: Limpieza por Pestaña
1. Ve a **Application** → **Storage**
2. Haz clic en **Clear storage**
3. Marca todas las opciones
4. Haz clic en **Clear site data**

## 🔄 Flujo de Prueba Completo

### Paso 1: Limpiar Estado
```javascript
// Ejecutar en consola
clearAllCookies();
```

### Paso 2: Verificar Estado Inicial
```javascript
// Verificar que no hay cookies
console.log('Cookies iniciales:', document.cookie);
// Debería mostrar: ""
```

### Paso 3: Probar Flujo de Autenticación
```javascript
// Función para probar el flujo completo
async function testAuthFlow() {
    const baseUrl = 'https://dev.imaarica.cl';
    
    try {
        console.log('1️⃣ Obteniendo CSRF token...');
        const csrfResponse = await fetch(`${baseUrl}/sanctum/csrf-cookie`, {
            method: 'GET',
            credentials: 'include'
        });
        console.log('CSRF Status:', csrfResponse.status);
        console.log('CSRF Headers:', Object.fromEntries(csrfResponse.headers.entries()));
        
        console.log('2️⃣ Haciendo login...');
        const loginResponse = await fetch(`${baseUrl}/api/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include',
            body: JSON.stringify({
                rut: '12345678-9',
                password: 'password123',
                remember: true
            })
        });
        console.log('Login Status:', loginResponse.status);
        console.log('Login Headers:', Object.fromEntries(loginResponse.headers.entries()));
        
        const loginData = await loginResponse.json();
        console.log('Login Data:', loginData);
        
        console.log('3️⃣ Obteniendo usuario...');
        const userResponse = await fetch(`${baseUrl}/api/user`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        });
        console.log('User Status:', userResponse.status);
        console.log('User Headers:', Object.fromEntries(userResponse.headers.entries()));
        
        const userData = await userResponse.json();
        console.log('User Data:', userData);
        
        console.log('4️⃣ Verificando cookies finales...');
        console.log('Cookies finales:', document.cookie);
        
    } catch (error) {
        console.error('❌ Error en el flujo:', error);
    }
}

// Ejecutar el test
testAuthFlow();
```

## 📊 Verificación de Headers de Respuesta

### Headers Esperados en `/sanctum/csrf-cookie`
```
Set-Cookie: XSRF-TOKEN=...; expires=...; path=/; domain=.imaarica.cl; secure; samesite=lax
```

### Headers Esperados en `/api/login`
```
Set-Cookie: gestin_de_plan_de_compras_session=...; expires=...; path=/; domain=.imaarica.cl; secure; httponly; samesite=lax
```

### Headers Esperados en `/api/user`
```
Set-Cookie: gestin_de_plan_de_compras_session=...; expires=...; path=/; domain=.imaarica.cl; secure; httponly; samesite=lax
```

## 🚨 Detección de Problemas

### Problema: Cookies Duplicadas
**Síntomas**:
- Múltiples entradas de la misma cookie en DevTools
- Errores 401 después del login

**Verificación**:
```javascript
// Contar cookies específicas
function countCookies() {
    const cookies = document.cookie.split(';');
    const xsrfCount = cookies.filter(c => c.includes('XSRF-TOKEN')).length;
    const sessionCount = cookies.filter(c => c.includes('gestin_de_plan_de_compras_session')).length;
    
    console.log(`XSRF-TOKEN: ${xsrfCount} cookies`);
    console.log(`Session: ${sessionCount} cookies`);
    
    return { xsrf: xsrfCount, session: sessionCount };
}

countCookies();
```

### Problema: Cookies con Dominio Incorrecto
**Síntomas**:
- Cookies con dominio `dev.imaarica.cl` en lugar de `.imaarica.cl`
- Cookies no persisten entre subdominios

**Verificación**:
```javascript
// Verificar dominio de cookies
function checkCookieDomains() {
    const cookies = document.cookie.split(';');
    cookies.forEach(cookie => {
        if (cookie.includes('XSRF-TOKEN') || cookie.includes('gestin_de_plan_de_compras_session')) {
            console.log('Cookie:', cookie.trim());
        }
    });
}

checkCookieDomains();
```

## 🔧 Comandos de Verificación en Laravel

### Verificar Configuración
```bash
php artisan config:show session
php artisan config:show sanctum
php artisan config:show cors
```

### Verificar Sesiones Activas
```bash
php artisan tinker
>>> DB::table('sessions')->select('id', 'user_id', 'ip_address', 'last_activity')->get();
```

### Limpiar Sesiones Antiguas
```bash
php artisan tinker
>>> DB::table('sessions')->where('last_activity', '<', now()->subHours(1))->delete();
```

## 📱 Verificación en Diferentes Navegadores

### Chrome/Edge
- DevTools → Application → Cookies

### Firefox
- DevTools → Storage → Cookies

### Safari
- Develop → Show Web Inspector → Storage → Cookies

## ✅ Checklist de Verificación

- [ ] Solo una cookie `XSRF-TOKEN` por dominio
- [ ] Solo una cookie `gestin_de_plan_de_compras_session` por dominio
- [ ] Dominio de cookies es `.imaarica.cl`
- [ ] Cookies tienen flag `Secure` (HTTPS)
- [ ] Cookies tienen `SameSite=Lax`
- [ ] Session cookie tiene `HttpOnly=true`
- [ ] XSRF cookie NO tiene `HttpOnly`
- [ ] Login retorna 200 OK
- [ ] `/api/user` retorna 200 OK con datos del usuario
- [ ] No hay errores CORS en la consola
- [ ] No hay errores 401 después del login

---

**Con esta guía completa, podrás identificar y resolver cualquier problema con las cookies duplicadas en tu aplicación Laravel + Angular.** 