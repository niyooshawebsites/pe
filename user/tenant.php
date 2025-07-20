<?php
require __DIR__ . '/../includes/session.php';

// printing the session array for debugging
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

// printing errors for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/php-error.log');

// If not logged in or not a user, redirect to login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../logout.php');
    exit();
}

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $typeOfProperty = $_POST['typeOfProperty'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $typeOfTenant = $_POST['typeOfTenant'] ?? '';
    $locality2 = htmlspecialchars(trim($_POST['locality2'] ?? ''));
    $state = htmlspecialchars(trim($_POST['state'] ?? ''));

    $userId = $_SESSION['user']['id'] ?? null;
    $name = htmlspecialchars($_SESSION['user']['name'] ?? '');
    $email = filter_var($_SESSION['user']['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $mobile = preg_replace('/[^0-9]/', '', $_SESSION['user']['mobile'] ?? '');

    if (!$userId || !$email || !$typeOfProperty || !$budget || !$typeOfTenant || !$locality2 || !$state) {
        $_SESSION['error_msg'] = 'Invalid or missing input fields.';
        header("Location: tenant.php");
        exit();
    }

    if (tenant($userId, $name, $email, $mobile, $typeOfProperty, $budget, $typeOfTenant, $locality2, $state)) {
        $_SESSION['success_msg'] = 'Request submitted successfully!';
    } else {
        $_SESSION['error_msg'] = 'Error submitting request. Please try again.';
    }

    header("Location: tenant.php");
    exit();
}

$allLocalities = fetchAllLocalities();

?>

<?php
$pageTitle = "User Dashboard - Tenant";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php' ?>

    <main class="flex-fill container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
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


                <!-- Responsive header -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <a href="dashboard.php" class="btn btn-outline-light">Back</a>
                    <a href="" class="btn btn-outline-light">Reset Form</a>
                </div>

                <div class="card shadow border-success">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Tenant</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="typeOfProperty" class="form-label">Type of property</label>
                                <select class="form-select" aria-label="Default select example" name="typeOfProperty">
                                    <option value="" selected>Select</option>
                                    <option value="1 BHK">1 BHK</option>
                                    <option value="2 BHK">2 BHK</option>
                                    <option value="3 BHK">3 BHK</option>
                                    <option value="4 BHK">4 BHK</option>
                                    <option value="House">House</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Bungalow">Bungalow</option>
                                    <option value="Pent House">Pent House</option>
                                    <option value="Studio Appartment">Studio Appartment</option>
                                    <option value="Shop">Shop</option>
                                    <option value="Land">Land</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="typeOfTenant" class="form-label">Tenant Type</label>
                                <select class="form-select" aria-label="Default select example" name="typeOfTenant">
                                    <option value="" selected>Select</option>
                                    <option value="Family">Family</option>
                                    <option value="Bachelors">Bachelors</option>
                                    <option value="Commercial">Commercial</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="locality2" class="form-label">Location</label>
                                <select class="form-select" aria-label="Default select example" name="locality2">
                                    <option value="" selected>Select</option>
                                    <?php foreach ($allLocalities as $locality): ?>
                                        <option value="<?= htmlspecialchars($locality['locality']) ?>">
                                            <?= htmlspecialchars($locality['locality']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <input type="hidden" name="state" class="form-control" id="state" placeholder="Enter the state" value="null" required>

                            <div class="mb-3">
                                <label for="budget" class="form-label">Monthly Budget</label>
                                <input type="number" name="budget" class="form-control" id="budget" required inputmode="numeric">
                            </div>

                            <!-- Hidden user info -->
                            <input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
                            <input type="hidden" name="mobile" value="<?= htmlspecialchars($_SESSION['user']['mobile'] ?? '') ?>">

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../comps/loan_calculator.php'; ?>
    <?php include '../comps/footer.php' ?>
</body>


</html>