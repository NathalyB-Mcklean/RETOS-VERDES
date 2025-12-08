<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Si ya está logueado, redirigir según corresponda
if (estaLogueado()) {
    $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
    unset($_SESSION['redirect_after_login']);
    header("Location: $redirect");
    exit;
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'login') {
        $resultado = $auth->login($_POST['email'], $_POST['password']);
        if ($resultado['success']) {
            // Redirigir a la página solicitada o al inicio
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
            exit;
        }
    }
    
    if ($_POST['action'] === 'register') {
        $resultado = $auth->registrar($_POST);
        if ($resultado['success']) {
            $success = $resultado['message'] . ' Por favor inicia sesión.';
        } else {
            $error = $resultado['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Retos Verdes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.css">

</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <div class="auth-logo-icon">🌱</div>
                <h1>Retos Verdes</h1>
                <p>Transforma tu comunidad</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="auth-tabs">
                <button class="auth-tab active" onclick="cambiarTab('login')">Iniciar Sesión</button>
                <button class="auth-tab" onclick="cambiarTab('register')">Registrarse</button>
            </div>
            
            <!-- Formulario de Login -->
            <form class="auth-form active" id="form-login" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="submit-btn">Iniciar Sesión</button>
            </form>
            
            <!-- Formulario de Registro -->
            <form class="auth-form" id="form-register" method="POST">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="avatar" id="avatar-input" value="👤">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required placeholder="Juan">
                    </div>
                    
                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="apellido" required placeholder="Pérez">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label>Contraseña (mínimo 6 caracteres)</label>
                    <input type="password" name="password" required placeholder="••••••••" minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Comunidad</label>
                    <select name="comunidad" required>
                        <option value="">Selecciona tu comunidad</option>
                        <option value="Ciudad de Panamá">Ciudad de Panamá</option>
                        <option value="Colón">Colón</option>
                        <option value="David">David</option>
                        <option value="Santiago">Santiago</option>
                        <option value="Chitré">Chitré</option>
                        <option value="Penonomé">Penonomé</option>
                        <option value="Aguadulce">Aguadulce</option>
                        <option value="La Chorrera">La Chorrera</option>
                        <option value="Arraiján">Arraiján</option>
                        <option value="Boquete">Boquete</option>
                        <option value="Otra">Otra</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Teléfono (opcional)</label>
                    <input type="tel" name="telefono" placeholder="+507 6000-0000">
                </div>
                
                <div class="form-group">
                    <label>Elige tu Avatar</label>
                    <div class="avatar-selector">
                        <div class="avatar-option selected" onclick="seleccionarAvatar('👤', this)">👤</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('👨', this)">👨</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('👩', this)">👩</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('🧑', this)">🧑</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('👧', this)">👧</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('👦', this)">👦</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('🌱', this)">🌱</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('🌳', this)">🌳</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('🐦', this)">🐦</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('🦋', this)">🦋</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('🐝', this)">🐝</div>
                        <div class="avatar-option" onclick="seleccionarAvatar('🌺', this)">🌺</div>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Crear Cuenta</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">← Volver al inicio</a>
            </div>
        </div>
    </div>
    
    <script>
        function cambiarTab(tab) {
            // Actualizar tabs
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            // Actualizar formularios
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            document.getElementById('form-' + tab).classList.add('active');
        }
        
        function seleccionarAvatar(emoji, element) {
            document.querySelectorAll('.avatar-option').forEach(o => o.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('avatar-input').value = emoji;
        }
    </script>
</body>
</html>