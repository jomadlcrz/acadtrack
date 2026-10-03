/* App JS */

// Configure Bootstrap Dropdown defaults to use fixed positioning and viewport boundary.
// This prevents dropdown menus inside tables and responsive cards from being clipped
// or creating unwanted scrollbars.
if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
    bootstrap.Dropdown.Default.popperConfig = { strategy: 'fixed' };
    bootstrap.Dropdown.Default.boundary = 'viewport';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Synchronize modal-open class to documentElement to lock background scroll
document.addEventListener('show.bs.modal', function() {
    document.documentElement.classList.add('modal-open');
});

document.addEventListener('hidden.bs.modal', function() {
    if (!document.querySelector('.modal.show')) {
        document.documentElement.classList.remove('modal-open');
    }
});

