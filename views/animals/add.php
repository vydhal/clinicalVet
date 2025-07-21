<?php
/**
 * Cadastro de Animal
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
$especies = [];
$tutores = [];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar espécies
    $stmt = $pdo->query("SELECT * FROM especies ORDER BY nome_especie");
    $especies = $stmt->fetchAll();

    // Buscar tutores da clínica
    if ($current_user['type'] === 'superadmin') {
        $stmt = $pdo->query("SELECT * FROM tutores ORDER BY nome_tutor");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tutores WHERE id_clinica = ? ORDER BY nome_tutor");
        $stmt->execute([$current_user['clinic_id']]);
    }
    $tutores = $stmt->fetchAll();

} catch(PDOException $e) {
    $error_message = "Erro ao carregar dados: " . $e->getMessage();
}

// Processar formulário
if ($_POST && isset($_POST['save_animal'])) {
    $nome_animal = trim($_POST['nome_animal']);
    $id_especie = $_POST['id_especie'];
    $raca = trim($_POST['raca']);
    $sexo = $_POST['sexo'];
    $porte = $_POST['porte'];
    $pelagem = trim($_POST['pelagem']);
    $peso = $_POST['peso'];
    $id_tutor = $_POST['id_tutor'];
    $observacoes = trim($_POST['observacoes']);
    
    if (empty($nome_animal) || empty($id_especie) || empty($id_tutor)) {
        $error_message = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        try {
            // Upload da foto se fornecida
            $foto_animal = null;
            if (isset($_FILES['foto_animal']) && $_FILES['foto_animal']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/animals/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['foto_animal']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $foto_animal = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $foto_animal;
                    
                    if (!move_uploaded_file($_FILES['foto_animal']['tmp_name'], $upload_path)) {
                        $error_message = "Erro ao fazer upload da foto.";
                    }
                } else {
                    $error_message = "Formato de arquivo não permitido. Use JPG, PNG ou GIF.";
                }
            }
            
            if (empty($error_message)) {
                $sql = "INSERT INTO animais (nome_animal, id_especie, raca, sexo, porte, pelagem, peso, id_tutor, observacoes, foto_animal, id_clinica, ativo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nome_animal,
                    $id_especie,
                    $raca,
                    $sexo,
                    $porte,
                    $pelagem,
                    $peso,
                    $id_tutor,
                    $observacoes,
                    $foto_animal,
                    $current_user['clinic_id']
                ]);
                
                $success_message = "Animal cadastrado com sucesso!";
                
                // Limpar formulário
                $_POST = [];
            }
            
        } catch(PDOException $e) {
            $error_message = "Erro ao cadastrar animal: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Cadastrar Animal</title>
    
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
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-plus me-2"></i>Cadastrar Animal</h2>
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
                                <i class="fas fa-paw me-2"></i>Dados do Animal
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome_animal" class="form-label">Nome do Animal *</label>
                                        <input type="text" class="form-control" id="nome_animal" name="nome_animal" 
                                               value="<?php echo isset($_POST['nome_animal']) ? htmlspecialchars($_POST['nome_animal']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="id_especie" class="form-label">Espécie *</label>
                                        <select class="form-select" id="id_especie" name="id_especie" required>
                                            <option value="">Selecione a espécie</option>
                                            <?php foreach ($especies as $especie): ?>
                                            <option value="<?php echo $especie['id_especie']; ?>"
                                                    <?php echo (isset($_POST['id_especie']) && $_POST['id_especie'] == $especie['id_especie']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($especie['nome_especie']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="raca" class="form-label">Raça</label>
                                        <input type="text" class="form-control" id="raca" name="raca" 
                                               value="<?php echo isset($_POST['raca']) ? htmlspecialchars($_POST['raca']) : ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="sexo" class="form-label">Sexo</label>
                                        <select class="form-select" id="sexo" name="sexo">
                                            <option value="macho" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] == 'macho') ? 'selected' : ''; ?>>Macho</option>
                                            <option value="femea" <?php echo (isset($_POST['sexo']) && $_POST['sexo'] == 'femea') ? 'selected' : ''; ?>>Fêmea</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="porte" class="form-label">Porte</label>
                                        <select class="form-select" id="porte" name="porte">
                                            <option value="pequeno" <?php echo (isset($_POST['porte']) && $_POST['porte'] == 'pequeno') ? 'selected' : ''; ?>>Pequeno</option>
                                            <option value="medio" <?php echo (isset($_POST['porte']) && $_POST['porte'] == 'medio') ? 'selected' : ''; ?>>Médio</option>
                                            <option value="grande" <?php echo (isset($_POST['porte']) && $_POST['porte'] == 'grande') ? 'selected' : ''; ?>>Grande</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="pelagem" class="form-label">Pelagem</label>
                                        <input type="text" class="form-control" id="pelagem" name="pelagem" 
                                               value="<?php echo isset($_POST['pelagem']) ? htmlspecialchars($_POST['pelagem']) : ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="peso" class="form-label">Peso (kg)</label>
                                        <input type="number" step="0.1" class="form-control" id="peso" name="peso" 
                                               value="<?php echo isset($_POST['peso']) ? htmlspecialchars($_POST['peso']) : ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="id_tutor" class="form-label">Tutor *</label>
                                        <select class="form-select" id="id_tutor" name="id_tutor" required>
                                            <option value="">Selecione o tutor</option>
                                            <?php foreach ($tutores as $tutor): ?>
                                            <option value="<?php echo $tutor['id_tutor']; ?>"
                                                    <?php echo (isset($_POST['id_tutor']) && $_POST['id_tutor'] == $tutor['id_tutor']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tutor['nome_tutor']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            <a href="../tutors/add.php" target="_blank">Cadastrar novo tutor</a>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label for="foto_animal" class="form-label">Foto do Animal</label>
                                        <input type="file" class="form-control" id="foto_animal" name="foto_animal" accept="image/*">
                                        <div class="form-text">Formatos aceitos: JPG, PNG, GIF (máx. 2MB)</div>
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label for="observacoes" class="form-label">Observações</label>
                                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <a href="list.php" class="btn btn-secondary me-2">Cancelar</a>
                                    <button type="submit" name="save_animal" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Salvar Animal
                                    </button>
                                </div>
                            </form>
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