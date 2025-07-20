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

require 'includes/db.php';
require 'includes/functions.php';

// if (isset($_SESSION['user'])) {
//   if ($_SESSION['user']['role'] === 'admin') {
//     header("Location: /admin/dashboard");
//     exit;
//   } elseif ($_SESSION['user']['role'] === 'user') {
//     header("Location: /user/dashboard");
//     exit;
//   }
// }

?>

<?php
$pageTitle = "Home";
$customCSS = "
    .outer-box {
      max-width: 95vw;
      height: 91vh;
      margin: auto;
    }

    .inner-box {
      height: 72vh;
      border: 2px solid #ccc;
      border-radius: 20px;
      padding: 20px;
      position: relative;
    }

    .sub-box {
      height: 100%;
    }

    .main-title {
      font-size: 2.5rem;
      font-weight: bold;
    }

    .start-btn {
      padding: 8px 20px;
      border: 1px solid #fff;
      border-radius: 10px;
      margin-top: 10px;
      color: #fff;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
    }

    .start-btn:hover {
      background-color: #fff;
      color: #000;
    }

    .custom-card {
      border: 2px solid #ccc;
      border-radius: 20px;
      padding: 20px;
      background-color: #1a1a1a;
    }

    @media (max-width: 767px) {

      .outer-box {
        height: 800px;
      }

      .inner-box {
        height: 350px !important;
        padding: 15px;
      }

      .main-title {
        font-size: 1.5rem;
      }

      .start-btn {
        padding: 6px 16px;
        font-size: 0.9rem;
      }

      .custom-card h2 {
        font-size: 1rem;
      }

      footer {
        position: relative;
        bottom: auto;
        left: auto;
        width: 100%;
        background-color: #1a1a1a;
        padding: 15px;
        text-align: center;
      }

      body {
        padding-bottom: 0 !important;
      }
    }
";
include 'comps/header.php';
?>

<body class="app-background text-white">
  <?php include 'comps/navbar.php' ?>

  <div class="container outer-box py-5">

    <div class="row mb-4">
      <!-- Left: Image with overlay text -->
      <div class="col-md-8 mb-3">
        <div class="inner-box" style="background-image: url('/assets/images/home.jpg'); background-size: cover; background-position: center center">
          <!-- Overlay Text -->
          <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center text-white text-center p-3" style="background-color: rgba(0, 0, 0, 0.4); border-radius: 20px;">
            <div class="main-title mb-2"><?php echo $config['APP_NAME']; ?></div>
            <p><?php echo $config['BUSINESS_ADDRESS']; ?></p>
            <!-- <a href="https://niyooshawebsitesllp.in/product/property-expert/" class="start-btn">Start your Journey</a> -->
            <a href="/login.php" class="start-btn">Start your journey</a>
          </div>
        </div>
      </div>

      <!-- Right: What can be done on this site -->
      <div class="col-md-4 mb-3">
        <div class="inner-box d-flex flex-column justify-content-center">
          <div class="row justify-content-center g-3">
            <!-- Buy -->
            <div class="col-12">
              <a href="properties_available_for_sale.php" class="text-decoration-none">
                <div class="custom-card text-center">
                  <h2 class="h4 fw-semibold text-primary">Buyers</h2>
                  <p class="text-light">Search listed properties or raise a custom request</p>
                </div>
              </a>
            </div>
            <!-- Sell -->
            <div class="col-12">
              <a href="/login.php" class="text-decoration-none">
                <div class="custom-card text-center">
                  <h2 class="h4 fw-semibold text-danger">Sellers</h2>
                  <p class="text-light">List your properties</p>
                </div>
              </a>
            </div>
            <!-- landlord -->
            <div class="col-12">
              <a href="/login.php" class="text-decoration-none">
                <div class="custom-card text-center">
                  <h2 class="h4 fw-semibold text-warning">Landlords</h2>
                  <p class="text-light">List your properties</p>
                </div>
              </a>
            </div>
            <!-- Tenants -->
            <div class="col-12">
              <a href="properties_available_for_rent.php" class="text-decoration-none">
                <div class="custom-card text-center">
                  <h2 class="h4 fw-semibold text-success">Tenants</h2>
                  <p class="text-light">Search listed properties or raise a custom request</p>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <?php include 'comps/loan_calculator.php' ?>
  <?php include 'comps/footer.php' ?>
</body>

</html>