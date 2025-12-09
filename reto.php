<?php
// Verificar que se recibió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: retos.php');
    exit;
}

$reto_id = intval($_GET['id']);

// Incluir archivos necesarios (auth.php ya inicia la sesión)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/models/Reto.php';

// Obtener usuario
$usuario = getUsuario();
$user_logged_in = estaLogueado();

// Crear instancia del modelo
try {
    $retoModel = new Reto();
} catch (Exception $e) {
    error_log("Error al crear instancia de Reto: " . $e->getMessage());
    die("Error al cargar el reto. Por favor, intenta más tarde.");
}

// Obtener información del reto
$reto = $retoModel->getRetoPorId($reto_id);

if (!$reto) {
    header('Location: retos.php');
    exit;
}

// Verificar si el usuario ya participa en este reto
$ya_participa = false;
if ($user_logged_in && isset($usuario['id'])) {
    $ya_participa = $retoModel->estaParticipando($usuario['id'], $reto_id);
}

// Procesar formulario de participación
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participar'])) {
    if (!$user_logged_in) {
        header('Location: login.php?redirect=reto.php?id=' . $reto_id);
        exit;
    }
    
    if (!$ya_participa && isset($usuario['id'])) {
        $resultado = $retoModel->unirseAReto($usuario['id'], $reto_id);
        if ($resultado['success']) {
            $mensaje = 'success';
            $ya_participa = true;
            // Actualizar datos del reto
            $reto = $retoModel->getRetoPorId($reto_id);
        } else {
            $mensaje = 'error';
        }
    }
}

// Calcular progreso
$progreso_global = $reto['meta_participantes'] > 0 
    ? ($reto['participantes_actuales'] / $reto['meta_participantes']) * 100 
    : 0;

// Obtener retos relacionados de la misma categoría
$retos_relacionados = [];
if (isset($reto['categoria_id'])) {
    $todos_retos = $retoModel->getRetos($reto['categoria_id']);
    // Filtrar el reto actual
    foreach ($todos_retos as $r) {
        if ($r['id'] != $reto_id && count($retos_relacionados) < 3) {
            $retos_relacionados[] = $r;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($reto['titulo']); ?> | Retos Verdes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="reto.css">
    <link href="https://fonts.googleapis.com/css2?family=Clash+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="retos.php" class="nav-link">Retos</a>
                    <a href="index.php#ranking" class="nav-link">Ranking</a>
                    <a href="comunidad.php" class="nav-link">Comunidad</a>
                </nav>
                <div class="header-actions">
                    <?php if ($user_logged_in): ?>
                        <div class="user-points">
                            <span class="points-icon">⭐</span>
                            <span class="points-value"><?php echo number_format($usuario['puntos_totales'] ?? 0); ?></span>
                        </div>
                        <a href="perfil.php" class="user-avatar">
                            <span><?php echo $usuario['avatar'] ?? '👤'; ?></span>
                        </a>
                        <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">Iniciar Sesión</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Notificaciones -->
    <?php if ($mensaje === 'success'): ?>
    <div class="notification success">
        <div class="container">
            <span class="notification-icon">✅</span>
            <span>¡Felicidades! Te has unido al reto exitosamente</span>
            <button class="notification-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
    </div>
    <?php elseif ($mensaje === 'error'): ?>
    <div class="notification error">
        <div class="container">
            <span class="notification-icon">❌</span>
            <span>Hubo un error al unirte al reto. Intenta de nuevo.</span>
            <button class="notification-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Inicio</a> / 
            <a href="retos.php">Retos</a> / 
            <span><?php echo htmlspecialchars($reto['titulo']); ?></span>
        </div>
    </div>

    <!-- Hero del Reto -->
    <section class="reto-hero">
        <div class="container">
            <div class="reto-hero-content">
                <div class="reto-hero-badge">
                    <span class="badge-icon"><?php echo $reto['categoria_icono'] ?? '🌱'; ?></span>
                    <span class="badge-text"><?php echo $reto['categoria_nombre'] ?? 'Categoría'; ?></span>
                </div>
                
                <h1 class="reto-title"><?php echo htmlspecialchars($reto['titulo']); ?></h1>
                
                <p class="reto-subtitle"><?php echo htmlspecialchars($reto['descripcion']); ?></p>
                
                <div class="reto-meta">
                    <div class="meta-item">
                        <span class="meta-icon">⏱️</span>
                        <div>
                            <div class="meta-label">Duración</div>
                            <div class="meta-value"><?php echo ucfirst($reto['duracion'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">👥</span>
                        <div>
                            <div class="meta-label">Participantes</div>
                            <div class="meta-value"><?php echo $reto['participantes_actuales'] ?? 0; ?></div>
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">⭐</span>
                        <div>
                            <div class="meta-label">Recompensa</div>
                            <div class="meta-value"><?php echo $reto['puntos_recompensa'] ?? 0; ?> pts</div>
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">📊</span>
                        <div>
                            <div class="meta-label">Dificultad</div>
                            <div class="meta-value"><?php echo ucfirst($reto['dificultad'] ?? 'Media'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Botón de participación -->
                <div class="reto-action">
                    <?php if (!$user_logged_in): ?>
                        <a href="login.php?redirect=reto.php?id=<?php echo $reto_id; ?>" class="btn-participate">
                            🚀 Iniciar Sesión para Participar
                        </a>
                    <?php elseif ($ya_participa): ?>
                        <div class="already-participating">
                            <span class="check-icon">✅</span>
                            <span>Ya estás participando en este reto</span>
                        </div>
                        <a href="mis-retos.php" class="btn-secondary">Ver Mis Retos</a>
                    <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="participar" class="btn-participate">
                                🚀 ¡Unirme al Reto!
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Progreso Global -->
    <section class="progreso-section">
        <div class="container">
            <div class="progreso-card">
                <h3>Progreso de la Comunidad</h3>
                <div class="progress-bar-large">
                    <div class="progress-fill-large" style="width: <?php echo min(100, $progreso_global); ?>%"></div>
                </div>
                <div class="progress-stats">
                    <span><?php echo $reto['participantes_actuales'] ?? 0; ?> de <?php echo $reto['meta_participantes'] ?? 0; ?> participantes</span>
                    <span><?php echo number_format($progreso_global, 1); ?>%</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Detalles del Reto -->
    <section class="reto-detalles">
        <div class="container">
            <div class="detalles-grid">
                <!-- Columna Principal -->
                <div class="detalles-main">
                    <!-- Descripción Completa -->
                    <div class="detalle-card">
                        <h2 class="card-title">📝 Descripción del Reto</h2>
                        <div class="card-content">
                            <p><?php echo nl2br(htmlspecialchars($reto['descripcion_larga'] ?? $reto['descripcion'])); ?></p>
                        </div>
                    </div>

                    <!-- Objetivos -->
                    <div class="detalle-card">
                        <h2 class="card-title">🎯 Objetivos</h2>
                        <div class="card-content">
                            <ul class="objetivos-list">
                                <li>Completar el reto dentro del período establecido</li>
                                <li>Documentar tu progreso con fotos</li>
                                <li>Compartir tu experiencia con la comunidad</li>
                                <li>Ganar <?php echo $reto['puntos_recompensa'] ?? 0; ?> puntos al completar</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Cómo Participar -->
                    <div class="detalle-card">
                        <h2 class="card-title">📋 Cómo Participar</h2>
                        <div class="card-content">
                            <ol class="steps-list">
                                <li>
                                    <span class="step-number">1</span>
                                    <div>
                                        <h4>Únete al Reto</h4>
                                        <p>Haz clic en "¡Unirme al Reto!" para comenzar</p>
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">2</span>
                                    <div>
                                        <h4>Realiza las Actividades</h4>
                                        <p>Completa las tareas del reto según las instrucciones</p>
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">3</span>
                                    <div>
                                        <h4>Documenta tu Progreso</h4>
                                        <p>Toma fotos y registra tus avances</p>
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">4</span>
                                    <div>
                                        <h4>Comparte y Gana</h4>
                                        <p>Sube tu evidencia y recibe tus puntos</p>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="detalles-sidebar">
                    <!-- Info Card -->
                    <div class="info-card">
                        <h3>📊 Información</h3>
                        <div class="info-items">
                            <div class="info-row">
                                <span class="info-label">Categoría:</span>
                                <span class="info-value">
                                    <?php echo ($reto['categoria_icono'] ?? '🌱') . ' ' . ($reto['categoria_nombre'] ?? 'N/A'); ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Duración:</span>
                                <span class="info-value"><?php echo ucfirst($reto['duracion'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dificultad:</span>
                                <span class="info-value"><?php echo ucfirst($reto['dificultad'] ?? 'Media'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Puntos:</span>
                                <span class="info-value badge-points">⭐ <?php echo $reto['puntos_recompensa'] ?? 0; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Participantes Recientes -->
                    <div class="info-card">
                        <h3>👥 Participantes</h3>
                        <p class="participants-count"><?php echo $reto['participantes_actuales'] ?? 0; ?> personas ya están participando</p>
                    </div>

                    <!-- Compartir -->
                    <div class="info-card">
                        <h3>📢 Compartir</h3>
                        <div class="share-buttons">
                            <button class="share-btn" onclick="compartir('facebook')">📘 Facebook</button>
                            <button class="share-btn" onclick="compartir('twitter')">🐦 Twitter</button>
                            <button class="share-btn" onclick="compartir('whatsapp')">💬 WhatsApp</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Retos Relacionados -->
    <?php if (!empty($retos_relacionados)): ?>
    <section class="retos-relacionados">
        <div class="container">
            <h2 class="section-title">🌟 Otros Retos que te Pueden Interesar</h2>
            <div class="challenges-grid">
                <?php foreach ($retos_relacionados as $reto_rel): ?>
                <div class="challenge-card">
                    <div class="challenge-header">
                        <span class="challenge-icon"><?php echo $reto_rel['categoria_icono'] ?? '🌱'; ?></span>
                        <span class="challenge-duration"><?php echo ucfirst($reto_rel['duracion'] ?? 'N/A'); ?></span>
                    </div>
                    <h4 class="challenge-title"><?php echo htmlspecialchars($reto_rel['titulo']); ?></h4>
                    <p class="challenge-description"><?php echo substr($reto_rel['descripcion'], 0, 100); ?>...</p>
                    <div class="challenge-footer">
                        <div class="challenge-info">
                            <span class="info-item">
                                <span class="info-icon">⭐</span>
                                <?php echo $reto_rel['puntos_recompensa'] ?? 0; ?> pts
                            </span>
                        </div>
                        <a href="reto.php?id=<?php echo $reto_rel['id']; ?>" class="btn btn-challenge">Ver Reto</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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
        function compartir(plataforma) {
            const url = window.location.href;
            const titulo = <?php echo json_encode($reto['titulo']); ?>;
            
            let shareUrl = '';
            
            switch(plataforma) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(titulo)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(titulo + ' ' + url)}`;
                    break;
            }
            
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }

        // Auto-hide notification después de 5 segundos
        const notification = document.querySelector('.notification');
        if (notification) {
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>