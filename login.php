<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Si ya está logueado, redirigir al inicio
if (estaLogueado()) {
    header('Location: index.php');
    exit;
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'login') {
        $resultado = $auth->login($_POST['email'], $_POST['password']);
        if ($resultado['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $resultado['message'];
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
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 24px;
        }
        
        .auth-box {
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            max-width: 450px;
            width: 100%;
            padding: 48px;
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .auth-logo-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        .auth-logo h1 {
            font-size: 28px;
            color: var(--primary-green);
            margin-bottom: 8px;
        }
        
        .auth-logo p {
            color: var(--light-text);
            font-size: 14px;
        }
        
        .auth-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 32px;
            background: var(--bg-light);
            padding: 4px;
            border-radius: 12px;
        }
        
        .auth-tab {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .auth-tab.active {
            background: white;
            color: var(--primary-green);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 14px;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--bg-light);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--light-green);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .avatar-selector {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-top: 8px;
        }
        
        .avatar-option {
            font-size: 32px;
            padding: 8px;
            border: 2px solid var(--bg-light);
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .avatar-option:hover {
            border-color: var(--light-green);
            transform: scale(1.1);
        }
        
        .avatar-option.selected {
            border-color: var(--primary-green);
            background: var(--bg-light);
        }
        
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(74, 124, 89, 0.3);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 2px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 2px solid #cfc;
        }
        
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        
        .back-link a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
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