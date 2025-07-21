<?php
echo "<h1>Teste PHP - Sistema Veterinário</h1>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";
echo "<p>Versão PHP: " . phpversion() . "</p>";

// Teste de conexão com banco
echo "<h2>Teste de Conexão com Banco</h2>";

$host = 'localhost';
$dbname = 'u324919422_veterinario';
$username = 'u324919422_vet_admin';
$password = 'Vydhal@112358';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexão com banco de dados: SUCESSO</p>";
    
    // Teste de query simples
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clinicas");
    $result = $stmt->fetch();
    echo "<p>Total de clínicas cadastradas: " . $result['total'] . "</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

echo "<h2>Informações do Servidor</h2>";
echo "<p>Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
?>
