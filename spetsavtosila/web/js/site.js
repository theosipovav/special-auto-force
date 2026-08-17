// Custom JavaScript for СПЕЦАВТОСИЛА

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Search form validation
    var searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            var query = this.querySelector('input[name="q"]').value.trim();
            if (!query) {
                e.preventDefault();
                alert('Введите поисковый запрос');
                return false;
            }
        });
    }

    // Request modal handling
    var requestModal = document.getElementById('requestModal');
    if (requestModal) {
        requestModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var productId = button.getAttribute('data-product-id');
            var productName = button.getAttribute('data-product-name');
            
            var modalTitle = requestModal.querySelector('.modal-title');
            modalTitle.textContent = 'Запрос на товар: ' + productName;
            
            var productIdInput = requestModal.querySelector('input[name="Request[product_id]"]');
            if (productIdInput) {
                productIdInput.value = productId;
            }
        });
    }

    // Auto-dismiss alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm delete actions
    var deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Вы уверены, что хотите удалить эту запись?')) {
                e.preventDefault();
            }
        });
    });

    // Image preview for file inputs
    var imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = input.parentElement.querySelector('.image-preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
});

// Carousel auto-play for product carousels
document.querySelectorAll('.product-carousel').forEach(function(carousel) {
    var bsCarousel = new bootstrap.Carousel(carousel, {
        interval: 3000,
        wrap: true
    });
});
