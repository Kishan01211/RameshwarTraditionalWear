// Product modal functionality
function showProductModal(productId) {
    fetch(`../api/product-details.php?id=${productId}`)
    .then(response => response.json())
    .then(product => {
        if (product.error) {
            alert('Product not found');
            return;
        }

        const images = product.image_url ? product.image_url.split(',') : ['../assets/images/placeholder.jpg'];

        // Set modal title
        document.getElementById('modalTitle').textContent = product.product_name;

        // Create carousel
        let carouselInner = '';
        images.forEach((image, index) => {
            carouselInner += `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${image}" class="d-block w-100" style="width:100%; height:auto; max-height:400px; object-fit:contain; border:1px solid #ddd;" alt="${product.product_name}">
                </div>
            `;
        });

        document.getElementById('carouselInner').innerHTML = carouselInner;

        // Set product details
        document.getElementById('modalDesc').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h5>Product Details</h5>
                    <p><strong>Price:</strong> <span class="price-tag">₹${product.price_per_day}/day</span></p>
                    <p><strong>Available Sizes:</strong> ${product.size}</p>
                    <p><strong>Available Colors:</strong> ${product.color}</p>
                    <p><strong>Stock:</strong> ${product.quantity_available} available</p>
                </div>
                <div class="col-md-6">
                    <h5>Description</h5>
                    <p>${product.description || 'No description available'}</p>
                    <div class="mt-3">
                        <a href="../user/rent.php?id=${product.id}" class="btn btn-primary btn-lg">Book Now</a>
                    </div>
                </div>
            </div>
        `;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error fetching product details:', error);
        alert('Error loading product details');
    });
}

// Close modal function
function closeModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
    if (modal) {
        modal.hide();
    }
}
