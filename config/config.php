<?php
/**
 * Configurações Gerais do Sistema
 * Sistema Veterinário
 */

// Configurações do sistema
define('SITE_NAME', 'Sistema Veterinário');
define('SITE_VERSION', '1.0.0');
define('SITE_URL', 'https://seudominio.com'); // Altere para seu domínio

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', 'uploads/animals/');

// Configurações de sessão
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos

// Configurações de paginação
define('ITEMS_PER_PAGE', 20);

// Configurações de segurança
define('PASSWORD_MIN_LENGTH', 6);
define('HASH_ALGORITHM', 'sha256'); // ou 'md5' para compatibilidade

// Configurações de email (se necessário no futuro)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Modo debug (desabilitar em produção)
define('DEBUG_MODE', false);

// Configurações de log
define('LOG_ERRORS', true);
define('LOG_PATH', 'logs/');

// Criar pasta de logs se não existir
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}
?>