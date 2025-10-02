#!/bin/bash

echo "🔄 Convirtiendo archivos .sh de Windows a Linux..."

# Verificar si dos2unix está instalado
if ! command -v dos2unix &> /dev/null; then
    echo "📦 Instalando dos2unix..."
    if command -v apt-get &> /dev/null; then
        sudo apt-get update && sudo apt-get install -y dos2unix
    elif command -v yum &> /dev/null; then
        sudo yum install -y dos2unix
    elif command -v dnf &> /dev/null; then
        sudo dnf install -y dos2unix
    else
        echo "❌ No se pudo instalar dos2unix automáticamente"
        echo "Instala manualmente: sudo apt-get install dos2unix"
        exit 1
    fi
fi

# Convertir todos los archivos .sh
echo "🔧 Convirtiendo terminadores de línea..."
for file in *.sh; do
    if [ -f "$file" ]; then
        echo "   📄 Procesando: $file"
        dos2unix "$file"
        chmod +x "$file"
    fi
done

echo "✅ Conversión completada!"
echo ""
echo "📋 Archivos convertidos:"
ls -la *.sh

echo ""
echo "🧪 Probando el script principal..."
if [ -f "install-mysql-drivers.sh" ]; then
    echo "Ejecutando: ./install-mysql-drivers.sh"
    ./install-mysql-drivers.sh
else
    echo "❌ No se encontró install-mysql-drivers.sh"
fi

