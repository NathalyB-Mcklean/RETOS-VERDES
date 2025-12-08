<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Datos del usuario
$user_name = $_SESSION['user_name'];
$user_points = $_SESSION['user_points'];
$user_avatar = $_SESSION['user_avatar'];
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'usuario@ejemplo.com';
$user_since = isset($_SESSION['user_since']) ? $_SESSION['user_since'] : '2024';

// Mis retos activos
$mis_retos = [
    [
        'id' => 1,
        'titulo' => 'Planta un Árbol Nativo',
        'progreso' => 75,
        'puntos_ganados' => 75,
        'puntos_totales' => 100,
        'estado' => 'En progreso',
        'icono' => '🌳'
    ],
    [
        'id' => 4,
        'titulo' => 'Reduce el Plástico',
        'progreso' => 30,
        'puntos_ganados' => 15,
        'puntos_totales' => 50,
        'estado' => 'En progreso',
        'icono' => '♻️'
    ]
];

// Retos completados
$retos_completados = [
    [
        'titulo' => 'Limpieza de Playa',
        'fecha' => '15 Nov 2024',
        'puntos' => 150,
        'icono' => '🏖️'
    ],
    [
        'titulo' => 'Huerta Comunitaria',
        'fecha' => '03 Nov 2024',
        'puntos' => 120,
        'icono' => '🌱'
    ],
    [
        'titulo' => 'Taller de Reciclaje',
        'fecha' => '28 Oct 2024',
        'puntos' => 80,
        'icono' => '📚'
    ]
];

// Logros
$logros = [
    ['titulo' => 'Primer Árbol', 'descripcion' => 'Plantaste tu primer árbol', 'icono' => '🌳', 'desbloqueado' => true],
    ['titulo' => 'Eco Guerrero', 'descripcion' => 'Completaste 5 retos', 'icono' => '⚔️', 'desbloqueado' => true],
    ['titulo' => 'Líder Verde', 'descripcion' => 'Alcanzaste 1000 puntos', 'icono' => '👑', 'desbloqueado' => true],
    ['titulo' => 'Guardián del Agua', 'descripcion' => 'Limpiaste 3 cuerpos de agua', 'icono' => '💧', 'desbloqueado' => false],
    ['titulo' => 'Maestro Reciclador', 'descripcion' => 'Reduce residuos por 30 días', 'icono' => '♻️', 'desbloqueado' => false],
    ['titulo' => 'Embajador Ambiental', 'descripcion' => 'Invita a 10 personas', 'icono' => '🌍', 'desbloqueado' => false]
];

// Estadísticas
$estadisticas = [
    'retos_completados' => 12,
    'retos_activos' => 2,
    'arboles_plantados' => 8,
    'kg_residuos' => 45,
    'horas_voluntariado' => 24,
    'personas_impactadas' => 150
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Retos Verdes</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Clash+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            border-radius: var(--border-radius);
            padding: 48px;
            color: white;
            margin-bottom: 32px;
            box-shadow: var(--shadow-lg);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            font-size: 64px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
        }

        .profile-details h1 {
            font-size: 36px;
            margin-bottom: 8px;
        }

        .profile-meta {
            display: flex;
            gap: 24px;
            margin-top: 16px;
            opacity: 0.9;
        }

        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-top: 32px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card-value {
            font-size: 32px;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }

        .stat-card-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 32px;
        }

        .profile-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-sm);
        }

        .section-title-profile {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .my-challenge-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .my-challenge-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .my-challenge-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .challenge-status {
            background: var(--light-green);
            color: var(--primary-green);
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .achievement-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .achievement-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: var(--transition);
        }

        .achievement-card.locked {
            opacity: 0.4;
            filter: grayscale(1);
        }

        .achievement-card:not(.locked):hover {
            transform: scale(1.05);
            background: var(--light-green);
        }

        .achievement-icon {
            font-size: 48px;
            margin-bottom: 8px;
        }

        .achievement-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .achievement-desc {
            font-size: 12px;
            color: var(--light-text);
        }

        .completed-challenge {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: var(--bg-light);
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .completed-icon {
            font-size: 32px;
            width: 56px;
            height: 56px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .completed-info {
            flex: 1;
        }

        .completed-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .completed-date {
            font-size: 13px;
            color: var(--light-text);
        }

        .completed-points {
            font-weight: 700;
            color: var(--primary-green);
        }

        .full-width-section {
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .profile-info {
                flex-direction: column;
                text-align: center;
            }

            .profile-content {
                grid-template-columns: 1fr;
            }

            .achievement-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
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
                    <a href="#mis-retos" class="nav-link">Mis Retos</a>
                    <a href="#comunidad" class="nav-link">Comunidad</a>
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

    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-info">
                <div class="profile-avatar-large"><?php echo $user_avatar; ?></div>
                <div class="profile-details">
                    <h1><?php echo $user_name; ?></h1>
                    <div class="profile-meta">
                        <div class="profile-meta-item">
                            <span>📧</span>
                            <span><?php echo $user_email; ?></span>
                        </div>
                        <div class="profile-meta-item">
                            <span>📅</span>
                            <span>Miembro desde <?php echo $user_since; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-card">
                    <span class="stat-card-value"><?php echo $estadisticas['retos_completados']; ?></span>
                    <span class="stat-card-label">Retos Completados</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card-value"><?php echo $estadisticas['arboles_plantados']; ?></span>
                    <span class="stat-card-label">Árboles Plantados</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card-value"><?php echo $estadisticas['kg_residuos']; ?> kg</span>
                    <span class="stat-card-label">Residuos Reciclados</span>
                </div>
                <div class="stat-card">
                    <span class="stat-card-value"><?php echo $estadisticas['horas_voluntariado']; ?>h</span>
                    <span class="stat-card-label">Horas de Voluntariado</span>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Mis Retos Activos -->
            <div class="profile-section">
                <h3 class="section-title-profile">🎯 Mis Retos Activos</h3>
                <?php foreach ($mis_retos as $reto): ?>
                <div class="my-challenge-card">
                    <div class="my-challenge-header">
                        <div class="my-challenge-title">
                            <span style="font-size: 24px;"><?php echo $reto['icono']; ?></span>
                            <span><?php echo $reto['titulo']; ?></span>
                        </div>
                        <span class="challenge-status"><?php echo $reto['estado']; ?></span>
                    </div>
                    <div class="challenge-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $reto['progreso']; ?>%"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 8px;">
                            <span class="progress-text"><?php echo $reto['progreso']; ?>% completado</span>
                            <span class="progress-text"><?php echo $reto['puntos_ganados']; ?>/<?php echo $reto['puntos_totales']; ?> pts</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Logros -->
            <div class="profile-section">
                <h3 class="section-title-profile">🏆 Logros</h3>
                <div class="achievement-grid">
                    <?php foreach ($logros as $logro): ?>
                    <div class="achievement-card <?php echo !$logro['desbloqueado'] ? 'locked' : ''; ?>">
                        <div class="achievement-icon"><?php echo $logro['icono']; ?></div>
                        <div class="achievement-title"><?php echo $logro['titulo']; ?></div>
                        <div class="achievement-desc"><?php echo $logro['descripcion']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Historial de Retos -->
            <div class="profile-section full-width-section">
                <h3 class="section-title-profile">📋 Retos Completados Recientemente</h3>
                <?php foreach ($retos_completados as $completado): ?>
                <div class="completed-challenge">
                    <div class="completed-icon"><?php echo $completado['icono']; ?></div>
                    <div class="completed-info">
                        <div class="completed-title"><?php echo $completado['titulo']; ?></div>
                        <div class="completed-date"><?php echo $completado['fecha']; ?></div>
                    </div>
                    <div class="completed-points">+<?php echo $completado['puntos']; ?> pts</div>
                </div>
                <?php endforeach; ?>
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
</body>
</html>