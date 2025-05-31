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
<html>
<head>
    <title>Cadastro de Usuário</title>
</head>
<body>
    <h2>Cadastro de Novo Usuário</h2>
    <form method="post">
        <label>Nome completo:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Nome de Usuário (login):</label><br>
        <input type="text" name="login" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <label>Confirmar senha:</label><br>
        <input type="password" name="confirma_senha" required><br><br>

        <input type="submit" value="Cadastrar">
    </form>
</body>
</html>

