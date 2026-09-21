<?php 
require_once '../config/conexao.php';
require_once '../app/models/UsuarioModel.php';
$usuarioModel = new PedidoModel($conexao);

// --- LOGICA 1: SALVAR NO BANCO DE DADOS (cadastrar OU editar) ---
$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $nome = $_POST['nome_usu'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $tipo = $_POST['tipo'];
    $id = $_GET['id_usu'] ?? null;

    try {
        if (!empty($id)) {
            // Veio id_usu na URL -> é uma edição
            $usuarioModel->editar($id, $nome, $email, $telefone, $tipo);
        } else {
            // Sem id_usu -> é um cadastro novo
            $stmt = $conexao->prepare("INSERT INTO usuarios (nome_usu, email, telefone, tipo) VALUES (:nome, :email, :telefone, :tipo)");
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone,
                ':tipo' => $tipo
            ]);
        }

        // Atualiza a página para limpar o formulário e mostrar o registro
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $erro = $e->getMessage();
    }
}

// --- LOGICA 1B: DELETAR DO BANCO DE DADOS (Quando clicar em "Excluir") ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar'])) {
    try {
        $usuarioModel->deletar($_POST['id_usu']);
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $erro = $e->getMessage();
    }
}

// --- LOGICA 1C: se veio ?id_usu= na URL, busca esse usuário pra preencher o formulário ---
$usuario_editando = null;
if (isset($_GET['id_usu'])) {
    $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id_usu = :id");
    $stmt->execute([':id' => $_GET['id_usu']]);
    $usuario_editando = $stmt->fetch();
}

// --- LOGICA 2: BUSCAR DO BANCO DE DADOS (Para exibir na tabela) ---
try {
    $stmt = $conexao->query("SELECT * FROM usuarios");
    $pedidos = $stmt->fetchAll();
} catch (PDOException $e) {
    $pedidos = [];
}

// --- LOGICA 3: CONTAGENS PARA A BARRA DE STATUS ---
$total_clientes = 0;
$total_admins = 0;
$total_entregadores = 0;
foreach ($pedidos as $item) {
    if ($item['tipo'] == 1) $total_clientes++;
    elseif ($item['tipo'] == 2) $total_admins++;
    elseif ($item['tipo'] == 3) $total_entregadores++;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rota Certa · Equipe da operação</title>
<style>
  :root {
    --bg: #0f1b2a;
    --panel: #152438;
    --panel-alt: #1b2c44;
    --border: #29405c;
    --border-soft: #223650;
    --text: #eaf0f6;
    --text-muted: #8ba0b8;
    --cliente: #55c6ff;
    --admin: #ff8a52;
    --entregador: #3ee0a0;
    --danger: #ff6b6b;
    --danger-bg: #33191c;
    --focus: #ffb37a;
    --radius: 10px;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    background: var(--bg);
    background-image:
      radial-gradient(circle at 12% 8%, rgba(85, 198, 255, 0.06), transparent 40%),
      radial-gradient(circle at 88% 92%, rgba(255, 138, 82, 0.06), transparent 40%);
    color: var(--text);
    font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
    line-height: 1.5;
    padding: 32px 20px 60px;
  }

  .mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }

  .shell {
    max-width: 1080px;
    margin: 0 auto;
  }

  /* ---------- Cabeçalho / hero ---------- */
  .topo {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 28px;
  }

  .marca h1 {
    font-size: 1.9rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0 0 4px;
  }

  .marca p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.95rem;
  }

  .status-barra {
    display: flex;
    gap: 22px;
    flex-wrap: wrap;
  }

  .status-item {
    display: flex;
    align-items: center;
    gap: 9px;
  }

  .status-ponto {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .status-item strong {
    font-size: 1.15rem;
    font-weight: 600;
  }

  .status-item span {
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  /* ---------- Layout principal ---------- */
  .painel {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 24px;
    align-items: start;
  }

  @media (max-width: 860px) {
    .painel { grid-template-columns: 1fr; }
  }

  section {
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 22px;
  }

  section > h2 {
    font-size: 1.05rem;
    font-weight: 600;
    margin: 0 0 4px;
  }

  section > .sub {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin: 0 0 18px;
  }

  /* ---------- Formulário ---------- */
  .campo {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
  }

  .campo label {
    font-size: 0.82rem;
    color: var(--text-muted);
    margin-bottom: 6px;
  }

  .campo input {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 7px;
    padding: 10px 12px;
    color: var(--text);
    font-family: inherit;
    font-size: 0.95rem;
  }

  .campo input::placeholder { color: #536880; }

  .campo input:focus-visible {
    outline: 2px solid var(--focus);
    outline-offset: 1px;
    border-color: var(--focus);
  }

  fieldset {
    border: none;
    padding: 0;
    margin: 4px 0 20px;
  }

  fieldset legend {
    font-size: 0.82rem;
    color: var(--text-muted);
    margin-bottom: 8px;
    padding: 0;
  }

  .funcoes {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .funcao-opcao {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid var(--border);
    border-radius: 7px;
    padding: 9px 12px;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  .funcao-opcao input {
    position: absolute;
    opacity: 0;
    inset: 0;
    margin: 0;
    cursor: pointer;
  }

  .funcao-ponto {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .funcao-opcao span { font-size: 0.9rem; }

  .funcao-opcao:has(input:checked) {
    background: var(--panel-alt);
  }
  .funcao-opcao:has(input:focus-visible) {
    outline: 2px solid var(--focus);
    outline-offset: 2px;
  }
  .funcao-opcao[data-tipo="1"]:has(input:checked) { border-color: var(--cliente); }
  .funcao-opcao[data-tipo="2"]:has(input:checked) { border-color: var(--admin); }
  .funcao-opcao[data-tipo="3"]:has(input:checked) { border-color: var(--entregador); }

  .botao-salvar {
    width: 100%;
    padding: 11px 18px;
    background: var(--admin);
    color: #1a0f08;
    border: none;
    border-radius: 7px;
    font-family: inherit;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: filter 0.15s ease;
  }

  .botao-salvar:hover { filter: brightness(1.08); }
  .botao-salvar:focus-visible {
    outline: 2px solid var(--focus);
    outline-offset: 2px;
  }

  .alerta-erro {
    background: var(--danger-bg);
    border: 1px solid var(--danger);
    color: #ffb3b3;
    border-radius: 7px;
    padding: 10px 12px;
    font-size: 0.85rem;
    margin-bottom: 16px;
  }

  /* ---------- Tabela / manifesto ---------- */
  .lista-cabeca {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
  }

  .contagem-total {
    color: var(--text-muted);
    font-size: 0.85rem;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
  }

  th {
    text-align: left;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--text-muted);
    padding: 0 10px 10px;
    border-bottom: 1px solid var(--border-soft);
  }

  td {
    padding: 12px 10px;
    border-bottom: 1px dashed var(--border-soft);
    font-size: 0.92rem;
    vertical-align: middle;
  }

  tbody tr:last-child td { border-bottom: none; }
  tbody tr:hover { background: var(--panel-alt); }

  .linha-funcao {
    border-left: 3px solid transparent;
  }
  .linha-funcao[data-tipo="1"] { border-left-color: var(--cliente); }
  .linha-funcao[data-tipo="2"] { border-left-color: var(--admin); }
  .linha-funcao[data-tipo="3"] { border-left-color: var(--entregador); }

  .contato { color: var(--text-muted); }

  .tag-funcao {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.85rem;
  }

  .tag-ponto {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .th-acoes, .td-acoes { text-align: right; }

  .td-acoes {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
  }

  .btn-editar {
    display: inline-block;
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 6px 12px;
    border-radius: 6px;
    font-family: inherit;
    font-size: 0.8rem;
    text-decoration: none;
    cursor: pointer;
    transition: border-color 0.15s ease, color 0.15s ease;
  }

  .btn-editar:hover {
    border-color: var(--focus);
    color: var(--text);
  }

  .btn-editar:focus-visible {
    outline: 2px solid var(--focus);
    outline-offset: 2px;
  }

  .link-cancelar {
    display: block;
    text-align: center;
    margin-top: 12px;
    color: var(--text-muted);
    font-size: 0.85rem;
    text-decoration: none;
  }

  .link-cancelar:hover { color: var(--text); }
  .link-cancelar:focus-visible {
    outline: 2px solid var(--focus);
    outline-offset: 2px;
  }

  .btn-excluir {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 6px 12px;
    border-radius: 6px;
    font-family: inherit;
    font-size: 0.8rem;
    cursor: pointer;
    transition: border-color 0.15s ease, color 0.15s ease;
  }

  .btn-excluir:hover {
    border-color: var(--danger);
    color: var(--danger);
  }

  .btn-excluir:focus-visible {
    outline: 2px solid var(--focus);
    outline-offset: 2px;
  }

  .vazio {
    text-align: center;
    padding: 34px 10px;
    color: var(--text-muted);
    font-size: 0.9rem;
  }

  @media (prefers-reduced-motion: reduce) {
    * { transition: none !important; }
  }
</style>
</head>
<body>

<div class="shell">

  <div class="topo">
    <div class="marca">
      <h1>Rota Certa</h1>
      <p>Cadastro e visão geral da equipe da operação</p>
    </div>
    <div class="status-barra">
      <div class="status-item">
        <span class="status-ponto" style="background:var(--cliente)"></span>
        <div><strong><?= $total_clientes ?></strong><br><span>clientes</span></div>
      </div>
      <div class="status-item">
        <span class="status-ponto" style="background:var(--admin)"></span>
        <div><strong><?= $total_admins ?></strong><br><span>admins</span></div>
      </div>
      <div class="status-item">
        <span class="status-ponto" style="background:var(--entregador)"></span>
        <div><strong><?= $total_entregadores ?></strong><br><span>entregadores</span></div>
      </div>
    </div>
  </div>

  <div class="painel">

    <section>
      <h2><?= $usuario_editando ? 'Editar integrante' : 'Novo integrante' ?></h2>
      <p class="sub"><?= $usuario_editando ? 'Atualize os dados e salve' : 'Adicione alguém à operação' ?></p>

      <?php if ($erro): ?>
        <div class="alerta-erro">Não foi possível salvar: <?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?id_usu=<?= htmlspecialchars($usuario_editando['id_usu'] ?? '') ?>">
        <div class="campo">
          <label for="nome_usu">Nome</label>
          <input type="text" id="nome_usu" name="nome_usu" placeholder="Ex: Roberto" value="<?= htmlspecialchars($usuario_editando['nome_usu'] ?? '') ?>" required>
        </div>
        <div class="campo">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" placeholder="Ex: roberto@email.com" value="<?= htmlspecialchars($usuario_editando['email'] ?? '') ?>" required>
        </div>
        <div class="campo">
          <label for="telefone">Telefone</label>
          <input type="text" id="telefone" name="telefone" placeholder="(12) 99999-9999" value="<?= htmlspecialchars($usuario_editando['telefone'] ?? '') ?>" required>
        </div>

        
          <h3>Função na operação</h3>
          <div class="funcoes">
            <label class="funcao-opcao" data-tipo="1">
              <input type="radio" name="tipo" value="1" <?= (!$usuario_editando || $usuario_editando['tipo'] == 1) ? 'checked' : '' ?>>
              <span class="funcao-ponto" style="background:var(--cliente)"></span>
              <span>Cliente</span>
            </label>
            <label class="funcao-opcao" data-tipo="2">
              <input type="radio" name="tipo" value="2" <?= ($usuario_editando && $usuario_editando['tipo'] == 2) ? 'checked' : '' ?>>
              <span class="funcao-ponto" style="background:var(--admin)"></span>
              <span>Admin</span>
            </label>
            <label class="funcao-opcao" data-tipo="3">
              <input type="radio" name="tipo" value="3" <?= ($usuario_editando && $usuario_editando['tipo'] == 3) ? 'checked' : '' ?>>
              <span class="funcao-ponto" style="background:var(--entregador)"></span>
              <span>Entregador</span>
            </label>
          </div>
<br>
        <button type="submit" name="salvar" class="botao-salvar"><?= $usuario_editando ? 'Salvar alterações' : 'Salvar usuário' ?></button>
        <?php if ($usuario_editando): ?>
          <a href="index.php" class="link-cancelar">Cancelar edição</a>
        <?php endif; ?>
      </form>
    </section>

    <section>
      <div class="lista-cabeca">
        <h2>Equipe cadastrada</h2>
        <span class="contagem-total"><?= count($pedidos) ?> ao todo</span>
      </div>

      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Contato</th>
            <th>Função</th>
            <th class="th-acoes"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($pedidos)):
  foreach ($pedidos as $item):
    $cor_var = 'var(--cliente)';
    $rotulo = 'Cliente';
    if ($item['tipo'] == 2) { $cor_var = 'var(--admin)'; $rotulo = 'Admin'; }
    elseif ($item['tipo'] == 3) { $cor_var = 'var(--entregador)'; $rotulo = 'Entregador'; }
?>
  <tr class="linha-funcao" data-tipo="<?= htmlspecialchars($item['tipo']) ?>">
    <td><?= htmlspecialchars($item['nome_usu']) ?></td>
    <td class="contato mono">
      <?= htmlspecialchars($item['email']) ?><br>
      <?= htmlspecialchars($item['telefone']) ?>
    </td>
    <td>
      <span class="tag-funcao">
        <span class="tag-ponto" style="background:<?= $cor_var ?>"></span>
        <?= $rotulo ?>
      </span>
    </td>
    <td class="td-acoes">
      <a href="index.php?id_usu=<?= htmlspecialchars($item['id_usu']) ?>" class="btn-editar">Editar</a>
      <form method="POST" action="index.php" onsubmit="return confirm('Tem certeza que deseja excluir?');">
        <input type="hidden" name="id_usu" value="<?= htmlspecialchars($item['id_usu']) ?>">
        <button type="submit" name="deletar" class="btn-excluir">Excluir</button>
      </form>
    </td>
  </tr>
  <?php endforeach; 
    else: ?>
  <tr>
    <td colspan="4" class="vazio">Nenhum integrante cadastrado ainda. Use o formulário ao lado para adicionar o primeiro.</td>
  </tr>
<?php endif; ?>
        </tbody>
      </table>
    </section>

  </div>
</div>
<footer id="rodape" style="font-size: 10px; text-align: center;font-family: fantasy;">
<p><small>&copy; 2026. direitos reservados a empresa growhtttt</small></p>
<nav>
<ul>
<li><a href="https://pranx.com/maze/">Política de Privacidade</a></li>
<li><a href="https://pranx.com/maze/">Termos de Uso</a></li>
<li><a href="https://pranx.com/maze/">contrato com o governo</a></li>
</ul>
</nav>
</footer>
</body>
</html>