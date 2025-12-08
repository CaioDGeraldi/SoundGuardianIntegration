<?php require 'config.php'; ?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Detalhes do Álbum</title><link rel="stylesheet" href="style.css"></head>
<body>
<main class="wrap">
  <a class="btn" href="inventory.php">← Voltar</a>
  <?php
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $q = $pdo->prepare('SELECT * FROM albums WHERE id = ?');
    $q->execute([$id]);
    $a = $q->fetch();
    if (!$a) { echo '<p>Álbum não encontrado.</p>'; exit; }
  ?>
  <section class="detail">
    <img src="<?php echo htmlspecialchars($a['cover_url']); ?>" class="cover-large">
    <div class="meta">
      <h1><?php echo htmlspecialchars($a['title']); ?></h1>
      <p class="muted"><?php echo htmlspecialchars($a['artist']); ?> • <?php echo htmlspecialchars($a['release_year']); ?></p>
      <p><strong>Gênero</strong> <?php echo htmlspecialchars($a['genre']); ?></p>
      <p><strong>Style</strong> <?php echo htmlspecialchars($a['style']); ?></p>
      <p><strong>Variações</strong> <?php echo htmlspecialchars($a['variations']); ?></p>
    </div>
    <div class="tracks">
      <h2>Tracklist</h2>
      <?php
        $tracks = json_decode($a['tracklist'], true);
        if ($tracks && is_array($tracks)) {
          echo '<ol>';
          foreach ($tracks as $t) echo '<li>'.htmlspecialchars($t).'</li>';
          echo '</ol>';
        } else {
          echo '<p class="muted">Nenhuma tracklist salva.</p>';
        }
      ?>
    </div>
  </section>
</main>
</body>
</html>