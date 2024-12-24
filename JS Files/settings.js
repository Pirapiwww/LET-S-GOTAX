function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = 'block'; // Menampilkan gambar preview
    };
    reader.readAsDataURL(event.target.files[0]);
}

function refreshPage() {
    location.reload();  // Segera me-refresh halaman
}

// Function of settings(personal).php

document.addEventListener('DOMContentLoaded', function() {
    const deleteLink = document.querySelector('a[href="#"].text-danger');
    
    deleteLink.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            fetch('delete-account.php', {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'login.php';
                } else {
                    alert('Failed to delete account. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    });
});