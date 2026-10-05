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

// Mobile Sidebar Drawer Navigation
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const sidebar = document.getElementById('appSidebar');

    function closeSidebar() {
        document.body.classList.remove('sidebar-open');
        if (sidebarToggle) {
            sidebarToggle.setAttribute('aria-expanded', 'false');
        }
    }

    function openSidebar() {
        document.body.classList.add('sidebar-open');
        if (sidebarToggle) {
            sidebarToggle.setAttribute('aria-expanded', 'true');
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (document.body.classList.contains('sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSidebar();
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function() {
            closeSidebar();
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });

    // Close when clicking any nav link on mobile
    if (sidebar) {
        sidebar.querySelectorAll('.sidebar-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        });
    }

    // Auto-close if resized to desktop breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992 && document.body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });
});

