<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'models/Reto.php';

// Verificar autenticación
requiereLogin();

$retoModel = new Reto();
$usuario = getUsuario();
$db = getDB();

// Obtener ID del reto
$reto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$reto_id) {
    header('Location: mis-retos.php');
    exit;
}

// Verificar que el usuario esté participando
if (!$retoModel->estaParticipando($usuario['id'], $reto_id)) {
    header('Location: reto.php?id=' . $reto_id);
    exit;
}

// Obtener datos del reto y participación
$reto = $retoModel->getRetoPorId($reto_id);
$participacion = $retoModel->getParticipacion($usuario['id'], $reto_id);

// Obtener reportes del usuario en este reto
$stmt = $db->prepare("
    SELECT * FROM reportes 
    WHERE usuario_id = :usuario_id AND reto_id = :reto_id 
    ORDER BY fecha_reporte DESC
");
$stmt->execute(['usuario_id' => $usuario['id'], 'reto_id' => $reto_id]);
$mis_reportes = $stmt->fetchAll();

// Definir pasos del reto según su categoría
$pasos = definirPasosReto($reto, $participacion, $mis_reportes);

// Calcular días activo en el reto
$fecha_union = new DateTime($participacion['fecha_union']);
$hoy = new DateTime();
$dias_activo = $fecha_union->diff($hoy)->days;

/**
 * Define los pasos necesarios para completar el reto
 */
function definirPasosReto($reto, $participacion, $reportes) {
    $pasos_base = [];
    
    // Paso 1: Siempre es unirse al reto
    $pasos_base[] = [
        'numero' => 1,
        'titulo' => 'Unirse al Reto',
        'descripcion' => 'Te has unido exitosamente a este reto',
        'completado' => true,
        'fecha_completado' => $participacion['fecha_union'],
        'puntos' => 10,
        'icono' => '✅'
    ];
    
    // Pasos según categoría del reto
    switch ($reto['categoria_slug']) {
        case 'arboles':
            $pasos_base[] = [
                'numero' => 2,
                'titulo' => 'Conseguir Semillas o Plántulas',
                'descripcion' => 'Obtén semillas de árboles nativos o plántulas de un vivero',
                'completado' => count($reportes) >= 1,
                'puntos' => 20,
                'icono' => '🌱',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=2'
            ];
            $pasos_base[] = [
                'numero' => 3,
                'titulo' => 'Preparar el Terreno',
                'descripcion' => 'Identifica y prepara el lugar donde plantarás',
                'completado' => count($reportes) >= 2,
                'puntos' => 20,
                'icono' => '🏞️',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=3'
            ];
            $pasos_base[] = [
                'numero' => 4,
                'titulo' => 'Plantar el Árbol',
                'descripcion' => 'Planta el árbol y documenta con fotos',
                'completado' => count($reportes) >= 3,
                'puntos' => 30,
                'icono' => '🌳',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=4'
            ];
            $pasos_base[] = [
                'numero' => 5,
                'titulo' => 'Seguimiento (30 días)',
                'descripcion' => 'Documenta el crecimiento del árbol después de 30 días',
                'completado' => count($reportes) >= 4,
                'puntos' => 20,
                'icono' => '📊',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=5'
            ];
            break;
            
        case 'agua':
            $pasos_base[] = [
                'numero' => 2,
                'titulo' => 'Inspección del Área',
                'descripcion' => 'Documenta el estado actual del río o quebrada',
                'completado' => count($reportes) >= 1,
                'puntos' => 25,
                'icono' => '📸',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=2'
            ];
            $pasos_base[] = [
                'numero' => 3,
                'titulo' => 'Organizar Limpieza',
                'descripcion' => 'Reúne un grupo y materiales (bolsas, guantes)',
                'completado' => count($reportes) >= 2,
                'puntos' => 25,
                'icono' => '👥',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=3'
            ];
            $pasos_base[] = [
                'numero' => 4,
                'titulo' => 'Realizar Limpieza',
                'descripcion' => 'Limpia el área y documenta la cantidad recolectada',
                'completado' => count($reportes) >= 3,
                'puntos' => 50,
                'icono' => '♻️',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=4'
            ];
            $pasos_base[] = [
                'numero' => 5,
                'titulo' => 'Comparativa Final',
                'descripcion' => 'Muestra fotos antes y después',
                'completado' => count($reportes) >= 4,
                'puntos' => 50,
                'icono' => '🎯',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=5'
            ];
            break;
            
        case 'fauna':
            $pasos_base[] = [
                'numero' => 2,
                'titulo' => 'Investigar Especies Locales',
                'descripcion' => 'Identifica qué polinizadores hay en tu zona',
                'completado' => count($reportes) >= 1,
                'puntos' => 20,
                'icono' => '🔍',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=2'
            ];
            $pasos_base[] = [
                'numero' => 3,
                'titulo' => 'Plantar Flores Nativas',
                'descripcion' => 'Planta al menos 5 especies de flores que atraen polinizadores',
                'completado' => count($reportes) >= 2,
                'puntos' => 30,
                'icono' => '🌺',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=3'
            ];
            $pasos_base[] = [
                'numero' => 4,
                'titulo' => 'Instalar Refugios',
                'descripcion' => 'Crea casitas para abejas o bebederos para colibríes',
                'completado' => count($reportes) >= 3,
                'puntos' => 30,
                'icono' => '🏠',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=4'
            ];
            break;
            
        case 'residuos':
            $pasos_base[] = [
                'numero' => 2,
                'titulo' => 'Auditoría de Plásticos',
                'descripcion' => 'Identifica todos los plásticos de un solo uso que usas',
                'completado' => count($reportes) >= 1,
                'puntos' => 15,
                'icono' => '📋',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=2'
            ];
            $pasos_base[] = [
                'numero' => 3,
                'titulo' => 'Buscar Alternativas',
                'descripcion' => 'Consigue alternativas reutilizables (bolsas, botellas, etc)',
                'completado' => count($reportes) >= 2,
                'puntos' => 20,
                'icono' => '🛍️',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=3'
            ];
            $pasos_base[] = [
                'numero' => 4,
                'titulo' => 'Semana Sin Plástico',
                'descripcion' => 'Documenta 7 días sin usar plásticos desechables',
                'completado' => count($reportes) >= 3,
                'puntos' => 40,
                'icono' => '🎯',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=4'
            ];
            $pasos_base[] = [
                'numero' => 5,
                'titulo' => 'Inspirar a Otros',
                'descripcion' => 'Comparte tu experiencia y motiva a 3 personas más',
                'completado' => count($reportes) >= 4,
                'puntos' => 25,
                'icono' => '📣',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=5'
            ];
            break;
            
        case 'educacion':
            $pasos_base[] = [
                'numero' => 2,
                'titulo' => 'Preparar Contenido',
                'descripcion' => 'Crea material educativo sobre el tema ambiental',
                'completado' => count($reportes) >= 1,
                'puntos' => 30,
                'icono' => '📚',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=2'
            ];
            $pasos_base[] = [
                'numero' => 3,
                'titulo' => 'Impartir Taller',
                'descripcion' => 'Da un taller o charla a al menos 10 personas',
                'completado' => count($reportes) >= 2,
                'puntos' => 60,
                'icono' => '🎓',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=3'
            ];
            $pasos_base[] = [
                'numero' => 4,
                'titulo' => 'Evaluación y Feedback',
                'descripcion' => 'Recopila feedback de los participantes',
                'completado' => count($reportes) >= 3,
                'puntos' => 30,
                'icono' => '📝',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=4'
            ];
            break;
            
        default:
            // Pasos genéricos para otros retos
            $pasos_base[] = [
                'numero' => 2,
                'titulo' => 'Planificar Acción',
                'descripcion' => 'Planifica cómo vas a completar este reto',
                'completado' => count($reportes) >= 1,
                'puntos' => 20,
                'icono' => '📝',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=2'
            ];
            $pasos_base[] = [
                'numero' => 3,
                'titulo' => 'Ejecutar Acción',
                'descripcion' => 'Realiza la acción ambiental y documéntala',
                'completado' => count($reportes) >= 2,
                'puntos' => 50,
                'icono' => '✨',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=3'
            ];
            $pasos_base[] = [
                'numero' => 4,
                'titulo' => 'Compartir Resultados',
                'descripcion' => 'Comparte tu experiencia con la comunidad',
                'completado' => count($reportes) >= 3,
                'puntos' => 30,
                'icono' => '📢',
                'accion' => 'subir-reporte.php?id=' . $reto['id'] . '&paso=4'
            ];
    }
    
    return $pasos_base;
}

// Calcular progreso basado en pasos completados
$pasos_completados = count(array_filter($pasos, fn($p) => $p['completado']));
$total_pasos = count($pasos);
$porcentaje_progreso = round(($pasos_completados / $total_pasos) * 100);

// Calcular puntos ganados hasta ahora
$puntos_ganados = array_sum(array_map(fn($p) => $p['completado'] ? $p['puntos'] : 0, $pasos));
$puntos_totales = array_sum(array_map(fn($p) => $p['puntos'], $pasos));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Progreso - <?php echo $reto['titulo']; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .detalle-hero {
            background: linear-gradient(135deg, <?php echo $reto['categoria_color']; ?>22 0%, <?php echo $reto['categoria_color']; ?>11 100%);
            padding: 32px 0;
            margin-top: 72px;
        }
        
        .progreso-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-md);
            margin-bottom: 32px;
        }
        
        .progreso-top {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .progreso-icon {
            font-size: 64px;
            width: 100px;
            height: 100px;
            background: <?php echo $reto['categoria_color']; ?>22;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .progreso-info h1 {
            font-size: 28px;
            margin-bottom: 8px;
            color: var(--dark-text);
        }
        
        .progreso-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .progreso-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            padding: 24px;
            background: var(--bg-light);
            border-radius: 12px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            display: block;
            font-size: 32px;
            font-weight: 700;
            color: <?php echo $reto['categoria_color']; ?>;
        }
        
        .stat-label {
            font-size: 13px;
            color: var(--light-text);
        }
        
        .progreso-bar-container {
            margin-top: 24px;
        }
        
        .progreso-bar-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .progreso-bar-label {
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .progreso-bar-percent {
            font-weight: 700;
            color: <?php echo $reto['categoria_color']; ?>;
            font-size: 18px;
        }
        
        .progreso-bar {
            height: 16px;
            background: var(--bg-light);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .progreso-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, <?php echo $reto['categoria_color']; ?> 0%, <?php echo $reto['categoria_color']; ?>aa 100%);
            width: <?php echo $porcentaje_progreso; ?>%;
            transition: width 1s ease;
            position: relative;
            overflow: hidden;
        }
        
        .progreso-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .pasos-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-sm);
        }
        
        .pasos-titulo {
            font-size: 24px;
            margin-bottom: 24px;
            color: var(--dark-text);
        }
        
        .paso-item {
            display: flex;
            gap: 20px;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 16px;
            background: var(--bg-light);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .paso-item:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-sm);
        }
        
        .paso-item.completado {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
        }
        
        .paso-item.activo {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            box-shadow: 0 0 0 3px rgba(255,193,7,0.2);
        }
        
        .paso-numero {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--light-text);
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }
        
        .paso-item.completado .paso-numero {
            background: #28a745;
            color: white;
        }
        
        .paso-item.activo .paso-numero {
            background: #ffc107;
            color: #856404;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .paso-contenido {
            flex: 1;
        }
        
        .paso-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .paso-icono {
            font-size: 28px;
        }
        
        .paso-titulo {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark-text);
        }
        
        .paso-item.completado .paso-titulo {
            color: #28a745;
        }
        
        .paso-descripcion {
            color: var(--light-text);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        
        .paso-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .paso-puntos {
            font-size: 14px;
            font-weight: 600;
            color: #ffc107;
        }
        
        .paso-accion {
            padding: 10px 20px;
            background: <?php echo $reto['categoria_color']; ?>;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .paso-accion:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .paso-item.completado .paso-accion {
            background: #28a745;
        }
        
        .paso-check {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: 32px;
        }
        
        .reportes-seccion {
            margin-top: 32px;
            background: white;
            border-radius: var(--border-radius);
            padding: 32px;
            box-shadow: var(--shadow-sm);
        }
        
        .reportes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        
        .reporte-mini {
            border-radius: 12px;
            overflow: hidden;
            background: var(--bg-light);
            transition: transform 0.3s ease;
        }
        
        .reporte-mini:hover {
            transform: scale(1.05);
        }
        
        .reporte-mini-img {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, #e0e0e0, #f5f5f5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }
        
        .reporte-mini-info {
            padding: 12px;
        }
        
        .reporte-mini-fecha {
            font-size: 12px;
            color: var(--light-text);
        }
        
        .reporte-mini-estado {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 4px;
        }
        
        .estado-pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .estado-aprobado {
            background: #d4edda;
            color: #155724;
        }
        
        .estado-rechazado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-motivacion {
            background: linear-gradient(135deg, #4a7c59 0%, #6fa182 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-top: 24px;
            text-align: center;
        }
        
        .alert-motivacion h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .reto-completado-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 32px;
            border-radius: var(--border-radius);
            text-align: center;
            margin-bottom: 32px;
            box-shadow: var(--shadow-lg);
        }
        
        .reto-completado-banner h2 {
            font-size: 32px;
            margin-bottom: 16px;
        }
        
        .reto-completado-banner .puntos-ganados {
            font-size: 48px;
            font-weight: 700;
            margin: 16px 0;
        }
        
        @media (max-width: 768px) {
            .progreso-top {
                flex-direction: column;
                text-align: center;
            }
            
            .progreso-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .paso-item {
                flex-direction: column;
            }
            
            .reportes-grid {
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
                    <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
                        <span class="logo-icon">🌱</span>
                        <h1>RETOS VERDES</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">Inicio</a>
                    <a href="mis-retos.php" class="nav-link active">Mis Retos</a>
                </nav>
                <div class="header-actions">
                    <div class="user-points">
                        <span class="points-icon">⭐</span>
                        <span class="points-value"><?php echo number_format($usuario['puntos_totales']); ?></span>
                    </div>
                    <a href="perfil.php" class="user-avatar">
                        <span><?php echo $usuario['avatar']; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="detalle-hero">
        <div class="container">
            <?php if ($participacion['estado'] === 'completado'): ?>
            <div class="reto-completado-banner">
                <h2>🎉 ¡Felicidades! Reto Completado</h2>
                <p>Has completado exitosamente este reto ambiental</p>
                <div class="puntos-ganados">+<?php echo $reto['puntos_recompensa']; ?> puntos</div>
                <p style="opacity: 0.9;">Completado el <?php echo formatearFecha($participacion['fecha_completado'], 'd/m/Y'); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="progreso-header">
                <div class="progreso-top">
                    <div class="progreso-icon">
                        <?php echo $reto['categoria_icono']; ?>
                    </div>
                    <div class="progreso-info">
                        <h1><?php echo $reto['titulo']; ?></h1>
                        <div class="progreso-meta">
                            <span class="meta-item">
                                📁 <?php echo $reto['categoria_nombre']; ?>
                            </span>
                            <span class="meta-item">
                                📅 Te uniste hace <?php echo $dias_activo; ?> días
                            </span>
                            <span class="meta-item">
                                <?php if ($participacion['estado'] === 'completado'): ?>
                                    ✅ Completado
                                <?php elseif ($participacion['estado'] === 'en_progreso'): ?>
                                    ⏳ En progreso
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="progreso-stats">
                    <div class="stat-item">
                        <span class="stat-label">Puntos</span>
                    </div>
                </div>
                
                <div class="progreso-bar-container">
                    <div class="progreso-bar-header">
                        <span class="progreso-bar-label">Tu Progreso</span>
                        <span class="progreso-bar-percent"><?php echo $porcentaje_progreso; ?>%</span>
                    </div>
                    <div class="progreso-bar">
                        <div class="progreso-bar-fill"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pasos del Reto -->
    <section style="padding: 48px 0;">
        <div class="container">
            <div class="pasos-container">
                <h2 class="pasos-titulo">📋 Pasos para Completar el Reto</h2>
                
                <?php foreach ($pasos as $index => $paso): ?>
                    <?php 
                    $es_siguiente = !$paso['completado'] && ($index === 0 || $pasos[$index-1]['completado']);
                    $clase_paso = '';
                    if ($paso['completado']) {
                        $clase_paso = 'completado';
                    } elseif ($es_siguiente) {
                        $clase_paso = 'activo';
                    }
                    ?>
                    <div class="paso-item <?php echo $clase_paso; ?>">
                        <div class="paso-numero">
                            <?php echo $paso['completado'] ? '✓' : $paso['numero']; ?>
                        </div>
                        
                        <div class="paso-contenido">
                            <div class="paso-header">
                                <span class="paso-icono"><?php echo $paso['icono']; ?></span>
                                <h3 class="paso-titulo"><?php echo $paso['titulo']; ?></h3>
                            </div>
                            
                            <p class="paso-descripcion"><?php echo $paso['descripcion']; ?></p>
                            
                            <div class="paso-footer">
                                <span class="paso-puntos">⭐ +<?php echo $paso['puntos']; ?> puntos</span>
                                
                                <?php if ($paso['completado']): ?>
                                    <span style="color: #28a745; font-weight: 600; font-size: 14px;">
                                        ✓ Completado
                                        <?php if (isset($paso['fecha_completado'])): ?>
                                            - <?php echo formatearFecha($paso['fecha_completado'], 'd/m/Y'); ?>
                                        <?php endif; ?>
                                    </span>
                                <?php elseif ($es_siguiente): ?>
                                    <a href="<?php echo $paso['accion']; ?>" class="paso-accion">
                                        Continuar →
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--light-text); font-size: 14px;">
                                        🔒 Completa los pasos anteriores
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($paso['completado']): ?>
                            <span class="paso-check">✅</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($participacion['estado'] !== 'completado'): ?>
                    <div class="alert-motivacion">
                        <h3>💪 ¡Sigue Adelante!</h3>
                        <p>Completa todos los pasos para ganar <?php echo $reto['puntos_recompensa']; ?> puntos y hacer la diferencia en tu comunidad</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mis Reportes -->
            <?php if (count($mis_reportes) > 0): ?>
            <div class="reportes-seccion">
                <h2 style="font-size: 24px; margin-bottom: 16px;">📸 Tus Reportes</h2>
                <p style="color: var(--light-text); margin-bottom: 24px;">
                    Has enviado <?php echo count($mis_reportes); ?> reporte(s) en este reto
                </p>
                
                <div class="reportes-grid">
                    <?php foreach ($mis_reportes as $reporte): ?>
                    <div class="reporte-mini">
                        <div class="reporte-mini-img">
                            <?php if ($reporte['imagen_url']): ?>
                                <img src="<?php echo $reporte['imagen_url']; ?>" alt="Reporte" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                📷
                            <?php endif; ?>
                        </div>
                        <div class="reporte-mini-info">
                            <div class="reporte-mini-fecha">
                                <?php echo formatearFecha($reporte['fecha_reporte'], 'd/m/Y'); ?>
                            </div>
                            <span class="reporte-mini-estado estado-<?php echo $reporte['estado']; ?>">
                                <?php 
                                $estados = [
                                    'pendiente' => '⏳ Pendiente',
                                    'aprobado' => '✅ Aprobado',
                                    'rechazado' => '❌ Rechazado'
                                ];
                                echo $estados[$reporte['estado']];
                                ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Botones de Acción -->
            <div style="display: flex; gap: 16px; margin-top: 32px; justify-content: center;">
                <a href="mis-retos.php" class="btn btn-challenge" style="padding: 14px 28px;">
                    ← Volver a Mis Retos
                </a>
                
                <?php if ($participacion['estado'] !== 'completado'): ?>
                    <?php 
                    // Encontrar el siguiente paso activo
                    $siguiente_paso = null;
                    foreach ($pasos as $index => $paso) {
                        if (!$paso['completado'] && ($index === 0 || $pasos[$index-1]['completado'])) {
                            $siguiente_paso = $paso;
                            break;
                        }
                    }
                    ?>
                    <?php if ($siguiente_paso && isset($siguiente_paso['accion'])): ?>
                    <a href="<?php echo $siguiente_paso['accion']; ?>" class="btn btn-primary" style="padding: 14px 28px;">
                        Continuar Reto →
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="index.php" class="btn btn-primary" style="padding: 14px 28px;">
                        Explorar Más Retos
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 Retos Verdes Comunitarios - Panamá 🇵🇦</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Animación de la barra de progreso al cargar
        window.addEventListener('load', function() {
            setTimeout(() => {
                const fill = document.querySelector('.progreso-bar-fill');
                if (fill) {
                    fill.style.width = '<?php echo $porcentaje_progreso; ?>%';
                }
            }, 100);
        });
        
        // Confetti si el reto está completado
        <?php if ($participacion['estado'] === 'completado'): ?>
        function lanzarConfetti() {
            // Aquí podrías integrar una librería de confetti
            console.log('🎉 ¡Reto completado!');
        }
        lanzarConfetti();
        <?php endif; ?>
    </script>
</body>
</html>-value"><?php echo $porcentaje_progreso; ?>%</span>
                        <span class="stat-label">Completado</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $pasos_completados; ?>/<?php echo $total_pasos; ?></span>
                        <span class="stat-label">Pasos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo count($mis_reportes); ?></span>
                        <span class="stat-label">Reportes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $puntos_ganados; ?>/<?php echo $puntos_totales; ?></span>
                        <span class="stat