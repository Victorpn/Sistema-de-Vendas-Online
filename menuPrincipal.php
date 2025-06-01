<?php
// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buscar anúncios aprovados de forma aleatória com uma imagem
$stmt = $pdo->prepare("
    SELECT a.*, 
           (SELECT caminho FROM fotos f WHERE f.anuncio_id = a.id LIMIT 1) AS foto
    FROM anuncio a
    WHERE a.aprovado = 1 AND a.status = 'pendente'
    ORDER BY RAND()
");
$stmt->execute();
$anunciosAprovados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Página Principal</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* Reset básico */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Corpo da página */
body {
  font-family: Arial, sans-serif;
  background-color: #fff;
  padding: 20px;
  color: #000;
}

/* Cabeçalho */
.cabecalho {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #b30000; /* vermelho escuro */
  padding: 20px;
  color: white;
  border-radius: 8px;
}

.logo {
  display: flex;
  align-items: end  ;
  font-size: 1.5em;
  font-weight: bold;
}

/* Navegação */
.botoes a {
  margin-left: 15px;
  background-color: white;
  color: #b30000;
  padding: 8px 12px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: bold;
  border: 2px solid #b30000;
  transition: all 0.3s;
}

.botoes a:hover {
  background-color: #b30000;
  color: white;
}

/* Título */
h2 {
  margin-top: 30px;
  margin-bottom: 20px;
  font-size: 1.8em;
  text-align: center;
  color: #b30000;
}

/* Container de anúncios com máximo de 5 por linha */
.anuncios-container {
  display: flex;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

/* Cartão do anúncio */
.anuncio {
  background-color: #000;
  color: #fff;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s, box-shadow 0.2s;
}

.anuncio:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Imagem do anúncio */
.anuncio img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  margin-bottom: 10px;
  border-radius: 5px;
  border: 2px solid #b30000;
}

/* Título do carro */
.anuncio h3 {
  margin: 0 0 8px;
  color: #ff4d4d;
}

/* Informações adicionais */
.anuncio p {
  margin: 4px 0;
  font-size: 0.95em;
}

  </style>
</head>
<body>
  <header class="cabecalho">
    <div class="logo">AnúnciosOnline</div>
    <nav class="botoes">
      <a href="login.php" class="btn login">Login</a>
      <a href="cadastrar.php">Cadastrar</a>
    </nav>
  </header>
  <div>
    <h2>Anúncios de Carros</h2> 
  </div>
  
  <div class="anuncios-container">
    <?php foreach ($anunciosAprovados as $anuncio): ?>
      <div class="anuncio">
        
        <img src="<?= htmlspecialchars($anuncio['foto']) ?>" >

        <?php if (!empty($anuncio['foto']) && file_exists($anuncio['foto'])): ?>
          <img src="uploads/  <?= htmlspecialchars($anuncio['foto']) ?>" alt="Foto do carro">
        <?php else: ?>
          <img src="img/sem-foto.jpg" alt="Sem foto">
        <?php endif; ?>
        <h3><?= htmlspecialchars($anuncio['modelo']) ?></h3>
        <p><strong>Valor:</strong> R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?></p>
        <p><strong>Cidade:</strong> <?= htmlspecialchars($anuncio['cidade']) ?></p>
        <p><strong>Cor:</strong> <?= htmlspecialchars($anuncio['cor']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
