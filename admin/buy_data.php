<?php
require __DIR__ . '/../includes/session.php';

// printing the session array for debugging
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

// If not logged in or not a user, redirect to login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
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
    markDoneForBuyProperty();
}

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Total rows for pagination
$totalRows = $conn->query("SELECT COUNT(*) FROM buyProperty")->fetch_row()[0];
$totalPages = ceil($totalRows / $limit);

// Fetch the paginated records
$purchaseData = listPurchaseProperty($limit, $offset);
?>

<?php
$pageTitle = "Admin Dashboard - Buy Data";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill">
        <div class="container-fluid p-4">
            <div class="row justify-content-center">
                <div class="col-12">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <a href="dashboard.php" class="btn btn-outline-light">Back</a>
                    </div>

                    <!-- Card Table -->
                    <div class="card shadow border-primary">
                        <div class="card-header bg-primary text-white text-center">
                            <h5 class="mb-0">All Buy Requests</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>Property Type</th>
                                            <th>Area (Sq. yard)</th>
                                            <th>Location</th>
                                            <th>Budget</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($purchaseData)): ?>
                                            <?php foreach ($purchaseData as $index => $row): ?>
                                                <tr>
                                                    <th scope="row"><?= $index + 1 ?></th>
                                                    <td><?= date('d-m-Y', strtotime($row['created_at'] ?? '')) ?></td>
                                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                                    <td><a href="tel:<?= htmlspecialchars($row['mobile']) ?>"><?= htmlspecialchars($row['mobile']) ?></a></td>
                                                    <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
                                                    <td><?= htmlspecialchars($row['typeOfProperty']) ?></td>
                                                    <td><?= formatIndianNumber($row['areaInGaj']) ?></td>
                                                    <td><?= htmlspecialchars($row['locality2']) ?></td>
                                                    <td>Rs <?= formatIndianNumber($row['budget']) ?></td>

                                                    <?php
                                                    // message on whatsapp
                                                    $name = htmlspecialchars($_SESSION['user']['name']);
                                                    $typeOfProperty = htmlspecialchars($row['typeOfProperty']);
                                                    $areaInGaj = formatIndianNumber($row['areaInGaj']);
                                                    $budget = formatIndianNumber($row['budget']);
                                                    $mobile = htmlspecialchars($row['mobile']);
                                                    $locality = htmlspecialchars($row['locality2']);

                                                    // Build the message
                                                    $plainMessage = "Hi $name 👋,\n\nYou want to purchase a property with the following details:\n\n🏡 *Property Type:* $typeOfProperty\n📐 *Area:* {$areaInGaj} Sq. Yard\n💰 *Budget:* ₹$budget\n📍 *Location:* $locality\n\nWe have a few properties that mathces your requirements.\n\nLet me know when can we have a conversation or a visit.";

                                                    // Encode for URL
                                                    $message = urlencode($plainMessage);

                                                    // Proper WhatsApp phone link
                                                    $clientPhone = '91' . $mobile;
                                                    $whatsappUrl = "https://wa.me/{$clientPhone}?text={$message}";

                                                    ?>
                                                    <td class="d-flex flex-column flex-md-row gap-1">
                                                        <a href="<?= $whatsappUrl ?>" target="_blank" data-bs-toggle="tooltip" title="Message on WhatsApp">
                                                            <i class="bi bi-whatsapp text-success mx-1" style="font-size: 25px"></i>
                                                        </a>
                                                        <a href="view_buy_property.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                                        <?php if ($row['done'] === 'yes'): ?>
                                                            <button class="btn btn-sm btn-secondary" disabled>Done</button>
                                                        <?php else: ?>
                                                            <a href="?action=mark_done&id=<?= $row['id'] ?>" class="btn btn-sm btn-dark" onclick="return confirm('Mark this as done?')">Done</a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="12" class="text-center">No data found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination UI -->
                            <form method="get" class="m-2">
                                <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <ul class="pagination mb-0">
                                        <!-- Previous Button -->
                                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>" tabindex="-1">Previous</a>
                                        </li>

                                        <!-- Current Page Indicator -->
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                Page <?= $page ?> of <?= $totalPages ?>
                                            </span>
                                        </li>

                                        <!-- Next Button -->
                                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                        </li>
                                    </ul>

                                    <!-- Direct Page Jump -->
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="pageInput" class="form-label m-0">Go to page:</label>
                                        <input type="number" name="page" id="pageInput" min="1" max="<?= $totalPages ?>" class="form-control" style="width: 80px;" required>
                                        <button type="submit" class="btn btn-outline-primary">Go</button>
                                    </div>
                                </nav>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../comps/footer.php'; ?>

</body>

</html>