<?php
session_start();

// Simulación de datos (posteriormente conectarás a tu base de datos)
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? $_SESSION['user_name'] : 'Invitado';
$user_points = $user_logged_in ? $_SESSION['user_points'] : 0;
$user_avatar = $user_logged_in ? $_SESSION['user_avatar'] : 'default-avatar.png';

// Retos activos de ejemplo
$retos_activos = [
    [
        'id' => 1,
        'titulo' => 'Planta un Árbol Nativo',
        'descripcion' => 'Planta árboles nativos en tu comunidad',
        'categoria' => 'arboles',
        'puntos' => 100,
        'participantes' => 45,
        'duracion' => 'Mensual',
        'icono' => '🌳',
        'progreso' => 60
    ],
    [
        'id' => 2,
        'titulo' => 'Limpieza de Quebrada',
        'descripcion' => 'Limpia ríos y quebradas locales',
        'categoria' => 'agua',
        'puntos' => 150,
        'participantes' => 32,
        'duracion' => 'Semanal',
        'icono' => '💧',
        'progreso' => 45
    ],
    [
        'id' => 3,
        'titulo' => 'Jardín de Polinizadores',
        'descripcion' => 'Crea espacios para abejas y mariposas',
        'categoria' => 'fauna',
        'puntos' => 80,
        'participantes' => 28,
        'duracion' => 'Mensual',
        'icono' => '🐦',
        'progreso' => 75
    ],
    [
        'id' => 4,
        'titulo' => 'Reduce el Plástico',
        'descripcion' => 'Elimina plásticos de un solo uso',
        'categoria' => 'residuos',
        'puntos' => 50,
        'participantes' => 67,
        'duracion' => 'Semanal',
        'icono' => '♻️',
        'progreso' => 30
    ]
];

// Ranking comunitario
$ranking = [
    ['posicion' => 1, 'nombre' => 'María González', 'puntos' => 2450, 'avatar' => '👩', 'cambio' => '+3'],
    ['posicion' => 2, 'nombre' => 'Carlos Ruiz', 'puntos' => 2180, 'avatar' => '👨', 'cambio' => '-1'],
    ['posicion' => 3, 'nombre' => 'Ana Martínez', 'puntos' => 1950, 'avatar' => '👧', 'cambio' => '+2'],
    ['posicion' => 4, 'nombre' => 'Luis Pérez', 'puntos' => 1780, 'avatar' => '🧑', 'cambio' => '-1'],
    ['posicion' => 5, 'nombre' => 'Sofia Torres', 'puntos' => 1650, 'avatar' => '👩', 'cambio' => '0']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retos Verdes Comunitarios | Panamá</title>
    <link rel="stylesheet" href="style.css">
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
                    <a href="#retos" class="nav-link active">Descubrir</a>
                    <a href="#ranking" class="nav-link">Ranking</a>
                    <a href="#mis-retos" class="nav-link">Mis Retos</a>
                    <a href="http://127.1.1.1/ReTOS-VERDES/comunidad.php" class="nav-link">Comunidad</a>
                </nav>
                <div class="header-actions">
                    <?php if ($user_logged_in): ?>
                        <div class="user-points">
                            <span class="points-icon">⭐</span>
                            <span class="points-value"><?php echo number_format($user_points); ?></span>
                        </div>
                        <a href="profile.php" class="user-avatar">
                            <span><?php echo $user_avatar; ?></span>
                        </a>
                        <a href="logout.php" class="btn-primary" style="background: #e74c3c; font-size: 14px; padding: 10px 20px;">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">Iniciar Sesión</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h2 class="hero-title">Transforma tu Comunidad</h2>
                    <p class="hero-subtitle">Únete a retos ambientales, gana puntos y haz la diferencia en Panamá 🇵🇦</p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">1,234</span>
                            <span class="stat-label">Árboles Plantados</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">567</span>
                            <span class="stat-label">Participantes</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">89</span>
                            <span class="stat-label">Comunidades</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="hero-illustration">
                        <span class="illustration-emoji">🌳</span>
                        <span class="illustration-emoji">💧</span>
                        <span class="illustration-emoji">🐦</span>
                        <span class="illustration-emoji">♻️</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorías -->
    <section class="categories">
        <div class="container">
            <h3 class="section-title">Explora por Categoría</h3>
            <div class="category-grid">
                <button class="category-card active" data-category="todos">
                    <span class="category-icon">🌍</span>
                    <span class="category-name">Todos</span>
                </button>
                <button class="category-card" data-category="arboles">
                    <span class="category-icon">🌳</span>
                    <span class="category-name">Árboles</span>
                </button>
                <button class="category-card" data-category="agua">
                    <span class="category-icon">💧</span>
                    <span class="category-name">Agua</span>
                </button>
                <button class="category-card" data-category="fauna">
                    <span class="category-icon">🐦</span>
                    <span class="category-name">Fauna</span>
                </button>
                <button class="category-card" data-category="residuos">
                    <span class="category-icon">♻️</span>
                    <span class="category-name">Residuos</span>
                </button>
                <button class="category-card" data-category="educacion">
                    <span class="category-icon">📚</span>
                    <span class="category-name">Educación</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Retos Activos -->
    <section class="challenges" id="retos">
        <div class="container">
            <div class="section-header">
                <h3 class="section-title">Retos Activos</h3>
                <a href="retos.php" class="see-more">Ver todos →</a>
            </div>
            <div class="challenges-grid">
                <?php foreach ($retos_activos as $reto): ?>
                <div class="challenge-card" data-category="<?php echo $reto['categoria']; ?>">
                    <div class="challenge-header">
                        <span class="challenge-icon"><?php echo $reto['icono']; ?></span>
                        <span class="challenge-duration"><?php echo $reto['duracion']; ?></span>
                    </div>
                    <h4 class="challenge-title"><?php echo $reto['titulo']; ?></h4>
                    <p class="challenge-description"><?php echo $reto['descripcion']; ?></p>
                    <div class="challenge-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $reto['progreso']; ?>%"></div>
                        </div>
                        <span class="progress-text"><?php echo $reto['progreso']; ?>% completado</span>
                    </div>
                    <div class="challenge-footer">
                        <div class="challenge-info">
                            <span class="info-item">
                                <span class="info-icon">👥</span>
                                <?php echo $reto['participantes']; ?> participantes
                            </span>
                            <span class="info-item">
                                <span class="info-icon">⭐</span>
                                <?php echo $reto['puntos']; ?> pts
                            </span>
                        </div>
                        <a href="reto.php?id=<?php echo $reto['id']; ?>" class="btn btn-challenge">¡Participar!</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Ranking Comunitario -->
    <section class="leaderboard" id="ranking">
        <div class="container">
            <div class="leaderboard-container">
                <div class="leaderboard-header">
                    <h3 class="section-title">🏆 Ranking Comunitario</h3>
                    <div class="ranking-filters">
                        <button class="filter-btn active">Semanal</button>
                        <button class="filter-btn">Mensual</button>
                        <button class="filter-btn">Anual</button>
                    </div>
                </div>
                
                <!-- Top 3 -->
                <div class="top-three">
                    <div class="top-card second">
                        <div class="top-avatar"><?php echo $ranking[1]['avatar']; ?></div>
                        <div class="top-badge">2</div>
                        <span class="top-name"><?php echo $ranking[1]['nombre']; ?></span>
                        <span class="top-points"><?php echo number_format($ranking[1]['puntos']); ?> pts</span>
                    </div>
                    <div class="top-card first">
                        <div class="top-crown">👑</div>
                        <div class="top-avatar winner"><?php echo $ranking[0]['avatar']; ?></div>
                        <div class="top-badge">1</div>
                        <span class="top-name"><?php echo $ranking[0]['nombre']; ?></span>
                        <span class="top-points"><?php echo number_format($ranking[0]['puntos']); ?> pts</span>
                    </div>
                    <div class="top-card third">
                        <div class="top-avatar"><?php echo $ranking[2]['avatar']; ?></div>
                        <div class="top-badge">3</div>
                        <span class="top-name"><?php echo $ranking[2]['nombre']; ?></span>
                        <span class="top-points"><?php echo number_format($ranking[2]['puntos']); ?> pts</span>
                    </div>
                </div>

                <!-- Lista de ranking -->
                <div class="ranking-list">
                    <?php for ($i = 3; $i < count($ranking); $i++): ?>
                    <div class="ranking-item">
                        <span class="ranking-position"><?php echo $ranking[$i]['posicion']; ?></span>
                        <div class="ranking-avatar"><?php echo $ranking[$i]['avatar']; ?></div>
                        <div class="ranking-info">
                            <span class="ranking-name"><?php echo $ranking[$i]['nombre']; ?></span>
                            <span class="ranking-points"><?php echo number_format($ranking[$i]['puntos']); ?> pts</span>
                        </div>
                        <span class="ranking-change <?php echo $ranking[$i]['cambio'][0] === '+' ? 'positive' : ($ranking[$i]['cambio'][0] === '-' ? 'negative' : ''); ?>">
                            <?php echo $ranking[$i]['cambio']; ?>
                        </span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ODS Section -->
    <section class="ods-section">
        <div class="container">
            <h3 class="section-title">Contribuimos a los ODS de la ONU</h3>
            <div class="ods-grid">
                <div class="ods-card" style="background: linear-gradient(135deg, #e5243b 0%, #c41230 100%);">
                    <span class="ods-number">5</span>
                    <span class="ods-text">Igualdad de Género</span>
                </div>
                <div class="ods-card" style="background: linear-gradient(135deg, #26bde2 0%, #1a9aba 100%);">
                    <span class="ods-number">6</span>
                    <span class="ods-text">Agua Limpia</span>
                </div>
                <div class="ods-card" style="background: linear-gradient(135deg, #fd9d24 0%, #e67e22 100%);">
                    <span class="ods-number">11</span>
                    <span class="ods-text">Ciudades Sostenibles</span>
                </div>
                <div class="ods-card" style="background: linear-gradient(135deg, #3f7e44 0%, #2d5a31 100%);">
                    <span class="ods-number">13</span>
                    <span class="ods-text">Acción Climática</span>
                </div>
                <div class="ods-card" style="background: linear-gradient(135deg, #56c02b 0%, #3f8e1f 100%);">
                    <span class="ods-number">15</span>
                    <span class="ods-text">Vida Terrestre</span>
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
</body>
</html>