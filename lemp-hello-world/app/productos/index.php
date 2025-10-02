<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Productos - LEMP Stack</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .main-content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 3px solid #ff6b6b;
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .menu-item {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .menu-item:hover::before {
            left: 100%;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #ff6b6b;
        }
        
        .menu-item h3 {
            color: #ff6b6b;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .menu-item p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .icon {
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            background: #e55a5a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Módulo de Productos</h1>
            <p>Gestión completa de inventario y productos</p>
        </div>
        
        <div class="main-content">
            <div class="section">
                <h2>🔧 Configuración</h2>
                <div class="menu-grid">
                    <a href="crear_tabla.php" class="menu-item">
                        <div class="icon">🏗️</div>
                        <h3>Crear Tabla</h3>
                        <p>Configurar la tabla de productos en la base de datos MySQL</p>
                    </a>
                </div>
            </div>
            
            <div class="section">
                <h2>📝 Gestión de Productos</h2>
                <div class="menu-grid">
                    <a href="crear_producto.php" class="menu-item">
                        <div class="icon">➕</div>
                        <h3>Crear Producto</h3>
                        <p>Agregar nuevos productos al inventario</p>
                    </a>
                    
                    <a href="listar_productos.php" class="menu-item">
                        <div class="icon">📋</div>
                        <h3>Listar Productos</h3>
                        <p>Visualizar y gestionar todos los productos</p>
                    </a>
                </div>
            </div>
            
            <div class="back-link">
                <a href="../index.php">← Volver al Menú Principal</a>
            </div>
        </div>
    </div>
</body>
</html>
