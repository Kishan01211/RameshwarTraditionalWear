// Dynamic filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    fetchCategories();
    fetchSizes();
    fetchColors();
    fetchPriceRange();
    loadProducts();

    // Filter form submission
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadProducts();
    });

    // Category change event
    document.addEventListener('change', function(e) {
        if (e.target.name === 'category') {
            fetchColors(e.target.value);
        }
    });

    // Simple modal renderer using existing modal in products.php
    window.showProductModal = function(productId) {
        try {
            const trigger = document.querySelector(`.product-card [onclick*="showProductModal(${productId})"]`);
            const card = trigger ? trigger.closest('.product-card') : null;
            if (!card) return;
            const product = JSON.parse(card.getAttribute('data-product'));
            const images = (product.image_url || '').split(',').map(s => s.trim()).filter(Boolean);
            const normalize = (img) => {
                img = String(img || '').trim().replace(/^\.+\/?/, '');
                if (!img) return '/rtwrs_web/assets/images/placeholder-product.svg';
                if (!img.startsWith('/rtwrs_web/')) img = '/rtwrs_web/' + img.replace(/^\/+/, '');
                return img;
            };
            const slides = (images.length ? images : ['/rtwrs_web/assets/images/placeholder-product.svg'])
                .map((img, idx) => `
                    <div class="carousel-item ${idx===0 ? 'active' : ''}">
                        <img src="${normalize(img)}" class="d-block w-100" alt="${product.product_name}" style="object-fit:contain; max-height:520px;">
                    </div>
                `).join('');
            document.getElementById('carouselInner').innerHTML = slides;
            document.getElementById('modalTitle').textContent = product.product_name;
            document.getElementById('modalDesc').innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div class="h5 mb-0 text-brown">₹${product.price_per_day}<span class="text-muted small">/day</span></div>
                    <a href="../user/rent.php?id=${product.id}" class="btn btn-primary">RENT NOW</a>
                </div>
                <p class="mt-3 mb-0 text-muted small">${product.description || ''}</p>
            `;
            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        } catch (e) {
            console.error('Failed to open modal', e);
        }
    }

    // Clear filters
    document.getElementById('clearFilters').addEventListener('click', function() {
        document.getElementById('filterForm').reset();
        loadProducts();
    });

    function fetchCategories() {
        fetch('../api/filter-handler.php?type=categories')
        .then(response => response.json())
        .then(categories => {
            let html = '';
            categories.forEach(category => {
                html += `
                    <div class="filter-checkbox">
                        <input type="radio" name="category" value="${category.id}" id="cat${category.id}" class="form-check-input">
                        <label for="cat${category.id}" class="form-check-label">${category.name}</label>
                    </div>
                `;
            });
            document.getElementById('categories').innerHTML = html;
        })
        .catch(error => console.error('Error fetching categories:', error));
    }

    function fetchSizes() {
        fetch('../api/filter-handler.php?type=sizes')
        .then(response => response.json())
        .then(sizes => {
            let html = '';
            sizes.forEach(size => {
                html += `
                    <div class="filter-checkbox">
                        <input type="checkbox" name="size" value="${size}" id="size${size}" class="form-check-input">
                        <label for="size${size}" class="form-check-label">${size}</label>
                    </div>
                `;
            });
            document.getElementById('sizes').innerHTML = html;
        })
        .catch(error => console.error('Error fetching sizes:', error));
    }

    function fetchColors(categoryId = null) {
        let url = '../api/filter-handler.php?type=colors';
        if (categoryId) {
            url += `&category=${categoryId}`;
        }

        fetch(url)
        .then(response => response.json())
        .then(colors => {
            let html = '';
            colors.forEach(color => {
                html += `
                    <div class="filter-checkbox">
                        <input type="checkbox" name="color" value="${color}" id="color${color}" class="form-check-input">
                        <label for="color${color}" class="form-check-label text-capitalize">${color}</label>
                    </div>
                `;
            });
            document.getElementById('colors').innerHTML = html;
        })
        .catch(error => console.error('Error fetching colors:', error));
    }

    function fetchPriceRange() {
        fetch('../api/filter-handler.php?type=price-range')
        .then(response => response.json())
        .then(range => {
            document.getElementById('minPrice').setAttribute('min', range.min_price);
            document.getElementById('maxPrice').setAttribute('max', range.max_price);
            document.getElementById('minPrice').setAttribute('placeholder', `Min: ₹${range.min_price}`);
            document.getElementById('maxPrice').setAttribute('placeholder', `Max: ₹${range.max_price}`);
        })
        .catch(error => console.error('Error fetching price range:', error));
    }

    function loadProducts() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);

        // Show loading
        document.getElementById('products').innerHTML = '<div class="text-center"><div class="loading"></div></div>';

        fetch('../api/filter-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(async (response) => {
            let data = null;
            try { data = await response.json(); } catch (e) { data = null; }
            if (!response.ok) {
                console.error('Products API HTTP error', response.status, data);
                throw new Error((data && (data.error || data.details)) || 'HTTP ' + response.status);
            }
            if (!Array.isArray(data)) {
                // API returned an error object or unexpected shape
                console.error('Products API returned non-array', data);
                throw new Error((data && (data.error || data.details)) || 'Unexpected response');
            }
            return data;
        })
        .then(products => {
            const grid = document.getElementById('products');
            const noMsg = document.getElementById('noProductsMsg');

            if (products.length === 0) {
                grid.innerHTML = '';
                noMsg.classList.remove('d-none');
                return;
            }

            noMsg.classList.add('d-none');
            let html = '';

            const ensureImages = (image_url) => {
                let images = image_url ? image_url.split(',') : [];
                images = images.map(img => {
                    img = String(img || '').trim().replace(/^\.+\/?/, '');
                    if (!img) return '';
                    if (!img.startsWith('/rtwrs_web/')) {
                        img = '/rtwrs_web/' + img.replace(/^\/+/, '');
                    }
                    return img;
                }).filter(Boolean);
                if (images.length === 0) {
                    images = ['/rtwrs_web/assets/images/placeholder-product.svg'];
                }
                return images;
            };

            products.forEach(product => {
                const images = ensureImages(product.image_url);
                const isNew = (Date.now() - Date.parse(product.created_at)) < (7 * 24 * 60 * 60 * 1000);
                const isLowStock = product.quantity_available <= 2;

                const cardData = JSON.stringify(product).replace(/"/g, '&quot;');
                html += `
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <div class="card product-card h-100 border-0 shadow-sm" data-product="${cardData}">
                            <div class="product-thumb" style="height:320px; background:#fff; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="showProductModal(${product.id})">
                                <img src="${images[0]}" alt="${product.product_name}" class="img-contain" onerror="this.onerror=null;this.src='/rtwrs_web/assets/images/placeholder-product.svg';" style="max-width:100%; max-height:100%; object-fit:contain;">
                            </div>
                            <div class="card-body p-2 d-flex flex-column">
                                <h6 class="product-title mb-1 text-truncate" title="${product.product_name}">${product.product_name}</h6>
                                <div class="text-muted small mb-1">${product.category || ''}</div>
                                ${isNew ? '<span class="badge bg-success-subtle text-success border mb-2">New</span>' : ''}
                                ${isLowStock ? `<span class=\"badge bg-warning-subtle text-warning border mb-2\">Only ${product.quantity_available} Left</span>` : ''}
                                <div class="text-muted small">Sizes: ${product.size || '-'}</div>
                                <div class="text-muted small mb-2">Colors: ${product.color || '-'}</div>
                                <div class="product-price text-brown fw-semibold mb-2">₹${product.price_per_day}<span class="text-muted small">/day</span></div>
                                <div class="mt-auto d-grid gap-2">
                                    <a href="../user/rent.php?id=${product.id}" class="btn btn-primary btn-sm">RENT NOW</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            grid.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading products:', error);
            const grid = document.getElementById('products');
            const noMsg = document.getElementById('noProductsMsg');
            noMsg.classList.add('d-none');
            grid.innerHTML = '<div class="alert alert-danger">Error loading products. Please try again.</div>';
        });
    }

    // Add form data to POST request
    FormData.prototype.append = function(name, value) {
        if (this.has(name)) {
            this.delete(name);
        }
        this.set(name, value);
    };
});
