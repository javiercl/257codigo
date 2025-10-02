<?php
require_once '../config.php';

try {
    // Verificar si la tabla existe antes de eliminarla
    $stmt = $pdo->query("SHOW TABLES LIKE 'productos'");
    if ($stmt->rowCount() > 0) {
        echo "⚠️ <strong>ADVERTENCIA:</strong> Se eliminará la tabla 'productos' existente y todos sus datos.<br>";
        echo "📊 Si tienes productos registrados, estos se perderán permanentemente.<br><br>";
    }
    
    // Eliminar la tabla existente si existe
    $pdo->exec("DROP TABLE IF EXISTS productos");
    echo "🗑️ Tabla 'productos' anterior eliminada.<br>";
    
    // Crear la nueva tabla productos con código de barras como identificador principal
    $sql = "CREATE TABLE productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo_barras VARCHAR(50) NOT NULL UNIQUE,
        descripcion VARCHAR(255) NOT NULL,
        clasificacion VARCHAR(100) NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        unidad_medida VARCHAR(20) NOT NULL,
        imagen VARCHAR(255) NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Nueva tabla 'productos' creada exitosamente con código de barras como identificador principal.<br>";
    echo "📝 <strong>Cambios realizados:</strong><br>";
    echo "   • Eliminado campo 'clave_producto'<br>";
    echo "   • 'codigo_barras' ahora es obligatorio y único<br>";
    echo "   • Código de barras es el identificador principal del producto<br><br>";
    
    // Verificar que la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'productos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ La tabla 'productos' existe en la base de datos.<br>";
        
        // Mostrar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE productos");
        echo "<br><strong>Estructura de la tabla:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(PDOException $e) {
    echo "❌ Error al crear la tabla: " . $e->getMessage();
}
?>

<br><br>
<a href="index.php">← Módulo de Productos</a>
<a href="../index.php" style="margin-left: 20px;">🏠 Menú Principal</a>
