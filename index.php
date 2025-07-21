<?php
/**
 * Página de Login - Versão Corrigida
 * Sistema Veterinário
 */

// Iniciar sessão
session_start();

// Configurações inline para evitar problemas de include
define('SITE_NAME', 'Sistema Veterinário');
define('DB_HOST', 'localhost');
define('DB_NAME', 'u324919422_veterinario');
define('DB_USER', 'u324919422_vet_admin');
define('DB_PASS', 'Vydhal@112358');

// Função para verificar se está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Se já estiver logado, redireciona para dashboard
if (isLoggedIn()) {
    header("Location: views/dashboard.php");
    exit();
}

$error_message = '';
$success_message = '';

// Processar login
if ($_POST && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT u.*, c.nome_clinica FROM usuarios u LEFT JOIN clinicas c ON u.id_clinica = c.id_clinica WHERE u.email = ? AND u.ativo = 1");
            $stmt->execute([$email]);
            
            if ($user = $stmt->fetch()) {
                // Verificar senha (MD5 para compatibilidade com dados de exemplo)
                if (md5($password) === $user['senha']) {
                    $_SESSION['user_id'] = $user['id_usuario'];
                    $_SESSION['user_name'] = $user['nome_usuario'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['tipo_usuario'];
                    $_SESSION['clinic_id'] = $user['id_clinica'];
                    $_SESSION['clinic_name'] = $user['nome_clinica'];
                    $_SESSION['login_time'] = time();
                    
                    header("Location: views/dashboard.php");
                    exit();
                } else {
                    $error_message = "Senha incorreta.";
                }
            } else {
                $error_message = "Usuário não encontrado.";
            }
        } catch(PDOException $e) {
            $error_message = "Erro de conexão: " . $e->getMessage();
        }
    }
}

// Verificar mensagens da URL
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'login_required':
            $error_message = "Você precisa fazer login para acessar esta página.";
            break;
    }
}

if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logout_success':
            $success_message = "Logout realizado com sucesso.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="login-header">
                        <i class="fas fa-paw fa-3x mb-3"></i>
                        <h2><?php echo SITE_NAME; ?></h2>
                        <p class="mb-0">Faça login para acessar o sistema</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Senha
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                                </button>
                            </div>
                        </form>
                        
                        <!-- Credenciais de demonstração -->
                        <div class="demo-credentials">
                            <h6><i class="fas fa-info-circle me-2"></i>Credenciais de Teste:</h6>
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <strong>Superadmin:</strong><br>
                                    Email: superadmin@sistema.com<br>
                                    Senha: 123456
                                </div>
                                <div class="col-12 mb-2">
                                    <strong>Admin:</strong><br>
                                    Email: admin1@petcare.com.br<br>
                                    Senha: 123456
                                </div>
                                <div class="col-12">
                                    <strong>Veterinário:</strong><br>
                                    Email: vet1@petcare.com.br<br>
                                    Senha: 123456
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>