<?php require 'config.php'; ?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Inventário</title><link rel="stylesheet" href="style.css"></head>
<body>
<main class="wrap">
  <header><h1>Inventário</h1><a class="btn" href="index.php">Adicionar álbum</a></header>
  <section class="grid">
  <?php
    $rows = $pdo->query('SELECT * FROM albums ORDER BY created_at DESC')->fetchAll();
    if (!$rows) { echo '<p>Nenhum álbum cadastrado.</p>'; }
    foreach($rows as $r){
      $track_preview = '';
      if ($r['tracklist']) {
        $arr = json_decode($r['tracklist'], true);
        if (is_array($arr)) {
          $track_preview = '<ol>';
          foreach(array_slice($arr,0,5) as $t) $track_preview .= '<li>'.htmlspecialchars($t).'</li>';
          $track_preview .= '</ol>';
        }
      }
      echo '<article class="card-item">';
      echo '<a href="album.php?id='.intval($r['id']).'">';
      echo '<img src="'.htmlspecialchars($r['cover_url']).'" class="cover">';
      echo '<h3>'.htmlspecialchars($r['title']).'</h3>';
      echo '<p class="muted">'.htmlspecialchars($r['artist']).' • '.htmlspecialchars($r['release_year']).'</p>';
      echo '</a>';
      echo $track_preview;
      echo '</article>';
    }
  ?>
  </section>
</main>
</body>
</html>