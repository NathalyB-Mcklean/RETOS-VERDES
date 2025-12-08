<?php
session_start();

// Verificar si el usuario está logueado
$user_logged_in = isset($_SESSION['user_id']);

// Si no está logueado, redirigir a login
if (!$user_logged_in) {
    $_SESSION['redirect_after_login'] = 'mis-retos.php';
    header('Location: login.php');
    exit();
}

// Datos del usuario
$user_name = $_SESSION['user_name'];
$user_points = $_SESSION['user_points'];
$user_avatar = $_SESSION['user_avatar'];

// Retos en los que está inscrito (en progreso)
$retos_inscritos = [
    [
        'id' => 1,
        'titulo' => 'Planta un Árbol Nativo',
        'descripcion' => 'Planta árboles nativos en tu comunidad',
        'categoria' => 'arboles',
        'puntos' => 100,
        'fecha_inicio' => '2025-12-01',
        'fecha_limite' => '2025-12-31',
        'icono' => '🌳',
        'progreso' => 60,
        'tareas_completadas' => 3,
        'tareas_totales' => 5,
        'estado' => 'en_progreso'
    ],
    [
        'id' => 2,
        'titulo' => 'Limpieza de Quebrada',
        'descripcion' => 'Limpia ríos y quebradas locales',
        'categoria' => 'agua',
        'puntos' => 150,
        'fecha_inicio' => '2025-12-05',
        'fecha_limite' => '2025-12-12',
        'icono' => '💧',
        'progreso' => 45,
        'tareas_completadas' => 2,
        'tareas_totales' => 4,
        'estado' => 'en_progreso'
    ],
    [
        'id' => 4,
        'titulo' => 'Reduce el Plástico',
        'descripcion' => 'Elimina plásticos de un solo uso',
        'categoria' => 'residuos',
        'puntos' => 50,
        'fecha_inicio' => '2025-12-03',
        'fecha_limite' => '2025-12-10',
        'icono' => '♻️',
        'progreso' => 30,
        'tareas_completadas' => 1,
        'tareas_totales' => 3,
        'estado' => 'en_progreso'
    ]
];

// Retos completados
$retos_completados = [
    [
        'id' => 5,
        'titulo' => 'Semana sin Plástico',
        'descripcion' => 'Una semana completa sin usar plásticos',
        'categoria' => 'residuos',
        'puntos' => 80,
        'fecha_completado' => '2025-11-28',
        'icono' => '♻️',
        'insignia' => '🏅',
        'posicion_ranking' => 12
    ],
    [
        'id' => 6,
        'titulo' => 'Voluntario de Reciclaje',
        'descripcion' => 'Participar en jornada de reciclaje comunitario',
        'categoria' => 'residuos',
        'puntos' => 120,
        'fecha_completado' => '2025-11-15',
        'icono' => '♻️',
        'insignia' => '🌟',
        'posicion_ranking' => 8
    ],
    [
        'id' => 7,
        'titulo' => 'Observador de Aves',
        'descripcion' => 'Registra 10 especies de aves diferentes',
        'categoria' => 'fauna',
        'puntos' => 90,
        'fecha_completado' => '2025-11-05',
        'icono' => '🐦',
        'insignia' => '⭐',
        'posicion_ranking' => 15
    ]
];

// Estadísticas del usuario
$estadisticas = [
    'total_retos_completados' => count($retos_completados),
    'total_puntos_ganados' => array_sum(array_column($retos_completados, 'puntos')),
    'retos_en_progreso' => count($retos_inscritos),
    'racha_dias' => 12,
    'posicion_general' => 45
];

// Calcular días restantes para retos activos
function diasRestantes($fecha_limite) {
    $hoy = new DateTime();
    $limite = new DateTime($fecha_limite);
    $diff = $hoy->diff($limite);
    return $diff->days;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Retos | Retos Verdes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="mis-retos.css">
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

    <!-- Hero Section -->
    <section class="my-challenges-hero">
        <div class="container">
            <div class="hero-content">
                <h2 class="page-title">Mis Retos</h2>
                <p class="page-subtitle">Sigue tu progreso y alcanza nuevas metas ambientales</p>
            </div>
        </div>
    </section>

    <!-- Estadísticas -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $estadisticas['total_retos_completados']; ?></span>
                        <span class="stat-label">Retos Completados</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo number_format($estadisticas['total_puntos_ganados']); ?></span>
                        <span class="stat-label">Puntos Ganados</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $estadisticas['retos_en_progreso']; ?></span>
                        <span class="stat-label">En Progreso</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔥</div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $estadisticas['racha_dias']; ?></span>
                        <span class="stat-label">Días de Racha</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabs de Retos -->
    <div class="container">
        <div class="challenges-tabs">
            <button class="tab-btn active" data-tab="en-progreso">
                En Progreso (<?php echo count($retos_inscritos); ?>)
            </button>
            <button class="tab-btn" data-tab="completados">
                Completados (<?php echo count($retos_completados); ?>)
            </button>
            <button class="tab-btn" data-tab="disponibles">
                Descubrir Nuevos
            </button>
        </div>

        <!-- Contenido de Tabs -->
        <div class="tab-content active" id="en-progreso">
            <div class="section-header">
                <h3 class="section-title">Retos en Progreso</h3>
                <p class="section-description">Continúa con tus retos activos y completa las tareas pendientes</p>
            </div>

            <?php if (empty($retos_inscritos)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📋</span>
                    <h3>No tienes retos activos</h3>
                    <p>¡Únete a nuevos retos y empieza a hacer la diferencia!</p>
                    <a href="index.php#retos" class="btn-primary">Explorar Retos</a>
                </div>
            <?php else: ?>
                <div class="challenges-grid">
                    <?php foreach ($retos_inscritos as $reto): ?>
                    <div class="challenge-card-progress">
                        <div class="challenge-header">
                            <span class="challenge-icon-large"><?php echo $reto['icono']; ?></span>
                            <div class="challenge-status">
                                <span class="status-badge in-progress">En Progreso</span>
                                <span class="days-left"><?php echo diasRestantes($reto['fecha_limite']); ?> días restantes</span>
                            </div>
                        </div>
                        
                        <h3 class="challenge-title"><?php echo $reto['titulo']; ?></h3>
                        <p class="challenge-description"><?php echo $reto['descripcion']; ?></p>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <span class="progress-label">Progreso General</span>
                                <span class="progress-percentage"><?php echo $reto['progreso']; ?>%</span>
                            </div>
                            <div class="progress-bar-large">
                                <div class="progress-fill-large" style="width: <?php echo $reto['progreso']; ?>%"></div>
                            </div>
                        </div>

                        <div class="tasks-info">
                            <span class="tasks-icon">✓</span>
                            <span class="tasks-text"><?php echo $reto['tareas_completadas']; ?>/<?php echo $reto['tareas_totales']; ?> tareas completadas</span>
                        </div>

                        <div class="challenge-footer">
                            <div class="points-reward">
                                <span class="points-icon">⭐</span>
                                <span><?php echo $reto['puntos']; ?> puntos</span>
                            </div>
                            <a href="reto-detalle.php?id=<?php echo $reto['id']; ?>" class="btn-continue">Continuar →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Retos Completados -->
        <div class="tab-content" id="completados">
            <div class="section-header">
                <h3 class="section-title">Retos Completados 🎉</h3>
                <p class="section-description">Celebra tus logros y el impacto que has generado</p>
            </div>

            <?php if (empty($retos_completados)): ?>
                <div class="empty-state">
                    <span class="empty-icon">🏆</span>
                    <h3>Aún no has completado retos</h3>
                    <p>¡Empieza tu primer reto y únete a la comunidad verde!</p>
                    <a href="index.php#retos" class="btn-primary">Ver Retos Disponibles</a>
                </div>
            <?php else: ?>
                <div class="completed-grid">
                    <?php foreach ($retos_completados as $reto): ?>
                    <div class="completed-card">
                        <div class="completed-badge"><?php echo $reto['insignia']; ?></div>
                        <div class="completed-icon"><?php echo $reto['icono']; ?></div>
                        <h4 class="completed-title"><?php echo $reto['titulo']; ?></h4>
                        <p class="completed-description"><?php echo $reto['descripcion']; ?></p>
                        
                        <div class="completed-stats">
                            <div class="completed-stat">
                                <span class="stat-icon">⭐</span>
                                <span class="stat-value">+<?php echo $reto['puntos']; ?> pts</span>
                            </div>
                            <div class="completed-stat">
                                <span class="stat-icon">🏅</span>
                                <span class="stat-value">Top <?php echo $reto['posicion_ranking']; ?></span>
                            </div>
                        </div>

                        <div class="completed-date">
                            <span class="date-icon">📅</span>
                            <span>Completado: <?php echo date('d/m/Y', strtotime($reto['fecha_completado'])); ?></span>
                        </div>

                        <div class="completed-actions">
                            <button class="btn-share">Compartir</button>
                            <button class="btn-repeat">Repetir Reto</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Descubrir Nuevos -->
        <div class="tab-content" id="disponibles">
            <div class="section-header">
                <h3 class="section-title">Descubre Nuevos Retos</h3>
                <p class="section-description">Explora retos disponibles y únete a nuevas aventuras verdes</p>
            </div>
            <div class="discover-redirect">
                <div class="redirect-icon">🌍</div>
                <h3>Explora todos los retos disponibles</h3>
                <p>Encuentra retos que se adapten a tus intereses y nivel de experiencia</p>
                <a href="index.php#retos" class="btn-primary-large">Ver Todos los Retos →</a>
            </div>
        </div>
    </div>

    <!-- Sección de Motivación -->
    <section class="motivation-section">
        <div class="container">
            <div class="motivation-card">
                <div class="motivation-content">
                    <h3 class="motivation-title">¡Sigue así! 💪</h3>
                    <p class="motivation-text">Estás haciendo una diferencia real en tu comunidad. Cada reto completado cuenta.</p>
                    <div class="next-goal">
                        <span class="goal-icon">🎯</span>
                        <span class="goal-text">Próximo hito: 5 retos completados para desbloquear la insignia "Eco Guerrero"</span>
                    </div>
                </div>
                <div class="motivation-illustration">
                    <span class="illustration-badge">🌟</span>
                </div>
            </div>
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

    <script src="script.js"></script>
    <script src="mis-retos.js"></script>
</body>
</html>