//Function of all settings php
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

