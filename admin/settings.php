<?php include "includes/admin-header.php"; ?>
<h2>Settings</h2>
<form method="POST">
  <!-- Dummy settings form. On submit, save to config or settings table -->
  Site Name: <input name="site_name" value="Rameshwar Traditional Wear"><br>
  Admin Email: <input name="admin_email" value="admin@rameshwar.com"><br>
  <button type="submit" class="btn btn-primary">Save</button>
</form>
<?php include "../includes/footer.php"; ?>
