document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.querySelector("form");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");

    loginForm.addEventListener("submit", function (event) {
        // Prevent form from submitting
        event.preventDefault();

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        // Validate email and password
        if (!email || !password) {
            alert("Email and password are required.");
            return;
        }

        if (!email.endsWith("@gmail.com")) {
            alert("Email must be a valid Gmail address (e.g., example@gmail.com)." );
            return;
        }

        alert("Login successful!");
        loginForm.submit();
    });
});
