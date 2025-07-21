<?php
/**
 * Lista de Tutores
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

// Conectar ao banco e buscar tutores
$tutores = [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT t.*, 
            (SELECT COUNT(*) FROM animais a WHERE a.id_tutor = t.id_tutor AND a.ativo = 1) as total_animais
            FROM tutores t 
            WHERE 1=1";
    
    $params = [];
    
    // Filtrar por clínica se não for superadmin
    if ($current_user['type'] !== 'superadmin') {
        $sql .= " AND t.id_clinica = ?";
        $params[] = $current_user['clinic_id'];
    }
    
    // Filtrar por busca se fornecida
    if (!empty($search)) {
        $sql .= " AND (t.nome_tutor LIKE ? OR t.telefone_tutor LIKE ? OR t.email_tutor LIKE ? OR t.cpf_tutor LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY t.nome_tutor ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tutores = $stmt->fetchAll();

} catch(PDOException $e) {
    $error_message = "Erro ao buscar tutores: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Lista de Tutores</title>
    
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
                        <h2><i class="fas fa-users me-2"></i>Lista de Tutores</h2>
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Cadastrar Tutor
                        </a>
                    </div>
                    
                    <!-- Search -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Buscar por nome, telefone, email ou CPF..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                    <?php if (!empty($search)): ?>
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Limpar
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Results -->
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>
                                <?php echo count($tutores); ?> tutor(es) encontrado(s)
                                <?php if (!empty($search)): ?>
                                    para "<?php echo htmlspecialchars($search); ?>"
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($tutores)): ?>
                                <div class="text-center p-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum tutor encontrado</h5>
                                    <p class="text-muted">
                                        <?php if (!empty($search)): ?>
                                            Tente uma busca diferente ou 
                                        <?php endif; ?>
                                        <a href="add.php">cadastre o primeiro tutor</a>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nome</th>
                                                <th>Telefone</th>
                                                <th>Email</th>
                                                <th>CPF</th>
                                                <th>Animais</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tutores as $tutor): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($tutor['nome_tutor']); ?></strong><br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($tutor['endereco'] ?? 'Endereço não informado'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($tutor['telefone_tutor'])): ?>
                                                        <a href="tel:<?php echo htmlspecialchars($tutor['telefone_tutor']); ?>" class="text-decoration-none">
                                                            <i class="fas fa-phone me-1"></i>
                                                            <?php echo htmlspecialchars($tutor['telefone_tutor']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($tutor['email_tutor'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($tutor['email_tutor']); ?>" class="text-decoration-none">
                                                            <i class="fas fa-envelope me-1"></i>
                                                            <?php echo htmlspecialchars($tutor['email_tutor']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($tutor['cpf_tutor'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $tutor['total_animais']; ?> animal(is)
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view.php?id=<?php echo $tutor['id_tutor']; ?>" 
                                                           class="btn btn-outline-primary" title="Ver detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?php echo $tutor['id_tutor']; ?>" 
                                                           class="btn btn-outline-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="../animals/add.php?tutor_id=<?php echo $tutor['id_tutor']; ?>" 
                                                           class="btn btn-outline-success" title="Cadastrar animal">
                                                            <i class="fas fa-paw"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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