<?php
/**
 * Funções Auxiliares do Sistema
 * Sistema Veterinário
 */

/**
 * Inicia sessão se não estiver iniciada
 */
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica se usuário está logado
 * @return bool
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verifica autenticação e redireciona se necessário
 * @param string|null $required_level
 */
function checkAuth($required_level = null) {
    startSession();
    
    if (!isLoggedIn()) {
        header("Location: /index.php?error=login_required");
        exit();
    }
    
    if ($required_level) {
        $user_type = $_SESSION['user_type'] ?? '';
        
        // Superadmin tem acesso a tudo
        if ($user_type === 'superadmin') {
            return true;
        }
        
        // Admin pode acessar área de veterinário
        if ($required_level === 'veterinario' && $user_type === 'admin') {
            return true;
        }
        
        // Verificação específica do nível
        if ($user_type !== $required_level) {
            header("Location: /unauthorized.php");
            exit();
        }
    }
    
    return true;
}

/**
 * Obtém dados do usuário logado
 * @return array|null
 */
function getCurrentUser() {
    startSession();
    
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'type' => $_SESSION['user_type'],
            'clinic_id' => $_SESSION['clinic_id'] ?? null,
            'clinic_name' => $_SESSION['clinic_name'] ?? null
        ];
    }
    
    return null;
}

/**
 * Faz logout do usuário
 */
function logout() {
    startSession();
    session_destroy();
    header("Location: /index.php?message=logout_success");
    exit();
}

/**
 * Sanitiza entrada de dados
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Valida email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida CPF
 * @param string $cpf
 * @return bool
 */
function validateCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se não é uma sequência de números iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Validação do algoritmo do CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Formata CPF
 * @param string $cpf
 * @return string
 */
function formatCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

/**
 * Formata telefone
 * @param string $phone
 * @return string
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) == 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
    } elseif (strlen($phone) == 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
    }
    
    return $phone;
}

/**
 * Formata data para exibição
 * @param string $date
 * @return string
 */
function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    
    return date('d/m/Y', strtotime($date));
}

/**
 * Formata data e hora para exibição
 * @param string $datetime
 * @return string
 */
function formatDateTime($datetime) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }
    
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Gera mensagem de alerta Bootstrap
 * @param string $message
 * @param string $type (success, danger, warning, info)
 * @return string
 */
function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Redireciona com mensagem
 * @param string $url
 * @param string $message
 * @param string $type
 */
function redirectWithMessage($url, $message, $type = 'success') {
    startSession();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Exibe e limpa mensagem flash
 * @return string
 */
function getFlashMessage() {
    startSession();
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return showAlert($message, $type);
    }
    
    return '';
}

/**
 * Gera ID único para upload de arquivos
 * @param string $prefix
 * @return string
 */
function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . '_' . time();
}

/**
 * Converte bytes para formato legível
 * @param int $bytes
 * @return string
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Log de erros personalizado
 * @param string $message
 * @param string $level
 */
function logError($message, $level = 'ERROR') {
    if (LOG_ERRORS) {
        $log_file = LOG_PATH . 'error_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Paginação simples
 * @param int $total_items
 * @param int $items_per_page
 * @param int $current_page
 * @param string $base_url
 * @return string
 */
function generatePagination($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $pagination = '<nav><ul class="pagination justify-content-center">';
    
    // Botão anterior
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . $prev_page . '">Anterior</a></li>';
    }
    
    // Números das páginas
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Botão próximo
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . $next_page . '">Próximo</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    
    return $pagination;
}
?>