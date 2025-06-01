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
    <style>
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #e9ecef;
    height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

form {
    background-color: #fff;
    padding: 30px 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}

form input[type="text"],
form input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 20px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

form input:focus {
    border-color: #007BFF;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
}

form input[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #007BFF;
    border: none;
    border-radius: 6px;
    color: white;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form input[type="submit"]:hover {
    background-color: #0056b3;
}

a.botao-secundario {
    display: inline-block;
    margin-top: 10px;
    padding: 10px;
    background-color: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

a.botao-secundario:hover {
    background-color: #5a6268;
}

    </style>
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

        <!-- Botão para ir ao cadastro -->
        <div style="text-align: center; margin-top: 15px;">
            <a href="cadastrar.php" class="botao-secundario">Não tem conta? Cadastre-se</a>
        </div>
    </form>
</body>
</html>
