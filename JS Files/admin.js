function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = 'block'; // Menampilkan gambar preview
    };
    reader.readAsDataURL(event.target.files[0]);
}

function showImageModal(imagePath) {
    // Set image source to the selected image
    document.getElementById('modalImage').src = imagePath;
    // Show the modal
    var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
    myModal.show();
}

document.addEventListener("DOMContentLoaded", function () {
    const signUpForm = document.querySelector("form");
    const usernameInput = document.getElementById("username");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");

    signUpForm.addEventListener("submit", function (event) {
        // Prevent form from submitting
        event.preventDefault();

        const username = usernameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        // Validate username, email, and password
        if (!username) {
            alert("Username is required.");
            return;
        }

        if (!email) {
            alert("Email is required.");
            return;
        }

        if (!email.endsWith("@gmail.com")) {
            alert("Email must be a valid Gmail address (e.g., example@gmail.com)." );
            return;
        }

        if (!password) {
            alert("Password is required.");
            return;
        }

        // If validation passes
        alert("Sign up successful!");
        // You can now submit the form, redirect, or perform additional actions
        signUpForm.submit();
    });
});