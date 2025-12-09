<?php
/**
 * Modelo Reto
 * Gestión de retos ambientales
 */

// Solo cargar database.php si la función getDB no existe
if (!function_exists('getDB')) {
    require_once __DIR__ . '/../config/database.php';
}

class Reto {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Obtener todos los retos con filtros (para página retos.php)
     */
    public function getRetos($categoria = 'todos', $busqueda = '') {
        try {
            $sql = "SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                           c.color as categoria_color, c.slug as categoria_slug, c.id as categoria_id,
                           (SELECT COUNT(*) FROM participaciones WHERE reto_id = r.id) as participantes_actuales,
                           (SELECT COUNT(*) FROM reportes WHERE reto_id = r.id AND estado = 'aprobado') as reportes_aprobados,
                           ROUND((SELECT COUNT(*) FROM participaciones WHERE reto_id = r.id) / 
                           NULLIF(r.meta_participantes, 0) * 100, 0) as progreso
                    FROM retos r
                    INNER JOIN categorias c ON r.categoria_id = c.id
                    WHERE r.estado = 'activo'";
            
            $params = [];
            
            // Filtro por categoría
            if ($categoria !== 'todos' && is_numeric($categoria)) {
                $sql .= " AND r.categoria_id = :categoria";
                $params['categoria'] = $categoria;
            }
            
            // Filtro por búsqueda
            if (!empty($busqueda)) {
                $sql .= " AND (r.titulo LIKE :busqueda OR r.descripcion LIKE :busqueda)";
                $params['busqueda'] = "%{$busqueda}%";
            }
            
            $sql .= " ORDER BY r.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            error_log("Error en getRetos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todas las categorías
     */
    public function getCategorias() {
        try {
            $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            error_log("Error en getCategorias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todos los retos activos
     */
    public function getRetosActivos($filtros = []) {
        try {
            $sql = "SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                           c.color as categoria_color,
                           (SELECT COUNT(*) FROM participaciones WHERE reto_id = r.id) as participantes_actuales,
                           ROUND((SELECT COUNT(*) FROM participaciones WHERE reto_id = r.id) / 
                           NULLIF(r.meta_participantes, 0) * 100, 0) as progreso
                    FROM retos r
                    INNER JOIN categorias c ON r.categoria_id = c.id
                    WHERE r.estado = 'activo'";
            
            $params = [];
            
            // Filtrar por categoría
            if (isset($filtros['categoria']) && $filtros['categoria'] !== 'todos') {
                $sql .= " AND c.slug = :categoria";
                $params['categoria'] = $filtros['categoria'];
            }
            
            // Filtrar por comunidad
            if (isset($filtros['comunidad']) && !empty($filtros['comunidad'])) {
                $sql .= " AND (r.comunidad = :comunidad OR r.comunidad = 'Todas')";
                $params['comunidad'] = $filtros['comunidad'];
            }
            
            // Filtrar por duración
            if (isset($filtros['duracion'])) {
                $sql .= " AND r.duracion = :duracion";
                $params['duracion'] = $filtros['duracion'];
            }
            
            $sql .= " ORDER BY r.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            error_log("Error al obtener retos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un reto por ID
     */
    public function getRetoPorId($id) {
        try {
            $sql = "SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                           c.slug as categoria_slug, c.color as categoria_color, c.id as categoria_id,
                           (SELECT COUNT(*) FROM participaciones WHERE reto_id = r.id) as participantes_actuales,
                           (SELECT COUNT(*) FROM reportes WHERE reto_id = r.id AND estado = 'aprobado') as reportes_aprobados
                    FROM retos r
                    INNER JOIN categorias c ON r.categoria_id = c.id
                    WHERE r.id = :id
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
            
        } catch(PDOException $e) {
            error_log("Error al obtener reto: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Unirse a un reto
     */
    public function unirseAReto($usuario_id, $reto_id) {
        try {
            // Verificar si ya está participando
            if ($this->estaParticipando($usuario_id, $reto_id)) {
                return ['success' => false, 'message' => 'Ya estás participando en este reto'];
            }
            
            // Verificar si el reto existe y está activo
            $reto = $this->getRetoPorId($reto_id);
            if (!$reto || $reto['estado'] !== 'activo') {
                return ['success' => false, 'message' => 'El reto no está disponible'];
            }
            
            // Insertar participación
            $sql = "INSERT INTO participaciones (usuario_id, reto_id, fecha_union) 
                    VALUES (:usuario_id, :reto_id, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'reto_id' => $reto_id
            ]);
            
            // Crear notificación
            $this->crearNotificacion($usuario_id, 
                '¡Te uniste a un reto!', 
                'Ahora eres parte de: ' . $reto['titulo'],
                'reto',
                '/reto.php?id=' . $reto_id
            );
            
            return ['success' => true, 'message' => '¡Te has unido al reto exitosamente!'];
            
        } catch(PDOException $e) {
            error_log("Error al unirse al reto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al unirse al reto'];
        }
    }
    
    /**
     * Verificar si un usuario está participando en un reto
     */
    public function estaParticipando($usuario_id, $reto_id) {
        try {
            $sql = "SELECT COUNT(*) FROM participaciones 
                    WHERE usuario_id = :usuario_id AND reto_id = :reto_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'reto_id' => $reto_id
            ]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch(PDOException $e) {
            error_log("Error al verificar participación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener participación de usuario
     */
    public function getParticipacion($usuario_id, $reto_id) {
        try {
            $sql = "SELECT * FROM participaciones 
                    WHERE usuario_id = :usuario_id AND reto_id = :reto_id
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'reto_id' => $reto_id
            ]);
            
            return $stmt->fetch();
            
        } catch(PDOException $e) {
            error_log("Error al obtener participación: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener retos del usuario
     */
    public function getRetosUsuario($usuario_id, $estado = null) {
        try {
            $sql = "SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                           p.progreso, p.estado as estado_participacion, p.puntos_obtenidos,
                           p.fecha_union, p.fecha_completado
                    FROM participaciones p
                    INNER JOIN retos r ON p.reto_id = r.id
                    INNER JOIN categorias c ON r.categoria_id = c.id
                    WHERE p.usuario_id = :usuario_id";
            
            $params = ['usuario_id' => $usuario_id];
            
            if ($estado) {
                $sql .= " AND p.estado = :estado";
                $params['estado'] = $estado;
            }
            
            $sql .= " ORDER BY p.fecha_union DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            error_log("Error al obtener retos del usuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Subir reporte de acción
     */
    public function subirReporte($datos) {
        try {
            // Validar que esté participando
            if (!$this->estaParticipando($datos['usuario_id'], $datos['reto_id'])) {
                return ['success' => false, 'message' => 'Debes unirte al reto primero'];
            }
            
            // Obtener participación
            $participacion = $this->getParticipacion($datos['usuario_id'], $datos['reto_id']);
            
            // Insertar reporte
            $sql = "INSERT INTO reportes (participacion_id, usuario_id, reto_id, descripcion, 
                                         imagen_url, latitud, longitud, fecha_accion)
                    VALUES (:participacion_id, :usuario_id, :reto_id, :descripcion, 
                            :imagen_url, :latitud, :longitud, :fecha_accion)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'participacion_id' => $participacion['id'],
                'usuario_id' => $datos['usuario_id'],
                'reto_id' => $datos['reto_id'],
                'descripcion' => sanitize($datos['descripcion']),
                'imagen_url' => $datos['imagen_url'] ?? null,
                'latitud' => $datos['latitud'] ?? null,
                'longitud' => $datos['longitud'] ?? null,
                'fecha_accion' => $datos['fecha_accion'] ?? date('Y-m-d')
            ]);
            
            // Actualizar progreso de la participación
            $nuevo_progreso = min($participacion['progreso'] + 25, 100);
            $this->actualizarProgreso($participacion['id'], $nuevo_progreso);
            
            return ['success' => true, 'message' => 'Reporte enviado exitosamente. Pendiente de aprobación.'];
            
        } catch(PDOException $e) {
            error_log("Error al subir reporte: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al enviar el reporte'];
        }
    }
    
    /**
     * Obtener reportes de un reto
     */
    public function getReportesReto($reto_id, $limite = 10) {
        try {
            $sql = "SELECT r.*, u.nombre, u.apellido, u.avatar
                    FROM reportes r
                    INNER JOIN usuarios u ON r.usuario_id = u.id
                    WHERE r.reto_id = :reto_id AND r.estado = 'aprobado'
                    ORDER BY r.fecha_reporte DESC
                    LIMIT :limite";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('reto_id', $reto_id, PDO::PARAM_INT);
            $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            error_log("Error al obtener reportes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Completar reto
     */
    public function completarReto($participacion_id, $usuario_id, $reto_id) {
        try {
            $this->db->beginTransaction();
            
            // Obtener puntos del reto
            $reto = $this->getRetoPorId($reto_id);
            
            // Actualizar participación
            $sql = "UPDATE participaciones 
                    SET estado = 'completado', 
                        progreso = 100, 
                        puntos_obtenidos = :puntos,
                        fecha_completado = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'puntos' => $reto['puntos_recompensa'],
                'id' => $participacion_id
            ]);
            
            // Actualizar puntos del usuario
            $sql = "UPDATE usuarios SET puntos_totales = puntos_totales + :puntos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['puntos' => $reto['puntos_recompensa'], 'id' => $usuario_id]);
            
            // Registrar en historial
            $sql = "INSERT INTO puntos_historico (usuario_id, puntos, tipo, concepto, referencia_id, referencia_tipo)
                    VALUES (:usuario_id, :puntos, 'ganados', :concepto, :referencia_id, 'reto')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'puntos' => $reto['puntos_recompensa'],
                'concepto' => 'Reto completado: ' . $reto['titulo'],
                'referencia_id' => $reto_id
            ]);
            
            // Verificar logros
            $this->verificarLogros($usuario_id);
            
            // Crear notificación
            $this->crearNotificacion($usuario_id, 
                '🎉 ¡Reto Completado!', 
                'Has ganado ' . $reto['puntos_recompensa'] . ' puntos por completar: ' . $reto['titulo'],
                'reto',
                '/perfil.php'
            );
            
            $this->db->commit();
            return ['success' => true, 'message' => '¡Felicidades! Has completado el reto'];
            
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("Error al completar reto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al completar el reto'];
        }
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function actualizarProgreso($participacion_id, $progreso) {
        $sql = "UPDATE participaciones SET progreso = :progreso WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['progreso' => $progreso, 'id' => $participacion_id]);
        
        // Si llegó a 100%, completar automáticamente
        if ($progreso >= 100) {
            $participacion = $this->getParticipacionPorId($participacion_id);
            $this->completarReto($participacion_id, $participacion['usuario_id'], $participacion['reto_id']);
        }
    }
    
    private function getParticipacionPorId($id) {
        $sql = "SELECT * FROM participaciones WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    private function verificarLogros($usuario_id) {
        // Aquí implementarías la lógica para verificar y otorgar logros
        // Por ejemplo, verificar si completó su primer reto, 10 retos, etc.
    }
    
    private function crearNotificacion($usuario_id, $titulo, $mensaje, $tipo, $url = null) {
        try {
            $sql = "INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, url)
                    VALUES (:usuario_id, :titulo, :mensaje, :tipo, :url)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo,
                'url' => $url
            ]);
        } catch(PDOException $e) {
            // Solo registrar el error, no detener la ejecución
            error_log("Error al crear notificación: " . $e->getMessage());
        }
    }
}