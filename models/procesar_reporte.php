<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/models/Reto.php';

// Verificar que el usuario está logueado
if (!estaLogueado()) {
    header('Location: login.php');
    exit;
}

$usuario = getUsuario();
$retoModel = new Reto();

// Procesar formulario de reporte
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos
    if (!isset($_POST['reto_id']) || empty($_POST['reto_id'])) {
        $error = 'ID del reto no válido';
    } elseif (!isset($_POST['descripcion']) || empty(trim($_POST['descripcion']))) {
        $error = 'La descripción es requerida';
    } else {
        $reto_id = intval($_POST['reto_id']);
        $descripcion = trim($_POST['descripcion']);
        
        // Verificar que el usuario participa en el reto
        $ya_participa = $retoModel->estaParticipando($usuario['id'], $reto_id);
        
        if (!$ya_participa) {
            $error = 'Debes estar participando en el reto para reportar progreso';
        } else {
            // Procesar imagen
            $imagen_url = null;
            
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                // Validar tipo de imagen
                $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $tipo_archivo = mime_content_type($_FILES['imagen']['tmp_name']);
                
                if (!in_array($tipo_archivo, $tipos_permitidos)) {
                    $error = 'Solo se permiten imágenes JPEG, PNG o GIF';
                } else {
                    // Crear directorio si no existe
                    $upload_dir = __DIR__ . '/uploads/reportes/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Generar nombre único
                    $nombre_archivo = uniqid('reporte_') . '_' . time() . '.' . pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $ruta_completa = $upload_dir . $nombre_archivo;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
                        // Guardar ruta relativa
                        $imagen_url = 'uploads/reportes/' . $nombre_archivo;
                    } else {
                        $error = 'Error al subir la imagen';
                    }
                }
            }
            
            if (!$error) {
                // Obtener participación
                $participacion = $retoModel->getParticipacion($usuario['id'], $reto_id);
                
                if ($participacion) {
                    // Insertar reporte en la base de datos
                    $resultado = $retoModel->crearReporte([
                        'participacion_id' => $participacion['id'],
                        'usuario_id' => $usuario['id'],
                        'reto_id' => $reto_id,
                        'descripcion' => $descripcion,
                        'imagen_url' => $imagen_url,
                        'fecha_accion' => date('Y-m-d')
                    ]);
                    
                    if ($resultado) {
                        // Actualizar progreso de la participación - CAMBIADO A 20% (5 tareas)
                        $nuevo_progreso = min(100, ($participacion['progreso'] ?? 0) + 20);
                        $retoModel->actualizarProgresoParticipacion($participacion['id'], $nuevo_progreso);
                        
                        // Log para debug
                        error_log("Reporte enviado: Usuario {$usuario['id']}, Reto {$reto_id}, Progreso anterior: {$participacion['progreso']}, Nuevo progreso: {$nuevo_progreso}");
                        
                        // Verificar si el reto está completado
                        if ($nuevo_progreso >= 100) {
                            $retoModel->completarParticipacion($participacion['id'], $reto_id, $usuario['id']);
                        }
                        
                        $mensaje = 'success';
                    } else {
                        $error = 'Error al guardar el reporte';
                    }
                } else {
                    $error = 'No se encontró tu participación en este reto';
                }
            }
        }
    }
}

// Redirigir de vuelta al reto
if ($error) {
    $_SESSION['error_reporte'] = $error;
} elseif ($mensaje === 'success') {
    $_SESSION['success_reporte'] = '¡Reporte enviado exitosamente! Se ha actualizado tu progreso.';
}

$reto_id = isset($_POST['reto_id']) ? $_POST['reto_id'] : (isset($_GET['id']) ? $_GET['id'] : '');
header('Location: reto.php?id=' . $reto_id);
exit;
?>