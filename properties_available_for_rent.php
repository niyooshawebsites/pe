<?php
require __DIR__ . '/includes/session.php';
$configFile = __DIR__ . '/config/config.php';
$config = require_once $configFile;

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

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 30;
$offset = ($page - 1) * $limit;

// Total rows for pagination
$totalRows = $conn->query("SELECT COUNT(*) FROM landLord")->fetch_row()[0];
$totalPages = ceil($totalRows / $limit);

$filters = [
    'max_rent' => $_GET['max_rent'] ?? null,
    'locality2' => $_GET['locality2'] ?? null,
    'state' => $_GET['state'] ?? null,
    'property_type' => $_GET['property_type'] ?? null,
    'tenant_type' => $_GET['tenant_type'] ?? null,
];

// Fetch the paginated records
$landLordData = listLandlordAndPublishedProperty($limit, $offset, $filters);
$allLocalities = fetchAllLocalities();

?>

<?php
$pageTitle = "All Rented Properties";
include 'comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include 'comps/navbar.php'; ?>

    <main class="flex-fill">
        <div class="container-fuild p-4">
            <div class="row justify-content-center">
                <div class="col-12">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                        <!-- Off-canvas Toggle Button (Visible only on small screens) -->
                        <div class="d-md-none d-flex justify-content-center my-2">
                            <button class="btn btn-outline-danger" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                                Filter
                            </button>
                        </div>
                    </div>

                    <!-- Data Table Card -->
                    <div class="card shadow p-4">
                        <div class="card-header bg-success text-white text-center">
                            <h5 class="mb-0">Properties For RENT</h5>
                        </div>
                        <div class="card-body p-0">

                            <form method="get" class="border-bottom p-3 d-none d-md-flex justify-content-center">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-4 col-6">
                                                <select class="form-select" name="property_type">
                                                    <option value="">Property Type</option>
                                                    <?php
                                                    $propertyOptions = ['1 BHK', '2 BHK', '3 BHK', '4 BHK', 'House', 'Villa', 'Bungalow', 'Pent House', 'Studio Appartment', 'Shop', 'Land'];
                                                    foreach ($propertyOptions as $option) {
                                                        $selected = (isset($_GET['property_type']) && $_GET['property_type'] === $option) ? 'selected' : '';
                                                        echo "<option value=\"$option\" $selected>$option</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <select class="form-select" aria-label="Default select example" name="locality2">
                                                    <option value="" selected>Select location</option>
                                                    <?php foreach ($allLocalities as $locality): ?>
                                                        <option value="<?= htmlspecialchars($locality['locality']) ?>">
                                                            <?= htmlspecialchars($locality['locality']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <select class="form-select" name="tenant_type">
                                                    <option value="">Select Tenant Type</option>
                                                    <option value="Family" <?= (isset($_GET['tenant_type']) && $_GET['tenant_type'] === 'Family') ? 'selected' : '' ?>>Family</option>
                                                    <option value="Unmarried" <?= (isset($_GET['tenant_type']) && $_GET['tenant_type'] === 'Unmarried') ? 'selected' : '' ?>>Unmarried</option>
                                                    <option value="Commercial" <?= (isset($_GET['tenant_type']) && $_GET['tenant_type'] === 'Commercial') ? 'selected' : '' ?>>Commercial</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <input type="number" name="max_rent" class="form-control" placeholder="Max Rent" value="<?= $_GET['max_rent'] ?? '' ?>">
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 d-md-flex justify-end gap-2 m-2">
                                                <button type="submit" class="btn btn-success">Filter</button>
                                                <a href="?" class="btn btn-secondary">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>


                            <!-- Off-canvas Filter for Mobile -->
                            <div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title">Filter</h5>
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <!-- Duplicate of the same filter form -->
                                    <form method="get" class="row g-3">
                                        <div class="col-12">
                                            <select class="form-select" aria-label="Default select example" name="property_type">
                                                <option value="" selected>Property Type</option>
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
                                        <div class="col-12">
                                            <select class="form-select" aria-label="Default select example" name="locality2">
                                                <option value="" selected>Select location</option>
                                                <?php foreach ($allLocalities as $locality): ?>
                                                    <option value="<?= htmlspecialchars($locality['locality']) ?>">
                                                        <?= htmlspecialchars($locality['locality']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <select class="form-select" aria-label="Default select example" name="tenant_type">
                                                <option value="" selected>Tenant Type</option>
                                                <option value="Family">Family</option>
                                                <option value="Unmarried">Unmarried</option>
                                                <option value="Commercial">Commercial</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <input type="number" name="max_rent" class="form-control" placeholder="Max Rent" value="<?= $_GET['max_rent'] ?? '' ?>">
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <button type="submit" class="btn btn-success">Apply Filter</button>
                                            <a href="?" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="container-fluid">
                                <div class="row">
                                    <?php if (!empty($landLordData)): ?>
                                        <?php foreach ($landLordData as $index => $row): ?>
                                            <?php
                                            // Split the images string into an array
                                            $imageArray = explode(',', $row['images']);
                                            $firstImage = !empty($imageArray[0]) ? $imageArray[0] : 'uploads/property_images/default.jpg';
                                            ?>
                                            <div class="col-md-3">
                                                <div class="card m-2 p-1 w-100">
                                                    <a href="more_details_about_property_on_rent.php?id=<?= $row['id'] ?>">
                                                        <img src="user/<?= htmlspecialchars($firstImage) ?>" class="card-img-top" alt="Property Image" style="height: 275px">
                                                    </a>
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?= htmlspecialchars($row['propertyToLet']) ?></h5>
                                                        <p class="card-text"><?= htmlspecialchars($row['locality2']) ?></p>
                                                        <p class="card-text">Rs <?= formatIndianNumber($row['rent']) ?></p>
                                                        <a href="more_details_about_property_on_rent.php?id=<?= $row['id'] ?>" class="btn btn-success">More Details</a>

                                                        <?php
                                                        // domain detection and link creation for sharing
                                                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                                                        $host = $_SERVER['HTTP_HOST'];
                                                        $self = "/more_details_about_property_on_rent.php?id=" . $row['id'];
                                                        $propertyUrl = urlencode($protocol . $host . $self);

                                                        ?>

                                                        <div class="mt-2">
                                                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $propertyUrl ?>" target="_blank" data-bs-toggle="tooltip" title="Share it on Facebook">
                                                                <i class="bi bi-facebook text-primary mx-1" style="font-size: 25px"></i>
                                                            </a>
                                                            <a href="https://twitter.com/intent/tweet?url=<?= $propertyUrl ?>" target="_blank" data-bs-toggle="tooltip" title="Share it on X (Twitter)">
                                                                <i class="bi bi-twitter-x text-dark mx-1" style="font-size: 25px"></i>
                                                            </a>
                                                            <a href="https://wa.me/?text=<?= $propertyUrl ?>" target="_blank" data-bs-toggle="tooltip" title="Share it on WhatsApp">
                                                                <i class="bi bi-whatsapp text-success mx-1" style="font-size: 25px"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <h1 class="text-center">No property listings found.</h1>
                                    <?php endif; ?>
                                </div>
                            </div>


                            <?php
                            // Get current filters
                            $currentFilters = $_GET;
                            unset($currentFilters['page']);
                            $filterQueryString = http_build_query($currentFilters);
                            ?>

                            <!-- Pagination UI -->
                            <form method="get" class="m-2">
                                <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <ul class="pagination mb-0">
                                        <!-- Previous Button -->
                                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= $filterQueryString ?>&page=<?= $page - 1 ?>" tabindex="-1">Prev</a>
                                        </li>

                                        <!-- Current Page Indicator -->
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                Page <?= $page ?> of <?= $totalPages ?>
                                            </span>
                                        </li>

                                        <!-- Next Button -->
                                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= $filterQueryString ?>&page=<?= $page + 1 ?>">Next</a>
                                        </li>
                                    </ul>

                                    <!-- Direct Page Jump -->
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="pageInput" class="form-label m-0">Go to page:</label>
                                        <input type="number" name="page" id="pageInput" min="1" max="<?= $totalPages ?>" class="form-control" style="width: 80px;" required>

                                        <!-- Preserve filters as hidden fields -->
                                        <?php foreach ($currentFilters as $key => $value): ?>
                                            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                                        <?php endforeach; ?>

                                        <button type="submit" class="btn btn-outline-success">Go</button>
                                    </div>
                                </nav>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'comps/loan_calculator.php' ?>
    <?php include 'comps/footer.php'; ?>

    <script>
        document.getElementById('pageInput').addEventListener('input', function() {
            const max = <?= $totalPages ?>;
            if (this.value > max) this.value = max;
            if (this.value < 1) this.value = 1;
        });
    </script>
</body>

</html>