<?php include "includes/admin-header.php"; include "../config/db.php";
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?"); $stmt->execute([$id]);
if(!$p = $stmt->fetch()) die("Product not found");

if($_SERVER['REQUEST_METHOD']=='POST'){
    // Image upload (optional)
    $imgName = $p['image_url'];
    if(!empty($_FILES['image']['name'])){
        $up = '../assets/images/'.basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $up);
        $imgName = "assets/images/".basename($_FILES['image']['name']);
    }
    // Normalize size and color from checkbox arrays
    $sizeInput  = $_POST['size']  ?? [];
    $colorInput = $_POST['color'] ?? [];
    $sizeValue  = is_array($sizeInput)  ? implode(',', array_map('trim', $sizeInput))  : trim((string)$sizeInput);
    $colorValue = is_array($colorInput) ? implode(',', array_map('trim', $colorInput)) : trim((string)$colorInput);
    $stmt = $pdo->prepare("UPDATE products SET product_number=?,category_id=?,product_name=?,description=?,image_url=?,size=?,color=?,price_per_day=?,quantity_available=?,status=? WHERE id=?");
    $stmt->execute([
        $_POST['product_number'], $_POST['category_id'], $_POST['product_name'], $_POST['description'], $imgName,
        $sizeValue, $colorValue, $_POST['price_per_day'],
        $_POST['quantity_available'], $_POST['status'], $id
    ]);
    echo "<div class='alert alert-success'>Updated!</div>";
}
?>
<h2>Edit Product</h2>
<div class="card" style="max-width:700px;margin:auto;">
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Product Number:</label>
        <input name="product_number" value="<?=htmlspecialchars($p['product_number'])?>", class="form-control" required>
        <small class="text-muted">Unique product identifier</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Category:</label>
        <select name="category_id" class="form-select">
          <?php foreach($pdo->query("SELECT id,name FROM categories") as $c){
            $sel = ($c['id']==$p['category_id'])?'selected':'';
            echo "<option value='{$c['id']}' $sel>{$c['name']}</option>"; } ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Name:</label>
        <input name="product_name" value="<?=htmlspecialchars($p['product_name'])?>", class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description:</label>
        <textarea name="description" class="form-control"><?=htmlspecialchars($p['description'])?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Image:</label>
        <input type="file" name="image" class="form-control"> <?php $images = array_map(function($img) {
    $img = ltrim(trim($img), '.');
    if (strpos($img, '/rtwrs_web/') !== 0) {
        $img = '/rtwrs_web/' . ltrim($img, '/');
    }
    return $img;
}, explode(',', $p['image_url'])); ?>
<small class="d-block mt-2">Current Images:</small>
        <div class="row mt-2">
          <?php foreach($images as $img): ?>
            <div class="col-md-4 mb-2">
              <img src="<?= htmlspecialchars($img) ?>" alt="Product Image" class="img-fluid rounded border" style="width:100%; height:auto; max-height:200px; object-fit:contain;">
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Size:</label>
        <div class="d-flex flex-wrap gap-3">
          <?php 
            $sizes = ["XS","S","M","L","XL","XXL","3XL"]; 
            $selectedSizes = array_filter(array_map('trim', explode(',', (string)$p['size'])));
            foreach ($sizes as $s): 
              $checked = in_array($s, $selectedSizes, true) ? 'checked' : '';
          ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="size[]" id="size_<?= htmlspecialchars($s) ?>" value="<?= htmlspecialchars($s) ?>" <?=$checked?>>
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
            $selectedColors = array_filter(array_map('trim', explode(',', (string)$p['color'])));
            foreach ($colors as $c): 
              $style = isset($colorHex[$c])
                ? (str_starts_with($colorHex[$c], 'linear')
                    ? 'background: '.$colorHex[$c].';'
                    : 'background-color: '.$colorHex[$c].';')
                : 'background-color: '.htmlspecialchars($c).';';
              $checked = in_array($c, $selectedColors, true) ? 'checked' : '';
          ?>
            <div class="form-check d-flex align-items-center gap-2">
              <input class="form-check-input" type="checkbox" name="color[]" id="color_<?= htmlspecialchars($c) ?>" value="<?= htmlspecialchars($c) ?>" <?=$checked?>>
              <span aria-hidden="true" style="display:inline-block;width:18px;height:18px;border-radius:50%;border:1px solid #ccc;<?= $style ?>"></span>
              <label class="form-check-label" for="color_<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <small class="text-muted">Select one or more colors.</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Price/Day:</label>
        <input type="number" name="price_per_day" value="<?=$p['price_per_day']?>" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Qty:</label>
        <input type="number" name="quantity_available" value="<?=$p['quantity_available']?>" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Status:</label>
        <select name="status" class="form-select">
          <option value="active" <?=$p['status']=='active'?'selected':''?>>Active</option>
          <option value="inactive" <?=$p['status']=='inactive'?'selected':''?>>Inactive</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Update</button>
    </form>
  </div>
</div>

