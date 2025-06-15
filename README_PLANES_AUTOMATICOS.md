# Configuración de Planes de Compra Automáticos

## 🚀 Comandos de Configuración Inicial

### 1. Verificar que los Seeders estén ejecutados
```bash
# Ejecutar seeders necesarios
php artisan db:seed --class=StatusPurchasePlanSeeder
php artisan db:seed --class=DirectionSeeder
php artisan db:seed --class=UserSeeder
```

### 2. Configurar el Cron de Laravel (OBLIGATORIO para automático)
```bash
# Editar crontab del servidor
crontab -e

# Agregar esta línea (reemplazar /ruta-del-proyecto con la ruta real)
* * * * * cd /ruta-del-proyecto && php artisan schedule:run >> /dev/null 2>&1
```

**Ejemplo con ruta completa:**
```bash
* * * * * cd /c/Users/User/Desktop/proyectos/plancompras/plancompra-back && php artisan schedule:run >> /dev/null 2>&1
```

## 🎯 Comandos de Ejecución

### Ejecutar Manualmente (Para Pruebas)
```bash
# Crear planes para el año actual
php artisan purchase-plans:create-annual

# Crear planes para un año específico
php artisan purchase-plans:create-annual --year=2024

# Forzar creación (actualizar existentes)
php artisan purchase-plans:create-annual --force
```

### Verificar el Schedule está funcionando
```bash
# Ver todos los comandos programados
php artisan schedule:list

# Ejecutar manualmente el schedule (simular cron)
php artisan schedule:run

# Ejecutar en modo debug
php artisan schedule:run --verbose
```

## 🔍 Comandos de Verificación

### Verificar Base de Datos
```bash
# Verificar que existan direcciones
php artisan tinker
>>> App\Models\Direction::count()
>>> App\Models\Direction::with('director')->get()

# Verificar estados de planes
>>> App\Models\StatusPurchasePlan::all()

# Verificar planes existentes
>>> App\Models\PurchasePlan::with('direction')->get()
```

### Verificar Logs
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Buscar logs específicos de planes
grep "planes de compra" storage/logs/laravel.log

# Ver últimas 50 líneas de logs
tail -50 storage/logs/laravel.log
```

## 🛠️ Comandos de Mantenimiento

### Limpiar Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimizar para Producción
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 🔧 Configuración del Servidor (Producción)

### Para Ubuntu/Debian
```bash
# Instalar cron si no está instalado
sudo apt-get install cron

# Verificar que cron esté corriendo
sudo service cron status

# Iniciar cron si no está corriendo
sudo service cron start

# Habilitar cron al inicio
sudo systemctl enable cron
```

### Para Windows (usando Task Scheduler)
```cmd
# Crear tarea programada que ejecute cada minuto:
schtasks /create /tn "Laravel Scheduler" /tr "php C:\ruta\del\proyecto\artisan schedule:run" /sc minute /mo 1
```

## 📋 Lista de Verificación Pre-Producción

### ✅ Requisitos Previos
- [ ] Base de datos configurada y migrada
- [ ] Seeders ejecutados (direcciones, estados, usuarios)
- [ ] Cron de Laravel configurado en el servidor
- [ ] Permisos de escritura en `storage/logs/`
- [ ] PHP CLI disponible en PATH del servidor

### ✅ Pruebas Recomendadas
```bash
# 1. Probar comando manual
php artisan purchase-plans:create-annual --year=2024

# 2. Verificar que se crearon los planes
php artisan tinker
>>> App\Models\PurchasePlan::where('year', 2024)->count()

# 3. Probar el schedule manualmente
php artisan schedule:run

# 4. Verificar logs
tail -20 storage/logs/laravel.log
```

## 🕐 Programación Automática

**La tarea está configurada para ejecutarse:**
- **Fecha**: 1 de junio de cada año
- **Hora**: 6:00 AM (servidor)
- **Comando**: `purchase-plans:create-annual`
- **Configuración**: `app/Console/Kernel.php`

## 🚨 Solución de Problemas

### El cron no se ejecuta
```bash
# Verificar sintaxis del crontab
crontab -l

# Verificar logs del cron del sistema
sudo tail -f /var/log/cron.log

# Probar manualmente
cd /ruta-del-proyecto && php artisan schedule:run
```

### No se crean planes
```bash
# Verificar que existan direcciones
php artisan tinker
>>> App\Models\Direction::count()

# Verificar que tengan directores asignados
>>> App\Models\Direction::whereNull('director_id')->count()

# Ejecutar con debug
php artisan purchase-plans:create-annual --verbose
```

### Errores de permisos
```bash
# Dar permisos a storage
chmod -R 775 storage/
chown -R www-data:www-data storage/

# O para desarrollo local
chmod -R 777 storage/
```

---

## 📞 Contacto y Soporte

Si tienes problemas con la configuración, verifica:
1. Los logs en `storage/logs/laravel.log`
2. Que el cron esté configurado correctamente
3. Que existan direcciones municipales en la base de datos
4. Que los seeders hayan sido ejecutados 