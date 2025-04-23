document.addEventListener('DOMContentLoaded', function() {
    const clockInForm = document.getElementById('clockInForm');
    const employeeIdInput = document.getElementById('employeeId');
    const messageDiv = document.getElementById('message');

    clockInForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const employeeId = employeeIdInput.value.trim();

        if (employeeId === '') {
            messageDiv.textContent = 'Please enter your employee ID.';
            messageDiv.style.color = 'red';
            return;
        }

        // Perform AJAX request to clock in
        fetch('clock_in_out.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `employee_id=${encodeURIComponent(employeeId)}&action=clock_in`
        })
        .then(response => response.text())
        .then(data => {
            messageDiv.textContent = data;
            messageDiv.style.color = 'green';
            employeeIdInput.value = ''; // Clear input after submission
        })
        .catch(error => {
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.style.color = 'red';
        });
    });
});