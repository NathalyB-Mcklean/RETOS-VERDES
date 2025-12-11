<?php
session_start();
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

// Obtener participación y reportes del usuario
$mi_participacion = null;
$mis_reportes = [];
$mi_progreso = 0;
$total_reportes = 0;

if ($user_logged_in && isset($usuario['id']) && $ya_participa) {
    $mi_participacion = $retoModel->getParticipacion($usuario['id'], $reto_id);
    if ($mi_participacion) {
        $mis_reportes = $retoModel->getReportesPorParticipacion($mi_participacion['id']);
        $mi_progreso = $mi_participacion['progreso'] ?? 0;
        $total_reportes = count($mis_reportes);
    }
}

// Mostrar mensajes de éxito/error de reportes
if (isset($_SESSION['success_reporte'])) {
    $mensaje_reporte = $_SESSION['success_reporte'];
    unset($_SESSION['success_reporte']);
} else {
    $mensaje_reporte = '';
}

if (isset($_SESSION['error_reporte'])) {
    $error_reporte = $_SESSION['error_reporte'];
    unset($_SESSION['error_reporte']);
} else {
    $error_reporte = '';
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
    <style>
        /* Estilos adicionales para la nueva funcionalidad */
        .reportar-section {
            padding: 48px 0;
            background: white;
        }

        .reportar-card {
            max-width: 800px;
            margin: 0 auto;
            background: #f8f9fa;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .reportar-card h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .reportar-card p {
            color: #7f8c8d;
            margin-bottom: 32px;
        }

        .mi-progreso-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .progreso-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .progreso-header h3 {
            font-size: 18px;
            color: #2c3e50;
        }

        .progreso-porcentaje {
            font-size: 24px;
            font-weight: 700;
            color: #4a7c59;
        }

        .progress-bar-large {
            height: 12px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-fill-large {
            height: 100%;
            background: linear-gradient(90deg, #4a7c59 0%, #3d6b4a 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progreso-info {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .completado-badge {
            background: #d5f4e6;
            color: #27ae60;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .form-reporte {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }

        .form-group textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            resize: vertical;
            transition: all 0.3s ease;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #4a7c59;
        }

        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px dashed #e9ecef;
            border-radius: 12px;
            background: #f8f9fa;
            cursor: pointer;
        }

        .form-group small {
            display: block;
            margin-top: 4px;
            color: #7f8c8d;
            font-size: 13px;
        }

        .btn-submit-reporte {
            width: 100%;
            padding: 18px 32px;
            background: linear-gradient(135deg, #4a7c59 0%, #3d6b4a 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(74, 124, 89, 0.3);
        }

        .btn-submit-reporte:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 124, 89, 0.4);
        }

        .mis-reportes {
            padding: 48px 0;
            background: #f8f9fa;
        }

        .mis-reportes h2 {
            text-align: center;
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 32px;
        }

        .reportes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }

        .reporte-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .reporte-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .reporte-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f8f9fa;
        }

        .reporte-fecha {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 600;
        }

        .reporte-estado {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .reporte-estado.pendiente {
            background: #ffeaa7;
            color: #e67e22;
        }

        .reporte-estado.aprobado {
            background: #d5f4e6;
            color: #27ae60;
        }

        .reporte-estado.rechazado {
            background: #fadbd8;
            color: #e74c3c;
        }

        .reporte-descripcion {
            color: #2c3e50;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .reporte-imagen {
            margin-bottom: 16px;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
        }

        .reporte-imagen img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .reporte-imagen img:hover {
            transform: scale(1.05);
        }

        .reporte-footer {
            font-size: 14px;
            color: #7f8c8d;
            border-top: 1px solid #f8f9fa;
            padding-top: 16px;
        }

        .modal-imagen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .modal-imagen img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        }

        .cerrar-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
            z-index: 10001;
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

    <!-- Notificación de reporte -->
    <?php if ($mensaje_reporte): ?>
    <div class="notification success">
        <div class="container">
            <span class="notification-icon">✅</span>
            <span><?php echo $mensaje_reporte; ?></span>
            <button class="notification-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
    </div>
    <?php elseif ($error_reporte): ?>
    <div class="notification error">
        <div class="container">
            <span class="notification-icon">❌</span>
            <span><?php echo $error_reporte; ?></span>
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

    <!-- Sección de Reportar Progreso -->
    <?php if ($user_logged_in && $ya_participa && $mi_participacion && $mi_participacion['estado'] == 'en_progreso'): ?>
    <section class="reportar-section">
        <div class="container">
            <div class="reportar-card">
                <h2>📤 Reportar Mi Progreso</h2>
                <p>Comparte tu avance en el reto y sube evidencia de tus acciones</p>
                
                <!-- Mi Progreso -->
                <div class="mi-progreso-card">
                    <div class="progreso-header">
                        <h3>Mi Progreso Personal</h3>
                        <span class="progreso-porcentaje"><?php echo $mi_progreso; ?>%</span>
                    </div>
                    <div class="progress-bar-large">
                        <div class="progress-fill-large" style="width: <?php echo $mi_progreso; ?>%"></div>
                    </div>
                    <div class="progreso-info">
                        <span><?php echo $total_reportes; ?> reportes enviados</span>
                        <?php if ($mi_progreso >= 100): ?>
                            <span class="completado-badge">✅ Reto Completado</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Formulario de Reporte -->
                <form action="procesar_reporte.php" method="POST" enctype="multipart/form-data" class="form-reporte">
                    <input type="hidden" name="reto_id" value="<?php echo $reto_id; ?>">
                    
                    <div class="form-group">
                        <label for="descripcion">📝 Describe tu progreso</label>
                        <textarea name="descripcion" id="descripcion" 
                                  placeholder="¿Qué hiciste para avanzar en el reto? Describe tus acciones..." 
                                  required rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen">📷 Subir foto (opcional)</label>
                        <input type="file" name="imagen" id="imagen" accept="image/*">
                        <small>Formatos permitidos: JPG, PNG, GIF. Máx. 5MB</small>
                    </div>
                    
                    <button type="submit" class="btn-submit-reporte">
                        🚀 Enviar Reporte
                    </button>
                </form>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Mis Reportes -->
    <?php if (!empty($mis_reportes)): ?>
    <section class="mis-reportes">
        <div class="container">
            <h2>Mis Reportes Anteriores</h2>
            <div class="reportes-grid">
                <?php foreach ($mis_reportes as $reporte): ?>
                <div class="reporte-card">
                    <div class="reporte-header">
                        <span class="reporte-fecha"><?php echo date('d/m/Y', strtotime($reporte['fecha_reporte'])); ?></span>
                        <span class="reporte-estado <?php echo $reporte['estado']; ?>">
                            <?php echo ucfirst($reporte['estado']); ?>
                        </span>
                    </div>
                    <p class="reporte-descripcion"><?php echo nl2br(htmlspecialchars($reporte['descripcion'])); ?></p>
                    
                    <?php if ($reporte['imagen_url']): ?>
                    <div class="reporte-imagen">
                        <img src="<?php echo $reporte['imagen_url']; ?>" 
                             alt="Reporte del reto" 
                             onclick="abrirImagen(this.src)">
                    </div>
                    <?php endif; ?>
                    
                    <div class="reporte-footer">
                        <span class="reporte-accion">Acción realizada el: <?php echo date('d/m/Y', strtotime($reporte['fecha_accion'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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

        // Modal para ver imágenes
        function abrirImagen(src) {
            const modal = document.createElement('div');
            modal.className = 'modal-imagen';
            modal.innerHTML = `
                <span class="cerrar-modal" onclick="cerrarModal()">&times;</span>
                <img src="${src}" alt="Imagen del reporte">
            `;
            document.body.appendChild(modal);
            modal.style.display = 'flex';
            
            // Cerrar al hacer click fuera de la imagen
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    cerrarModal();
                }
            });
        }

        function cerrarModal() {
            const modal = document.querySelector('.modal-imagen');
            if (modal) {
                modal.remove();
            }
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });
    </script>
</body>
</html>