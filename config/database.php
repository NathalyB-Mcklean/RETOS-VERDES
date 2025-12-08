<?php
/**
 * Configuración de Base de Datos
 * Retos Verdes Comunitarios - Panamá
 */

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'retos_verdes_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración del sitio
define('SITE_NAME', 'Retos Verdes Comunitarios');
define('SITE_URL', 'http://localhost/retos-verdes');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Configuración de seguridad
define('SESSION_LIFETIME', 86400); // 24 horas
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

/**
 * Clase Database - Manejo de conexión PDO
 */
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error al conectar con la base de datos. Por favor, contacta al administrador.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar singleton");
    }
}

/**
 * Función helper para obtener la conexión
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Función para sanitizar datos de entrada
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Función para validar email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Función para generar código aleatorio
 */
function generarCodigo($length = 8) {
    return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
}

/**
 * Función para formatear fecha
 */
function formatearFecha($fecha, $formato = 'd/m/Y') {
    return date($formato, strtotime($fecha));
}

/**
 * Función para calcular diferencia de días
 */
function diasRestantes($fecha_fin) {
    $hoy = new DateTime();
    $fin = new DateTime($fecha_fin);
    $diff = $hoy->diff($fin);
    return $diff->days;
}