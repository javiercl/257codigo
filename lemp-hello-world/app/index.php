<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prácticas PHP - LEMP Stack</title>
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
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #4facfe;
            text-decoration: none;
        }
        
        .logo .icon {
            font-size: 2rem;
            margin-right: 10px;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: block;
            padding: 20px 25px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: #4facfe;
            background: rgba(79, 172, 254, 0.1);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: #4facfe;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 80%;
        }
        
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 250px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1001;
        }
        
        .nav-item:hover .dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: #4facfe;
            padding-left: 30px;
        }
        
        .dropdown-item .icon {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 10px;
        }
        
        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .hero-section {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            color: #333;
            margin-bottom: 20px;
            font-weight: 300;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .status-item {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .status-item:hover {
            transform: translateY(-5px);
        }
        
        .status-item .icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .status-item .label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .status-item .value {
            color: #4facfe;
            font-size: 1rem;
        }
        
        .content-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .section-title {
            color: #333;
            margin-bottom: 30px;
            font-size: 2rem;
            text-align: center;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 80px;
            height: 4px;
            background: #4facfe;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .quick-link {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .quick-link:hover {
            background: #4facfe;
            color: white;
            transform: translateX(10px);
            border-color: #4facfe;
        }
        
        .quick-link .icon {
            font-size: 2rem;
            margin-right: 20px;
        }
        
        .quick-link-content h3 {
            margin-bottom: 5px;
            font-size: 1.2rem;
        }
        
        .quick-link-content p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .footer {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            text-align: center;
            color: #666;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .tech-stack {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .tech-item {
            background: #4facfe;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: white;
                flex-direction: column;
                transition: left 0.3s ease;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            }
            
            .nav-menu.active {
                left: 0;
            }
            
            .nav-item {
                width: 100%;
            }
            
            .nav-link {
                padding: 20px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .dropdown {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                background: #f8f9fa;
                border-radius: 0;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-section {
                padding: 40px 20px;
            }
            
            .content-section {
                padding: 20px;
            }
            
            .quick-links {
                grid-template-columns: 1fr;
            }
            
            .tech-stack {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="icon">🚀</span>
                Prácticas PHP
            </a>
            
            <ul class="nav-menu" id="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Inicio</a>
                </li>
                <li class="nav-item">
                    <a href="contacto/" class="nav-link">Contacto</a>
                    <div class="dropdown">
                        <a href="contacto/" class="dropdown-item">
                            <span class="icon">📧</span>
                            Módulo de Contacto
                        </a>
                        <a href="contacto/crear_tabla.php" class="dropdown-item">
                            <span class="icon">🏗️</span>
                            Crear Tabla
                        </a>
                        <a href="contacto/contacto.php" class="dropdown-item">
                            <span class="icon">📝</span>
                            Formulario
                        </a>
                        <a href="contacto/ver_mensajes.php" class="dropdown-item">
                            <span class="icon">📋</span>
                            Ver Mensajes
                        </a>
                        <a href="verificar_drivers.php" class="dropdown-item">
                            <span class="icon">🔍</span>
                            Verificar Drivers
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">Prácticas</a>
                    <div class="dropdown">
                        <a href="practica2.php" class="dropdown-item">
                            <span class="icon">🔢</span>
                            Práctica 2
                        </a>
                        <a href="practica3.php" class="dropdown-item">
                            <span class="icon">🎯</span>
                            Práctica 3
                        </a>
                        <a href="practica4.php" class="dropdown-item">
                            <span class="icon">⚡</span>
                            Práctica 4
                        </a>
                    </div>
                </li>
            </ul>
            
            <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
    
    <div class="main-content">
        <div class="hero-section">
            <h1>LEMP Stack Development</h1>
            <p>Entorno de desarrollo con Linux, Nginx, MySQL y PHP</p>
        </div>
        
        <div class="status-grid">
            <div class="status-item">
                <div class="icon">🐘</div>
                <div class="label">PHP</div>
                <div class="value">8.2-FPM</div>
            </div>
            <div class="status-item">
                <div class="icon">🌐</div>
                <div class="label">Nginx</div>
                <div class="value">Latest</div>
            </div>
            <div class="status-item">
                <div class="icon">🗄️</div>
                <div class="label">MySQL</div>
                <div class="value">8.0</div>
            </div>
            <div class="status-item">
                <div class="icon">🐳</div>
                <div class="label">Docker</div>
                <div class="value">Compose</div>
            </div>
        </div>
        
        <div class="content-section">
            <h2 class="section-title">Accesos Rápidos</h2>
            <div class="quick-links">
                <a href="contacto/" class="quick-link">
                    <div class="icon">📧</div>
                    <div class="quick-link-content">
                        <h3>Módulo de Contacto</h3>
                        <p>Gestión completa de formularios</p>
                    </div>
                </a>
                
                <a href="contacto/crear_tabla.php" class="quick-link">
                    <div class="icon">🏗️</div>
                    <div class="quick-link-content">
                        <h3>Crear Tabla</h3>
                        <p>Configurar la base de datos</p>
                    </div>
                </a>
                
                <a href="contacto/contacto.php" class="quick-link">
                    <div class="icon">📝</div>
                    <div class="quick-link-content">
                        <h3>Formulario</h3>
                        <p>Enviar mensajes</p>
                    </div>
                </a>
                
                <a href="verificar_drivers.php" class="quick-link">
                    <div class="icon">🔍</div>
                    <div class="quick-link-content">
                        <h3>Verificar Drivers</h3>
                        <p>Estado del sistema</p>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>LEMP Stack Development Environment</strong></p>
            <p>Desarrollado con PHP, MySQL, Nginx y Docker</p>
            <div class="tech-stack">
                <div class="tech-item">PHP 8.2</div>
                <div class="tech-item">MySQL 8.0</div>
                <div class="tech-item">Nginx</div>
                <div class="tech-item">Docker</div>
                <div class="tech-item">phpMyAdmin</div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleMobileMenu() {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('active');
        }
        
        // Cerrar menú móvil al hacer clic en un enlace
        document.querySelectorAll('.nav-link, .dropdown-item').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('nav-menu').classList.remove('active');
            });
        });
    </script>
</body>
</html>