<?php
session_start();

// Proteção: apenas ADM pode acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['UsuarioNivel'] !== 'ADM') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=sistema", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Processo de upgrade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_user_id'], $_POST['admin_password'])) {
    $upgradeUserId = (int)$_POST['upgrade_user_id'];
    $adminPassword = $_POST['admin_password'];
    
    // Buscar a senha do admin no banco (supondo que está na tabela usuario, campo senha)
    $stmt = $pdo->prepare("SELECT senha FROM usuario WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar senha (supondo senha hash com password_hash)
    if ($admin) {
    $senhaBanco = $admin['senha'];
    if ($senhaBanco === $adminPassword || password_verify($adminPassword, $senhaBanco)) {
        // senha ok
        $stmt = $pdo->prepare("UPDATE usuario SET nivel = 'ADM' WHERE id = ?");
        $stmt->execute([$upgradeUserId]);
        $msg = "Usuário promovido a ADM com sucesso!";
    } else {
        $msg = "Senha incorreta. Operação cancelada.";
    }
} else {
    $msg = "Erro no sistema. Admin não encontrado.";
}

}

// Buscar usuários com filtro de pesquisa
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";
$stmt = $pdo->prepare("SELECT id, login, nivel FROM usuario WHERE login LIKE ? ORDER BY login");
$stmt->execute([$searchParam]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gerenciar Usuários - Admin</title>
<style>
    body { font-family: Arial; margin: 40px; background-color: #f7f7f7; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #eee; }
    .msg { margin: 20px 0; padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; }
    .error { background: #f8d7da; color: #721c24; }
</style>
<script>
function confirmarUpgrade(userId, userName) {
    const senha = prompt(`Digite sua senha para confirmar a promoção do usuário ${userName} para ADM:`);
    if (senha) {
        // Criar um formulário dinamicamente para enviar POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'upgrade_user_id';
        inputId.value = userId;
        form.appendChild(inputId);
        
        const inputSenha = document.createElement('input');
        inputSenha.type = 'hidden';
        inputSenha.name = 'admin_password';
        inputSenha.value = senha;
        form.appendChild(inputSenha);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</head>
<body>

<p style="text-align: right;">
    Logado como: <strong><?= htmlspecialchars($_SESSION['nome_usuario'] ?? 'ADM') ?></strong> | 
    <a href="logout.php">Logout</a>
</p>

<h2>Lista de Usuários</h2>

<form method="GET" action="">
    <input type="text" name="search" placeholder="Pesquisar por nome ou letra" value="<?= htmlspecialchars($search) ?>" autofocus>
    <button type="submit">Buscar</button>
</form>

<?php if (!empty($msg)): ?>
    <div class="msg <?= strpos($msg, 'incorreta') !== false ? 'error' : '' ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Usuário</th>
            <th>Nível</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars($usuario['login']) ?></td>
                <td><?= htmlspecialchars($usuario['nivel']) ?></td>
                <td>
                    <?php if ($usuario['nivel'] !== 'ADM'): ?>
                        <button onclick="confirmarUpgrade(<?= $usuario['id'] ?>, '<?= addslashes($usuario['login']) ?>')">Tornar ADM</button>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
