<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Debug: Check session
if (!isset($_SESSION)) {
    session_start();
}

// Debug: Print session data
error_log("Session data: " . print_r($_SESSION, true));

// Redirect if already logged in
if (is_logged_in()) {
    error_log("User already logged in, redirecting to index");
    header("Location: ../index.php");
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $db = new Database();
        
        try {
            // Debug: Print login attempt
            error_log("Login attempt for username: " . $username);
            
            $stmt = $db->query(
                "SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1",
                [$username]
            );
            
            if ($stmt) {
                $user = $stmt->fetch();
                
                // Debug: Print user data (without password)
                $debugUser = $user ? (array)$user : null;
                if (isset($debugUser['password'])) unset($debugUser['password']);
                error_log("Found user: " . print_r($debugUser, true));
                
                if ($user && password_verify($password, $user->password)) {
                    // Set session variables
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['username'] = $user->username;
                    $_SESSION['role'] = $user->role;

                    // Debug: Print new session data
                    error_log("New session data: " . print_r($_SESSION, true));

                    // Update last login
                    $db->update('users', 
                        ['last_login' => date('Y-m-d H:i:s')],
                        'id = ?',
                        [$user->id]
                    );

                    // Log the successful login
                    $db->insert('user_logs', [
                        'user_id' => $user->id,
                        'action' => 'login',
                        'description' => 'User logged in successfully',
                        'ip_address' => $_SERVER['REMOTE_ADDR'],
                        'user_agent' => $_SERVER['HTTP_USER_AGENT']
                    ]);

                    // Debug: Print redirect path
                    $redirect_path = "../index.php";
                    error_log("Redirecting to: " . $redirect_path);
                    
                    // Clear any output buffers
                    while (ob_get_level()) {
                        ob_end_clean();
                    }

                    // Set headers to prevent caching
                    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                    header("Cache-Control: post-check=0, pre-check=0", false);
                    header("Pragma: no-cache");
                    
                    // Redirect
                    header("Location: " . $redirect_path);
                    exit();
                } else {
                    error_log("Invalid password for username: " . $username);
                    $error = 'Invalid username or password';
                }
            } else {
                error_log("Database error in login");
                $error = 'Database error occurred';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: none;
            border-bottom: none;
            padding-bottom: 0;
        }
        .btn-primary {
            padding: 12px;
            font-weight: 500;
        }
        .form-control {
            padding: 12px;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <h1 class="h3"><?php echo SITE_NAME; ?></h1>
            <p class="text-muted">Please login to continue</p>
        </div>
        
        <div class="card">
            <div class="card-header text-center pt-4">
                <h2 class="h4 mb-0">Login</h2>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($username); ?>" required autofocus>
                        </div>
                        <div class="invalid-feedback">Please enter your username.</div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                        <a href="<?php echo base_url('auth/register.php'); ?>" class="btn btn-light">
                            <i class="fas fa-user-plus"></i> Don't have an account? Register
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo base_url(); ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 