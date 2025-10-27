# WordPress con SSL/TLS en Producción

Esta configuración está diseñada para ejecutar WordPress en producción con certificados SSL/TLS gestionados por Certbot (Let's Encrypt).

## 📋 Requisitos Previos

1. **Dominio configurado**: El dominio `cms.hotland.com.mx` debe apuntar al servidor
2. **Puertos abiertos**: 80 (HTTP) y 443 (HTTPS)
3. **Docker y Docker Compose** instalados

## 🚀 Instalación

### Paso 1: Preparar el entorno

```bash
# Crear los directorios necesarios
mkdir -p certbot/www certbot/conf

# Crear directorio para archivos de WordPress
mkdir -p wordpress_files

# Dar permisos correctos a los directorios
chmod -R 777 wordpress_files
chmod -R 777 certbot
```

### Paso 2: Iniciar los servicios

```bash
# Iniciar solo WordPress, MySQL y Nginx (sin SSL)
docker-compose up -d wordpress db nginx
```

### Paso 3: Obtener certificados SSL

```bash
# Obtener los certificados SSL de Let's Encrypt
docker-compose run --rm certbot

# Si necesitas renovar, usa:
docker-compose run --rm certbot renew
```

### Paso 4: Reiniciar Nginx con configuración SSL

```bash
# Reiniciar Nginx para cargar la configuración con SSL
docker-compose restart nginx
```

## 🔧 Configuración

### Variables de Entorno

Edita el archivo `docker-compose.yaml` para cambiar:

- **Dominio**: `cms.hotland.com.mx` (reemplaza en múltiples lugares)
- **Email**: `admin@hotland.com.mx`
- **Credenciales de base de datos**: `wp_user`, `wp_pass`, `root_pass`

### Nginx

La configuración de Nginx incluye:

- ✅ Redirección automática HTTP → HTTPS
- ✅ Certificados SSL/TLS de Let's Encrypt
- ✅ Configuración de seguridad moderna (TLS 1.2/1.3)
- ✅ Headers de seguridad (HSTS, X-Frame-Options, etc.)
- ✅ Cache para archivos estáticos
- ✅ Proxy reverso a WordPress

### WordPress

WordPress está configurado para:

- ✅ Usar URLs HTTPS
- ✅ Forzar SSL en el admin
- ✅ Deshabilitar debug en producción
- ✅ Aumentar límite de memoria a 512MB

## 🔄 Renovación de Certificados

Los certificados de Let's Encrypt duran 90 días. Para renovar automáticamente:

```bash
# Agregar a cron para renovación automática
docker-compose run --rm certbot renew --dry-run

# Configurar renovación automática con cron
echo "0 0 * * * cd $(pwd) && docker-compose run --rm certbot renew && docker-compose restart nginx" | crontab -
```

## 📝 Comandos Útiles

```bash
# Ver logs
docker-compose logs -f nginx
docker-compose logs -f wordpress

# Reiniciar servicios
docker-compose restart nginx
docker-compose restart wordpress

# Detener todo
docker-compose down

# Iniciar todo
docker-compose up -d
```

## 🔒 Seguridad

- ✅ Certificados SSL/TLS válidos
- ✅ Headers de seguridad
- ✅ HSTS habilitado
- ✅ Cifrado moderno (TLS 1.2/1.3)
- ✅ OCSP Stapling
- ✅ WordPress con SSL forzado

## 📧 Soporte

Para problemas con certificados:
- Verifica que el dominio apunte al servidor
- Confirma que los puertos 80 y 443 estén abiertos
- Revisa los logs: `docker-compose logs certbot`

## 🌐 URLs

- **Producción**: https://cms.hotland.com.mx
- **Admin**: https://cms.hotland.com.mx/wp-admin
- **Auto-redirect HTTP**: http://cms.hotland.com.mx → https://cms.hotland.com.mx
