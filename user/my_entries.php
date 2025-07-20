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

// If not logged in or not an admin, redirect to login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
  header('Location: ../logout.php');
  exit();
}

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';
?>

<?php
$pageTitle = "User Dashboard - My Entries";
$customCSS = "
    #map {
      height: 80vh;
      width: 100%;
    }";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
  <?php include '../comps/navbar.php' ?>

  <main class="flex-fill">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <div class="text-center mb-4">
            <h1 class="h3">My Requests</h1>
          </div>

          <div class="row justify-content-center g-4">
            <!-- Buy Card -->
            <div class="col-12 col-md-10">
              <a href="buy_data.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-primary">
                    Buy Requests
                  </h2>
                  <p class="text-white-50 fs-6">
                    Get all your buy entries.
                  </p>
                </div>
              </a>
            </div>
            <!-- List Card -->
            <div class="col-12 col-md-10">
              <a href="sell_data.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-danger">
                    Sell Requests
                  </h2>
                  <p class="text-white-50 fs-6">
                    Get all your sell entries.
                  </p>
                </div>
              </a>
            </div>

            <!-- Landlord Card -->
            <div class="col-12 col-md-10">
              <a href="landLord_data.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-warning">
                    Landlord Requests
                  </h2>
                  <p class="text-white-50 fs-6">
                    Get all your Landlord entries.
                  </p>
                </div>
              </a>
            </div>

            <!-- Tenant Card -->
            <div class="col-12 col-md-10">
              <a href="tenant_data.php" class="text-decoration-none">
                <div class="custom-card">
                  <h2 class="h3 fw-semibold mb-3 text-success">
                    Tenant Requests
                  </h2>
                  <p class="text-white-50 fs-6">
                    Get all your renter entries.
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