<?php
session_start();

// Simulación de datos del usuario
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? $_SESSION['user_name'] : 'Invitado';
$user_points = $user_logged_in ? $_SESSION['user_points'] : 0;
$user_avatar = $user_logged_in ? $_SESSION['user_avatar'] : 'default-avatar.png';

// Publicaciones de la comunidad
$publicaciones = [
    [
        'id' => 1,
        'usuario' => 'María González',
        'avatar' => '👩',
        'tiempo' => 'Hace 2 horas',
        'contenido' => '¡Acabamos de plantar 50 árboles nativos en el Parque Omar! Increíble ver a la comunidad unida por el medio ambiente 🌳💚',
        'imagen' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800&q=80',
        'reto' => 'Planta un Árbol Nativo',
        'likes' => 124,
        'comentarios' => 18,
        'compartidos' => 9
    ],
    [
        'id' => 2,
        'usuario' => 'Carlos Ruiz',
        'avatar' => '👨',
        'tiempo' => 'Hace 5 horas',
        'contenido' => 'Terminamos la limpieza de la Quebrada Juan Díaz. Recolectamos más de 200kg de residuos. ¡Sigamos así Panamá! 💪',
        'imagen' => 'https://images.unsplash.com/photo-1618477461853-cf6ed80faba5?w=800&q=80',
        'reto' => 'Limpieza de Quebrada',
        'likes' => 98,
        'comentarios' => 12,
        'compartidos' => 15
    ],
    [
        'id' => 3,
        'usuario' => 'Ana Martínez',
        'avatar' => '👧',
        'tiempo' => 'Hace 1 día',
        'contenido' => 'Mi jardín de polinizadores está floreciendo. Ya veo abejas y mariposas todos los días 🦋🐝',
        'imagen' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=800&q=80',
        'reto' => 'Jardín de Polinizadores',
        'likes' => 156,
        'comentarios' => 24,
        'compartidos' => 7
    ],
    [
        'id' => 4,
        'usuario' => 'Luis Pérez',
        'avatar' => '🧑',
        'tiempo' => 'Hace 2 días',
        'contenido' => 'Llevo 1 mes sin usar plásticos de un solo uso. Es más fácil de lo que pensaba. ¡Únanse al reto! ♻️',
        'imagen' => false,
        'reto' => 'Reduce el Plástico',
        'likes' => 87,
        'comentarios' => 31,
        'compartidos' => 21
    ]
];

// Eventos comunitarios próximos
$eventos = [
    [
        'id' => 1,
        'titulo' => 'Jornada de Reforestación',
        'fecha' => '15 Dic 2025',
        'hora' => '8:00 AM',
        'lugar' => 'Parque Natural Metropolitano',
        'participantes' => 45,
        'icono' => '🌳'
    ],
    [
        'id' => 2,
        'titulo' => 'Limpieza de Playas',
        'fecha' => '18 Dic 2025',
        'hora' => '7:00 AM',
        'lugar' => 'Playa Kobbe',
        'participantes' => 67,
        'icono' => '🏖️'
    ],
    [
        'id' => 3,
        'titulo' => 'Taller de Compostaje',
        'fecha' => '20 Dic 2025',
        'hora' => '3:00 PM',
        'lugar' => 'Centro Comunitario',
        'participantes' => 23,
        'icono' => '♻️'
    ]
];

// Grupos de la comunidad
$grupos = [
    ['nombre' => 'Guardianes del Agua', 'miembros' => 234, 'icono' => '💧'],
    ['nombre' => 'Plantadores Urbanos', 'miembros' => 189, 'icono' => '🌱'],
    ['nombre' => 'Cero Residuos PTY', 'miembros' => 156, 'icono' => '♻️'],
    ['nombre' => 'Fauna Panameña', 'miembros' => 142, 'icono' => '🐦']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidad | Retos Verdes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="comunidad.css">
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
                    <a href="mis-retos.php" class="nav-link">Mis Retos</a>
                    <a href="comunidad.php" class="nav-link active">Comunidad</a>
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

    <!-- Hero Community -->
    <section class="community-hero">
        <div class="container">
            <div class="community-hero-content">
                <h2 class="page-title">Comunidad Verde</h2>
                <p class="page-subtitle">Conecta, comparte y celebra tus logros ambientales con otros panameños</p>
            </div>
        </div>
    </section>

    <!-- Contenido Principal -->
    <div class="container">
        <div class="community-layout">
            
            <!-- Columna Izquierda - Sidebar -->
            <aside class="community-sidebar">
                
                <!-- Nueva Publicación -->
                <?php if ($user_logged_in): ?>
                <div class="create-post-card">
                    <div class="user-input">
                        <div class="user-avatar-small"><?php echo $user_avatar; ?></div>
                        <button class="post-input" onclick="openPostModal()">
                            ¿Qué logro ambiental quieres compartir?
                        </button>
                    </div>
                    <div class="post-actions">
                        <button class="post-action-btn">📷 Foto</button>
                        <button class="post-action-btn">🏆 Reto</button>
                        <button class="post-action-btn">📍 Ubicación</button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Grupos -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Grupos Populares</h3>
                    <div class="groups-list">
                        <?php foreach ($grupos as $grupo): ?>
                        <div class="group-item">
                            <span class="group-icon"><?php echo $grupo['icono']; ?></span>
                            <div class="group-info">
                                <span class="group-name"><?php echo $grupo['nombre']; ?></span>
                                <span class="group-members"><?php echo $grupo['miembros']; ?> miembros</span>
                            </div>
                            <button class="btn-join">Unirse</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Eventos Próximos -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Próximos Eventos</h3>
                    <div class="events-list">
                        <?php foreach ($eventos as $evento): ?>
                        <div class="event-item">
                            <span class="event-icon"><?php echo $evento['icono']; ?></span>
                            <div class="event-info">
                                <h4 class="event-title"><?php echo $evento['titulo']; ?></h4>
                                <p class="event-date">📅 <?php echo $evento['fecha']; ?> - <?php echo $evento['hora']; ?></p>
                                <p class="event-location">📍 <?php echo $evento['lugar']; ?></p>
                                <span class="event-participants">👥 <?php echo $evento['participantes']; ?> asistirán</span>
                            </div>
                            <button class="btn-event">Asistir</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>

            <!-- Columna Central - Feed -->
            <main class="community-feed">
                
                <!-- Filtros -->
                <div class="feed-filters">
                    <button class="filter-tab active">Recientes</button>
                    <button class="filter-tab">Populares</button>
                    <button class="filter-tab">Siguiendo</button>
                    <button class="filter-tab">Mis Publicaciones</button>
                </div>

                <!-- Publicaciones -->
                <div class="posts-container">
                    <?php foreach ($publicaciones as $post): ?>
                    <article class="post-card">
                        <div class="post-header">
                            <div class="post-user">
                                <div class="post-avatar"><?php echo $post['avatar']; ?></div>
                                <div class="post-user-info">
                                    <h4 class="post-username"><?php echo $post['usuario']; ?></h4>
                                    <span class="post-time"><?php echo $post['tiempo']; ?></span>
                                </div>
                            </div>
                            <button class="post-menu">⋯</button>
                        </div>

                        <div class="post-content">
                            <p class="post-text"><?php echo $post['contenido']; ?></p>
                            
                            <?php if ($post['imagen']): ?>
                            <div class="post-image">
                                <img src="<?php echo $post['imagen']; ?>" alt="<?php echo $post['reto']; ?>" loading="lazy">
                            </div>
                            <?php endif; ?>

                            <?php if ($post['reto']): ?>
                            <div class="post-challenge-tag">
                                <span class="challenge-tag-icon">🏆</span>
                                <span>Reto: <?php echo $post['reto']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="post-stats">
                            <span class="post-stat">❤️ <?php echo $post['likes']; ?></span>
                            <span class="post-stat">💬 <?php echo $post['comentarios']; ?> comentarios</span>
                            <span class="post-stat">🔄 <?php echo $post['compartidos']; ?> compartidos</span>
                        </div>

                        <div class="post-actions">
                            <button class="post-action-button">
                                <span>👍</span> Me gusta
                            </button>
                            <button class="post-action-button">
                                <span>💬</span> Comentar
                            </button>
                            <button class="post-action-button">
                                <span>🔄</span> Compartir
                            </button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <div class="load-more">
                    <button class="btn-load-more">Cargar más publicaciones</button>
                </div>

            </main>

            <!-- Columna Derecha - Widgets -->
            <aside class="community-widgets">
                
                <!-- Impacto de la Comunidad -->
                <div class="widget-card">
                    <h3 class="widget-title">Impacto Comunitario</h3>
                    <div class="impact-stats">
                        <div class="impact-item">
                            <span class="impact-icon">🌳</span>
                            <div class="impact-info">
                                <span class="impact-number">1,234</span>
                                <span class="impact-label">Árboles Plantados</span>
                            </div>
                        </div>
                        <div class="impact-item">
                            <span class="impact-icon">♻️</span>
                            <div class="impact-info">
                                <span class="impact-number">2,567 kg</span>
                                <span class="impact-label">Residuos Reciclados</span>
                            </div>
                        </div>
                        <div class="impact-item">
                            <span class="impact-icon">💧</span>
                            <div class="impact-info">
                                <span class="impact-number">12</span>
                                <span class="impact-label">Ríos Limpiados</span>
                            </div>
                        </div>
                        <div class="impact-item">
                            <span class="impact-icon">🐦</span>
                            <div class="impact-info">
                                <span class="impact-number">89</span>
                                <span class="impact-label">Especies Protegidas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Miembros Activos -->
                <div class="widget-card">
                    <h3 class="widget-title">Miembros Activos</h3>
                    <div class="active-members">
                        <div class="member-item">
                            <div class="member-avatar">👩</div>
                            <span class="member-name">María González</span>
                            <span class="member-badge">🔥</span>
                        </div>
                        <div class="member-item">
                            <div class="member-avatar">👨</div>
                            <span class="member-name">Carlos Ruiz</span>
                            <span class="member-badge">⭐</span>
                        </div>
                        <div class="member-item">
                            <div class="member-avatar">👧</div>
                            <span class="member-name">Ana Martínez</span>
                            <span class="member-badge">🌟</span>
                        </div>
                        <div class="member-item">
                            <div class="member-avatar">🧑</div>
                            <span class="member-name">Luis Pérez</span>
                            <span class="member-badge">💚</span>
                        </div>
                    </div>
                </div>

                <!-- Hashtags Populares -->
                <div class="widget-card">
                    <h3 class="widget-title">Tendencias</h3>
                    <div class="trending-tags">
                        <a href="#" class="tag">#ReforestaciónPTY</a>
                        <a href="#" class="tag">#PanamáVerde</a>
                        <a href="#" class="tag">#CeroPlástico</a>
                        <a href="#" class="tag">#AguaLimpia</a>
                        <a href="#" class="tag">#BiodiversidadPA</a>
                        <a href="#" class="tag">#EcoWarriors</a>
                    </div>
                </div>

            </aside>

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
    
    <!-- JavaScript unificado -->
    <script>
    // ============================================
    // PÁGINA DE COMUNIDAD - JAVASCRIPT
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        
        // ============================================
        // FILTROS DEL FEED
        // ============================================
        const filterTabs = document.querySelectorAll('.filter-tab');
        
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remover clase active de todos
                filterTabs.forEach(t => t.classList.remove('active'));
                // Agregar clase active al clickeado
                this.classList.add('active');
                
                // Aquí puedes agregar lógica para filtrar publicaciones
                const filter = this.textContent.trim().toLowerCase();
                console.log('Filtro seleccionado:', filter);
                
                // Animación de carga
                showLoadingAnimation();
            });
        });
        
        // ============================================
        // ACCIONES DE PUBLICACIONES
        // ============================================
        
        // Me gusta
        const likeButtons = document.querySelectorAll('.post-action-button');
        likeButtons.forEach(button => {
            if (button.textContent.includes('Me gusta')) {
                button.addEventListener('click', function() {
                    this.classList.toggle('liked');
                    
                    if (this.classList.contains('liked')) {
                        this.style.color = '#e74c3c';
                        this.querySelector('span').textContent = '❤️';
                        showNotification('¡Te gustó esta publicación!');
                    } else {
                        this.style.color = '';
                        this.querySelector('span').textContent = '👍';
                    }
                });
            }
        });
        
        // Comentar
        likeButtons.forEach(button => {
            if (button.textContent.includes('Comentar')) {
                button.addEventListener('click', function() {
                    const postCard = this.closest('.post-card');
                    openCommentModal(postCard);
                });
            }
        });
        
        // Compartir
        likeButtons.forEach(button => {
            if (button.textContent.includes('Compartir')) {
                button.addEventListener('click', function() {
                    showShareOptions();
                });
            }
        });
        
        // ============================================
        // UNIRSE A GRUPOS
        // ============================================
        const joinButtons = document.querySelectorAll('.btn-join');
        
        joinButtons.forEach(button => {
            button.addEventListener('click', function() {
                const groupName = this.closest('.group-item').querySelector('.group-name').textContent;
                
                if (this.textContent === 'Unirse') {
                    this.textContent = 'Unido ✓';
                    this.style.background = '#95a5a6';
                    showNotification(`Te uniste al grupo: ${groupName}`);
                } else {
                    this.textContent = 'Unirse';
                    this.style.background = '';
                }
            });
        });
        
        // ============================================
        // ASISTIR A EVENTOS
        // ============================================
        const eventButtons = document.querySelectorAll('.btn-event');
        
        eventButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventTitle = this.closest('.event-item').querySelector('.event-title').textContent;
                
                if (this.textContent === 'Asistir') {
                    this.textContent = 'Asistiré ✓';
                    this.style.background = 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)';
                    showNotification(`Confirmaste tu asistencia a: ${eventTitle}`);
                } else {
                    this.textContent = 'Asistir';
                    this.style.background = '';
                }
            });
        });
        
        // ============================================
        // CARGAR MÁS PUBLICACIONES
        // ============================================
        const loadMoreBtn = document.querySelector('.btn-load-more');
        
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                this.textContent = 'Cargando...';
                this.disabled = true;
                
                // Simular carga de más publicaciones
                setTimeout(() => {
                    showNotification('Nuevas publicaciones cargadas');
                    this.textContent = 'Cargar más publicaciones';
                    this.disabled = false;
                }, 1500);
            });
        }
        
        // ============================================
        // MENÚ DE OPCIONES DE PUBLICACIÓN
        // ============================================
        const postMenus = document.querySelectorAll('.post-menu');
        
        postMenus.forEach(menu => {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
                showPostOptionsMenu(this);
            });
        });
        
        // ============================================
        // TAGS CLICKEABLES
        // ============================================
        const tags = document.querySelectorAll('.tag');
        
        tags.forEach(tag => {
            tag.addEventListener('click', function(e) {
                e.preventDefault();
                const tagName = this.textContent;
                showNotification(`Explorando ${tagName}`);
                // Aquí puedes agregar lógica para filtrar por hashtag
            });
        });
        
    });

    // ============================================
    // FUNCIONES AUXILIARES
    // ============================================

    function openPostModal() {
        showNotification('Función de crear publicación próximamente');
        // Aquí puedes abrir un modal para crear publicaciones
    }

    function openCommentModal(postCard) {
        showNotification('Función de comentarios próximamente');
        // Aquí puedes abrir un modal para comentar
    }

    function showShareOptions() {
        const options = confirm('¿Compartir esta publicación en tu perfil?');
        if (options) {
            showNotification('¡Publicación compartida!');
        }
    }

    function showPostOptionsMenu(button) {
        const menu = document.createElement('div');
        menu.className = 'post-options-menu';
        menu.innerHTML = `
            <div class="menu-option" onclick="showNotification('Publicación guardada')">💾 Guardar</div>
            <div class="menu-option" onclick="showNotification('Publicación ocultada')">🙈 Ocultar</div>
            <div class="menu-option" onclick="showNotification('Usuario reportado')">⚠️ Reportar</div>
        `;
        
        // Posicionar el menú
        const rect = button.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = rect.bottom + 'px';
        menu.style.right = (window.innerWidth - rect.right) + 'px';
        menu.style.background = 'white';
        menu.style.borderRadius = '8px';
        menu.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        menu.style.padding = '8px';
        menu.style.zIndex = '1000';
        
        document.body.appendChild(menu);
        
        // Cerrar al hacer click fuera
        setTimeout(() => {
            document.addEventListener('click', function closeMenu() {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            });
        }, 100);
    }

    function showLoadingAnimation() {
        const feed = document.querySelector('.posts-container');
        feed.style.opacity = '0.5';
        
        setTimeout(() => {
            feed.style.opacity = '1';
        }, 500);
    }

    function showNotification(message) {
        // Crear notificación
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
            z-index: 10000;
            animation: slideIn 0.3s ease;
            font-weight: 600;
        `;
        
        document.body.appendChild(notification);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Agregar animaciones CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .menu-option {
            padding: 10px 16px;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.3s ease;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .menu-option:hover {
            background: #f8f9fa;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>