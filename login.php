<?php
require __DIR__ . '/includes/session.php';
$configFile = __DIR__ . '/config/config.php';

// printing the session array for debugging
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

// printing errors for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/php-error.log');

if (!file_exists($configFile)) {
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    header("Location: http://$host$uri/setup/index.php");
    exit;
}

require 'includes/db.php';
require 'includes/functions.php';

$error = '';

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] == "admin") {
        header("Location: /admin/dashboard.php");
        exit;
    } elseif ($_SESSION['user']['role'] == "user") {
        header("Location: /user/dashboard.php");
        exit;
    }
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    if (!$email || empty($password)) {
        $error = "Please enter a valid email and password.";
    } else {
        $user = loginUser($email, $password);

        if ($user) {
            session_unset();
            session_destroy();
            session_start();
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            header("Location: " . ($user['role'] == 'admin' ? '/admin/dashboard.php' : '/user/dashboard.php'));
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<?php
$pageTitle = "Login";
include 'comps/header.php';
?>

<body class="app-background text-white">
    <?php include 'comps/navbar.php' ?>

    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6">
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success_msg'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_msg']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error_msg'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_msg']); ?>
                <?php endif; ?>

                <div class="card shadow border-primary">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>Don't have an account? <a href="register.php">Register here</a></small>
                    </div>
                </div>
                <div class="border border-dashed rounded p-2 my-5 d-flex flex-column justify-content-center ">
                    <h6 class="alert alert-danger text-center">LOGIN DETAILS</h6>
                    <p class="alert alert-info text-center">ADMIN >> Email: admin@gmail.com | Password: admin</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'comps/loan_calculator.php' ?>
    <?php include 'comps/footer.php' ?>
</body>

</html>