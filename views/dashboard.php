<?php
/**
 * Dashboard Principal - Versão Corrigida
 * Sistema Veterinário
 */

// Iniciar sessão
session_start();

// Configurações inline
define('SITE_NAME', 'Sistema Veterinário');
define('SITE_VERSION', '1.0.0');
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
    header("Location: ../index.php?error=login_required");
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

// Conectar ao banco
$stats = [];
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Total de animais
    if ($current_user['type'] === 'superadmin') {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM animais WHERE ativo = 1");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM animais WHERE id_clinica = ? AND ativo = 1");
        $stmt->execute([$current_user['clinic_id']]);
    }
    $stats['total_animals'] = $stmt->fetch()['total'];

    // Total de tutores
    if ($current_user['type'] === 'superadmin') {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tutores");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tutores WHERE id_clinica = ?");
        $stmt->execute([$current_user['clinic_id']]);
    }
    $stats['total_tutors'] = $stmt->fetch()['total'];

    // Total de consultas este mês
    if ($current_user['type'] === 'superadmin') {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM historico_medico WHERE tipo_evento = 'Consulta' AND MONTH(data_hora_evento) = MONTH(NOW()) AND YEAR(data_hora_evento) = YEAR(NOW())");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM historico_medico WHERE tipo_evento = 'Consulta' AND id_clinica = ? AND MONTH(data_hora_evento) = MONTH(NOW()) AND YEAR(data_hora_evento) = YEAR(NOW())");
        $stmt->execute([$current_user['clinic_id']]);
    }
    $stats['consultations_month'] = $stmt->fetch()['total'];

    // Total de consultas hoje
    if ($current_user['type'] === 'superadmin') {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM historico_medico WHERE tipo_evento = 'Consulta' AND DATE(data_hora_evento) = CURDATE()");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM historico_medico WHERE tipo_evento = 'Consulta' AND id_clinica = ? AND DATE(data_hora_evento) = CURDATE()");
        $stmt->execute([$current_user['clinic_id']]);
    }
    $stats['consultations_today'] = $stmt->fetch()['total'];

} catch(PDOException $e) {
    $stats = [
        'total_animals' => 0,
        'total_tutors' => 0,
        'consultations_month' => 0,
        'consultations_today' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
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
        
        .card-stats {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .card-stats:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        
                        <?php if ($current_user['type'] !== 'superadmin'): ?>
                        <a class="nav-link" href="animals/list.php">
                            <i class="fas fa-paw me-2"></i>Animais
                        </a>
                        
                        <a class="nav-link" href="tutors/list.php">
                            <i class="fas fa-users me-2"></i>Tutores
                        </a>
                        
                        <a class="nav-link" href="consultations/list.php">
                            <i class="fas fa-stethoscope me-2"></i>Consultas
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($current_user['type'] === 'admin' || $current_user['type'] === 'superadmin'): ?>
                        <a class="nav-link" href="admin/users.php">
                            <i class="fas fa-user-md me-2"></i>Usuários
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($current_user['type'] === 'superadmin'): ?>
                        <a class="nav-link" href="admin/clinics.php">
                            <i class="fas fa-hospital me-2"></i>Clínicas
                        </a>
                        <?php endif; ?>
                        
                        <hr class="my-3">
                        
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Sair
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Welcome Card -->
                    <div class="card welcome-card mb-4">
                        <div class="card-body">
                            <h4 class="card-title">
                                <i class="fas fa-hand-wave me-2"></i>
                                Bem-vindo, <?php echo htmlspecialchars($current_user['name']); ?>!
                            </h4>
                            <p class="card-text mb-0">
                                <?php if ($current_user['clinic_name']): ?>
                                    <?php echo htmlspecialchars($current_user['clinic_name']); ?> • 
                                <?php endif; ?>
                                <?php echo date('d/m/Y H:i'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stats-icon bg-primary text-white me-3">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0"><?php echo $stats['total_animals']; ?></h5>
                                        <small class="text-muted">Total de Animais</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stats-icon bg-success text-white me-3">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0"><?php echo $stats['total_tutors']; ?></h5>
                                        <small class="text-muted">Total de Tutores</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stats-icon bg-warning text-white me-3">
                                        <i class="fas fa-stethoscope"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0"><?php echo $stats['consultations_month']; ?></h5>
                                        <small class="text-muted">Consultas este Mês</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body d-flex align-items-center">
                                    <div class="stats-icon bg-info text-white me-3">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0"><?php echo $stats['consultations_today']; ?></h5>
                                        <small class="text-muted">Consultas Hoje</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bolt me-2"></i>Ações Rápidas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php if ($current_user['type'] !== 'superadmin'): ?>
                                        <div class="col-md-6 mb-3">
                                            <a href="animals/add.php" class="btn btn-primary w-100">
                                                <i class="fas fa-plus me-2"></i>Cadastrar Animal
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <a href="consultations/add.php" class="btn btn-success w-100">
                                                <i class="fas fa-stethoscope me-2"></i>Nova Consulta
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <a href="tutors/add.php" class="btn btn-info w-100">
                                                <i class="fas fa-user-plus me-2"></i>Cadastrar Tutor
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <a href="animals/search.php" class="btn btn-warning w-100">
                                                <i class="fas fa-search me-2"></i>Buscar Animal
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($current_user['type'] === 'admin'): ?>
                                        <div class="col-md-6 mb-3">
                                            <a href="admin/users.php" class="btn btn-secondary w-100">
                                                <i class="fas fa-user-md me-2"></i>Gerenciar Usuários
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($current_user['type'] === 'superadmin'): ?>
                                        <div class="col-md-6 mb-3">
                                            <a href="admin/clinics.php" class="btn btn-primary w-100">
                                                <i class="fas fa-hospital me-2"></i>Gerenciar Clínicas
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <a href="admin/users.php" class="btn btn-secondary w-100">
                                                <i class="fas fa-users-cog me-2"></i>Gerenciar Usuários
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informações do Sistema
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Versão:</strong> <?php echo SITE_VERSION; ?></p>
                                    <p><strong>Usuário:</strong> <?php echo htmlspecialchars($current_user['name']); ?></p>
                                    <p><strong>Tipo:</strong> <?php echo ucfirst($current_user['type']); ?></p>
                                    <?php if ($current_user['clinic_name']): ?>
                                    <p><strong>Clínica:</strong> <?php echo htmlspecialchars($current_user['clinic_name']); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Login:</strong> <?php echo date('d/m/Y H:i', $_SESSION['login_time']); ?></p>
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