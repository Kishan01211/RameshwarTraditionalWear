<?php
// Start session and enforce admin auth BEFORE any output
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// DB connection
include "../config/db.php";
if($_SERVER['REQUEST_METHOD']=='POST'){
    // Duplicate check first (by product_number OR same name within category)
    $productNumber = trim((string)($_POST['product_number'] ?? ''));
    $duplicate = false;
    try {
        $dup = $pdo->prepare("SELECT id FROM products WHERE product_number = ? LIMIT 1");
        $dup->execute([$productNumber]);
        if ($dup->fetch()) {
            $duplicate = true;
            echo "<div class='alert alert-danger'>Duplicate product: this product number already exists.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error checking duplicates: " . htmlspecialchars($e->getMessage()) . "</div>";
        $duplicate = true; // fail-safe: block insert on error
    }

    if (!$duplicate) {
    // Handle image upload (single for demo; for multiple, use array and loop)
    $imgName = '';
    if(!empty($_FILES['image']['name'])){
        $up = '../assets/images/'.basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $up);
        $imgName = "assets/images/".basename($_FILES['image']['name']);
        // Always store as assets/images/...
    }
    // When displaying, always normalize to webroot-relative:
    $displayImg = $imgName ? ('/rtwrs_web/' . ltrim($imgName, '/')) : '';

    // Normalize size and color (support multi-select)
    $sizeInput  = $_POST['size']  ?? [];
    $colorInput = $_POST['color'] ?? [];
    $sizeValue  = is_array($sizeInput)  ? implode(',', array_map('trim', $sizeInput))  : trim((string)$sizeInput);
    $colorValue = is_array($colorInput) ? implode(',', array_map('trim', $colorInput)) : trim((string)$colorInput);

    $stmt = $pdo->prepare("INSERT INTO products 
    (product_number, category_id, product_name, description, image_url, size, color, price_per_day, quantity_available, status)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $_POST['product_number'], $_POST['category_id'], $_POST['product_name'], $_POST['description'],
        $imgName, $sizeValue, $colorValue,
        $_POST['price_per_day'], $_POST['quantity_available'], 'active'
    ]);
    header('Location: products-add.php?added=1');
    exit;
    }
}
?>
<?php include "includes/admin-header.php"; ?>
<?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert" style="max-width:700px;margin:16px auto 0;">
    <i class="fas fa-check-circle me-2"></i>Product added successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<h2>Add Product</h2>
<div class="card" style="max-width:700px;margin:auto;">
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Product Number:</label>
        <input name="product_number" class="form-control" placeholder="e.g. PRD001" required>
        <small class="text-muted">Unique product identifier (e.g. PRD001, PRD002)</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Category:</label>
        <select name="category_id" class="form-select">
          <?php foreach($pdo->query("SELECT id,name FROM categories") as $c)
            echo "<option value='{$c['id']}'>{$c['name']}</option>"; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Name:</label>
        <input name="product_name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description:</label>
        <textarea name="description" class="form-control"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Image:</label>
        <input type="file" name="image" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Size:</label>
        <div class="d-flex flex-wrap gap-3">
          <?php $sizes = ["XS","S","M","L","XL","XXL","3XL"]; foreach ($sizes as $s): ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="size[]" id="size_<?= htmlspecialchars($s) ?>" value="<?= htmlspecialchars($s) ?>">
              <label class="form-check-label" for="size_<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <small class="text-muted">Select one or more sizes.</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Color:</label>
        <div class="d-flex flex-wrap gap-3">
          <?php 
          $colors = [
            "Black","White","Red","Blue","Green","Yellow","Pink","Purple","Orange","Brown",
            "Grey","Gold","Silver","Maroon","Navy","Beige","Multicolor"
          ];
          $colorHex = [
            'Black'=>'#000000','White'=>'#FFFFFF','Red'=>'#FF0000','Blue'=>'#0d6efd','Green'=>'#198754','Yellow'=>'#FFC107',
            'Pink'=>'#FFC0CB','Purple'=>'#6f42c1','Orange'=>'#FD7E14','Brown'=>'#8B4513','Grey'=>'#6c757d','Gold'=>'#D4AF37',
            'Silver'=>'#C0C0C0','Maroon'=>'#800000','Navy'=>'#001F3F','Beige'=>'#F5F5DC','Multicolor'=>'linear-gradient(45deg, red, orange, yellow, green, blue, indigo, violet)'
          ];
          foreach ($colors as $c): 
            $style = isset($colorHex[$c])
              ? (str_starts_with($colorHex[$c], 'linear')
                  ? 'background: '.$colorHex[$c].';'
                  : 'background-color: '.$colorHex[$c].';')
              : 'background-color: '.htmlspecialchars($c).';';
          ?>
            <div class="form-check d-flex align-items-center gap-2">
              <input class="form-check-input" type="checkbox" name="color[]" id="color_<?= htmlspecialchars($c) ?>" value="<?= htmlspecialchars($c) ?>">
              <span aria-hidden="true" style="display:inline-block;width:18px;height:18px;border-radius:50%;border:1px solid #ccc;<?= $style ?>"></span>
              <label class="form-check-label" for="color_<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <small class="text-muted">Select one or more colors.</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Price/Day:</label>
        <input type="number" name="price_per_day" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Qty:</label>
        <input type="number" name="quantity_available" class="form-control">
      </div>
      <button type="submit" class="btn btn-success">Add Product</button>
    </form>
  </div>
</div>
<?php include "includes/admin-footer.php"; ?>
