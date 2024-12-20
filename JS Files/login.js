document.addEventListener("DOMContentLoaded", function () {
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");

    signUpForm.addEventListener("submit", function (event) {
        // Prevent form from submitting
        event.preventDefault();

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        // Validate username, email, and password

        if (!email.endsWith("@gmail.com")) {
            alert("Email must be a valid Gmail address (e.g., example@gmail.com)." );
            return;
        }

        if (!password) {
            alert("Password is required.");
            return;
        }

        // If validation passes
        alert("Login successful!");
        // You can now submit the form, redirect, or perform additional actions
        signUpForm.submit();
    });
});
