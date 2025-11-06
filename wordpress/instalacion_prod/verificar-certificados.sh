#!/bin/bash

# Script para verificar que los certificados SSL están disponibles

echo "🔍 Verificando certificados SSL..."
echo ""

DOMAIN="cms.hotland.com.mx"
CERT_PATH="certbot/conf/live/$DOMAIN"

# Verificar directorio de certificados
if [ ! -d "$CERT_PATH" ]; then
    echo "❌ No se encontró el directorio de certificados: $CERT_PATH"
    echo ""
    echo "📋 Para obtener los certificados, ejecuta:"
    echo "   docker-compose run --rm certbot"
    exit 1
fi

echo "✅ Directorio de certificados encontrado: $CERT_PATH"
echo ""

# Verificar archivos de certificados
FILES=("fullchain.pem" "privkey.pem" "chain.pem" "cert.pem")
ALL_FILES_EXIST=true

for file in "${FILES[@]}"; do
    if [ -f "$CERT_PATH/$file" ]; then
        SIZE=$(stat -f%z "$CERT_PATH/$file" 2>/dev/null || stat -c%s "$CERT_PATH/$file" 2>/dev/null)
        echo "✅ $file existe ($SIZE bytes)"
    else
        echo "❌ $file NO existe"
        ALL_FILES_EXIST=false
    fi
done

echo ""

if [ "$ALL_FILES_EXIST" = false ]; then
    echo "❌ Faltan algunos archivos de certificados"
    echo "   Ejecuta: docker-compose run --rm certbot"
    exit 1
fi

# Verificar validez del certificado (si openssl está disponible)
if command -v openssl &> /dev/null; then
    echo "🔐 Verificando validez del certificado..."
    if openssl x509 -in "$CERT_PATH/fullchain.pem" -noout -text 2>/dev/null | grep -q "Issuer:"; then
        EXPIRY=$(openssl x509 -in "$CERT_PATH/fullchain.pem" -noout -enddate 2>/dev/null | cut -d= -f2)
        SUBJECT=$(openssl x509 -in "$CERT_PATH/fullchain.pem" -noout -subject 2>/dev/null | cut -d= -f2-)
        echo "✅ Certificado válido"
        echo "   Sujeto: $SUBJECT"
        echo "   Expira: $EXPIRY"
    else
        echo "⚠️  No se pudo verificar la validez del certificado"
    fi
fi

echo ""
echo "✅ Todos los certificados están disponibles"
echo "   Puedes activar HTTPS ejecutando: ./activar-https.sh"

