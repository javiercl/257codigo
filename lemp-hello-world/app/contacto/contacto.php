<?php
require_once '../config.php';

$mensaje = '';
$error = '';

// Procesar formulario
if ($_POST) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensaje_texto = trim($_POST['mensaje'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre es requerido';
    } elseif (empty($email)) {
        $error = 'El email es requerido';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido';
    } elseif (empty($mensaje_texto)) {
        $error = 'El mensaje es requerido';
    } else {
        try {
            // Insertar en la base de datos
            $stmt = $pdo->prepare("INSERT INTO contacto (nombre, email, mensaje) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $email, $mensaje_texto]);
            
            $mensaje = 'Mensaje enviado correctamente. Gracias por contactarnos.';
            
            // Limpiar variables para evitar reenvío
            $nombre = $email = $mensaje_texto = '';
            
        } catch(PDOException $e) {
            $error = 'Error al enviar el mensaje. Inténtalo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de contacto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            height: 120px;
            resize: vertical;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .mensaje {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .mensaje.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Formulario de contacto</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="contacto.php" method="post">
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="mensaje">Mensaje *</label>
                <textarea id="mensaje" name="mensaje" required><?php echo htmlspecialchars($mensaje_texto ?? ''); ?></textarea>
            </div>
            
            <button type="submit">Enviar Mensaje</button>
        </form>
        
        <div class="back-link">
            <a href="index.php">← Módulo de Contacto</a>
            <a href="../index.php" style="margin-left: 20px;">🏠 Menú Principal</a>
        </div>
    </div>
</body>
</html>