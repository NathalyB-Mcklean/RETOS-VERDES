<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'models/Reto.php';

$retoModel = new Reto();
$usuario = getUsuario();
$user_logged_in = estaLogueado();

// Filtros
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todos';
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Obtener todos los retos
$retos = $retoModel->getRetos($categoria, $busqueda);

// Obtener categorías para el filtro
$categorias = $retoModel->getCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los Retos | Retos Verdes</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Clash+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .retos-header {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 48px 0;
            margin-top: 72px;
        }
        
        .retos-header h1 {
            font-size: 42px;
            color: var(--primary-green);
            margin-bottom: 16px;
        }
        
        .retos-header p {
            font-size: 18px;
            color: var(--light-text);
        }
        
        .filters-section {
            padding: 32px 0;
            background: white;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 72px;
            z-index: 100;
        }
        
        .filters-container {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 2px solid var(--bg-light);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: var(--transition);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary-green);
        }
        
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-btn-cat {
            padding: 10px 20px;
            border: 2px solid var(--bg-light);
            background: white;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--dark-text);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .filter-btn-cat:hover {
            border-color: var(--light-green);
            background: var(--bg-light);
        }
        
        .filter-btn-cat.active {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
        }
        
        .retos-count {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .retos-grid-section {
            padding: 48px 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 16px;
        }
        
        .empty-state h3 {
            font-size: 24px;
            color: var(--dark-text);
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: var(--light-text);
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
                        <span class="logo-icon">🌱</span>
                        <h1>RETOS VERDES</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">Descubrir</a>
                    <a href="retos.php" class="nav-link active">Retos</a>
                    <a href="index.php#ranking" class="nav-link">Ranking</a>
                    <a href="#comunidad" class="nav-link">Comunidad</a>
                </nav>
                <div class="header-actions">
                    <?php if ($user_logged_in): ?>
                        <div class="user-points">
                            <span class="points-icon">⭐</span>
                            <span class="points-value"><?php echo number_format($usuario['puntos_totales']); ?></span>
                        </div>
                        <a href="perfil.php" class="user-avatar">
                            <span><?php echo $usuario['avatar']; ?></span>
                        </a>
                        <a href="logout.php" class="btn-logout">
                            <span class="btn-icon">🚪</span>
                            <span>Cerrar Sesión</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">
                            <span class="btn-icon">🔐</span>
                            <span>Iniciar Sesión</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Retos Header -->
    <section class="retos-header">
        <div class="container">
            <h1>Explora Todos los Retos</h1>
            <p>Únete a la comunidad y haz la diferencia en Panamá 🇵🇦</p>
        </div>
    </section>

    <!-- Filtros -->
    <section class="filters-section">
        <div class="container">
            <form action="retos.php" method="GET">
                <div class="filters-container">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input 
                            type="text" 
                            name="busqueda" 
                            placeholder="Buscar retos..." 
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                        >
                    </div>
                    
                    <div class="filter-buttons">
                        <a href="retos.php?categoria=todos<?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                           class="filter-btn-cat <?php echo $categoria === 'todos' ? 'active' : ''; ?>">
                            🌍 Todos
                        </a>
                        <?php foreach ($categorias as $cat): ?>
                        <a href="retos.php?categoria=<?php echo $cat['id']; ?><?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                           class="filter-btn-cat <?php echo $categoria == $cat['id'] ? 'active' : ''; ?>">
                            <?php echo $cat['icono']; ?> <?php echo $cat['nombre']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
            
            <div style="margin-top: 16px;">
                <span class="retos-count">
                    <?php echo count($retos); ?> retos disponibles
                </span>
            </div>
        </div>
    </section>

    <!-- Grid de Retos -->
    <section class="retos-grid-section">
        <div class="container">
            <?php if (!empty($retos)): ?>
            <div class="challenges-grid">
                <?php foreach ($retos as $reto): ?>
                <div class="challenge-card">
                    <div class="challenge-header">
                        <span class="challenge-icon"><?php echo $reto['categoria_icono']; ?></span>
                        <span class="challenge-duration"><?php echo ucfirst($reto['duracion']); ?></span>
                    </div>
                    <h4 class="challenge-title"><?php echo $reto['titulo']; ?></h4>
                    <p class="challenge-description"><?php echo substr($reto['descripcion'], 0, 100); ?>...</p>
                    
                    <div class="challenge-progress">
                        <div class="progress-bar">
                            <?php 
                            $progreso_global = $reto['meta_participantes'] > 0 
                                ? ($reto['participantes_actuales'] / $reto['meta_participantes']) * 100 
                                : 0;
                            ?>
                            <div class="progress-fill" style="width: <?php echo min(100, $progreso_global); ?>%"></div>
                        </div>
                        <span class="progress-text">
                            <?php echo $reto['participantes_actuales']; ?>/<?php echo $reto['meta_participantes']; ?> participantes
                        </span>
                    </div>
                    
                    <div class="challenge-footer">
                        <div class="challenge-info">
                            <span class="info-item">
                                <span class="info-icon">👥</span>
                                <?php echo $reto['participantes_actuales']; ?> participantes
                            </span>
                            <span class="info-item">
                                <span class="info-icon">⭐</span>
                                <?php echo $reto['puntos_recompensa']; ?> pts
                            </span>
                        </div>
                        <a href="reto.php?id=<?php echo $reto['id']; ?>" class="btn btn-challenge">Ver Reto</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <h3>No se encontraron retos</h3>
                <p>Intenta con otros filtros o términos de búsqueda</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Retos Verdes Comunitarios</h4>
                    <p>Transformando comunidades panameñas a través de la acción ambiental</p>
                </div>
                <div class="footer-section">
                    <h5>Enlaces</h5>
                    <a href="#sobre">Sobre Nosotros</a>
                    <a href="#como-funciona">Cómo Funciona</a>
                    <a href="#contacto">Contacto</a>
                </div>
                <div class="footer-section">
                    <h5>Legal</h5>
                    <a href="#privacidad">Privacidad</a>
                    <a href="#terminos">Términos</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Retos Verdes Comunitarios - Panamá 🇵🇦</p>
            </div>
        </div>
    </footer>

    <script>
        // Auto-submit del formulario cuando se escribe en búsqueda
        const searchInput = document.querySelector('input[name="busqueda"]');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    </script>
</body>
</html>