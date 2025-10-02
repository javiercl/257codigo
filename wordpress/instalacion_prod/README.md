# WordPress con Docker + SSL - Producción

Este proyecto configura WordPress con Docker para producción en el subdominio `cms.hotland.com.mx`, incluyendo SSL automático con Let's Encrypt.

## 🏗️ Arquitectura

- **WordPress**: Aplicación principal
- **MySQL 8.0**: Base de datos
- **Nginx**: Servidor web y proxy reverso
- **Certbot**: Certificados SSL automáticos

## 🚀 Inicio Rápido

### 1. Preparar el entorno
```bash
# Crear directorios necesarios
mkdir -p nginx/conf.d certbot/conf certbot/www

# Verificar que Docker esté funcionando
docker --version
docker-compose --version

# Verificar que el dominio resuelve a esta IP
nslookup cms.hotland.com.mx
```

### 2. Crear configuración inicial (sin SSL)
```bash
# Crear archivo de configuración temporal sin SSL
cat > nginx/conf.d/default.conf << 'EOF'
# Configuración temporal para obtener certificados SSL
server {
    listen 80;
    server_name cms.hotland.com.mx;
    
    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;
    
    # Validación para Certbot (Let's Encrypt)
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
        try_files $uri =404;
    }
    
    # Proxy temporal a WordPress (sin SSL)
    location / {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

# Iniciar servicios
docker-compose up -d

# Verificar que están funcionando
docker-compose ps
```

### 3. Obtener certificados SSL
```bash
# Generar certificados SSL
docker-compose run --rm certbot

# Verificar que se crearon
ls -la certbot/conf/live/cms.hotland.com.mx/
```

### 4. Crear configuración de producción (con SSL)
```bash
# Crear archivo de configuración con SSL
cat > nginx/conf.d/default.conf << 'EOF'
# Configuración de Nginx para WordPress - Producción
# Subdominio: cms.hotland.com.mx

# Redirección HTTP a HTTPS
server {
    listen 80;
    server_name cms.hotland.com.mx;
    
    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;
    
    # Redirección automática a HTTPS
    return 301 https://$server_name$request_uri;
}

# Configuración HTTPS
server {
    listen 443 ssl http2;
    server_name cms.hotland.com.mx;
    
    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;
    
    # Certificados SSL
    ssl_certificate /etc/letsencrypt/live/cms.hotland.com.mx/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/cms.hotland.com.mx/privkey.pem;
    
    # Configuración SSL moderna
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Headers de seguridad
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    
    # Configuración de archivos estáticos
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Cache para archivos estáticos
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Validación para Certbot (Let's Encrypt)
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
        try_files $uri =404;
    }
    
    # Proxy principal a WordPress
    location / {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
        
        # Buffer settings
        proxy_buffering on;
        proxy_buffer_size 4k;
        proxy_buffers 8 4k;
        proxy_busy_buffers_size 8k;
    }
    
    # Configuración específica para WordPress
    location ~ ^/wp-admin {
        proxy_pass http://wordpress:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Denegar acceso a archivos sensibles
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(wp-config\.php|wp-config-sample\.php|readme\.html|license\.txt) {
        deny all;
        access_log off;
        log_not_found off;
    }
}
EOF

# Verificar configuración de Nginx
docker-compose exec nginx nginx -t

# Recargar Nginx
docker-compose exec nginx nginx -s reload
```

### 5. Acceder a WordPress
```bash
# Abrir en el navegador
# https://cms.hotland.com.mx
# https://cms.hotland.com.mx/wp-admin
```

## 📁 Estructura de Archivos

```
wordpress/instalacion_prod/
├── docker-compose.yaml          # Configuración de servicios Docker
├── README.md                    # Este archivo
├── nginx/                       # Se crea con: mkdir -p nginx/conf.d
│   └── conf.d/
│       └── default.conf         # Se crea con comandos del README
└── certbot/                     # Se crea con: mkdir -p certbot/conf certbot/www
    ├── conf/                    # Certificados SSL
    └── www/                     # Validación webroot
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

## 🔐 Gestión de Certificados SSL

### Generar Certificados Iniciales
```bash
# Verificar que el dominio es accesible
curl -I http://cms.hotland.com.mx

# Generar certificados SSL
docker-compose run --rm certbot

# Verificar certificados
ls -la certbot/conf/live/cms.hotland.com.mx/

# Ver información del certificado
docker-compose exec nginx openssl x509 -in /etc/letsencrypt/live/cms.hotland.com.mx/cert.pem -text -noout
```

### Renovar Certificados
```bash
# Renovar certificados existentes
docker-compose run --rm certbot renew

# Forzar renovación
docker-compose run --rm certbot renew --force-renewal

# Verificar renovación
docker-compose exec nginx nginx -t
docker-compose exec nginx nginx -s reload
```

### Configuración de Renovación Automática

#### Opción 1: Cron Job (Recomendado)
```bash
# Editar crontab
crontab -e

# Agregar esta línea para renovar diariamente a las 2:00 AM
0 2 * * * cd /ruta/completa/a/wordpress/instalacion_prod && docker-compose run --rm certbot renew && docker-compose exec nginx nginx -s reload
```

#### Opción 2: Script de Renovación
```bash
# Crear script de renovación
cat > renew-ssl.sh << 'EOF'
#!/bin/bash
cd /ruta/completa/a/wordpress/instalacion_prod
docker-compose run --rm certbot renew
docker-compose exec nginx nginx -s reload
echo "$(date): SSL renewal completed" >> ssl-renewal.log
EOF

# Hacer ejecutable
chmod +x renew-ssl.sh

# Agregar al crontab
echo "0 2 * * * /ruta/completa/a/wordpress/instalacion_prod/renew-ssl.sh" | crontab -
```

## 🌐 Configuración de Dominio

### DNS
Asegúrate de que el subdominio `cms.hotland.com.mx` apunte a la IP del servidor:
```
A    cms.hotland.com.mx → [IP_DEL_SERVIDOR]
```

### Puertos
- **HTTP**: 80 (redirección a HTTPS)
- **HTTPS**: 443 (acceso principal)

### URLs de Acceso
- **WordPress**: https://cms.hotland.com.mx
- **Admin**: https://cms.hotland.com.mx/wp-admin

## 🔐 Seguridad

### Headers de Seguridad
El Nginx está configurado con:
- HSTS (HTTP Strict Transport Security)
- X-Frame-Options
- X-Content-Type-Options
- X-XSS-Protection

### Certificados SSL
- Renovación automática con Let's Encrypt
- Configuración SSL moderna (TLS 1.2/1.3)
- Cifrados seguros


## 🛠️ Solución de Problemas

### Error de Certificados SSL
```bash
# Verificar que el dominio es accesible
curl -I http://cms.hotland.com.mx

# Verificar configuración de Nginx
docker-compose exec nginx nginx -t

# Ver logs de Certbot
docker-compose logs certbot

# Recrear certificados
docker-compose run --rm certbot --force-renewal
```

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

## 📞 Soporte

Si tienes problemas:
1. Verifica los logs: `docker-compose logs`
2. Verifica la configuración: `docker-compose exec nginx nginx -t`
3. Verifica el estado: `docker-compose ps`
4. Verifica el DNS: `nslookup cms.hotland.com.mx`
5. Verifica los certificados: `ls -la certbot/conf/live/cms.hotland.com.mx/`

## 🎯 Próximos Pasos

1. **Configurar WordPress**: Accede a https://cms.hotland.com.mx/wp-admin
2. **Configurar tema**: Instala y personaliza tu tema
3. **Configurar plugins**: Instala plugins necesarios
4. **Configurar backup**: Configura backup automático de la base de datos
5. **Configurar monitoreo**: Configura alertas y monitoreo
6. **Configurar renovación SSL**: Configura renovación automática de certificados