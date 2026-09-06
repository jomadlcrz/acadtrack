// Grading page JS
document.addEventListener('DOMContentLoaded', function() {
    const gradeInputs = document.querySelectorAll('.grading-table input[type="number"]');

    gradeInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0) this.value = 0;
            if (value > 100) this.value = 100;
        });
    });
});
