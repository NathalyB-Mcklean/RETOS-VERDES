<?php
session_start();

// Verificar si el usuario está logueado
$user_logged_in = isset($_SESSION['user_id']);

if (!$user_logged_in) {
    $_SESSION['redirect_after_login'] = 'reto-detalle.php' . (isset($_GET['id']) ? '?id=' . $_GET['id'] : '');
    header('Location: login.php');
    exit();
}

// Datos del usuario
$user_name = $_SESSION['user_name'];
$user_points = $_SESSION['user_points'];
$user_avatar = $_SESSION['user_avatar'];

// Obtener ID del reto
$reto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Base de datos simulada de retos
$retos_db = [
    1 => [
        'id' => 1,
        'titulo' => 'Planta un Árbol Nativo',
        'descripcion' => 'Contribuye a la reforestación plantando árboles nativos en tu comunidad. Cada árbol cuenta para restaurar nuestros bosques.',
        'descripcion_larga' => 'Este reto tiene como objetivo aumentar la cobertura forestal en Panamá mediante la siembra de árboles nativos. Los árboles nativos son esenciales para mantener el equilibrio del ecosistema, proporcionan hábitat para la fauna local y ayudan a prevenir la erosión del suelo.',
        'categoria' => 'arboles',
        'puntos' => 100,
        'fecha_inicio' => '2025-12-01',
        'fecha_limite' => '2025-12-31',
        'icono' => '🌳',
        'progreso' => 60,
        'participantes' => 234,
        'impacto' => '450 árboles plantados',
        'dificultad' => 'Media',
        'duracion' => '30 días',
        'tareas' => [
            [
                'id' => 1,
                'titulo' => 'Identifica el área de plantación',
                'descripcion' => 'Encuentra un espacio adecuado en tu comunidad',
                'completada' => true,
                'puntos' => 15
            ],
            [
                'id' => 2,
                'titulo' => 'Consigue semillas o plántulas nativas',
                'descripcion' => 'Obtén especies como caoba, espavé o roble',
                'completada' => true,
                'puntos' => 20
            ],
            [
                'id' => 3,
                'titulo' => 'Prepara el terreno',
                'descripcion' => 'Limpia y prepara el área de siembra',
                'completada' => true,
                'puntos' => 25
            ],
            [
                'id' => 4,
                'titulo' => 'Planta los árboles',
                'descripcion' => 'Siembra al menos 3 árboles',
                'completada' => false,
                'puntos' => 30
            ],
            [
                'id' => 5,
                'titulo' => 'Documenta tu trabajo',
                'descripcion' => 'Sube fotos del proceso',
                'completada' => false,
                'puntos' => 10
            ]
        ]
    ],
    2 => [
        'id' => 2,
        'titulo' => 'Limpieza de Quebrada',
        'descripcion' => 'Limpia ríos y quebradas locales para proteger nuestras fuentes de agua',
        'descripcion_larga' => 'Las quebradas y ríos son vitales para el suministro de agua potable y la biodiversidad acuática. Este reto busca eliminar residuos y contaminantes de estos cuerpos de agua.',
        'categoria' => 'agua',
        'puntos' => 150,
        'fecha_inicio' => '2025-12-05',
        'fecha_limite' => '2025-12-12',
        'icono' => '💧',
        'progreso' => 45,
        'participantes' => 156,
        'impacto' => '300 kg de residuos recolectados',
        'dificultad' => 'Alta',
        'duracion' => '7 días',
        'tareas' => [
            [
                'id' => 1,
                'titulo' => 'Forma un equipo',
                'descripcion' => 'Reúne al menos 5 personas',
                'completada' => true,
                'puntos' => 25
            ],
            [
                'id' => 2,
                'titulo' => 'Organiza los materiales',
                'descripcion' => 'Bolsas, guantes, ganchos recolectores',
                'completada' => true,
                'puntos' => 25
            ],
            [
                'id' => 3,
                'titulo' => 'Realiza la limpieza',
                'descripcion' => 'Recolecta residuos de la quebrada',
                'completada' => false,
                'puntos' => 60
            ],
            [
                'id' => 4,
                'titulo' => 'Clasifica y reporta',
                'descripcion' => 'Separa los residuos y documenta',
                'completada' => false,
                'puntos' => 40
            ]
        ]
    ],
    4 => [
        'id' => 4,
        'titulo' => 'Reduce el Plástico',
        'descripcion' => 'Elimina plásticos de un solo uso de tu vida diaria',
        'descripcion_larga' => 'Los plásticos de un solo uso son una de las principales fuentes de contaminación. Este reto te ayudará a adoptar alternativas sostenibles.',
        'categoria' => 'residuos',
        'puntos' => 50,
        'fecha_inicio' => '2025-12-03',
        'fecha_limite' => '2025-12-10',
        'icono' => '♻️',
        'progreso' => 30,
        'participantes' => 412,
        'impacto' => '2,500 plásticos evitados',
        'dificultad' => 'Baja',
        'duracion' => '7 días',
        'tareas' => [
            [
                'id' => 1,
                'titulo' => 'Identifica tus plásticos',
                'descripcion' => 'Lista los plásticos que usas diariamente',
                'completada' => true,
                'puntos' => 10
            ],
            [
                'id' => 2,
                'titulo' => 'Encuentra alternativas',
                'descripcion' => 'Bolsas reutilizables, botellas de agua',
                'completada' => false,
                'puntos' => 20
            ],
            [
                'id' => 3,
                'titulo' => 'Implementa los cambios',
                'descripcion' => 'Usa alternativas por una semana',
                'completada' => false,
                'puntos' => 20
            ]
        ]
    ]
];

// Verificar si el reto existe
if (!isset($retos_db[$reto_id])) {
    header('Location: mis-retos.php');
    exit();
}

$reto = $retos_db[$reto_id];

// Calcular días restantes
function diasRestantes($fecha_limite) {
    $hoy = new DateTime();
    $limite = new DateTime($fecha_limite);
    $diff = $hoy->diff($limite);
    return $diff->days;
}

$dias_restantes = diasRestantes($reto['fecha_limite']);

// Calcular tareas completadas
$tareas_completadas = count(array_filter($reto['tareas'], function($t) { return $t['completada']; }));
$tareas_totales = count($reto['tareas']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $reto['titulo']; ?> | Retos Verdes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="reto-detalle.css">
    <link href="https://fonts.googleapis.com/css2?family=Clash+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <span class="logo-icon">🌱</span>
                    <h1>RETOS VERDES</h1>
                </div>
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">Descubrir</a>
                    <a href="index.php#ranking" class="nav-link">Ranking</a>
                    <a href="mis-retos.php" class="nav-link active">Mis Retos</a>
                    <a href="comunidad.php" class="nav-link">Comunidad</a>
                </nav>
                <div class="header-actions">
                    <div class="user-points">
                        <span class="points-icon">⭐</span>
                        <span class="points-value"><?php echo number_format($user_points); ?></span>
                    </div>
                    <a href="profile.php" class="user-avatar">
                        <span><?php echo $user_avatar; ?></span>
                    </a>
                    <a href="logout.php" class="btn-primary" style="background: #e74c3c; font-size: 14px; padding: 10px 20px;">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Inicio</a>
            <span class="separator">→</span>
            <a href="mis-retos.php">Mis Retos</a>
            <span class="separator">→</span>
            <span class="current"><?php echo $reto['titulo']; ?></span>
        </div>
    </div>

    <!-- Hero del Reto -->
    <section class="challenge-detail-hero">
        <div class="container">
            <div class="hero-content-detail">
                <div class="hero-left">
                    <div class="challenge-icon-hero"><?php echo $reto['icono']; ?></div>
                    <div class="hero-text">
                        <h1 class="challenge-title-hero"><?php echo $reto['titulo']; ?></h1>
                        <p class="challenge-subtitle"><?php echo $reto['descripcion']; ?></p>
                        
                        <div class="challenge-meta">
                            <div class="meta-item">
                                <span class="meta-icon">👥</span>
                                <span><?php echo $reto['participantes']; ?> participantes</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">📊</span>
                                <span><?php echo $reto['dificultad']; ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">⏱️</span>
                                <span><?php echo $reto['duracion']; ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">🌍</span>
                                <span><?php echo $reto['impacto']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="hero-right">
                    <div class="progress-card-hero">
                        <div class="progress-header-hero">
                            <h3>Tu Progreso</h3>
                            <span class="progress-percent"><?php echo $reto['progreso']; ?>%</span>
                        </div>
                        <div class="progress-bar-hero">
                            <div class="progress-fill-hero" style="width: <?php echo $reto['progreso']; ?>%"></div>
                        </div>
                        <div class="progress-stats">
                            <div class="stat">
                                <span class="stat-label">Tareas</span>
                                <span class="stat-value"><?php echo $tareas_completadas; ?>/<?php echo $tareas_totales; ?></span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Días restantes</span>
                                <span class="stat-value <?php echo $dias_restantes <= 3 ? 'urgent' : ''; ?>"><?php echo $dias_restantes; ?></span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Puntos</span>
                                <span class="stat-value">⭐ <?php echo $reto['puntos']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido Principal -->
    <div class="container">
        <div class="detail-layout">
            <!-- Columna Principal -->
            <div class="main-column">
                <!-- Descripción Detallada -->
                <section class="detail-section">
                    <h2 class="section-title-detail">Acerca de este reto</h2>
                    <p class="detail-description"><?php echo $reto['descripcion_larga']; ?></p>
                </section>

                <!-- Lista de Tareas -->
                <section class="detail-section">
                    <h2 class="section-title-detail">Tareas del Reto</h2>
                    <p class="section-subtitle">Completa todas las tareas para ganar los puntos del reto</p>
                    
                    <div class="tasks-list">
                        <?php foreach ($reto['tareas'] as $index => $tarea): ?>
                        <div class="task-item <?php echo $tarea['completada'] ? 'completed' : ''; ?>">
                            <div class="task-number"><?php echo $index + 1; ?></div>
                            <div class="task-content">
                                <div class="task-header">
                                    <h4 class="task-title"><?php echo $tarea['titulo']; ?></h4>
                                    <div class="task-points">+<?php echo $tarea['puntos']; ?> pts</div>
                                </div>
                                <p class="task-description"><?php echo $tarea['descripcion']; ?></p>
                                
                                <?php if ($tarea['completada']): ?>
                                    <div class="task-status completed-status">
                                        <span class="status-icon">✓</span>
                                        <span>Completada</span>
                                    </div>
                                <?php else: ?>
                                    <button class="btn-complete-task" data-task-id="<?php echo $tarea['id']; ?>" data-reto-id="<?php echo $reto['id']; ?>">
                                        Marcar como completada
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="sidebar-column">
                <!-- Recompensas -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">🏆 Recompensas</h3>
                    <div class="rewards-list">
                        <div class="reward-item">
                            <span class="reward-icon">⭐</span>
                            <span class="reward-text"><?php echo $reto['puntos']; ?> Puntos</span>
                        </div>
                        <div class="reward-item">
                            <span class="reward-icon">🏅</span>
                            <span class="reward-text">Insignia de <?php echo $reto['categoria']; ?></span>
                        </div>
                        <div class="reward-item">
                            <span class="reward-icon">📊</span>
                            <span class="reward-text">Estadísticas actualizadas</span>
                        </div>
                    </div>
                </div>

                <!-- Tiempo Restante -->
                <div class="sidebar-card <?php echo $dias_restantes <= 3 ? 'urgent-card' : ''; ?>">
                    <h3 class="sidebar-title">⏰ Tiempo Restante</h3>
                    <div class="time-remaining">
                        <span class="time-number"><?php echo $dias_restantes; ?></span>
                        <span class="time-label">días</span>
                    </div>
                    <p class="time-description">Fecha límite: <?php echo date('d/m/Y', strtotime($reto['fecha_limite'])); ?></p>
                </div>

                <!-- Impacto -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">🌍 Impacto Colectivo</h3>
                    <p class="impact-text"><?php echo $reto['impacto']; ?></p>
                    <p class="impact-description">Gracias a <?php echo $reto['participantes']; ?> participantes</p>
                </div>

                <!-- Botón Abandonar -->
                <button class="btn-abandon" data-reto-id="<?php echo $reto['id']; ?>">
                    Abandonar reto
                </button>
            </div>
        </div>
    </div>

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

    <script src="script.js"></script>
    <script>
    // ============================================
    // RETO DETALLE - JAVASCRIPT
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        
        // Botones de completar tarea
        const completeButtons = document.querySelectorAll('.btn-complete-task');
        
        completeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.getAttribute('data-task-id');
                const retoId = this.getAttribute('data-reto-id');
                const taskItem = this.closest('.task-item');
                
                // Mostrar confirmación
                const confirmar = confirm('¿Has completado esta tarea?');
                
                if (confirmar) {
                    // Marcar como completada
                    taskItem.classList.add('completed');
                    
                    // Reemplazar botón con estado completado
                    this.outerHTML = `
                        <div class="task-status completed-status">
                            <span class="status-icon">✓</span>
                            <span>Completada</span>
                        </div>
                    `;
                    
                    // Mostrar notificación
                    showNotification('¡Tarea completada! +' + this.closest('.task-content').querySelector('.task-points').textContent, 'success');
                    
                    // Actualizar progreso
                    actualizarProgreso();
                    
                    // Aquí puedes hacer una petición AJAX para guardar en la base de datos
                    // guardarTareaCompletada(retoId, taskId);
                }
            });
        });
        
        // Botón abandonar reto
        const abandonButton = document.querySelector('.btn-abandon');
        
        if (abandonButton) {
            abandonButton.addEventListener('click', function() {
                const retoId = this.getAttribute('data-reto-id');
                
                const confirmar = confirm('¿Estás seguro de que quieres abandonar este reto? Perderás todo tu progreso.');
                
                if (confirmar) {
                    showNotification('Has abandonado el reto', 'info');
                    
                    setTimeout(() => {
                        window.location.href = 'mis-retos.php';
                    }, 1500);
                    
                    // Aquí puedes hacer una petición AJAX para actualizar la base de datos
                    // abandonarReto(retoId);
                }
            });
        }
        
        // Animar barra de progreso al cargar
        setTimeout(() => {
            const progressBar = document.querySelector('.progress-fill-hero');
            if (progressBar) {
                const width = progressBar.style.width;
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = width;
                }, 100);
            }
        }, 300);
        
    });

    // ============================================
    // FUNCIONES AUXILIARES
    // ============================================

    function actualizarProgreso() {
        const totalTareas = document.querySelectorAll('.task-item').length;
        const tareasCompletadas = document.querySelectorAll('.task-item.completed').length;
        const porcentaje = Math.round((tareasCompletadas / totalTareas) * 100);
        
        // Actualizar barra de progreso
        const progressBar = document.querySelector('.progress-fill-hero');
        const progressPercent = document.querySelector('.progress-percent');
        const statValue = document.querySelector('.progress-stats .stat:first-child .stat-value');
        
        if (progressBar) {
            progressBar.style.width = porcentaje + '%';
        }
        
        if (progressPercent) {
            progressPercent.textContent = porcentaje + '%';
        }
        
        if (statValue) {
            statValue.textContent = tareasCompletadas + '/' + totalTareas;
        }
        
        // Si se completaron todas las tareas
        if (tareasCompletadas === totalTareas) {
            setTimeout(() => {
                mostrarRetoCompletado();
            }, 1000);
        }
    }

    function mostrarRetoCompletado() {
        const modal = document.createElement('div');
        modal.className = 'completion-modal';
        modal.innerHTML = `
            <div class="completion-content">
                <div class="completion-icon">🎉</div>
                <h2>¡Reto Completado!</h2>
                <p>Has completado todas las tareas de este reto</p>
                <div class="completion-rewards">
                    <div class="reward">⭐ +100 Puntos</div>
                    <div class="reward">🏅 Nueva Insignia</div>
                </div>
                <button class="btn-finish" onclick="window.location.href='mis-retos.php?tab=completados'">
                    Ver Mis Logros
                </button>
            </div>
        `;
        
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        `;
        
        document.body.appendChild(modal);
    }

    function showNotification(mensaje, tipo = 'success') {
        const notification = document.createElement('div');
        notification.className = 'custom-notification';
        
        const icon = tipo === 'success' ? '✓' : 'ℹ';
        const bgColor = tipo === 'success' 
            ? 'linear-gradient(135deg, #2ecc71 0%, #27ae60 100%)'
            : 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)';
        
        notification.innerHTML = `
            <span class="notification-icon">${icon}</span>
            <span class="notification-text">${mensaje}</span>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            animation: slideInRight 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Animaciones CSS
    const styleSheet = document.createElement('style');
    styleSheet.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .completion-content {
            background: white;
            padding: 48px;
            border-radius: 24px;
            text-align: center;
            max-width: 500px;
            animation: scaleIn 0.3s ease;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .completion-icon {
            font-size: 96px;
            margin-bottom: 24px;
        }
        
        .completion-content h2 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 12px;
        }
        
        .completion-content p {
            color: #7f8c8d;
            margin-bottom: 32px;
            font-size: 16px;
        }
        
        .completion-rewards {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 32px;
        }
        
        .completion-rewards .reward {
            padding: 12px 24px;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .btn-finish {
            padding: 16px 40px;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-finish:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(46, 204, 113, 0.3);
        }
    `;
    document.head.appendChild(styleSheet);
    </script>
</body>
</html>