<?php
// Conexão com o banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=sistema", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Montar consulta SQL com filtros
$sql = "
    SELECT a.*, 
           (SELECT caminho FROM fotos f WHERE f.anuncio_id = a.id LIMIT 1) AS foto
    FROM anuncio a
    WHERE a.aprovado = 1 AND a.status = 'pendente'
";

$params = [];

if (!empty($_GET['modelo'])) {
    $sql .= " AND a.modelo LIKE :modelo";
    $params[':modelo'] = '%' . $_GET['modelo'] . '%';
}

// Validação para valor não negativo
if (isset($_GET['valor']) && is_numeric($_GET['valor']) && $_GET['valor'] >= 0) {
    $sql .= " AND a.valor <= :valor";
    $params[':valor'] = $_GET['valor'];
}

$sql .= " ORDER BY RAND()";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$anunciosAprovados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Página Principal</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f5f5;
      color: #333;
      padding: 0 20px 40px;
    }

    .cabecalho {
      background-color: #b30000;
      color: white;
      padding: 20px 30px;
      border-radius: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 20px 0;
    }

    .logo {
      font-size: 2em;
      font-weight: bold;
    }

    .botoes a {
      margin-left: 15px;
      background-color: white;
      color: #b30000;
      padding: 10px 18px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      border: 2px solid white;
      transition: all 0.3s ease;
    }

    .botoes a:hover {
      background-color: #fff;
      color: #b30000;
      border-color: #b30000;
    }

    h2 {
      text-align: center;
      margin: 30px 0 20px;
      color: #b30000;
      font-size: 2em;
    }

    form {
      max-width: 800px;
      margin: 0 auto 30px;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      justify-content: center;
    }

    form input, form button {
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 1em;
    }

    form input[type="number"] {
      min: 0; /* Limita no navegador */
    }

    form button {
      background-color: #b30000;
      color: white;
      border: none;
      cursor: pointer;
    }

    .anuncios-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 25px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .anuncio {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .anuncio:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .anuncio img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-bottom: 3px solid #b30000;
    }

    .anuncio h3 {
      color: #b30000;
      font-size: 1.2em;
      margin: 12px 15px 5px;
    }

    .anuncio p {
      margin: 5px 15px 10px;
      font-size: 0.95em;
      color: #444;
    }

    @media (max-width: 600px) {
      .cabecalho {
        flex-direction: column;
        text-align: center;
        gap: 10px;
      }

      .botoes {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
      }

      form {
        flex-direction: column;
      }
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

  <h2>Anúncios de Carros</h2>

  <form method="GET">
    <input type="text" name="modelo" placeholder="Pesquisar por modelo" value="<?= isset($_GET['modelo']) ? htmlspecialchars($_GET['modelo']) : '' ?>">
    <input type="number" step="0.01" min="0" name="valor" placeholder="Valor máximo" value="<?= isset($_GET['valor']) ? htmlspecialchars($_GET['valor']) : '' ?>">
    <button type="submit">Pesquisar</button>
  </form>

  <div class="anuncios-container">
    <?php foreach ($anunciosAprovados as $anuncio): ?>
      <?php
        $foto = !empty($anuncio['foto']) && file_exists($anuncio['foto']) 
          ? htmlspecialchars($anuncio['foto']) 
          : 'img/sem-foto.jpg';
      ?>
      <a href="#" onclick="imprimirAnuncio(this)" style="text-decoration: none; color: inherit;">
        <div class="anuncio">
          <img src="<?= $foto ?>" alt="Foto do carro">
          <h3><?= htmlspecialchars($anuncio['modelo']) ?></h3>
          <p><strong>Valor:</strong> R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?></p>
          <p><strong>Cidade:</strong> <?= htmlspecialchars($anuncio['cidade']) ?></p>
          <p><strong>Cor:</strong> <?= htmlspecialchars($anuncio['cor']) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <script>
    function imprimirAnuncio(el) {
      const anuncio = el.querySelector('.anuncio').cloneNode(true);
      const win = window.open('', '_blank');
      win.document.write('<html><head><title>Imprimir Anúncio</title>');
      win.document.write('<style>body{font-family:sans-serif;padding:20px} img{max-width:100%;height:auto} .anuncio{max-width:600px;margin:auto;border:1px solid #ccc;padding:15px;border-radius:10px;}</style>');
      win.document.write('</head><body>');
      win.document.body.appendChild(anuncio);
      win.document.write('</body></html>');
      win.document.close();
      win.focus();
      win.print();
    }
  </script>
</body>
</html>
