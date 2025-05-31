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
        body { font-family: Arial; margin: 40px; background-color: #f7f7f7; }
        h2 { color: #333; }
        .anuncio { background: #fff; padding: 15px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 20px; }
        .info { margin-bottom: 10px; }
        a { color: #007BFF; text-decoration: none; margin-right: 10px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<p style="text-align: right;">
    Logado como: <strong><?= $_SESSION['nome_usuario'] ?? 'ADM' ?></strong> | 
    <a href="logout.php">Logout</a>
</p>

<p>
  <a href="gerenciarUsuarios.php" style="padding: 8px 12px; background: #007BFF; color: white; border-radius: 5px; text-decoration: none;">
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
