<?php
/**
 * Busca de Animais
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

// Conectar ao banco e buscar animais
$animals = [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_performed = !empty($search);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($search_performed) {
        $sql = "SELECT a.*, t.nome_tutor, t.telefone_tutor, e.nome_especie 
                FROM animais a 
                LEFT JOIN tutores t ON a.id_tutor = t.id_tutor 
                LEFT JOIN especies e ON a.id_especie = e.id_especie 
                WHERE a.ativo = 1";
        
        $params = [];
        
        // Filtrar por clínica se não for superadmin
        if ($current_user['type'] !== 'superadmin') {
            $sql .= " AND a.id_clinica = ?";
            $params[] = $current_user['clinic_id'];
        }
        
        // Busca mais ampla
        $sql .= " AND (a.nome_animal LIKE ? OR t.nome_tutor LIKE ? OR a.id_animal = ? OR a.raca LIKE ? OR e.nome_especie LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = $search;
        $params[] = "%$search%";
        $params[] = "%$search%";
        
        $sql .= " ORDER BY a.nome_animal ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $animals = $stmt->fetchAll();
    }

} catch(PDOException $e) {
    $error_message = "Erro ao buscar animais: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Buscar Animal</title>
    
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
        .animal-card {
            transition: transform 0.2s ease;
            cursor: pointer;
        }
        .animal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .animal-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
        }
        .search-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
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
                        
                        <a class="nav-link active" href="list.php">
                            <i class="fas fa-paw me-2"></i>Animais
                        </a>
                        
                        <a class="nav-link" href="../tutors/list.php">
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
                    <!-- Search Hero -->
                    <div class="search-hero">
                        <div class="text-center">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h2>Buscar Animal</h2>
                            <p class="mb-4">Digite o nome do animal, tutor, ID, raça ou espécie</p>
                            
                            <form method="GET" class="row g-3 justify-content-center">
                                <div class="col-md-8">
                                    <div class="input-group input-group-lg">
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Ex: Rex, João Silva, 123, Golden Retriever..." 
                                               value="<?php echo htmlspecialchars($search); ?>" autofocus>
                                        <button type="submit" class="btn btn-light">
                                            <i class="fas fa-search me-2"></i>Buscar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="list.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>Ver Todos os Animais
                                </a>
                                <a href="add.php" class="btn btn-outline-success">
                                    <i class="fas fa-plus me-2"></i>Cadastrar Animal
                                </a>
                                <a href="../tutors/list.php" class="btn btn-outline-info">
                                    <i class="fas fa-users me-2"></i>Ver Tutores
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results -->
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($search_performed): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-search-plus me-2"></i>
                                    Resultados para "<?php echo htmlspecialchars($search); ?>"
                                    <span class="badge bg-primary ms-2"><?php echo count($animals); ?> encontrado(s)</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($animals)): ?>
                                    <div class="text-center p-5">
                                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Nenhum animal encontrado</h5>
                                        <p class="text-muted">
                                            Tente buscar por:
                                        </p>
                                        <ul class="list-unstyled text-muted">
                                            <li>• Nome do animal (ex: Rex, Mimi)</li>
                                            <li>• Nome do tutor (ex: João Silva)</li>
                                            <li>• ID do animal (ex: 123)</li>
                                            <li>• Raça (ex: Golden Retriever)</li>
                                            <li>• Espécie (ex: Cão, Gato)</li>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($animals as $animal): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card animal-card h-100" onclick="window.location.href='view.php?id=<?php echo $animal['id_animal']; ?>'">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <?php if (!empty($animal['foto_animal'])): ?>
                                                            <img src="../../uploads/animals/<?php echo htmlspecialchars($animal['foto_animal']); ?>" 
                                                                 class="animal-photo me-3" alt="Foto do animal">
                                                        <?php else: ?>
                                                            <div class="animal-photo bg-secondary d-flex align-items-center justify-content-center me-3">
                                                                <i class="fas fa-paw text-white fa-2x"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="flex-grow-1">
                                                            <h5 class="card-title mb-1">
                                                                <?php echo htmlspecialchars($animal['nome_animal']); ?>
                                                                <small class="text-muted">#<?php echo $animal['id_animal']; ?></small>
                                                            </h5>
                                                            <p class="card-text text-muted mb-0">
                                                                <?php echo htmlspecialchars($animal['nome_especie'] ?? 'N/A'); ?> • 
                                                                <?php echo ucfirst($animal['sexo']); ?> • 
                                                                <?php echo htmlspecialchars($animal['porte']); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <strong>Tutor:</strong> <?php echo htmlspecialchars($animal['nome_tutor'] ?? 'N/A'); ?>
                                                    </div>
                                                    
                                                    <?php if (!empty($animal['raca'])): ?>
                                                    <div class="mb-2">
                                                        <strong>Raça:</strong> <?php echo htmlspecialchars($animal['raca']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($animal['telefone_tutor'])): ?>
                                                    <div class="mb-3">
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone me-1"></i>
                                                            <?php echo htmlspecialchars($animal['telefone_tutor']); ?>
                                                        </small>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="d-flex gap-2">
                                                        <a href="view.php?id=<?php echo $animal['id_animal']; ?>" 
                                                           class="btn btn-sm btn-primary flex-fill">
                                                            <i class="fas fa-eye me-1"></i>Ver
                                                        </a>
                                                        <a href="../consultations/add.php?animal_id=<?php echo $animal['id_animal']; ?>" 
                                                           class="btn btn-sm btn-success flex-fill">
                                                            <i class="fas fa-stethoscope me-1"></i>Consulta
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Dicas de busca -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-lightbulb me-2"></i>Dicas de Busca
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li>Digite o <strong>nome do animal</strong> (ex: Rex, Mimi)</li>
                                            <li>Digite o <strong>nome do tutor</strong> (ex: João Silva)</li>
                                            <li>Digite o <strong>ID do animal</strong> (ex: 123)</li>
                                            <li>Digite a <strong>raça</strong> (ex: Golden Retriever)</li>
                                            <li>Digite a <strong>espécie</strong> (ex: Cão, Gato)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-bolt me-2"></i>Ações Rápidas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="list.php" class="btn btn-outline-primary">
                                                <i class="fas fa-list me-2"></i>Ver Todos os Animais
                                            </a>
                                            <a href="add.php" class="btn btn-outline-success">
                                                <i class="fas fa-plus me-2"></i>Cadastrar Novo Animal
                                            </a>
                                            <a href="../tutors/add.php" class="btn btn-outline-info">
                                                <i class="fas fa-user-plus me-2"></i>Cadastrar Novo Tutor
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>