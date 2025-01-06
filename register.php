<?php
session_start();
require_once "config/database.php";


if(isset($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    

    if(empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field wajib diisi.";
    } elseif($password !== $confirm_password) {
        $error = "Password tidak cocok.";
    } elseif(strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        
        $query = "SELECT COUNT(*) FROM customers WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        
        if($count > 0) {
            $error = "Email sudah terdaftar.";
        } else {
        
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO customers (name, email, password, phone, address) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if($stmt->execute([$name, $email, $hashed_password, $phone, $address])) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Novel Budiono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-form {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
    </style>
</head>
<body>
    
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="register-form">
            <h2 class="text-center mb-4">Daftar Akun</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <br>
                    <a href="login.php">Klik disini untuk login</a>
                </div>
            <?php else: ?>
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        <div class="invalid-feedback">
                            Nama harus diisi.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        <div class="invalid-feedback">
                            Masukkan email yang valid.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               minlength="6" required>
                        <div class="invalid-feedback">
                            Password minimal 6 karakter.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" required>
                        <div class="invalid-feedback">
                            Password tidak cocok.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" 
                                  rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Daftar</button>
                    </div>

                    <div class="text-center mt-3">
                        Sudah punya akun? <a href="login.php">Login disini</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>


    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })

            
            var password = document.getElementById("password")
            var confirm_password = document.getElementById("confirm_password")

            function validatePassword(){
                if(password.value != confirm_password.value) {
                    confirm_password.setCustomValidity("Passwords tidak cocok")
                } else {
                    confirm_password.setCustomValidity('')
                }
            }

            password.onchange = validatePassword
            confirm_password.onkeyup = validatePassword
        })()
    </script>
</body>
</html> 