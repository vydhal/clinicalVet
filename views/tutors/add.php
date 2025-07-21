<?php
/**
 * Cadastro de Tutor
 * Sistema Veterinário
 */

// Iniciar sessão
session_start();

// Configurações inline
define('SITE_NAME', 'Sistema Veterinário');
define('DB_HOST', 'localhost');
define('DB_NAME', 'u324919422_veterinario');
define('DB_USER', 'u324919422_vet_admin');
define('DB_PASS', 'Vydhal@112358');

// Função para verificar se está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Verificar autenticação
if (!isLoggedIn()) {
    header("Location: ../../index.php?error=login_required");
    exit();
}

// Obter dados do usuário logado
$current_user = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'type' => $_SESSION['user_type'],
    'clinic_id' => $_SESSION['clinic_id'] ?? null,
    'clinic_name' => $_SESSION['clinic_name'] ?? null
];

$error_message = '';
$success_message = '';

// Processar formulário
if ($_POST && isset($_POST['save_tutor'])) {
    $nome_tutor = trim($_POST['nome_tutor']);
    $telefone_tutor = trim($_POST['telefone_tutor']);
    $email_tutor = trim($_POST['email_tutor']);
    $cpf_tutor = trim($_POST['cpf_tutor']);
    $endereco = trim($_POST['endereco']);
    
    if (empty($nome_tutor)) {
        $error_message = "O nome do tutor é obrigatório.";
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar se CPF já existe (se fornecido)
            if (!empty($cpf_tutor)) {
                $stmt = $pdo->prepare("SELECT id_tutor FROM tutores WHERE cpf_tutor = ? AND id_clinica = ?");
                $stmt->execute([$cpf_tutor, $current_user['clinic_id']]);
                if ($stmt->fetch()) {
                    $error_message = "Já existe um tutor cadastrado com este CPF.";
                }
            }
            
            // Verificar se email já existe (se fornecido)
            if (empty($error_message) && !empty($email_tutor)) {
                $stmt = $pdo->prepare("SELECT id_tutor FROM tutores WHERE email_tutor = ? AND id_clinica = ?");
                $stmt->execute([$email_tutor, $current_user['clinic_id']]);
                if ($stmt->fetch()) {
                    $error_message = "Já existe um tutor cadastrado com este email.";
                }
            }
            
            if (empty($error_message)) {
                $sql = "INSERT INTO tutores (nome_tutor, telefone_tutor, email_tutor, cpf_tutor, endereco, id_clinica) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nome_tutor,
                    $telefone_tutor ?: null,
                    $email_tutor ?: null,
                    $cpf_tutor ?: null,
                    $endereco ?: null,
                    $current_user['clinic_id']
                ]);
                
                $success_message = "Tutor cadastrado com sucesso!";
                
                // Limpar formulário
                $_POST = [];
            }
            
        } catch(PDOException $e) {
            $error_message = "Erro ao cadastrar tutor: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Cadastrar Tutor</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-paw fa-2x mb-2"></i>
                        <h5><?php echo SITE_NAME; ?></h5>
                        <small><?php echo htmlspecialchars($current_user['name']); ?></small><br>
                        <small class="text-muted"><?php echo ucfirst($current_user['type']); ?></small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        
                        <a class="nav-link" href="../animals/list.php">
                            <i class="fas fa-paw me-2"></i>Animais
                        </a>
                        
                        <a class="nav-link active" href="list.php">
                            <i class="fas fa-users me-2"></i>Tutores
                        </a>
                        
                        <a class="nav-link" href="../consultations/list.php">
                            <i class="fas fa-stethoscope me-2"></i>Consultas
                        </a>
                        
                        <?php if ($current_user['type'] === 'admin' || $current_user['type'] === 'superadmin'): ?>
                        <a class="nav-link" href="../admin/users.php">
                            <i class="fas fa-user-md me-2"></i>Usuários
                        </a>
                        <?php endif; ?>
                        
                        <hr class="my-3">
                        
                        <a class="nav-link" href="../../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Sair
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-user-plus me-2"></i>Cadastrar Tutor</h2>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Voltar à Lista
                        </a>
                    </div>
                    
                    <!-- Messages -->
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
                    
                    <!-- Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>Dados do Tutor
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome_tutor" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="nome_tutor" name="nome_tutor" 
                                               value="<?php echo isset($_POST['nome_tutor']) ? htmlspecialchars($_POST['nome_tutor']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="telefone_tutor" class="form-label">Telefone</label>
                                        <input type="tel" class="form-control" id="telefone_tutor" name="telefone_tutor" 
                                               value="<?php echo isset($_POST['telefone_tutor']) ? htmlspecialchars($_POST['telefone_tutor']) : ''; ?>"
                                               placeholder="(11) 99999-9999">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email_tutor" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email_tutor" name="email_tutor" 
                                               value="<?php echo isset($_POST['email_tutor']) ? htmlspecialchars($_POST['email_tutor']) : ''; ?>"
                                               placeholder="tutor@email.com">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="cpf_tutor" class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="cpf_tutor" name="cpf_tutor" 
                                               value="<?php echo isset($_POST['cpf_tutor']) ? htmlspecialchars($_POST['cpf_tutor']) : ''; ?>"
                                               placeholder="000.000.000-00">
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label for="endereco" class="form-label">Endereço Completo</label>
                                        <textarea class="form-control" id="endereco" name="endereco" rows="3"
                                                  placeholder="Rua, número, bairro, cidade, CEP..."><?php echo isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <a href="list.php" class="btn btn-secondary me-2">Cancelar</a>
                                    <button type="submit" name="save_tutor" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Salvar Tutor
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Info Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informações Importantes
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Apenas o <strong>nome</strong> é obrigatório</li>
                                <li>O <strong>CPF</strong> e <strong>email</strong> devem ser únicos (se informados)</li>
                                <li>Após cadastrar o tutor, você poderá <strong>cadastrar animais</strong> para ele</li>
                                <li>Todas as informações podem ser <strong>editadas posteriormente</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Máscaras para campos -->
    <script>
        // Máscara para telefone
        document.getElementById('telefone_tutor').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = value;
        });
        
        // Máscara para CPF
        document.getElementById('cpf_tutor').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
            } else if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '$1.$2');
            }
            e.target.value = value;
        });
    </script>
</body>
</html>