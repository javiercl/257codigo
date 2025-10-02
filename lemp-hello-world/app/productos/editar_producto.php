<?php
require_once '../config.php';

$mensaje = '';
$error = '';
$producto = null;

// Obtener ID del producto
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: listar_productos.php');
    exit;
}

// Obtener datos del producto
try {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        header('Location: listar_productos.php');
        exit;
    }
} catch(PDOException $e) {
    $error = "Error al obtener el producto: " . $e->getMessage();
}

// Procesar formulario
if ($_POST) {
    $codigo_barras = trim($_POST['codigo_barras'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $clasificacion = trim($_POST['clasificacion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $unidad_medida = trim($_POST['unidad_medida'] ?? '');
    $imagen = $producto['imagen']; // Mantener imagen actual por defecto
    
    // Validaciones
    if (empty($codigo_barras)) {
        $error = 'El código de barras es requerido';
    } elseif (empty($descripcion)) {
        $error = 'La descripción es requerida';
    } elseif (empty($clasificacion)) {
        $error = 'La clasificación es requerida';
    } elseif (empty($precio) || !is_numeric($precio) || $precio <= 0) {
        $error = 'El precio debe ser un número mayor a 0';
    } elseif (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $error = 'El stock debe ser un número mayor o igual a 0';
    } elseif (empty($unidad_medida)) {
        $error = 'La unidad de medida es requerida';
    } else {
        // Procesar nueva imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'imagenes/';
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['imagen']['type'], $allowedTypes)) {
                $error = 'Tipo de archivo no permitido. Solo se permiten JPG, PNG, GIF y WebP.';
            } elseif ($_FILES['imagen']['size'] > $maxSize) {
                $error = 'El archivo es demasiado grande. Máximo 5MB.';
            } else {
                // Eliminar imagen anterior si existe
                if ($producto['imagen'] && file_exists($producto['imagen'])) {
                    unlink($producto['imagen']);
                }
                
                $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $fileName = $codigo_barras . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
                    $imagen = $uploadPath;
                } else {
                    $error = 'Error al subir la imagen.';
                }
            }
        }
        
        if (empty($error)) {
            try {
                // Verificar si el código de barras ya existe (excluyendo el producto actual)
                $stmt = $pdo->prepare("SELECT id FROM productos WHERE codigo_barras = ? AND id != ?");
                $stmt->execute([$codigo_barras, $id]);
                if ($stmt->rowCount() > 0) {
                    $error = 'El código de barras ya existe';
                } else {
                    // Actualizar en la base de datos
                    $stmt = $pdo->prepare("UPDATE productos SET codigo_barras = ?, descripcion = ?, clasificacion = ?, precio = ?, stock = ?, unidad_medida = ?, imagen = ? WHERE id = ?");
                    $stmt->execute([$codigo_barras, $descripcion, $clasificacion, $precio, $stock, $unidad_medida, $imagen, $id]);
                    
                    $mensaje = 'Producto actualizado correctamente.';
                    
                    // Actualizar los datos del producto para mostrar los cambios
                    $producto['codigo_barras'] = $codigo_barras;
                    $producto['descripcion'] = $descripcion;
                    $producto['clasificacion'] = $clasificacion;
                    $producto['precio'] = $precio;
                    $producto['stock'] = $stock;
                    $producto['unidad_medida'] = $unidad_medida;
                    $producto['imagen'] = $imagen;
                }
            } catch(PDOException $e) {
                $error = 'Error al actualizar el producto. Inténtalo de nuevo.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            height: 80px;
            resize: vertical;
        }
        button {
            background-color: #ff6b6b;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #e55a5a;
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
            color: #ff6b6b;
            text-decoration: none;
            margin: 0 10px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ Editar Producto</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">El código de barras es el identificador único del producto</p>
        
        <?php if ($mensaje): ?>
            <div class="mensaje success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($producto): ?>
        <form action="editar_producto.php?id=<?php echo $producto['id']; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="codigo_barras">Código de Barras *</label>
                <input type="text" id="codigo_barras" name="codigo_barras" value="<?php echo htmlspecialchars($producto['codigo_barras']); ?>" required placeholder="Ej: 1234567890123">
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción *</label>
                <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="imagen">Imagen del Producto</label>
                <?php if ($producto['imagen']): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen actual" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                        <p style="font-size: 12px; color: #666; margin: 5px 0;">Imagen actual</p>
                    </div>
                <?php endif; ?>
                <input type="file" id="imagen" name="imagen" accept="image/*">
                <small style="color: #666; font-size: 12px;">Formatos permitidos: JPG, PNG, GIF, WebP. Máximo 5MB. Dejar vacío para mantener la imagen actual.</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="clasificacion">Clasificación *</label>
                    <select id="clasificacion" name="clasificacion" required>
                        <option value="">Seleccionar...</option>
                        <option value="Electrónicos" <?php echo ($producto['clasificacion'] == 'Electrónicos') ? 'selected' : ''; ?>>Electrónicos</option>
                        <option value="Ropa" <?php echo ($producto['clasificacion'] == 'Ropa') ? 'selected' : ''; ?>>Ropa</option>
                        <option value="Hogar" <?php echo ($producto['clasificacion'] == 'Hogar') ? 'selected' : ''; ?>>Hogar</option>
                        <option value="Deportes" <?php echo ($producto['clasificacion'] == 'Deportes') ? 'selected' : ''; ?>>Deportes</option>
                        <option value="Libros" <?php echo ($producto['clasificacion'] == 'Libros') ? 'selected' : ''; ?>>Libros</option>
                        <option value="Alimentación" <?php echo ($producto['clasificacion'] == 'Alimentación') ? 'selected' : ''; ?>>Alimentación</option>
                        <option value="Otros" <?php echo ($producto['clasificacion'] == 'Otros') ? 'selected' : ''; ?>>Otros</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="unidad_medida">Unidad de Medida *</label>
                    <select id="unidad_medida" name="unidad_medida" required>
                        <option value="">Seleccionar...</option>
                        <option value="Pieza" <?php echo ($producto['unidad_medida'] == 'Pieza') ? 'selected' : ''; ?>>Pieza</option>
                        <option value="Kilogramo" <?php echo ($producto['unidad_medida'] == 'Kilogramo') ? 'selected' : ''; ?>>Kilogramo</option>
                        <option value="Litro" <?php echo ($producto['unidad_medida'] == 'Litro') ? 'selected' : ''; ?>>Litro</option>
                        <option value="Metro" <?php echo ($producto['unidad_medida'] == 'Metro') ? 'selected' : ''; ?>>Metro</option>
                        <option value="Caja" <?php echo ($producto['unidad_medida'] == 'Caja') ? 'selected' : ''; ?>>Caja</option>
                        <option value="Paquete" <?php echo ($producto['unidad_medida'] == 'Paquete') ? 'selected' : ''; ?>>Paquete</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="precio">Precio *</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
                </div>
            </div>
            
            <button type="submit">Actualizar Producto</button>
        </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="listar_productos.php">← Lista de Productos</a>
            <a href="index.php">📦 Módulo de Productos</a>
            <a href="../index.php">🏠 Menú Principal</a>
        </div>
    </div>
</body>
</html>
