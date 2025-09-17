// Rental calculation and booking functionality
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    const totalPriceInput = document.getElementById('totalPrice');
    const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
    const upiSection = document.getElementById('upiSection');
    const rentalForm = document.getElementById('rentalForm');

    // Price per day from PHP
    const pricePerDay = parseFloat(document.getElementById('pricePerDay').value) || 0;

    // Calculate total price
    function calculateTotal() {
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            const timeDiff = endDate - startDate;
            const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;

            if (daysDiff > 0) {
                const totalPrice = daysDiff * pricePerDay;
                totalPriceInput.value = `₹${totalPrice}`;
                document.getElementById('totalDays').textContent = daysDiff;
                document.getElementById('calculatedTotal').value = totalPrice;
            }
        }
    }

    // Date change events
    if (startDateInput) {
        startDateInput.addEventListener('change', calculateTotal);
    }
    if (endDateInput) {
        endDateInput.addEventListener('change', calculateTotal);
    }

    // Payment method change
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            if (upiSection) {
                upiSection.style.display = this.value === 'UPI' ? 'block' : 'none';
            }
        });
    }

    // Form submission
    if (rentalForm) {
        rentalForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Basic validation
            if (!startDateInput.value || !endDateInput.value) {
                alert('Please select both start and end dates');
                return;
            }

            if (new Date(startDateInput.value) >= new Date(endDateInput.value)) {
                alert('End date must be after start date');
                return;
            }

            if (!paymentMethodSelect.value) {
                alert('Please select a payment method');
                return;
            }

            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Processing...';

            // Submit form
            const formData = new FormData(this);

            fetch('../api/rent-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking confirmed successfully!');
                    window.location.href = '../user/my-bookings.php';
                } else {
                    alert(data.message || 'Booking failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    if (startDateInput) {
        startDateInput.setAttribute('min', today);
    }
    if (endDateInput) {
        endDateInput.setAttribute('min', today);
    }
});
