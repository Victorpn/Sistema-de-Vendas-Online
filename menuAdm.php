<?php
session_start();

// Proteção: apenas ADM pode acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['UsuarioNivel'] !== 'ADM') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=sistema", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ações
$acao = $_GET['acao'] ?? '';
$id = $_GET['id'] ?? null;

if ($acao === 'aprovar' && $id) {
    $stmt = $pdo->prepare("UPDATE anuncio SET aprovado = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: menuAdm.php');
    exit;
}

if ($acao === 'rejeitar' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("UPDATE anuncio SET status = 'rejeitado', aprovado = 0 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: menuAdm.php");
    exit();
}

if ($acao === 'excluir' && $id) {
    $stmt = $pdo->prepare("DELETE FROM anuncio WHERE id = ? AND excluir_pendente = 1");
    $stmt->execute([$id]);
    header('Location: menuAdm.php');
    exit;
}

// Buscar anúncios pendentes
$stmt = $pdo->query("SELECT a.*, u.login FROM anuncio a JOIN usuario u ON a.usuario_id = u.id WHERE a.aprovado = 0");
$pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar anúncios com pedido de exclusão
$stmt = $pdo->query("SELECT a.*, u.login FROM anuncio a JOIN usuario u ON a.usuario_id = u.id WHERE a.excluir_pendente = 1");
$exclusoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Área do Administrador</title>
    <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: Arial, sans-serif;
    background-color: #fff;
    padding: 30px;
    color: #000;
  }

  /* Cabeçalho */
  p[style*="text-align: right"] {
    font-size: 14px;
    text-align: right;
    margin-bottom: 20px;
  }

  p[style*="text-align: right"] strong {
    color: #b30000;
  }

  p[style*="text-align: right"] a {
    color:rgb(255, 255, 255);
    text-decoration: none;
    font-weight: bold;
  }

  p[style*="text-align: right"] a:hover {
    text-decoration: underline;
  }

  /* Botão Gerenciar Usuários */
  p > a {
    background-color: #b30000;
    color: white;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: background-color 0.3s;
    margin-bottom: 30px;
  }

  p > a:hover {
    background-color: #800000;
  }

  h2 {
    color: #b30000;
    margin-bottom: 15px;
    font-size: 1.6em;
    border-bottom: 2px solid #b30000;
    padding-bottom: 6px;
  }

  .anuncio {
    background-color: #000;
    color: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .anuncio:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  .info {
    margin-bottom: 8px;
    font-size: 15px;
  }

  /* Botões de ação */
  .anuncio a {
    display: inline-block;
    margin-top: 10px;
    margin-right: 10px;
    padding: 8px 14px;
    border-radius: 5px;
    font-weight: bold;
    text-decoration: none;
    color: white;
    transition: background-color 0.3s;
  }

  .anuncio a[href*="aprovar"] {
    background-color: #28a745;
  }

  .anuncio a[href*="aprovar"]:hover {
    background-color: #1e7e34;
  }

  .anuncio a[href*="rejeitar"] {
    background-color: #dc3545;
  }

  .anuncio a[href*="rejeitar"]:hover {
    background-color: #a71d2a;
  }

  .anuncio a[href*="excluir"] {
    background-color: #fd7e14;
  }

  .anuncio a[href*="excluir"]:hover {
    background-color: #e8590c;
  }

  /* Mensagens informativas */
  p {
    font-size: 15px;
    color: #333;
  }

  /* Responsividade */
  @media (max-width: 600px) {
    body {
      padding: 15px;
    }

    .anuncio {
      padding: 15px;
    }

    .anuncio a {
      font-size: 14px;
      padding: 6px 10px;
    }
  }
</style>
</head>
<body>

<p style="text-align: right;">
    Logado como: <strong><?= $_SESSION['nome_usuario'] ?? 'ADM' ?></strong> | 
    <a href="logout.php">Logout</a>
</p>

<p>
  <a href="gerenciarUsuarios.php">
    Gerenciar Usuários
  </a>
</p>

<h2>Anúncios Pendentes de Aprovação</h2>
<?php if (count($pendentes) === 0): ?>
    <p>Nenhum anúncio pendente.</p>
<?php else: ?>
    <?php foreach ($pendentes as $anuncio): ?>
    <div class="anuncio">
        <div class="info"><strong>Usuário:</strong> <?= htmlspecialchars($anuncio['login']) ?></div>
        <div class="info"><strong>Modelo:</strong> <?= htmlspecialchars($anuncio['modelo']) ?></div>
        <div class="info"><strong>Valor:</strong> R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?></div>
        <div class="info"><strong>Cidade:</strong> <?= htmlspecialchars($anuncio['cidade']) ?></div>
        <div class="info"><strong>Cor:</strong> <?= htmlspecialchars($anuncio['cor']) ?></div>
        <a href="?acao=aprovar&id=<?= $anuncio['id'] ?>" onclick="return confirm('Deseja aprovar este anúncio?')">Aprovar ✅</a>
        <a href="?acao=rejeitar&id=<?= $anuncio['id'] ?>" onclick="return confirm('Deseja rejeitar este anúncio?')">Rejeitar ❌</a>
    </div>
<?php endforeach; ?>

<?php endif; ?>

<h2>Anúncios com Pedido de Exclusão</h2>
<?php if (count($exclusoes) === 0): ?>
    <p>Nenhum pedido de exclusão.</p>
<?php else: ?>
    <?php foreach ($exclusoes as $anuncio): ?>
        <div class="anuncio">
            <div class="info"><strong>Usuário:</strong> <?= htmlspecialchars($anuncio['login']) ?></div>
            <div class="info"><strong>Modelo:</strong> <?= htmlspecialchars($anuncio['modelo']) ?></div>
            <div class="info"><strong>Valor:</strong> R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?></div>
            <div class="info"><strong>Cidade:</strong> <?= htmlspecialchars($anuncio['cidade']) ?></div>
            <div class="info"><strong>Cor:</strong> <?= htmlspecialchars($anuncio['cor']) ?></div>
            <a href="?acao=excluir&id=<?= $anuncio['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este anúncio?')">Excluir 🚫</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
