<?php
session_start();

// Proteção
if (!isset($_SESSION['usuario_id']) || $_SESSION['UsuarioNivel'] !== 'USER') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=sistema", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$usuario_id = $_SESSION['usuario_id'];
$acao = $_GET['acao'] ?? '';
$id = $_GET['id'] ?? null;

// CRIAR ou ATUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = $_POST['modelo'];
    $valor = $_POST['valor'];
    $cidade = $_POST['cidade'];
    $cor = $_POST['cor'];
    $idEdicao = $_POST['id'] ?? null;

    if (!empty($idEdicao)) {
        // Atualiza o anúncio sem alterar o status de aprovação
        $stmt = $pdo->prepare("UPDATE anuncio SET modelo=?, valor=?, cidade=?, cor=? WHERE id=? AND usuario_id=?");
        $stmt->execute([$modelo, $valor, $cidade, $cor, $idEdicao, $usuario_id]);

        $anuncio_id = $idEdicao;
    } else {
        $stmt = $pdo->prepare("INSERT INTO anuncio (modelo, valor, cidade, cor, usuario_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$modelo, $valor, $cidade, $cor, $usuario_id]);
        $anuncio_id = $pdo->lastInsertId();
    }

    // Salvar imagens
    $dir = "uploads/";
    if (!is_dir($dir)) mkdir($dir);

    if (!empty($_FILES['fotos']['name'][0])) {
        foreach ($_FILES['fotos']['tmp_name'] as $i => $tmpFile) {
            if ($_FILES['fotos']['error'][$i] === 0) {
                $nome = uniqid() . "_" . basename($_FILES['fotos']['name'][$i]);
                $destino = $dir . $nome;
                if (move_uploaded_file($tmpFile, $destino)) {
                    $stmt = $pdo->prepare("INSERT INTO fotos (anuncio_id, caminho) VALUES (?, ?)");
                    $stmt->execute([$anuncio_id, $destino]);
                }
            }
        }
    }

    header('Location: menuUser.php');
    exit;
}

// Exclusão direta (se quiser manter)
if ($acao === 'excluir' && $id) {
    // Exclui imagens associadas
    $stmt = $pdo->prepare("SELECT caminho FROM fotos WHERE anuncio_id = ? AND EXISTS (SELECT 1 FROM anuncio WHERE id = ? AND usuario_id = ?)");
    $stmt->execute([$id, $id, $usuario_id]);
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fotos as $foto) {
        if (file_exists($foto['caminho'])) {
            unlink($foto['caminho']);
        }
    }

    $pdo->prepare("DELETE FROM fotos WHERE anuncio_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM anuncio WHERE id = ? AND usuario_id = ?")->execute([$id, $usuario_id]);

    header('Location: menuUser.php');
    exit;
}

// EDIÇÃO - busca o anúncio independentemente do status
$anuncioEdicao = null;
if ($acao === 'editar' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM anuncio WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);
    $anuncioEdicao = $stmt->fetch(PDO::FETCH_ASSOC);
}

// LISTAR todos os anúncios do usuário
$stmt = $pdo->prepare("SELECT * FROM anuncio WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Anúncios</title>
    <style>
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 40px auto;
    max-width: 900px;
    background-color: #f4f6f8;
    color: #333;
}

h2, h3 {
    color: #222;
    border-bottom: 3px solid #007BFF;
    padding-bottom: 6px;
    margin-bottom: 20px;
    font-weight: 700;
}

p {
    font-size: 0.95rem;
    text-align: right;
}

a {
    color: #007BFF;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
    margin-right: 12px;
}

a:hover {
    color: #0056b3;
    text-decoration: underline;
}

form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 40px;
}

form label {
    display: block;
    margin-top: 12px;
    font-weight: 600;
    font-size: 0.95rem;
}

form input[type="text"],
form input[type="number"],
form input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1.6px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

form input:focus {
    border-color: #007BFF;
    outline: none;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

form button {
    margin-top: 18px;
    background-color: #007BFF;
    color: white;
    border: none;
    font-size: 1rem;
    padding: 12px;
    width: 100%;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #0056b3;
}

.anuncio {
    background: #ffffff;
    padding: 18px;
    margin-bottom: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: relative;
}

.anuncio strong {
    display: inline-block;
    margin-top: 5px;
}

.anuncio img {
    width: 140px;
    margin: 8px 10px 5px 0;
    border-radius: 8px;
    border: 1px solid #ddd;
    object-fit: cover;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

.anuncio .status {
    margin-top: 10px;
    font-weight: 600;
}

.anuncio .status span {
    font-size: 0.95rem;
}

.anuncio .status span.aprovado {
    color: green;
}

.anuncio .status span.rejeitado {
    color: red;
}

.anuncio .status em {
    color: #888;
}

.anuncio a {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 14px;
    background-color: #e9ecef;
    color: #007BFF;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.anuncio a:hover {
    background-color: #d4e2f0;
}

@media (max-width: 600px) {
    body {
        margin: 20px;
    }

    .anuncio img {
        width: 100%;
        max-width: 100%;
    }

    form button {
        font-size: 1rem;
        padding: 10px;
    }
}

    </style>
</head>
<body>

<p style="text-align: right;">
    Logado como: <strong><?= htmlspecialchars($_SESSION['nome_usuario'] ?? 'Usuário') ?></strong> | 
    <a href="logout.php">Logout</a>
</p>

<h2>Gerenciar Meus Anúncios</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($anuncioEdicao['id'] ?? '') ?>">
    <label>Modelo: <input type="text" name="modelo" value="<?= htmlspecialchars($anuncioEdicao['modelo'] ?? '') ?>" required></label>
    <label>Valor: <input type="number" step="0.01" name="valor" value="<?= htmlspecialchars($anuncioEdicao['valor'] ?? '') ?>" required></label>
    <label>Cidade: <input type="text" name="cidade" value="<?= htmlspecialchars($anuncioEdicao['cidade'] ?? '') ?>" required></label>
    <label>Cor: <input type="text" name="cor" value="<?= htmlspecialchars($anuncioEdicao['cor'] ?? '') ?>" required></label>
    <label>Fotos do veículo:</label>
    <input type="file" name="fotos[]" multiple accept="image/*">
    <button type="submit"><?= $anuncioEdicao ? 'Atualizar' : 'Criar' ?> Anúncio</button>
</form>

<h3>Seus Anúncios</h3>

<?php foreach ($anuncios as $anuncio): ?>
    <div class="anuncio">
        <strong>Modelo:</strong> <?= htmlspecialchars($anuncio['modelo']) ?><br>
        <strong>Valor:</strong> R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?><br>
        <strong>Cidade:</strong> <?= htmlspecialchars($anuncio['cidade']) ?><br>
        <strong>Cor:</strong> <?= htmlspecialchars($anuncio['cor']) ?><br>
        <strong>Status:</strong> 
        <?php
            if ($anuncio['status'] === 'rejeitado') {
                echo "<span style='color: red; font-weight: bold;'>Rejeitado ❌</span>";
            } elseif (!$anuncio['aprovado']) {
                echo "<em>Aguardando aprovação do ADM</em>";
            } else {
                echo "<strong>Aprovado ✅</strong>";
            }
        ?>
        <br>

        <?php
        $stmtFotos = $pdo->prepare("SELECT caminho FROM fotos WHERE anuncio_id = ?");
        $stmtFotos->execute([$anuncio['id']]);
        $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fotos as $foto) {
            echo "<img src='" . htmlspecialchars($foto['caminho']) . "' alt='Foto'>";
        }
        ?>

        <div style="margin-top:10px;">
            <a href="?acao=editar&id=<?= $anuncio['id'] ?>">Editar</a>
            <a href="?acao=excluir&id=<?= $anuncio['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este anúncio?')">Excluir</a>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
