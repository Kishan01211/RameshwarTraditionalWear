<?php
require_once '../includes/header.php';
// Load DB for testimonials slider
require_once '../config/db.php';
?>

<!-- Hero Section -->
<section class="hero-section position-relative d-flex align-items-center justify-content-center">
    <div class="hero-bg-image" id="heroBgImage"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content text-center text-white">
        <h1 class="display-4 mb-3">Welcome to Rameshwar Traditional Wear</h1>
        <p class="lead mb-4">Rent Premium Traditional Outfits for Every Occasion</p>
        <a href="products.php" class="btn btn-lg btn-warning fw-bold shadow-lg hero-btn">Browse Collection</a>
    </div>
</section>

<!-- Image Gallery Section -->
<section class="py-5 bg-light" id="homeGallery">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="mb-2">Style Inspiration Gallery</h2>
            <p class="text-muted mb-0">A glimpse of our traditional wear collection</p>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            <?php
            // Define gallery images (use web paths). Any missing image will hide gracefully via onerror handler.
            $gallery = [
                '/rtwrs_web/assets/images/slide1.jpeg',
                '/rtwrs_web/assets/images/slide2.png',
                '/rtwrs_web/assets/images/img3.jpg',
                '/rtwrs_web/assets/images/p1.jpeg',
                '/rtwrs_web/assets/images/p2.jpeg',
                '/rtwrs_web/assets/images/p3.jpeg',
                '/rtwrs_web/assets/images/slide1.jpeg',
                '/rtwrs_web/assets/images/slide2.png'
            ];
            foreach ($gallery as $idx => $src): ?>
                <div class="col">
                    <a href="#" class="d-block gallery-item" data-index="<?= $idx ?>" data-src="<?= htmlspecialchars($src) ?>" title="View larger">
                        <div class="ratio ratio-1x1 overflow-hidden rounded shadow-sm gallery-thumb-wrapper">
                            <img src="<?= htmlspecialchars($src) ?>"
                                 alt="Traditional wear gallery image"
                                 class="w-100 h-100 object-fit-cover gallery-thumb"
                                 onerror="(function(el){var c=el.closest('.col'); if(c){c.style.display='none';} else {el.style.display='none';}})(this)" />
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header border-0">
        <h5 class="modal-title">Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 position-relative">
        <img id="galleryModalImg" src="" alt="Preview" class="w-100 d-block" style="max-height:70vh; object-fit:contain; background:#000;" />
        <button type="button" class="btn btn-light position-absolute top-50 start-0 translate-middle-y ms-2" id="galleryPrev" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button type="button" class="btn btn-light position-absolute top-50 end-0 translate-middle-y me-2" id="galleryNext" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      <div class="modal-footer border-0 justify-content-between">
        <small class="text-muted">Use arrow keys to navigate</small>
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Inline styles specific to gallery (kept minimal) -->
<style>
  .gallery-thumb-wrapper { transition: transform .25s ease, box-shadow .25s ease; }
  .gallery-thumb-wrapper:hover { transform: translateY(-3px); box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.15) !important; }
  .object-fit-cover { object-fit: cover; }
  @media (prefers-reduced-motion: reduce) { .gallery-thumb-wrapper { transition: none; } }
</style>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="mb-2">Our Categories</h2>
            <p class="text-muted">Carefully curated for every occasion</p>
        </div>
        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-md-6 col-lg-4">
                <a href="products.php?category=1" class="category-card d-block text-decoration-none h-100" aria-label="Browse Sherwani category">
                    <div class="cat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h4 class="mb-1">Sherwani</h4>
                    <p class="text-muted mb-3">Elegant sherwanis for weddings and special occasions</p>
                    <span class="cat-cta">Explore <i class="fas fa-arrow-right ms-1"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="products.php?category=2" class="category-card d-block text-decoration-none h-100" aria-label="Browse Kurta category">
                    <div class="cat-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <h4 class="mb-1">Kurta</h4>
                    <p class="text-muted mb-3">Comfortable kurtas for festivals and events</p>
                    <span class="cat-cta">Explore <i class="fas fa-arrow-right ms-1"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="products.php?category=3" class="category-card d-block text-decoration-none h-100" aria-label="Browse Blazer category">
                    <div class="cat-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4 class="mb-1">Blazer</h4>
                    <p class="text-muted mb-3">Stylish blazers for formal occasions</p>
                    <span class="cat-cta">Explore <i class="fas fa-arrow-right ms-1"></i></span>
                </a>
            </div>
        </div>
    </div>
    </section>

<!-- New Arrivals Section (image-only like reference) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="mb-1">New Arrivals</h2>
                <p class="text-muted mb-0">Fresh styles just added</p>
            </div>
            <a href="products.php" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <?php
        // Fetch exactly the latest 6 products with defensive fallbacks on column names
        $newArrivals = [];
        try {
            // Common schema: image_url used by admin add
            $stmt = $pdo->query("SELECT id, product_name, image_url AS image, created_at FROM products ORDER BY created_at DESC LIMIT 6");
            $newArrivals = $stmt->fetchAll();
        } catch (Throwable $e0) {
          try {
            // Variant 1
            $stmt = $pdo->query("SELECT id, product_name, image, created_at FROM products ORDER BY created_at DESC LIMIT 6");
            $newArrivals = $stmt->fetchAll();
          } catch (Throwable $e1) {
            try {
                // Variant 2
                $stmt = $pdo->query("SELECT id, product_name, product_image AS image, added_on AS created_at FROM products ORDER BY id DESC LIMIT 6");
                $newArrivals = $stmt->fetchAll();
            } catch (Throwable $e2) {
                try {
                    // Minimal fallback: return something and use UI fallback image
                    $stmt = $pdo->query("SELECT id, product_name FROM products ORDER BY id DESC LIMIT 6");
                    $newArrivals = $stmt->fetchAll();
                } catch (Throwable $e3) {
                    $newArrivals = [];
                }
            }
          }
        }
        ?>

        <?php if (empty($newArrivals)): ?>
            <div class="text-center text-muted py-4">No products to show yet.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($newArrivals as $idx => $p): 
                    $rawImg = isset($p['image']) ? trim((string)$p['image']) : '';
                    if ($rawImg !== '') {
                        $isAbsolute = str_starts_with($rawImg, '/rtwrs_web/') || str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://');
                        $norm = $isAbsolute ? $rawImg : ('/rtwrs_web/' . ltrim($rawImg, '/'));
                        $img = htmlspecialchars($norm);
                    } else {
                        $img = '/rtwrs_web/assets/images/img3.jpg';
                    }
                ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#" class="d-block new-arrival-item" data-index="<?= $idx ?>" data-src="<?= $img ?>" title="View larger">
                        <div class="ratio ratio-1x1 overflow-hidden rounded new-arrival-thumb">
                            <img src="<?= $img ?>" alt="New arrival image" class="w-100 h-100 new-arrival-img" onerror="(function(el){var c=el.closest('.col-6, .col, [class*=\"col-\"]'); if(c){c.style.display='none';} else {el.style.display='none';}})(this)" />
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    </section>

<!-- New Arrivals Modal (lightbox) -->
<div class="modal fade" id="arrivalsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header border-0">
        <h5 class="modal-title">Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 position-relative">
        <img id="arrivalsModalImg" src="" alt="Preview" class="w-100 d-block" style="max-height:70vh; object-fit:contain; background:#000;" />
        <button type="button" class="btn btn-light position-absolute top-50 start-0 translate-middle-y ms-2" id="arrivalsPrev" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button type="button" class="btn btn-light position-absolute top-50 end-0 translate-middle-y me-2" id="arrivalsNext" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      <div class="modal-footer border-0 justify-content-between">
        <small class="text-muted">Use arrow keys to navigate</small>
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>

<!-- How It Works Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-search fa-3x text-primary mb-3"></i>
                <h4>Browse & Select</h4>
                <p>Choose from our wide collection of traditional wear</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                <h4>Book Dates</h4>
                <p>Select your rental dates and provide details</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-star fa-3x text-primary mb-3"></i>
                <h4>Wear & Enjoy</h4>
                <p>Look great at your special occasion</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section (Dynamic Slider) -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">What Our Customers Say</h2>
        <?php
        $testimonials = [];
        try {
            // Prefer published-only; fallback to all if column isn't available yet
            try {
                $stmt = $pdo->query("SELECT f.id, f.feedback, f.rating, f.image, f.created_at, u.name AS uname
                                      FROM feedback f JOIN users u ON f.user_id=u.id
                                      WHERE f.is_published = 1
                                      ORDER BY f.created_at DESC
                                      LIMIT 10");
                $testimonials = $stmt->fetchAll();
            } catch (Throwable $e) {
                // Column missing, fallback without filter
                $stmt = $pdo->query("SELECT f.id, f.feedback, f.rating, f.image, f.created_at, u.name AS uname
                                      FROM feedback f JOIN users u ON f.user_id=u.id
                                      ORDER BY f.created_at DESC
                                      LIMIT 10");
                $testimonials = $stmt->fetchAll();
            }
        } catch (Throwable $e) {
            $testimonials = [];
        }

        if (!$testimonials): ?>
            <div class="text-center text-muted">
                <i class="fas fa-comment-dots fa-2x mb-2"></i>
                <div>Feedback will appear here once published by admin.</div>
            </div>
        <?php else: ?>
            <div id="feedbackCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3500" data-bs-pause="hover" data-bs-touch="true">
                <div class="carousel-indicators">
                    <?php 
                      $slides = array_chunk($testimonials, 4);
                      foreach ($slides as $i => $_): ?>
                        <button type="button" data-bs-target="#feedbackCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i===0?'active':'' ?>" aria-current="<?= $i===0?'true':'false' ?>" aria-label="Slide <?= $i+1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php 
                      $slideIndex = 0;
                      foreach ($slides as $group): ?>
                        <div class="carousel-item <?= $slideIndex===0?'active':'' ?>">
                          <div class="row g-4">
                            <?php foreach ($group as $t): 
                              $img = $t['image'] ? htmlspecialchars($t['image']) : '/rtwrs_web/assets/images/placeholder.jpg';
                              $name = htmlspecialchars($t['uname']);
                              $rating = max(1, min(5, (int)$t['rating']));
                              $stars = '';
                              for ($i=1; $i<=5; $i++) {
                                  $stars .= $i <= $rating ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>';
                              }
                            ?>
                              <div class="col-12 col-sm-6 col-lg-3">
                                <div class="testimonial-card h-100 p-3 text-center">
                                  <div class="testimonial-image-wrapper mb-3">
                                    <img src="<?= $img ?>" alt="Feedback image" class="testimonial-img">
                                  </div>
                                  <div class="mb-2 star-rating" aria-label="Rating: <?= $rating ?> out of 5"><?php echo $stars; ?></div>
                                  <p class="mb-2">"<?= nl2br(htmlspecialchars($t['feedback'])) ?>"</p>
                                  <strong class="d-block">- <?= $name ?></strong>
                                </div>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                        <?php $slideIndex++; endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#feedbackCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#feedbackCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Link Hero CSS -->
<link rel="stylesheet" href="../assets/css/hero.css">

<!-- Hero Image Slider Script -->
<script>
// Array of hero section images (update paths as needed)
document.addEventListener('DOMContentLoaded', function(){
  const heroImages = [
      "/rtwrs_web/assets/images/slide1.jpeg",
      "/rtwrs_web/assets/images/slide2.png",
      "/rtwrs_web/assets/images/img3.jpg"
  ];

  let heroIndex = 0;
  const heroBg = document.getElementById('heroBgImage');
  if (!heroBg) return; // guard if element not present

  // Preload images for smooth transitions
  heroImages.forEach(src => { const img = new Image(); img.src = src; });

  function setHeroImage(idx) {
      heroBg.style.opacity = 0;
      setTimeout(() => {
          heroBg.style.backgroundImage = `url('${heroImages[idx]}')`;
          heroBg.style.opacity = 1;
      }, 420);
  }

  // Initial image
  setHeroImage(heroIndex);

  // Cycle images every 4 seconds
  setInterval(() => {
      heroIndex = (heroIndex + 1) % heroImages.length;
      setHeroImage(heroIndex);
  }, 4000);
});
</script>

<!-- Gallery Lightbox Script -->
<script>
  (function() {
    function init() {
      const items = Array.from(document.querySelectorAll('#homeGallery .gallery-item'));
      if (!items.length) return;

      const modalEl = document.getElementById('galleryModal');
      const modalImg = document.getElementById('galleryModalImg');
      const prevBtn = document.getElementById('galleryPrev');
      const nextBtn = document.getElementById('galleryNext');
      const modal = new bootstrap.Modal(modalEl);

      let current = 0;

      function show(index) {
        current = (index + items.length) % items.length;
        const src = items[current].dataset.src;
        modalImg.style.opacity = 0;
        // Preload image
        const img = new Image();
        img.onload = () => { modalImg.src = src; modalImg.style.opacity = 1; };
        img.onerror = () => { modalImg.src = ''; };
        img.src = src;
      }

      items.forEach((el, i) => {
        el.addEventListener('click', (e) => {
          e.preventDefault();
          show(i);
          modal.show();
        });
      });

      prevBtn.addEventListener('click', () => show(current - 1));
      nextBtn.addEventListener('click', () => show(current + 1));

      // Keyboard navigation when modal open
      modalEl.addEventListener('shown.bs.modal', () => {
        const keyHandler = (e) => {
          if (e.key === 'ArrowLeft') show(current - 1);
          if (e.key === 'ArrowRight') show(current + 1);
        };
        window.addEventListener('keydown', keyHandler);
        modalEl.addEventListener('hidden.bs.modal', () => window.removeEventListener('keydown', keyHandler), { once: true });
      });
    }
    if (window.bootstrap && window.bootstrap.Modal) {
      init();
    } else {
      window.addEventListener('load', init, { once: true });
    }
  })();
</script>

<!-- New Arrivals Lightbox Script -->
<script>
  (function() {
    function init() {
      const items = Array.from(document.querySelectorAll('.new-arrival-item'));
      if (!items.length) return;

      const modalEl = document.getElementById('arrivalsModal');
      const modalImg = document.getElementById('arrivalsModalImg');
      const prevBtn = document.getElementById('arrivalsPrev');
      const nextBtn = document.getElementById('arrivalsNext');
      const modal = new bootstrap.Modal(modalEl);

      let current = 0;

      function show(index) {
        current = (index + items.length) % items.length;
        const src = items[current].dataset.src;
        modalImg.style.opacity = 0;
        const img = new Image();
        img.onload = () => { modalImg.src = src; modalImg.style.opacity = 1; };
        img.onerror = () => { modalImg.src = ''; };
        img.src = src;
      }

      items.forEach((el, i) => {
        el.addEventListener('click', (e) => {
          e.preventDefault();
          show(i);
          modal.show();
        });
      });

      prevBtn.addEventListener('click', () => show(current - 1));
      nextBtn.addEventListener('click', () => show(current + 1));

      modalEl.addEventListener('shown.bs.modal', () => {
        const keyHandler = (e) => {
          if (e.key === 'ArrowLeft') show(current - 1);
          if (e.key === 'ArrowRight') show(current + 1);
        };
        window.addEventListener('keydown', keyHandler);
        modalEl.addEventListener('hidden.bs.modal', () => window.removeEventListener('keydown', keyHandler), { once: true });
      });
    }
    if (window.bootstrap && window.bootstrap.Modal) {
      init();
    } else {
      window.addEventListener('load', init, { once: true });
    }
  })();
</script>

<?php require_once '../includes/footer.php'; ?>
