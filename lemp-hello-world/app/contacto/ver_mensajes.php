<?php
require_once '../config.php';

try {
    // Obtener todos los mensajes ordenados por fecha
    $stmt = $pdo->query("SELECT * FROM contacto ORDER BY fecha DESC");
    $mensajes = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error al obtener los mensajes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes de Contacto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .mensaje-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fafafa;
        }
        .mensaje-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .mensaje-nombre {
            font-weight: bold;
            color: #333;
            font-size: 18px;
        }
        .mensaje-fecha {
            color: #666;
            font-size: 14px;
        }
        .mensaje-email {
            color: #007bff;
            margin-bottom: 10px;
        }
        .mensaje-texto {
            color: #555;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .no-mensajes {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid #007bff;
            border-radius: 4px;
            display: inline-block;
        }
        .back-link a:hover {
            background-color: #007bff;
            color: white;
        }
        .stats {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mensajes de Contacto</h1>
        
        <?php if (isset($error)): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($mensajes)): ?>
            <div class="stats">
                <strong>Total de mensajes: <?php echo count($mensajes); ?></strong>
            </div>
            
            <?php foreach ($mensajes as $mensaje): ?>
                <div class="mensaje-card">
                    <div class="mensaje-header">
                        <div class="mensaje-nombre"><?php echo htmlspecialchars($mensaje['nombre']); ?></div>
                        <div class="mensaje-fecha"><?php echo date('d/m/Y H:i:s', strtotime($mensaje['fecha'])); ?></div>
                    </div>
                    <div class="mensaje-email">📧 <?php echo htmlspecialchars($mensaje['email']); ?></div>
                    <div class="mensaje-texto"><?php echo htmlspecialchars($mensaje['mensaje']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-mensajes">
                No hay mensajes de contacto aún.
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.php">← Módulo de Contacto</a>
            <a href="../index.php" style="margin-left: 20px;">🏠 Menú Principal</a>
        </div>
    </div>
</body>
</html>
