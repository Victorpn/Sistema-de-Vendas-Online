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
        /* Reset básico */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 40px;
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            color: #333;
        }

        /* Header e informações do usuário */
        p[style*="text-align: right"] {
            font-size: 14px;
            color: #555;
        }

        p[style*="text-align: right"] strong {
            color: #222;
        }

        a {
            color: #1d4ed8; /* Azul */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        /* Botão Gerenciar Usuários */
        p > a {
            display: inline-block;
            padding: 10px 18px;
            background-color: #2563eb;
            color: white;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        p > a:hover {
            background-color: #1e40af;
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.6);
        }

        h2 {
            color: #1e3a8a;
            margin-top: 40px;
            margin-bottom: 20px;
            font-weight: 700;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 8px;
        }

        /* Cards dos anúncios */
        .anuncio {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .anuncio:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .info {
            margin-bottom: 10px;
            font-size: 16px;
        }

        /* Links dentro dos anúncios como botões */
        .anuncio a {
            display: inline-block;
            margin-right: 12px;
            margin-top: 10px;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
            transition: background-color 0.3s ease, color 0.3s ease;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        .anuncio a[href*="aprovar"] {
            background-color: #22c55e; /* Verde */
            color: white;
        }
        .anuncio a[href*="aprovar"]:hover {
            background-color: #16a34a;
        }

        .anuncio a[href*="rejeitar"] {
            background-color: #ef4444; /* Vermelho */
            color: white;
        }
        .anuncio a[href*="rejeitar"]:hover {
            background-color: #b91c1c;
        }

        .anuncio a[href*="excluir"] {
            background-color: #f97316; /* Laranja */
            color: white;
        }
        .anuncio a[href*="excluir"]:hover {
            background-color: #c2410c;
        }

        /* Mensagem sem anúncios */
        p {
            font-style: italic;
            color: #555;
            font-size: 16px;
        }

        /* Responsividade simples */
        @media (max-width: 600px) {
            body {
                margin: 20px;
            }
            .anuncio {
                padding: 15px 20px;
            }
            .anuncio a {
                padding: 6px 10px;
                font-size: 14px;
            }
            p > a {
                padding: 8px 14px;
                font-size: 14px;
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
