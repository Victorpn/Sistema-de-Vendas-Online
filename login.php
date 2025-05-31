<?php
include('config.php');
session_start();

if (isset($_POST['botao']) && $_POST['botao'] == "Entrar") {
    $loginOuEmail = $_POST['login'];
    $senha = $_POST['senha'];

    $stmt = mysqli_prepare($con, "SELECT * FROM usuario WHERE login = ? OR email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $loginOuEmail, $loginOuEmail);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($coluna = mysqli_fetch_array($result)) {
        // Tenta password_verify e, se falhar, compara direto (texto puro)
        if (password_verify($senha, $coluna["senha"]) || $senha === $coluna["senha"]) {
            $_SESSION["usuario_id"] = $coluna["id"];
            $_SESSION["nome_usuario"] = $coluna["login"];
            $_SESSION["UsuarioNivel"] = $coluna["nivel"];

            if ($coluna['nivel'] == "USER") {
                header("Location: menuUser.php");
                exit;
            } elseif ($coluna['nivel'] == "ADM") {
                header("Location: menuAdm.php");
                exit;
            }
        } else {
            echo "<script>alert('Senha incorreta!');</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!');</script>";
    }
}

?>

<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form action="#" method="post">
        <label for="login">Login ou E-mail:</label><br>
        <input type="text" name="login" id="login" required><br>
        <label for="senha">Senha:</label><br>
        <input type="password" name="senha" id="senha" required><br>
        <input type="submit" name="botao" value="Entrar">
    </form>
</body>
</html>
