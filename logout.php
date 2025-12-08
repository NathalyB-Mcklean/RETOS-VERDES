<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redirigir al index
header('Location: index.php');
exit();
?>