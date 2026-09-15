<?php 
$host = '127.0.0.1';
$dbname = 'db_pedidos';
$username = 'root';
$password = ''; 

try {
    $conexao = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "Conexão com o db_pedidos realizada com sucesso!";

} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
    exit;
}
