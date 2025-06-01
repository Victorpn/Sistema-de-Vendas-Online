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

// Buscar usuários com filtro de pesquisa pelo nome e login
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

$stmt = $pdo->prepare("SELECT id, login, nivel, nome FROM usuario WHERE login LIKE ? OR nome LIKE ? ORDER BY login");
$stmt->execute([$searchParam, $searchParam]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gerenciar Usuários - Admin</title>
<style>
* {
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 40px auto;
    max-width: 900px;
    background-color: #f4f6f8;
    color: #333;
    line-height: 1.6;
}

h2 {
    color: #222;
    border-bottom: 3px solid rgb(255, 0, 0);
    padding-bottom: 8px;
    margin-bottom: 25px;
    font-weight: 700;
}

p {
    font-size: 0.9rem;
}

a {
    color:rgb(255, 0, 0);
    text-decoration: none;
    transition: color 0.3s ease;
}

a:hover {
    text-decoration: underline;
    color:rgb(255, 0, 0);
}

form {
    margin-bottom: 25px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

form input[type="text"] {
    flex: 1;
    padding: 10px 12px;
    border: 1.8px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

form input[type="text"]:focus {
    border-color:rgb(255, 0, 0);
    outline: none;
    box-shadow: 0 0 5px rgba(161, 0, 0, 0.5);
}

form button[type="submit"] {
    background-color:rgb(255, 0, 0);
    border: none;
    color: white;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button[type="submit"]:hover {
    background-color:rgb(134, 3, 3);
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 14px 18px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 1rem;
}

th {
    background-color:rgb(255, 0, 0);
    color: white;
    font-weight: 700;
}

tbody tr:hover {
    background-color: #f1f8ff;
}

button {
    background-color: #28a745;
    border: none;
    color: white;
    padding: 8px 15px;
    font-size: 0.9rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #1e7e34;
}

.msg {
    margin: 20px 0;
    padding: 12px 18px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1rem;
}

.msg.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1.5px solid #f5c6cb;
}

.msg:not(.error) {
    background-color: #d4edda;
    color: #155724;
    border: 1.5px solid #c3e6cb;
}

@media (max-width: 600px) {
    body {
        margin: 20px;
    }

    table, thead, tbody, th, td, tr {
        display: block;
    }

    thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
    }

    tr {
        margin-bottom: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
        padding: 10px 15px;
    }

    td {
        border: none;
        position: relative;
        padding-left: 50%;
        font-size: 0.9rem;
    }

    td:before {
        position: absolute;
        top: 14px;
        left: 15px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: 700;
        color: #555;
    }

    td:nth-of-type(1):before { content: "Nome"; }
    td:nth-of-type(2):before { content: "Usuário"; }
    td:nth-of-type(3):before { content: "Nível"; }
    td:nth-of-type(4):before { content: "Ações"; }
}
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
    <input type="text" name="search" placeholder="Pesquisar por nome ou login" value="<?= htmlspecialchars($search) ?>" autofocus>
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
            <th>Nome</th>
            <th>Usuário (login)</th>
            <th>Nível</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars($usuario['nome']) ?></td>
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
