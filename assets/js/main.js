// FrozoFun Customer Site JavaScript Functions

// Cart management
let cart = JSON.parse(localStorage.getItem('frozofun_cart') || '[]');

// Update cart badge for logged-in users by fetching from server
function updateLoggedInCartBadge() {
    if (window.isUserLoggedIn) {
        fetch('/cart/count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.count || '0';
                        cartBadge.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart badge:', error);
            });
    }
}

// Update cart display
function updateCartDisplay() {
    // Only update cart display for guest users
    // For logged-in users, cart count is handled server-side
    if (!window.isUserLoggedIn) {
        const cartCount = cart.reduce((total, item) => total + parseFloat(item.qty), 0);
        const cartBadge = document.querySelector('.cart-badge');
        if (cartBadge) {
            cartBadge.textContent = cartCount || '0';
            cartBadge.style.display = cartCount > 0 ? 'flex' : 'none';
        }
    }
}

// Add item to cart
function addToCart(type, id, name, price, packSize = 1) {
    if (window.isUserLoggedIn) {
        // Add to database cart for logged-in users
        fetch('/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: type,
                id: id,
                qty: 1,
                pack_size: packSize
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Update cart badge by fetching updated count
                updateLoggedInCartBadge();
            } else {
                showAlert('danger', data.message || 'Failed to add to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Failed to add to cart');
        });
    } else {
        // Add to localStorage cart for guest users
        const existingItem = cart.find(item => item.type === type && item.id === id && item.pack_size === packSize);
        
        if (existingItem) {
            existingItem.qty = parseFloat(existingItem.qty) + 1;
        } else {
            cart.push({
                type: type, // 'item' or 'meal'
                id: parseInt(id),
                name: name,
                price: parseFloat(price),
                pack_size: parseFloat(packSize),
                qty: 1
            });
        }
        
        localStorage.setItem('frozofun_cart', JSON.stringify(cart));
        updateCartDisplay();
        
        // Show success message
        showAlert('success', `${name} added to cart!`);
    }
}

// Remove item from cart
function removeFromCart(type, id, packSize = 1) {
    cart = cart.filter(item => !(item.type === type && item.id === id && item.pack_size === packSize));
    localStorage.setItem('frozofun_cart', JSON.stringify(cart));
    updateCartDisplay();
}

// Update cart item quantity
function updateCartItemQty(type, id, packSize, newQty) {
    const item = cart.find(item => item.type === type && item.id === id && item.pack_size === packSize);
    if (item) {
        if (newQty <= 0) {
            removeFromCart(type, id, packSize);
        } else {
            item.qty = parseFloat(newQty);
            localStorage.setItem('frozofun_cart', JSON.stringify(cart));
            updateCartDisplay();
        }
    }
}

// Clear cart
function clearCart() {
    cart = [];
    localStorage.setItem('frozofun_cart', JSON.stringify(cart));
    updateCartDisplay();
}

// Show alert message
function showAlert(type, message, duration = 3000) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after duration
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, duration);
}

// Product quick view modal
function showProductModal(type, id) {
    fetch(`/products/details.php?type=${type}&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('productModal');
                const modalBody = modal.querySelector('.modal-body');
                const modalTitle = modal.querySelector('.modal-title');
                
                modalTitle.textContent = data.product.name;
                modalBody.innerHTML = data.html;
                
                new bootstrap.Modal(modal).show();
            } else {
                showAlert('danger', 'Failed to load product details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Failed to load product details');
        });
}

// Live search functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    
    if (!searchInput || !searchResults) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performLiveSearch(query);
        }, 300);
    });
    
    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            searchResults.style.display = 'block';
        }
    });
}

function performLiveSearch(query) {
    const searchResults = document.getElementById('searchResults');
    
    fetch(`/api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data.results);
        })
        .catch(error => {
            console.error('Search error:', error);
            searchResults.innerHTML = '<div class="p-3 text-muted">Search failed</div>';
            searchResults.style.display = 'block';
        });
}

function displaySearchResults(results) {
    const searchResults = document.getElementById('searchResults');
    
    if (results.length === 0) {
        searchResults.innerHTML = '<div class="p-3 text-muted">No products found</div>';
        searchResults.style.display = 'block';
        return;
    }
    
    let html = '';
    results.forEach(result => {
        html += `
            <div class="search-result-item p-2 border-bottom" style="cursor: pointer;" 
                 onclick="window.location.href='${result.url}'">
                <div class="d-flex align-items-center">
                    ${result.image ? `<img src="${result.image}" alt="${result.name}" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">` : '<div class="me-2" style="width: 40px; height: 40px; background: #f8f9fa; border-radius: 4px;"></div>'}
                    <div class="flex-grow-1">
                        <div class="fw-medium" style="font-size: 0.9rem;">${result.display_name}</div>
                        <div class="text-muted" style="font-size: 0.8rem;">${result.price}</div>
                    </div>
                    <small class="badge bg-light text-dark">${result.type}</small>
                </div>
            </div>
        `;
    });
    
    searchResults.innerHTML = html;
    searchResults.style.display = 'block';
    
    // Add hover effects
    searchResults.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

// Legacy search for product pages (kept for compatibility)
function performSearch(query) {
    const productCards = document.querySelectorAll('.product-card');
    const lowerQuery = query.toLowerCase();
    
    productCards.forEach(card => {
        const productName = card.querySelector('.card-title').textContent.toLowerCase();
        const isVisible = productName.includes(lowerQuery);
        card.closest('.col').style.display = isVisible ? 'block' : 'none';
    });
}

// Category filtering
function filterByCategory(categoryId) {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const cardCategoryId = card.dataset.categoryId;
        const isVisible = categoryId === 'all' || cardCategoryId === categoryId;
        card.closest('.col').style.display = isVisible ? 'block' : 'none';
    });
    
    // Update active category button
    document.querySelectorAll('.category-filter').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[onclick="filterByCategory('${categoryId}')"]`).classList.add('active');
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Address selection
function selectAddress(addressId) {
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    const selectedCard = document.querySelector(`[data-address-id="${addressId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Update hidden input if exists
    const hiddenInput = document.getElementById('selected_address_id');
    if (hiddenInput) {
        hiddenInput.value = addressId;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Clear localStorage cart for logged-in users to avoid conflicts
    if (window.isUserLoggedIn) {
        localStorage.removeItem('frozofun_cart');
        cart = [];
    }
    
    updateCartDisplay();
    initializeSearch();
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Handle AJAX form submissions
function submitForm(formId, successCallback) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) {
                successCallback(data);
            } else {
                showAlert('success', data.message);
            }
        } else {
            showAlert('danger', data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while processing your request');
    });
}