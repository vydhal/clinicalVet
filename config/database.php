<?php
/**
 * Configuração do Banco de Dados
 * Sistema Veterinário
 */

// Configurações para produção (Hostinger)
define('DB_HOST', 'localhost');
define('DB_NAME', 'u324919422_veterinario');
define('DB_USER', 'u324919422_vet_admin');
define('DB_PASS', 'Vydhal@112358');

// Configurações para desenvolvimento local (descomente se necessário)
/*
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'veterinary_local');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}
*/

// Configurações gerais
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Timezone
date_default_timezone_set('America/Sao_Paulo');
?>
