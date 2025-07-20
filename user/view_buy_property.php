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

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../logout.php');
    exit();
}

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

// Validate property ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid property ID.";
    exit();
}

$propertyId = intval($_GET['id']);
global $conn;

$stmt = $conn->prepare("SELECT * FROM buyProperty WHERE id = ?");
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Property not found.";
    exit();
}

$property = $result->fetch_assoc();

$propertyType = $property['typeOfProperty'];
$propertyState = $property['state'];
$propertyLocality = $property['locality2'];
$propertyArea = $property['areaInGaj'];
$propertyBudget = $property['budget'];

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Total rows for pagination
$totalRows = countSuggestedPropertiesForBuyers($propertyType, $propertyState, $propertyLocality, $propertyArea, $propertyBudget);

$totalPages = ceil($totalRows / $limit);

// Fetch the paginated records
$listData = suggestProperties($limit, $offset, $propertyType, $propertyState, $propertyLocality, $propertyArea, $propertyBudget);
?>

<?php
$pageTitle = "User Dashboard - View Buy Property";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill py-4">
        <div class="container">
            <a href="buy_data.php" class="btn btn-outline-light mb-3">&larr; Back</a>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">Property Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Type of Property</strong> <?= htmlspecialchars($property['typeOfProperty']) ?></div>
                        <div class="col-md-4"><strong>Area (Sq. Yard):</strong> <?= formatIndianNumber($property['areaInGaj']) ?></div>
                        <div class="col-md-4"><strong>Budget:</strong> ₹<?= formatIndianNumber($property['budget']) ?></div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Locality:</strong> <?= htmlspecialchars($property['locality2']) ?></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12">
                    <!-- Data Table Card -->
                    <div class="card shadow border-danger">
                        <div class="card-header bg-danger text-white text-center">
                            <h5 class="mb-0">Suggested Properties</h5>
                        </div>
                        <div class="card-body p-0">

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
                                                        <a href="view_on_sale_property_suggestion.php?sid=<?= $row['id'] ?>&bid=<?= $propertyId ?>" class="btn btn-sm btn-danger">View</a>
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

</body>

</html>