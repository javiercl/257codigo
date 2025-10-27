# WordPress con Docker - Desarrollo Local

Este proyecto configura WordPress con Docker para desarrollo local, optimizado para desarrollo y testing. Sin SSL para facilitar el desarrollo.

## 🏗️ Arquitectura

- **WordPress**: Aplicación principal
- **MySQL 8.0**: Base de datos
- **Nginx**: Servidor web y proxy reverso (puerto 8080)

## 🚀 Inicio Rápido

### 1. Preparar el entorno
```bash
# Crear directorios necesarios
mkdir -p nginx/conf.d

# Verificar que Docker esté funcionando
docker --version
docker-compose --version
```

### Configuración de Nginx (`nginx/conf.d/default.conf`)
```nginx
# Configuración de Nginx para WordPress - Desarrollo Local
# URLs: localhost:8080, cms.local:8080

server {
    listen 80;
    server_name localhost cms.local;
    
    # Logs detallados para desarrollo
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log debug;
    
    # Configuración para archivos estáticos
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Sin cache en desarrollo
        expires -1;
        add_header Cache-Control "no-cache, no-store, must-revalidate";
    }
    
    # Proxy principal a WordPress
    location / {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        
        # Timeouts más largos para desarrollo
        proxy_connect_timeout 120s;
        proxy_send_timeout 120s;
        proxy_read_timeout 120s;
    }
    
    # Configuración específica para WordPress admin
    location ~ ^/wp-admin {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Configuración para archivos de WordPress
    location ~ ^/wp-content {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Configuración para archivos de WordPress
    location ~ ^/wp-includes {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 2. Iniciar servicios
```bash
# Iniciar todos los servicios
docker-compose up -d

# Verificar que están funcionando
docker-compose ps
```

### 3. Acceder a WordPress
```bash
# Abrir en el navegador
# http://localhost:8080
# http://cms.local:8080 (opcional, requiere configuración de hosts)
```

**Nota**: WordPress está configurado automáticamente para funcionar con el puerto 8080. No necesitas configurar manualmente las URLs.

## 📁 Estructura de Archivos

```
wordpress/instalacion_local/
├── docker-compose.yaml          # Configuración de servicios Docker
├── README.md                    # Este archivo
└── nginx/
    └── conf.d/
        └── default.conf         # Configuración de Nginx para desarrollo
```

## 🔧 Comandos de Gestión

### Gestión de Servicios
```bash
# Iniciar servicios
docker-compose up -d

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs wordpress
docker-compose logs nginx
docker-compose logs db

# Detener servicios
docker-compose down

# Reiniciar servicios
docker-compose restart

# Reiniciar un servicio específico
docker-compose restart wordpress
docker-compose restart nginx
docker-compose restart db
```

### Estado y Monitoreo
```bash
# Ver estado de contenedores
docker-compose ps

# Ver uso de recursos
docker stats

# Verificar configuración de Nginx
docker-compose exec nginx nginx -t

# Recargar configuración de Nginx
docker-compose exec nginx nginx -s reload
```

### Base de Datos
```bash
# Conectar a MySQL
docker-compose exec db mysql -u wp_user -p wp_db

# Backup de la base de datos
docker-compose exec db mysqldump -u wp_user -p wp_db > backup.sql

# Restaurar backup
docker-compose exec -T db mysql -u wp_user -p wp_db < backup.sql

# Ver logs de MySQL
docker-compose logs db
```

## 🌐 URLs de Acceso

### URLs Principales
- **WordPress**: http://localhost:8080
- **Admin**: http://localhost:8080/wp-admin

### URLs Opcionales (requieren configuración de hosts)
- **WordPress**: http://cms.local:8080
- **Admin**: http://cms.local:8080/wp-admin

### Configuración de Hosts (Opcional)
Para usar `cms.local:8080`, agrega esta línea a tu archivo hosts:

**Windows**: `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac**: `/etc/hosts`
```
127.0.0.1    cms.local
```

### Puertos
- **HTTP**: 8080 (Nginx)
- **MySQL**: 3306 (interno del contenedor)

## 🔧 Configuración de Desarrollo

### Características de Desarrollo
- **Sin SSL**: Para facilitar el desarrollo
- **Sin cache**: Archivos estáticos sin cache
- **Logs detallados**: Para debugging
- **Timeouts largos**: Para desarrollo
- **Puerto 8080**: Evita conflictos con otros servicios

## 🛠️ Solución de Problemas

### Error de Conexión
```bash
# Verificar que los servicios estén funcionando
docker-compose ps

# Ver logs de errores
docker-compose logs

# Reiniciar servicios
docker-compose restart
```

### Error de Nginx
```bash
# Verificar configuración
docker-compose exec nginx nginx -t

# Recargar configuración
docker-compose exec nginx nginx -s reload

# Ver logs de Nginx
docker-compose logs nginx
```

### Error de Base de Datos
```bash
# Verificar logs de MySQL
docker-compose logs db

# Conectar a MySQL
docker-compose exec db mysql -u wp_user -p wp_db

# Reiniciar base de datos
docker-compose restart db
```

### Error de WordPress
```bash
# Ver logs de WordPress
docker-compose logs wordpress

# Reiniciar WordPress
docker-compose restart wordpress
```

## 📞 Soporte

Si tienes problemas:
1. Verifica los logs: `docker-compose logs`
2. Verifica la configuración: `docker-compose exec nginx nginx -t`
3. Verifica el estado: `docker-compose ps`
4. Reinicia los servicios: `docker-compose restart`

## 🎯 Próximos Pasos

1. **Configurar WordPress**: Accede a http://localhost:8080/wp-admin
2. **Configurar tema**: Instala y personaliza tu tema
3. **Configurar plugins**: Instala plugins necesarios
4. **Configurar backup**: Usa los comandos de backup de MySQL
5. **Desarrollar**: Modifica archivos y ve los cambios en tiempo real
