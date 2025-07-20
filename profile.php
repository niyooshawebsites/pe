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

// If not logged in or not a user, redirect to login
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

require 'includes/db.php';
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $userId = $_SESSION['user']['id'] ?? null;

    if (!$userId) {
        $error = "Unauthorized access.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        if (resetUserPassword($userId, $password, $name, $mobile)) {
            $success = "Password updated successfully.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}


?>

<?php
$pageTitle = "Profile";
include 'comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include 'comps/navbar.php'; ?>

    <main class="flex-fill py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                    <div class="card shadow border-primary mt-5">
                        <div class="card-header bg-primary text-white text-center">
                            <h4 class="mb-0">Reset Details</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            <form method="post">
                                <p class="text-danger">*All the fields are mandatory to reset</p>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" id="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="text" class="form-control" name="mobile" id="mobile" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="password" id="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">RESET</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'comps/loan_calculator.php' ?>
    <?php include 'comps/footer.php'; ?>

</body>


</html>