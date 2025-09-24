<?php
require_once '../config.php';

try {
    // Crear la tabla contacto
    $sql = "CREATE TABLE IF NOT EXISTS contacto (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mensaje TEXT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Tabla 'contacto' creada exitosamente.<br>";
    
    // Verificar que la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'contacto'");
    if ($stmt->rowCount() > 0) {
        echo "✅ La tabla 'contacto' existe en la base de datos.<br>";
        
        // Mostrar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE contacto");
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
<a href="index.php">← Módulo de Contacto</a>
<a href="../index.php" style="margin-left: 20px;">🏠 Menú Principal</a>
