<?php
// Start session first
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Check if user is logged in BEFORE including header
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=rent.php');
    exit;
}

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Include files after all header redirects are done
require_once '../includes/header.php';
require_once '../config/db.php';

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active' AND quantity_available > 0");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$images = array_filter(array_map(function($img) {
    $img = ltrim(trim($img), '.');
    if (strpos($img, '/rtwrs_web/') !== 0) {
        $img = '/rtwrs_web/' . ltrim($img, '/');
    }
    // Check if file exists, else skip
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $img;
    return (file_exists($filePath) && is_file($filePath)) ? $img : null;
}, explode(',', $product['image_url'])));
if (empty($images)) {
    $images[] = '/rtwrs_web/assets/images/placeholder.jpg'; // fallback placeholder
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="booking-form">
                <h2 class="mb-4">Book: <?= htmlspecialchars($product['product_name']) ?></h2>

                <div class="mb-3">
                    <div class="product-image-container">
                        <img id="mainProductImage" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="img-fluid rounded" style="width:100%; height:auto; max-height:400px; object-fit:contain; border:1px solid #ddd;">
                    </div>
                    <?php if(count($images) > 1): ?>
                        <div class="row mt-3">
                            <?php foreach($images as $index => $img): ?>
                                <div class="col-3 mb-2">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="img-fluid rounded thumbnail-image" style="width:100%; height:auto; max-height:80px; object-fit:contain; border:2px solid <?= $index === 0 ? '#007bff' : '#ddd' ?>; cursor:pointer;" onclick="changeMainImage('<?= htmlspecialchars($img) ?>', this)">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <h4 class="price-tag">₹<?= $product['price_per_day'] ?>/day</h4>
                    <p><strong>Available Sizes:</strong> <?= htmlspecialchars($product['size']) ?></p>
                    <p><strong>Available Colors:</strong> <?= htmlspecialchars($product['color']) ?></p>
                    <p><strong>Stock:</strong> <?= $product['quantity_available'] ?> available</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="booking-form">
                <h3 class="mb-4">Rental Details</h3>
                <div class="mb-3">
                    <p class="text-muted mb-1">Choose rental options. Payment will be completed on the checkout page.</p>
                </div>

                <form id="rentalMiniForm">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="product_price" value="<?= htmlspecialchars($product['price_per_day']) ?>">

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Size</label>
                            <select class="form-select" id="selected_size" required>
                                <option value="">Choose size...</option>
                                <?php foreach (explode(',', (string)$product['size']) as $size): $size = trim($size); if($size==='') continue; ?>
                                  <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <select class="form-select" id="selected_color" required>
                                <option value="">Choose color...</option>
                                <?php foreach (explode(',', (string)$product['color']) as $color): $color = trim($color); if($color==='') continue; ?>
                                  <option value="<?= htmlspecialchars($color) ?>"><?= htmlspecialchars($color) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <button type="button" id="btnAddToCart" class="btn btn-outline-secondary flex-fill">
                            <i class="fas fa-cart-plus me-1"></i> Add to Cart
                        </button>
                        <button type="button" id="btnBookNow" class="btn btn-primary flex-fill">
                            <i class="fas fa-bolt me-1"></i> Book Now
                        </button>
                    </div>
                </form>

                <!-- Hidden form posts to product-add.php to keep existing logic -->
                <form id="cartForm" method="POST" action="product-add.php" class="d-none">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="product_price" value="<?= htmlspecialchars($product['price_per_day']) ?>">
                    <input type="hidden" name="selected_size" id="cart_selected_size" value="">
                    <input type="hidden" name="selected_color" id="cart_selected_color" value="">
                    <input type="hidden" name="start_date" id="cart_start_date" value="">
                    <input type="hidden" name="end_date" id="cart_end_date" value="">
                    <input type="hidden" name="book_now" id="cart_book_now" value="">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Image Lightbox -->
<div id="imageLightbox" class="lightbox-overlay" aria-hidden="true" role="dialog" aria-label="Image viewer">
  <button class="lightbox-close" aria-label="Close image viewer">&times;</button>
  <div class="lightbox-content">
    <img id="lightboxImg" src="" alt="Zoomed product image">
  </div>
  <div class="lightbox-controls">
    <button id="zoomOutBtn" class="btn btn-light" aria-label="Zoom out">-</button>
    <button id="zoomResetBtn" class="btn btn-light" aria-label="Reset zoom">100%</button>
    <button id="zoomInBtn" class="btn btn-light" aria-label="Zoom in">+</button>
  </div>
  <div class="lightbox-hint">Scroll to zoom • Drag to pan • Esc to close</div>
  <div class="lightbox-backdrop"></div>
</div>

<?php require_once '../includes/footer.php'; ?>

<style>
/* Image zoom styles (page-local) */
.product-image-container { position: relative; }
.img-zoom-lens {
  position: absolute;
  display: none;
  width: 200px; /* larger, box lens */
  height: 200px;
  border: 2px solid rgba(0,0,0,.2);
  border-radius: 6px; /* square-ish edges */
  box-shadow: 0 4px 18px rgba(0,0,0,.15);
  background-repeat: no-repeat;
  background-position: center;
  background-size: 200% 200%; /* 2x zoom */
  pointer-events: none;
  z-index: 3;
}
@media (max-width: 575.98px) {
  .img-zoom-lens { width: 140px; height: 140px; }
}

/* Lightbox */
.lightbox-overlay { position: fixed; inset: 0; display: none; z-index: 1060; }
.lightbox-overlay.active { display: block; }
.lightbox-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.8); }
.lightbox-content { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.lightbox-content img { max-width: none; max-height: none; transform-origin: center center; cursor: grab; user-select: none; -webkit-user-drag: none; }
.lightbox-close { position: absolute; top: 16px; right: 16px; font-size: 32px; line-height: 1; border: 0; background: transparent; color: #fff; z-index: 2; }
.lightbox-controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 2; }
.lightbox-controls .btn { padding: 6px 10px; }
.lightbox-hint { position: absolute; bottom: 60px; left: 50%; transform: translateX(-50%); color: #ddd; font-size: 12px; z-index: 2; }
</style>

<script>
function changeMainImage(imageSrc, thumbnailElement) {
    // Update main image
    document.getElementById('mainProductImage').src = imageSrc;
    // Update lens background when image changes
    const lens = document.getElementById('imgLens');
    if (lens) lens.style.backgroundImage = `url('${imageSrc}')`;
    // If lightbox is open, update too
    const lb = document.getElementById('imageLightbox');
    const lbImg = document.getElementById('lightboxImg');
    if (lb && lb.classList.contains('active') && lbImg) {
      lbImg.src = imageSrc;
    }
    
    // Update thumbnail borders
    document.querySelectorAll('.thumbnail-image').forEach(img => {
        img.style.border = '2px solid #ddd';
    });
    
    // Highlight selected thumbnail
    if (thumbnailElement) {
        thumbnailElement.style.border = '2px solid #007bff';
    }
}

// Wire up Add to Cart / Book Now using existing cart flow
function fillCartForm(bookNow) {
  const sz = document.getElementById('selected_size').value;
  const cl = document.getElementById('selected_color').value;
  const sd = document.getElementById('start_date').value;
  const ed = document.getElementById('end_date').value;
  if (!sd || !ed || !sz || !cl) {
    alert('Please select start date, end date, size and color.');
    return false;
  }
  document.getElementById('cart_selected_size').value = sz;
  document.getElementById('cart_selected_color').value = cl;
  document.getElementById('cart_start_date').value = sd;
  document.getElementById('cart_end_date').value = ed;
  document.getElementById('cart_book_now').value = bookNow ? '1' : '';
  return true;
}

document.getElementById('btnAddToCart').addEventListener('click', function(){
  if (fillCartForm(false)) document.getElementById('cartForm').submit();
});
document.getElementById('btnBookNow').addEventListener('click', function(){
  if (fillCartForm(true)) document.getElementById('cartForm').submit();
});

// Simple magnifier lens for main product image
(function(){
  const container = document.querySelector('.product-image-container');
  const img = document.getElementById('mainProductImage');
  if (!container || !img) return;

  // Create lens element
  const lens = document.createElement('div');
  lens.id = 'imgLens';
  lens.className = 'img-zoom-lens';
  container.appendChild(lens);

  const zoom = 2; // 2x
  let iw = 0, ih = 0;

  function setBackground() {
    lens.style.backgroundImage = `url('${img.src}')`;
    // Ensure the natural size is used to compute background-size accurately
    iw = img.naturalWidth || img.width;
    ih = img.naturalHeight || img.height;
    lens.style.backgroundSize = `${iw*zoom}px ${ih*zoom}px`;
  }

  function getPos(e){
    const rect = container.getBoundingClientRect();
    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
    return { x, y, rect };
  }

  function move(e){
    e.preventDefault();
    const { x, y, rect } = getPos(e);
    const lw = lens.offsetWidth;
    const lh = lens.offsetHeight;
    let lx = x - lw/2;
    let ly = y - lh/2;
    // Constrain within container
    let cx = Math.max(0, Math.min(lx, rect.width - lw));
    let cy = Math.max(0, Math.min(ly, rect.height - lh));
    lens.style.left = cx + 'px';
    lens.style.top  = cy + 'px';

    // Background position (scale coordinates by zoom and offset by lens center)
    const bx = -((cx + lw/2) * (iw/rect.width) * zoom - lw/2);
    const by = -((cy + lh/2) * (ih/rect.height) * zoom - lh/2);
    lens.style.backgroundPosition = `${bx}px ${by}px`;
  }

  function show(){ lens.style.display = 'block'; }
  function hide(){ lens.style.display = 'none'; }

  function bind(){
    setBackground();
    container.addEventListener('mousemove', move, { passive: false });
    container.addEventListener('mouseenter', show);
    container.addEventListener('mouseleave', hide);
    container.addEventListener('touchstart', function(e){ show(); move(e); }, { passive: false });
    container.addEventListener('touchmove', move, { passive: false });
    container.addEventListener('touchend', hide);
  }

  if (img.complete) {
    bind();
  } else {
    img.addEventListener('load', bind);
  }

  // Also update when thumbnails change the image
  img.addEventListener('load', setBackground);
})();

// Fullscreen lightbox with zoom/pan
(function(){
  const openOnClick = true;
  const mainImg = document.getElementById('mainProductImage');
  const overlay = document.getElementById('imageLightbox');
  const lbImg   = document.getElementById('lightboxImg');
  const btnIn   = document.getElementById('zoomInBtn');
  const btnOut  = document.getElementById('zoomOutBtn');
  const btnReset= document.getElementById('zoomResetBtn');
  const btnClose= document.querySelector('.lightbox-close');
  const backdrop= document.querySelector('.lightbox-backdrop');
  if (!mainImg || !overlay || !lbImg) return;

  let scale = 1, minScale = 1, maxScale = 4;
  let pos = { x: 0, y: 0 };
  let isDown = false, start = { x: 0, y: 0 }, imgStart = { x: 0, y: 0 };

  function applyTransform(){
    lbImg.style.transform = `translate(${pos.x}px, ${pos.y}px) scale(${scale})`;
  }
  function reset(){ scale = 1; pos = {x:0,y:0}; applyTransform(); }
  function zoom(delta, cx, cy){
    const prev = scale;
    scale = Math.min(maxScale, Math.max(minScale, scale + delta));
    // Zoom towards cursor: adjust translate to keep (cx,cy) stable
    const rect = lbImg.getBoundingClientRect();
    const ox = cx - (rect.left + rect.width/2);
    const oy = cy - (rect.top + rect.height/2);
    pos.x -= (ox/prev)*(scale-prev);
    pos.y -= (oy/prev)*(scale-prev);
    applyTransform();
  }
  function open(){
    lbImg.src = mainImg.src;
    reset();
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden','false');
  }
  function close(){
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden','true');
  }

  if (openOnClick) mainImg.addEventListener('click', open);
  btnClose.addEventListener('click', close);
  backdrop.addEventListener('click', close);
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') close(); });

  // Wheel zoom
  overlay.addEventListener('wheel', (e)=>{
    e.preventDefault();
    const delta = e.deltaY < 0 ? 0.1 : -0.1;
    zoom(delta, e.clientX, e.clientY);
  }, { passive:false });

  // Drag to pan
  lbImg.addEventListener('mousedown', (e)=>{ isDown=true; lbImg.style.cursor='grabbing'; start={x:e.clientX,y:e.clientY}; imgStart={...pos}; });
  window.addEventListener('mousemove', (e)=>{ if(!isDown) return; pos.x = imgStart.x + (e.clientX-start.x); pos.y = imgStart.y + (e.clientY-start.y); applyTransform(); });
  window.addEventListener('mouseup', ()=>{ isDown=false; lbImg.style.cursor='grab'; });

  // Touch pan/zoom (pinch – simplified: 2-finger to zoom around center)
  let lastDist = null;
  overlay.addEventListener('touchstart', (e)=>{ if(e.touches.length===1){ start={x:e.touches[0].clientX,y:e.touches[0].clientY}; imgStart={...pos}; } lastDist=null; }, {passive:false});
  overlay.addEventListener('touchmove', (e)=>{
    if (e.touches.length===1){
      pos.x = imgStart.x + (e.touches[0].clientX-start.x);
      pos.y = imgStart.y + (e.touches[0].clientY-start.y);
      applyTransform();
    } else if (e.touches.length===2){
      const dx = e.touches[0].clientX - e.touches[1].clientX;
      const dy = e.touches[0].clientY - e.touches[1].clientY;
      const dist = Math.hypot(dx, dy);
      if (lastDist){
        const delta = (dist - lastDist) / 200; // sensitivity
        const cx = (e.touches[0].clientX + e.touches[1].clientX)/2;
        const cy = (e.touches[0].clientY + e.touches[1].clientY)/2;
        zoom(delta, cx, cy);
      }
      lastDist = dist;
    }
    e.preventDefault();
  }, {passive:false});

  // Buttons
  btnIn.addEventListener('click', ()=> zoom(+0.2, window.innerWidth/2, window.innerHeight/2));
  btnOut.addEventListener('click', ()=> zoom(-0.2, window.innerWidth/2, window.innerHeight/2));
  btnReset.addEventListener('click', reset);
})();
</script>
