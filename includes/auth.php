<?php
/**
 * Sistema de Autenticación
 * Gestión de sesiones y usuarios
 */

require_once __DIR__ . '/../config/database.php';

// Configurar sesión segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Clase Auth - Gestión de autenticación
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function registrar($datos) {
        try {
            // Validar datos
            if (!$this->validarDatosRegistro($datos)) {
                return ['success' => false, 'message' => 'Datos inválidos'];
            }
            
            // Verificar si el email ya existe
            if ($this->emailExiste($datos['email'])) {
                return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
            }
            
            // Encriptar contraseña
            $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            // Insertar usuario
            $sql = "INSERT INTO usuarios (nombre, apellido, email, password, comunidad, telefono, avatar) 
                    VALUES (:nombre, :apellido, :email, :password, :comunidad, :telefono, :avatar)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'nombre' => sanitize($datos['nombre']),
                'apellido' => sanitize($datos['apellido']),
                'email' => sanitize($datos['email']),
                'password' => $password_hash,
                'comunidad' => sanitize($datos['comunidad'] ?? ''),
                'telefono' => sanitize($datos['telefono'] ?? ''),
                'avatar' => $datos['avatar'] ?? '👤'
            ]);
            
            return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
            
        } catch(PDOException $e) {
            error_log("Error en registro: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar usuario'];
        }
    }
    
    /**
     * Iniciar sesión
     */
    public function login($email, $password) {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = :email AND estado = 'activo' LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => sanitize($email)]);
            
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                // Actualizar último acceso
                $this->actualizarUltimoAcceso($usuario['id']);
                
                // Establecer sesión
                $this->establecerSesion($usuario);
                
                return ['success' => true, 'message' => 'Inicio de sesión exitoso'];
            }
            
            return ['success' => false, 'message' => 'Credenciales incorrectas'];
            
        } catch(PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al iniciar sesión'];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Verificar si usuario está autenticado
     */
    public static function verificarAutenticacion() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Requerir autenticación (redirigir si no está logueado)
     */
    public static function requerirAuth() {
        if (!self::verificarAutenticacion()) {
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
    
    /**
     * Obtener datos del usuario actual
     */
    public static function getUsuarioActual() {
        if (!self::verificarAutenticacion()) {
            return null;
        }
        
        try {
            $db = getDB();
            $sql = "SELECT id, nombre, apellido, email, avatar, comunidad, puntos_totales, nivel 
                    FROM usuarios WHERE id = :id AND estado = 'activo' LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $_SESSION['user_id']]);
            
            return $stmt->fetch();
            
        } catch(PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar perfil de usuario
     */
    public function actualizarPerfil($usuario_id, $datos) {
        try {
            $campos = [];
            $params = ['id' => $usuario_id];
            
            if (isset($datos['nombre'])) {
                $campos[] = "nombre = :nombre";
                $params['nombre'] = sanitize($datos['nombre']);
            }
            if (isset($datos['apellido'])) {
                $campos[] = "apellido = :apellido";
                $params['apellido'] = sanitize($datos['apellido']);
            }
            if (isset($datos['comunidad'])) {
                $campos[] = "comunidad = :comunidad";
                $params['comunidad'] = sanitize($datos['comunidad']);
            }
            if (isset($datos['telefono'])) {
                $campos[] = "telefono = :telefono";
                $params['telefono'] = sanitize($datos['telefono']);
            }
            if (isset($datos['avatar'])) {
                $campos[] = "avatar = :avatar";
                $params['avatar'] = $datos['avatar'];
            }
            
            if (empty($campos)) {
                return ['success' => false, 'message' => 'No hay datos para actualizar'];
            }
            
            $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true, 'message' => 'Perfil actualizado exitosamente'];
            
        } catch(PDOException $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar perfil'];
        }
    }
    
    /**
     * Cambiar contraseña
     */
    public function cambiarPassword($usuario_id, $password_actual, $password_nueva) {
        try {
            // Verificar contraseña actual
            $sql = "SELECT password FROM usuarios WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $usuario_id]);
            $usuario = $stmt->fetch();
            
            if (!password_verify($password_actual, $usuario['password'])) {
                return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
            }
            
            // Actualizar contraseña
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET password = :password WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['password' => $password_hash, 'id' => $usuario_id]);
            
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
            
        } catch(PDOException $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar contraseña'];
        }
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function validarDatosRegistro($datos) {
        return !empty($datos['nombre']) && 
               !empty($datos['apellido']) && 
               !empty($datos['email']) && 
               !empty($datos['password']) &&
               validarEmail($datos['email']) &&
               strlen($datos['password']) >= 6;
    }
    
    private function emailExiste($email) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => sanitize($email)]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function establecerSesion($usuario) {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nombre'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_avatar'] = $usuario['avatar'];
        $_SESSION['user_points'] = $usuario['puntos_totales'];
        $_SESSION['user_level'] = $usuario['nivel'];
        $_SESSION['login_time'] = time();
    }
    
    private function actualizarUltimoAcceso($usuario_id) {
        $sql = "UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $usuario_id]);
    }
}

/**
 * Funciones helper para verificación rápida
 */
function estaLogueado() {
    return Auth::verificarAutenticacion();
}

function getUsuario() {
    return Auth::getUsuarioActual();
}

function requiereLogin() {
    Auth::requerirAuth();
}