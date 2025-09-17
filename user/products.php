<?php
require_once '../includes/header.php';
require_once '../config/db.php';
?>
<link rel="stylesheet" href="../assets/css/products.css">


<div class="container">
    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="filter-section">
                <h5 class="mb-3">Filters</h5>
                <form id="filterForm">
                    <input type="hidden" name="type" value="products">


                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <div id="categories"></div>
                    </div>


                    <!-- Price Range Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price Range (₹/day)</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" id="minPrice" name="minPrice" class="form-control" placeholder="Min" min="0">
                            </div>
                            <div class="col-6">
                                <input type="number" id="maxPrice" name="maxPrice" class="form-control" placeholder="Max" min="0">
                            </div>
                        </div>
                    </div>


                    <!-- Size Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Size</label>
                        <div id="sizes"></div>
                    </div>


                    <!-- Color Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Color</label>
                        <div id="colors"></div>
                    </div>


                    <button type="submit" class="btn btn-primary w-100 mb-2">Apply Filters</button>
                    <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">Clear All</button>
                </form>
            </div>
        </div>


        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Traditional Wear Collection</h2>
                <div class="d-flex align-items-center">
                    <span class="me-2">Sort by:</span>
                    <select class="form-select" id="sortBy" name="sort" style="width: auto;">
                        <option value="newest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>


            <div id="products" class="row"></div>


            <div id="noProductsMsg" class="no-products d-none">
                <i class="fas fa-search"></i>
                <h4>No products found</h4>
                <p>Try adjusting your filters or search criteria</p>
            </div>
        </div>
    </div>
</div>


<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                    <div class="carousel-inner" id="carouselInner"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#modalCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#modalCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <div id="modalDesc"></div>
            </div>
        </div>
    </div>
</div>


<script src="../assets/js/filters.js"></script>
<?php require_once '../includes/footer.php'; ?>