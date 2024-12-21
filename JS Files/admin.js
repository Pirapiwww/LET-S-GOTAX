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