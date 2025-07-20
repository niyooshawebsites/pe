<?php
require __DIR__ . '/../includes/session.php';

// printing the session array for debugging
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

// If not logged in or not a user, redirect to login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../logout.php');
    exit();
}

// printing errors for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/php-error.log');

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

if (isset($_GET['action']) && $_GET['action'] === 'mark_done' && isset($_GET['id'])) {
    markDone();
}

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Total rows for pagination
$totalRows = $conn->query("SELECT COUNT(*) FROM listProperty")->fetch_row()[0];
$totalPages = ceil($totalRows / $limit);

$filters = [
    'typeOfProperty' => $_GET['typeOfProperty'] ?? null,
    'min_area' => $_GET['min_area'] ?? null,
    'max_area' => $_GET['max_area'] ?? null,
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
    'locality2' => $_GET['locality2'] ?? null
];

// Fetch the paginated records
$listData = listListedAndPublishedProperties($limit, $offset, $filters);
$allLocalities = fetchAllLocalities();

?>

<?php
$pageTitle = "User Dashboard - All Listed Properties";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <a href="dashboard.php" class="btn btn-outline-light">Back</a>

                        <!-- Off-canvas Toggle Button (Visible only on small screens) -->
                        <div class="d-md-none d-flex justify-content-center my-2">
                            <button class="btn btn-outline-danger" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                                Filter
                            </button>
                        </div>
                    </div>

                    <!-- Data Table Card -->
                    <div class="card shadow border-danger">
                        <div class="card-header bg-danger text-white text-center">
                            <h5 class="mb-0">All Properties For SALE</h5>
                        </div>
                        <div class="card-body p-0">

                            <!-- Filter Form -->
                            <form method="get" class="border-bottom p-3 d-none d-md-flex">
                                <div class="row">
                                    <div class="col-md-2">
                                        <select class="form-select" aria-label="Default select example" name="typeOfProperty">
                                            <option value="" selected>Property Type</option>
                                            <option value="1 BHK">1 BHK</option>
                                            <option value="2 BHK">2 BHK</option>
                                            <option value="3 BHK">3 BHK</option>
                                            <option value="4 BHK">4 BHK</option>
                                            <option value="House">House</option>
                                            <option value="Villa">Villa</option>
                                            <option value="Bungalow">Bungalow</option>
                                            <option value="Pent House">Pent House</option>
                                            <option value="Shop">Shop</option>
                                            <option value="Land">Land</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row g-2 align-items-end">
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
                                                <input type="number" name="min_area" class="form-control" placeholder="Min Area" value="<?= $_GET['min_area'] ?? '' ?>">
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <input type="number" name="max_area" class="form-control" placeholder="Max Area" value="<?= $_GET['max_area'] ?? '' ?>">
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <input type="number" name="min_price" class="form-control" placeholder="Min Price" value="<?= $_GET['min_price'] ?? '' ?>">
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <input type="number" name="max_price" class="form-control" placeholder="Max Price" value="<?= $_GET['max_price'] ?? '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 d-md-flex justify-end gap-2 m-2">
                                                <button type="submit" class="btn btn-danger">Filter</button>
                                                <a href="" class="btn btn-secondary">Reset</a>
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
                                            <select class="form-select" aria-label="Default select example" name="typeOfProperty">
                                                <option value="" selected>Property Type</option>
                                                <option value="1 BHK">1 BHK</option>
                                                <option value="2 BHK">2 BHK</option>
                                                <option value="3 BHK">3 BHK</option>
                                                <option value="4 BHK">4 BHK</option>
                                                <option value="House">House</option>
                                                <option value="Villa">Villa</option>
                                                <option value="Bungalow">Bungalow</option>
                                                <option value="Pent House">Pent House</option>
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
                                            <input type="number" name="min_area" class="form-control" placeholder="Min Area" value="<?= $_GET['min_area'] ?? '' ?>">
                                        </div>
                                        <div class="col-12">
                                            <input type="number" name="max_area" class="form-control" placeholder="Max Area" value="<?= $_GET['max_area'] ?? '' ?>">
                                        </div>
                                        <div class="col-12">
                                            <input type="number" name="min_price" class="form-control" placeholder="Min Price" value="<?= $_GET['min_price'] ?? '' ?>">
                                        </div>
                                        <div class="col-12">
                                            <input type="number" name="max_price" class="form-control" placeholder="Max Price" value="<?= $_GET['max_price'] ?? '' ?>">
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <button type="submit" class="btn btn-danger">Apply Filter</button>
                                            <a href="" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Property Type</th>
                                            <th>Area (Sq. Yard)</th>
                                            <th>Location</th>
                                            <th>Expected Price</th>
                                            <th>Action</th>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($listData)): ?>
                                            <?php foreach ($listData as $index => $row): ?>
                                                <tr>
                                                    <th scope="row"><?= $offset + $index + 1 ?></th>
                                                    <td><?= date('d-m-Y', strtotime($row['created_at'] ?? '')) ?></td>
                                                    <td><?= htmlspecialchars($row['typeOfProperty']) ?></td>
                                                    <td><?= formatIndianNumber($row['areaInGaj']) ?></td>
                                                    <td><?= htmlspecialchars($row['locality2']) ?></td>
                                                    <td>Rs <?= formatIndianNumber($row['exprectedPrice']) ?></td>
                                                    <td class="d-flex flex-column flex-md-row gap-1">
                                                        <a href="view_on_sale_property.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="12" class="text-center">No property listings found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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

                                        <button type="submit" class="btn btn-outline-danger">Go</button>
                                    </div>
                                </nav>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <?php include '../comps/loan_calculator.php'; ?>
    <?php include '../comps/footer.php'; ?>

    <script>
        document.getElementById('pageInput').addEventListener('input', function() {
            const max = <?= $totalPages ?>;
            if (this.value > max) this.value = max;
            if (this.value < 1) this.value = 1;
        });
    </script>
</body>

</html>