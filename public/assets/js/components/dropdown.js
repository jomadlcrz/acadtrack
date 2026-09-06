// Dropdown Component JS
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.matches('.dropdown-toggle')) {
        const dropdowns = document.querySelectorAll('.dropdown.show');
        dropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('show');
        });
    }
});
