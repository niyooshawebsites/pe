<?php
require __DIR__ . '/../includes/session.php';

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

// If not logged in or not an admin, redirect to login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit();
}
?>

<?php
$pageTitle = "Admin Dashboard";
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
                        <h1 class="h3">Requests</h1>
                    </div>

                    <div class="row justify-content-center g-4">
                        <!-- Buy Card -->
                        <div class="col-12 col-md-10">
                            <a href="buy_data.php" class="text-decoration-none">
                                <div class="custom-card">
                                    <h2 class="h3 fw-semibold mb-3 text-primary text-center">
                                        Buy Property Requests
                                    </h2>
                                </div>
                            </a>
                        </div>
                        
                        <!-- List Card -->
                        <div class="col-12 col-md-10">
                            <a href="sell_data.php" class="text-decoration-none">
                                <div class="custom-card">
                                    <h2 class="h3 fw-semibold mb-3 text-danger text-center">
                                        Sell Property Requests
                                    </h2>
                                </div>
                            </a>
                        </div>

                        <!-- Landlord Card -->
                        <div class="col-12 col-md-10">
                            <a href="landLord_data.php" class="text-decoration-none">
                                <div class="custom-card">
                                    <h2 class="h3 fw-semibold mb-3 text-warning text-center">
                                        Let Property Requests
                                    </h2>
                                </div>
                            </a>
                        </div>

                        <!-- Tenant Card -->
                        <div class="col-12 col-md-10">
                            <a href="tenant_data.php" class="text-decoration-none">
                                <div class="custom-card">
                                    <h2 class="h3 fw-semibold mb-3 text-success text-center">
                                        Take Property on Rent Requests
                                    </h2>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

    <?php include '../comps/footer.php' ?>
</body>

</html>