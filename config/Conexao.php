<?php 

// Configurações de acesso ao Laragon
$host = '127.0.0.1';
$dbname = 'db_pedidos';
$username = 'root';
$password = ''; // Padrão do Laragon é vazio

try {
    // Cria a conexão usando a biblioteca PDO (mais segura)
    $conexao = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configura o PHP para lançar erros caso aconteça algum problema no SQL
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configura o retorno padrão como array associativo (fácil de ler)
    $conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Se chegar aqui, conectou com sucesso!
    // (Você pode apagar essa linha depois para não poluir a tela)
    echo "Conexão com o db_pedidos realizada com sucesso!";

} catch (PDOException $e) {
    // Se der o erro "Connection refused" ou "Unknown database", ele vai cair aqui
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
    exit;
}
