// Fungsi untuk konfirmasi hapus
function confirmDelete(id, type) {
    if(confirm('Apakah Anda yakin ingin menghapus item ini?')) {
        window.location.href = `delete_${type}.php?id=${id}`;
    }
}

// Fungsi untuk preview gambar sebelum upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            
            // Validasi ukuran file
            if (input.files[0].size > 2000000) { // 2MB
                alert('Ukuran file terlalu besar. Maksimal 2MB');
                input.value = '';
                preview.src = '#';
                preview.style.display = 'none';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Validasi form
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}); 