<?php
require_once '../config.php';

$mensaje = '';
$error = '';

// Procesar eliminación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    try {
        // Obtener información del producto antes de eliminarlo
        $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt->execute([$_GET['eliminar']]);
        $producto_eliminar = $stmt->fetch();
        
        // Eliminar el producto de la base de datos
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$_GET['eliminar']]);
        
        // Eliminar la imagen del servidor si existe
        if ($producto_eliminar && $producto_eliminar['imagen'] && file_exists($producto_eliminar['imagen'])) {
            unlink($producto_eliminar['imagen']);
        }
        
        $mensaje = 'Producto eliminado correctamente.';
    } catch(PDOException $e) {
        $error = 'Error al eliminar el producto.';
    }
}

try {
    // Obtener todos los productos ordenados por fecha de creación
    $stmt = $pdo->query("SELECT * FROM productos ORDER BY fecha_creacion DESC");
    $productos = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error al obtener los productos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Productos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
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
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .producto-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            background-color: #fafafa;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .producto-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            transform: translateY(-3px);
            border-color: #ff6b6b;
        }
        .producto-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .producto-codigo {
            font-weight: bold;
            color: #ff6b6b;
            font-size: 16px;
            line-height: 1.2;
        }
        .producto-fecha {
            color: #666;
            font-size: 11px;
            text-align: right;
        }
        .producto-content {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }
        .producto-imagen-container {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
            border: 2px solid #ff6b6b;
            overflow: hidden;
        }
        .producto-imagen {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .producto-imagen:hover {
            transform: scale(1.1);
        }
        .producto-details {
            flex: 1;
            min-width: 0;
        }
        .producto-descripcion {
            font-weight: 500;
            color: #333;
            font-size: 14px;
            line-height: 1.3;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .producto-clasificacion {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 8px;
        }
        .producto-precio-stock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .precio {
            color: #28a745;
            font-weight: bold;
            font-size: 16px;
        }
        .stock {
            color: #007bff;
            font-weight: bold;
            font-size: 12px;
        }
        .stock.bajo {
            color: #dc3545;
        }
        .unidad-medida {
            color: #666;
            font-size: 11px;
            font-style: italic;
        }
        .acciones {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .btn-editar {
            background-color: #007bff;
            color: white;
        }
        .btn-editar:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }
        .btn-eliminar {
            background-color: #dc3545;
            color: white;
        }
        .btn-eliminar:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }
        .no-productos {
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
            color: #ff6b6b;
            text-decoration: none;
            margin: 0 10px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .stats {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
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
        .btn-nuevo {
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .btn-nuevo:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .no-imagen {
            width: 100%;
            height: 100%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 24px;
        }
        @media (max-width: 768px) {
            .productos-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .producto-content {
                flex-direction: column;
                gap: 8px;
            }
            .producto-imagen-container {
                width: 100%;
                height: 120px;
                align-self: center;
            }
            .acciones {
                justify-content: center;
            }
        }
        @media (max-width: 480px) {
            .producto-card {
                padding: 12px;
            }
            .producto-codigo {
                font-size: 14px;
            }
            .producto-descripcion {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Lista de Productos</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Identificados por código de barras único</p>
        
        <?php if ($mensaje): ?>
            <div class="mensaje success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div style="text-align: center;">
            <a href="crear_producto.php" class="btn-nuevo">➕ Crear Nuevo Producto</a>
        </div>
        
        <?php if (!empty($productos)): ?>
            <div class="stats">
                <strong>Total de productos: <?php echo count($productos); ?></strong>
            </div>
            
            <div class="productos-grid">
                <?php foreach ($productos as $producto): ?>
                    <div class="producto-card">
                        <div class="producto-header">
                            <div class="producto-codigo"><?php echo htmlspecialchars($producto['codigo_barras']); ?></div>
                            <div class="producto-fecha"><?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?></div>
                        </div>
                        
                        <div class="producto-content">
                            <div class="producto-imagen-container">
                                <?php if ($producto['imagen']): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen del producto" class="producto-imagen">
                                <?php else: ?>
                                    <div class="no-imagen">📦</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="producto-details">
                                <div class="producto-descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></div>
                                <div class="producto-clasificacion"><?php echo htmlspecialchars($producto['clasificacion']); ?></div>
                                
                                <div class="producto-precio-stock">
                                    <div class="precio">$<?php echo number_format($producto['precio'], 2); ?></div>
                                    <div class="stock <?php echo $producto['stock'] < 10 ? 'bajo' : ''; ?>">
                                        <?php echo $producto['stock']; ?>
                                    </div>
                                </div>
                                
                                <div class="unidad-medida"><?php echo htmlspecialchars($producto['unidad_medida']); ?></div>
                            </div>
                        </div>
                        
                        <div class="acciones">
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-editar">✏️ Editar</a>
                            <a href="listar_productos.php?eliminar=<?php echo $producto['id']; ?>" 
                               class="btn btn-eliminar" 
                               onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">🗑️ Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-productos">
                No hay productos registrados aún.
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.php">← Módulo de Productos</a>
            <a href="../index.php">🏠 Menú Principal</a>
        </div>
    </div>
</body>
</html>
