<?php 
require_once '../config/conexao.php';

// --- LOGICA 1: SALVAR NO BANCO DE DADOS (Quando clicar no botão) ---
$erro_cadastro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome = $_POST['nome_usu'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $tipo = $_POST['tipo'];

    try {
        // Prepara o comando SQL para evitar ataques de SQL Injection
        $stmt = $conexao->prepare("INSERT INTO usuarios (nome_usu, email, telefone, tipo) VALUES (:nome, :email, :telefone, :tipo)");
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':telefone' => $telefone,
            ':tipo' => $tipo
        ]);
        
        // Atualiza a página para limpar o formulário e mostrar o novo registro
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $erro_cadastro = $e->getMessage();
    }
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
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
      <h2>Novo integrante</h2>
      <p class="sub">Adicione alguém à operação</p>

      <?php if ($erro_cadastro): ?>
        <div class="alerta-erro">Não foi possível cadastrar: <?= htmlspecialchars($erro_cadastro) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php">
        <div class="campo">
          <label for="nome_usu">Nome</label>
          <input type="text" id="nome_usu" name="nome_usu" placeholder="Ex: Roberto" required>
        </div>
        <div class="campo">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" placeholder="Ex: roberto@email.com" required>
        </div>
        <div class="campo">
          <label for="telefone">Telefone</label>
          <input type="text" id="telefone" name="telefone" placeholder="(12) 99999-9999" required>
        </div>

        <fieldset>
          <legend>Função na operação</legend>
          <div class="funcoes">
            <label class="funcao-opcao" data-tipo="1">
              <input type="radio" name="tipo" value="1" checked>
              <span class="funcao-ponto" style="background:var(--cliente)"></span>
              <span>Cliente</span>
            </label>
            <label class="funcao-opcao" data-tipo="2">
              <input type="radio" name="tipo" value="2">
              <span class="funcao-ponto" style="background:var(--admin)"></span>
              <span>Admin</span>
            </label>
            <label class="funcao-opcao" data-tipo="3">
              <input type="radio" name="tipo" value="3">
              <span class="funcao-ponto" style="background:var(--entregador)"></span>
              <span>Entregador</span>
            </label>
          </div>
        </fieldset>

        <button type="submit" name="cadastrar" class="botao-salvar">Salvar usuário</button>
      </form>
    </section>

    <section>
      <div class="lista-cabeca">
        <h2 style="margin:0">Equipe cadastrada</h2>
        <span class="contagem-total"><?= count($pedidos) ?> ao todo</span>
      </div>

      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Contato</th>
            <th>Função</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($pedidos)): ?>
            <?php foreach ($pedidos as $item): ?>
              <?php
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
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="3" class="vazio">Nenhum integrante cadastrado ainda. Use o formulário ao lado para adicionar o primeiro.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

  </div>
</div>

</body>
</html>     