<?php 
#aqui vai ser criada as classes para cuidar da logica de todo o projeto 


class PedidoController {

    public function index($pdo) {
        require_once "../app/Models/UsuarioModel.php";
        $model = new PedidoModel($pdo);

        // Se o formulário foi enviado via POST, cadastra o prato
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
            $nome = trim($_POST['nome_usu'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $tipo = trim($_POST['tipo']?? '');

            if (!empty($nome) && !empty($email) && !empty($telefone) && $tipo  > 0) {
                $model->cadastrar($nome, $email, $telefone, $tipo);
                
                // Redireciona para evitar reenvio do formulário ao atualizar a página
                header("Location: index.php");
                exit;
            }
        }

        // Busca a lista atualizada de produtos
        $usuarios = $model->buscarTodosUsuarios();

        // Renderiza a View
        require_once "./Views/exibicao.php";
    }
}