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
?>

<?php
$pageTitle = "404";
include 'comps/header.php';
?>

<body class="app-background">
    <?php include 'comps/navbar.php' ?>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6">
                <div class="card shadow border-primary">
                    <div class="card-body text-center">
                        <h1 class="display-1 text-danger">404</h1>
                        <h2 class="display-3">Page not found!</h2>
                        <a href="/index.php" class="btn btn-primary mt-3">Back to home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'comps/loan_calculator.php' ?>
    <?php include 'comps/footer.php' ?>
</body>

</html>