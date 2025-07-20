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
} ?>

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';


<?php
$pageTitle = "User Dashboard";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
  <?php include '../comps/navbar.php' ?>

  <main class="flex-fill">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <div class="text-center mb-4">
            <h1 class="h3">New Request</h1>
          </div>

          <div class="row justify-content-center g-4">
            <!-- Buy Card -->
            <div class="col-12 col-md-10">
              <a href="buy_property.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-primary">
                    Buy Property
                  </h2>
                  <p class="text-white-50 fs-6">
                    Buy properties from all around Sikar, with the trust of our brokers.
                  </p>
                </div>
              </a>
            </div>
            <!-- List Card -->
            <div class="col-12 col-md-10">
              <a href="sell_property.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-danger">
                    Sell Property
                  </h2>
                  <p class="text-white-50 fs-6">
                    Get the best value for your properties. List them on Kahnv Properties to bypass middlemen and get maximum profits.
                  </p>
                </div>
              </a>
            </div>

            <!-- Landlord Card -->
            <div class="col-12 col-md-10">
              <a href="landLord.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-warning">
                    Let Property
                  </h2>
                  <p class="text-white-50 fs-6">
                    Maximize your rental income—register your property now and let us do the rest.
                  </p>
                </div>
              </a>
            </div>

            <!-- Tenant Card -->
            <div class="col-12 col-md-10">
              <a href="tenant.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-success">
                    Take Property on Rent
                  </h2>
                  <p class="text-white-50 fs-6">
                    Find your perfect home—browse verified rental listings tailored to your needs.
                  </p>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </main>

  <?php include '../comps/loan_calculator.php'; ?>
  <?php include '../comps/footer.php' ?>

</body>

</html>