<?php
// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema";

$conn = new mysqli($host, $user, $pass, $db);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $login = trim($_POST["login"]);
    $senha = $_POST["senha"];
    $confirma_senha = $_POST["confirma_senha"];
    $nivel = 'USER';

    // Verifica se as senhas coincidem
    if ($senha !== $confirma_senha) {
        echo "<script>alert('As senhas não coincidem.');</script>";
    } else {
        // Verifica se o login já existe
        $stmt = $conn->prepare("SELECT id FROM usuario WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Esse nome de usuário já está em uso. Escolha outro.');</script>";
        } else {
            // Criptografa a senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Insere o novo usuário
            $stmt = $conn->prepare("INSERT INTO usuario (nome, email, login, senha, nivel) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $email, $login, $senha_hash, $nivel);

            if ($stmt->execute()) {
                echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Erro ao cadastrar usuário.');</script>";
            }
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Usuário</title>
    <style>
            body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #fff;
        height: 100vh;
        margin: 0;
        color: #000;

        display: flex;
        justify-content: center; /* centraliza horizontalmente */
        align-items: center;     /* centraliza verticalmente */
    }

    .container {
        width: 100%;
        max-width: 400px;
        /* Aqui garante que o título fique em cima do form */
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }

    .titulo {
        color: #c40000;
        text-align: center;
        margin-bottom: 25px;
    }

    form {
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1.5px solid #c40000;
    }

    label {
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
        color: #000;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 18px;
        border: 1.5px solid #333;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    input:focus {
        border-color: #c40000;
        outline: none;
        box-shadow: 0 0 5px rgba(196, 0, 0, 0.25);
    }

    input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #c40000;
        border: none;
        border-radius: 6px;
        color: #fff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
        background-color: #900000;
    }

    </style>
</head>
<body>
    <div class="container">
        <h2 class="titulo">Cadastro de Novo Usuário</h2>
        <form method="post">
            <label>Nome completo:</label>
            <input type="text" name="nome" required>

            <label>E-mail:</label>
            <input type="email" name="email" required>

            <label>Nome de Usuário (login):</label>
            <input type="text" name="login" required>

            <label>Senha:</label>
            <input type="password" name="senha" required>

            <label>Confirmar senha:</label>
            <input type="password" name="confirma_senha" required>

            <input type="submit" value="Cadastrar">
        </form>
    </div>
</body>
</html>

