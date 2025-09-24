<?php
echo "<h1>🔍 Verificación de Drivers PHP</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
    .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .info { color: #17a2b8; }
    .section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
</style>";

echo "<div class='container'>";

// Verificar versión de PHP
echo "<div class='section'>";
echo "<h2>📊 Información de PHP</h2>";
echo "<p><strong>Versión PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>SAPI:</strong> " . php_sapi_name() . "</p>";
echo "</div>";

// Verificar drivers MySQL
echo "<div class='section'>";
echo "<h2>🗄️ Drivers MySQL</h2>";

$drivers = [
    'PDO' => extension_loaded('pdo'),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'MySQLi' => extension_loaded('mysqli')
];

foreach ($drivers as $driver => $loaded) {
    $status = $loaded ? '✅ Instalado' : '❌ No instalado';
    $class = $loaded ? 'success' : 'error';
    echo "<p><strong>$driver:</strong> <span class='$class'>$status</span></p>";
}
echo "</div>";

// Verificar conexión a MySQL
echo "<div class='section'>";
echo "<h2>🔌 Conexión a MySQL</h2>";

try {
    require_once 'config.php';
    echo "<p class='success'>✅ Conexión a MySQL exitosa</p>";
    echo "<p class='info'>Base de datos: mydb</p>";
    echo "<p class='info'>Host: db (Docker)</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Mostrar todas las extensiones cargadas
echo "<div class='section'>";
echo "<h2>📋 Todas las Extensiones PHP</h2>";
echo "<p>Extensiones cargadas: " . implode(', ', get_loaded_extensions()) . "</p>";
echo "</div>";

echo "</div>";

echo "<br><a href='index.php'>← Volver al menú principal</a>";
?>
