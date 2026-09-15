<?php
class PedidoModel {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }
    public function buscarTodosUsuarios() {
        $sql = "SELECT * FROM usuarios ORDER BY id_usu DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar($nome, $email, $telefone, $tipo) {
        $sql = "INSERT INTO usuarios (nome_usu, email, telefone, tipo) VALUES (:nome_usu, :email, :telefone, :tipo)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':nome_usu' => $nome,
            'email' => $email,
            ':telefone' => $telefone,
            ':tipo' => $tipo
        ]);
    }
    public function deletar($id) {
    $sql = "DELETE FROM usuarios WHERE id_usu = :id";
    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':id' => $id
    ]);
}
public function editar($id, $nome, $email, $telefone, $tipo) {
    $sql = "UPDATE usuarios 
            SET nome_usu = :nome_usu, email = :email, telefone = :telefone, tipo = :tipo 
            WHERE id_usu = :id";
    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':id'       => $id,
        ':nome_usu' => $nome,
        ':email'    => $email,
        ':telefone' => $telefone,
        ':tipo'     => $tipo
    ]);
}
}