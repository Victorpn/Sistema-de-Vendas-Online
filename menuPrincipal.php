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
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      padding: 20px;
    }

    .cabecalho {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #007BFF;
      padding: 20px;
      color: white;
    }

    .botoes a {
      margin-left: 15px;
      background-color: white;
      color: #007BFF;
      padding: 8px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: bold;
    }

    .anuncios-container {
      margin-top: 30px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .anuncio {
      background-color: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }

    .anuncio img {
      max-width: 100%;
      height: 150px;
      object-fit: cover;
      margin-bottom: 10px;
      border-radius: 5px;
    }

    .anuncio h3 {
      margin: 0 0 5px;
    }

    .anuncio p {
      margin: 5px 0;
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

  <h2>Anúncios de Carros Aprovados</h2>
  <div class="anuncios-container">
    <?php foreach ($anunciosAprovados as $anuncio): ?>
      <div class="anuncio">
        
        <img src="<?= htmlspecialchars($anuncio['foto']) ?>" alt="Foto do carro">

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
