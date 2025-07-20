<?php
require __DIR__ . '/includes/session.php';
$configFile = __DIR__ . '/config/config.php';

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

$config = require_once $configFile;
require 'includes/db.php';
require 'includes/functions.php';

if ($_SESSION['user']) {
    if ($_SESSION['user']['role'] == "admin")
        header("Location: /admin/dashboard.php");

    if ($_SESSION['user']['role'] == "user")
        header("Location: /user/dashboard.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $role = 'user';

    if (!$email || empty($name) || empty($mobile) || empty($password)) {
        $error = "All fields are required and must be valid.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $result = registerUser($name, $email, $mobile, $hashedPassword, $role);


        if ($result === true) {
            $_SESSION['success_msg'] = 'Registration successful! Please login.';
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error_msg'] = $result;
            header("Location: register.php");
            exit();
        }
    }
}

?>

<?php
$pageTitle = "Register";
include 'comps/header.php';
?>

<body class="app-background">
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
                        <h4>Create an Account</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" name="mobile" class="form-control" id="mobile" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" id="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>Already have an account? <a href="login.php">Login here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'comps/loan_calculator.php' ?>
    <?php include 'comps/footer.php' ?>

</body>

</html>