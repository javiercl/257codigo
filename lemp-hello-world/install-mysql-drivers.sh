#!/bin/bash

echo "🔧 Instalando drivers MySQL para PHP..."

# Detener contenedores si están corriendo
echo "⏹️ Deteniendo contenedores..."
docker-compose down

# Reconstruir imagen PHP con drivers MySQL
echo "🏗️ Reconstruyendo imagen PHP..."
docker-compose build --no-cache php

# Iniciar contenedores
echo "🚀 Iniciando contenedores..."
docker-compose up -d

# Verificar instalación
echo "✅ Verificando instalación de drivers..."
docker exec lemp-php php -m | grep -E "(pdo|mysql)"

echo "🎉 ¡Instalación completada!"
echo ""
echo "📋 Drivers MySQL instalados:"
echo "   - PDO (PHP Data Objects)"
echo "   - PDO MySQL (Driver MySQL para PDO)"
echo "   - MySQLi (MySQL Improved Extension)"
echo ""
echo "🌐 Accesos:"
echo "   - Aplicación: http://localhost:8080"
echo "   - phpMyAdmin: http://localhost:8081"
echo ""
echo "🔍 Para verificar la conexión, visita: http://localhost:8080/crear_tabla.php"
