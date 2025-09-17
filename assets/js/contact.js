// Contact Page JavaScript - RTWRS
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Toggle
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Animate hamburger menu
            const bars = navToggle.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                if (navMenu.classList.contains('active')) {
                    if (index === 0) bar.style.transform = 'rotate(45deg) translate(5px, 5px)';
                    if (index === 1) bar.style.opacity = '0';
                    if (index === 2) bar.style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    bar.style.transform = 'none';
                    bar.style.opacity = '1';
                }
            });
        });

        // Close mobile menu when clicking on a link
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                const bars = navToggle.querySelectorAll('.bar');
                bars.forEach(bar => {
                    bar.style.transform = 'none';
                    bar.style.opacity = '1';
                });
            });
        });
    }

    // Form Validation and Enhancement
    const contactForm = document.querySelector('.contact-form');
    const formInputs = document.querySelectorAll('.contact-form input, .contact-form select, .contact-form textarea');

    if (contactForm) {
        // Add floating label effect
        formInputs.forEach(input => {
            // Check if input has value on page load
            if (input.value.trim() !== '') {
                input.classList.add('has-value');
            }

            // Add event listeners
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
                if (this.value.trim() !== '') {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });

            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
                
                // Real-time validation
                validateField(this);
            });
        });

        // Form submission with loading state
        contactForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Sending...</span>';
            submitBtn.disabled = true;
            
            // If form validation fails, restore button
            setTimeout(() => {
                if (!validateForm()) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    e.preventDefault();
                }
            }, 100);
        });
    }

    // Field validation function
    function validateField(field) {
        const fieldGroup = field.parentElement;
        const fieldName = field.name;
        let isValid = true;
        let errorMessage = '';

        // Remove existing error
        const existingError = fieldGroup.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        fieldGroup.classList.remove('error');

        // Validation rules
        switch (fieldName) {
            case 'name':
                if (field.value.trim().length < 2) {
                    isValid = false;
                    errorMessage = 'Name must be at least 2 characters long';
                }
                break;
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value.trim())) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
                break;
            case 'phone':
                if (field.value.trim() !== '') {
                    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                    if (!phoneRegex.test(field.value.replace(/[\s\-\(\)]/g, ''))) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    }
                }
                break;
            case 'subject':
                if (field.value.trim().length < 5) {
                    isValid = false;
                    errorMessage = 'Subject must be at least 5 characters long';
                }
                break;
            case 'message':
                if (field.value.trim().length < 10) {
                    isValid = false;
                    errorMessage = 'Message must be at least 10 characters long';
                }
                break;
        }

        // Show error if validation failed
        if (!isValid && field.value.trim() !== '') {
            fieldGroup.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = errorMessage;
            fieldGroup.appendChild(errorDiv);
        }

        return isValid;
    }

    // Form validation function
    function validateForm() {
        let isFormValid = true;
        const requiredFields = contactForm.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!validateField(field) || field.value.trim() === '') {
                isFormValid = false;
            }
        });

        return isFormValid;
    }

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated');
                
                // Add specific animation classes based on element
                if (entry.target.classList.contains('contact-info-section')) {
                    entry.target.classList.add('animate__fadeInLeft');
                } else if (entry.target.classList.contains('contact-form-section')) {
                    entry.target.classList.add('animate__fadeInRight');
                } else {
                    entry.target.classList.add('animate__fadeInUp');
                }
                
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe sections for animation
    const sections = document.querySelectorAll('.contact-info-section, .contact-form-section, .map-section, .categories-section');
    sections.forEach(section => {
        observer.observe(section);
    });

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Format Indian phone numbers
            if (value.length > 0) {
                if (value.startsWith('91')) {
                    value = '+91 ' + value.substring(2);
                } else if (value.length === 10) {
                    value = '+91 ' + value;
                }
                
                // Add spacing for readability
                if (value.startsWith('+91 ') && value.length > 7) {
                    const number = value.substring(4);
                    if (number.length > 5) {
                        value = '+91 ' + number.substring(0, 5) + ' ' + number.substring(5);
                    }
                }
            }
            
            e.target.value = value;
        });
    }

    // Auto-resize textarea
    const messageTextarea = document.getElementById('message');
    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Copy contact info to clipboard
    const contactLinks = document.querySelectorAll('.contact-details a[href^="tel:"], .contact-details a[href^="mailto:"]');
    contactLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                const text = this.textContent.trim();
                navigator.clipboard.writeText(text).then(() => {
                    showToast('Copied to clipboard: ' + text);
                });
            }
        });
    });

    // Toast notification function
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    // Add CSS for toast animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .field-error {
            color: var(--error-color);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: var(--error-color);
        }
        .form-group.focused .form-icon {
            color: var(--primary-color);
        }
    `;
    document.head.appendChild(style);

    // Initialize map interaction
    const mapIframe = document.querySelector('.map-container iframe');
    if (mapIframe) {
        mapIframe.addEventListener('load', function() {
            console.log('Map loaded successfully');
        });
    }

    // Social media link tracking (optional analytics)
    const socialLinks = document.querySelectorAll('.social-link, .footer-social a');
    socialLinks.forEach(link => {
        link.addEventListener('click', function() {
            const platform = this.classList.contains('facebook') ? 'Facebook' :
                           this.classList.contains('instagram') ? 'Instagram' :
                           this.classList.contains('whatsapp') ? 'WhatsApp' :
                           this.classList.contains('twitter') ? 'Twitter' : 'Social';
            
            console.log('Social media click:', platform);
            // Add analytics tracking here if needed
        });
    });

    // Lazy loading for images (if any are added later)
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    console.log('Contact page JavaScript initialized successfully');
});
