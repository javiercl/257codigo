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

### Paso 2: Verificar configuración de Nginx

```bash
# Verificar que el archivo default.conf existe
ls -la nginx/conf.d/default.conf

# Si no existe, créalo o verifica que estás en el directorio correcto
```

### Paso 3: Iniciar los servicios

```bash
# IMPORTANTE: Ejecuta docker-compose desde el directorio instalacion_prod
cd wordpress/instalacion_prod

# Iniciar solo WordPress, MySQL y Nginx (sin SSL)
docker-compose up -d wordpress db nginx

# Verificar que Nginx puede ver el archivo de configuración
chmod +x verificar-nginx.sh
./verificar-nginx.sh
```

### Paso 4: Obtener certificados SSL

```bash
# Obtener los certificados SSL de Let's Encrypt
docker-compose run --rm certbot

# Si necesitas renovar, usa:
docker-compose run --rm certbot renew
```

### Paso 5: Activar HTTPS

**Opción A: Usando el script automático (Recomendado)**

```bash
# Dar permisos de ejecución a los scripts
chmod +x verificar-certificados.sh activar-https.sh

# Verificar que los certificados están disponibles
./verificar-certificados.sh

# Activar HTTPS automáticamente
./activar-https.sh
```

**Opción B: Manualmente**

1. Edita `nginx/conf.d/default.conf`
2. Comenta el bloque HTTP (líneas 8-90)
3. Descomenta la redirección HTTP → HTTPS (líneas 105-112)
4. Descomenta el bloque HTTPS completo (líneas 117-207)
5. Reinicia Nginx: `docker-compose restart nginx`

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
# Probar renovación (dry-run)
docker-compose run --rm certbot renew --dry-run

# Renovar certificados
docker-compose run --rm certbot renew

# Configurar renovación automática con cron (renueva cada 30 días)
echo "0 2 1 * * cd $(pwd) && docker-compose run --rm certbot renew && docker-compose restart nginx" | crontab -
```

**Nota:** Los certificados se renuevan automáticamente si quedan menos de 30 días para expirar.

## 📝 Comandos Útiles

### Scripts de Gestión

```bash
# Verificar que Nginx puede ver el archivo default.conf
chmod +x verificar-nginx.sh
./verificar-nginx.sh

# Verificar que los certificados SSL están disponibles
chmod +x verificar-certificados.sh
./verificar-certificados.sh
```

### Solución de Problemas

**Problema: Nginx no encuentra el archivo default.conf**

```bash
# 1. Verifica que estás en el directorio correcto
pwd
# Debe mostrar: .../wordpress/instalacion_prod

# 2. Verifica que el archivo existe
ls -la nginx/conf.d/default.conf

# 3. Verifica que el contenedor puede ver el archivo
docker-compose exec nginx ls -la /etc/nginx/conf.d/

# 4. Si el archivo no aparece en el contenedor:
#    - Detén el contenedor: docker-compose down
#    - Verifica permisos: chmod 644 nginx/conf.d/default.conf
#    - Reinicia: docker-compose up -d nginx
```

### Comandos Docker

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
