<?php include "includes/admin-header.php"; include "../config/db.php"; ?>
<h2>Notifications</h2>
<ul>
<?php foreach($pdo->query("SELECT * FROM admin_notifications ORDER BY created_at DESC") as $n): ?>
  <li>
      <b><?=htmlspecialchars($n['title'])?></b> (<?=htmlspecialchars($n['type'])?>)
      <br><?=htmlspecialchars($n['message'])?>
      <span style="color:#888">(<?=$n['created_at']?>)</span>
  </li>
<?php endforeach; ?>
</ul>
<?php include "../includes/footer.php"; ?>
