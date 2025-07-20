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
    markDone();
}

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Total rows for pagination
$totalRows = $conn->query("SELECT COUNT(*) FROM listProperty")->fetch_row()[0];
$totalPages = ceil($totalRows / $limit);

// Fetch the paginated records
$listData = listListedProperties($limit, $offset);

?>

<?php
$pageTitle = "Admin Dashboard - List Data";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill">
        <div class="container-fuild p-5">
            <div class="row justify-content-center">
                <div class="col-12">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <a href="dashboard.php" class="btn btn-outline-light">Back</a>
                    </div>

                    <!-- Data Table Card -->
                    <div class="card shadow border-danger">
                        <div class="card-header bg-danger text-white text-center">
                            <h5 class="mb-0">All Sell Requests</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th>#</th>
                                            <th class="text-cemter">Date</th>
                                            <th>Name</th>
                                            <th class="text-cemter">Mobile</th>
                                            <th>Email</th>
                                            <th>Location</th>
                                            <th class="text-cemter">Area (Sq. Yard)</th>
                                            <th class="text-cemter">Expected Price</th>
                                            <th class="text-cemter">Interested</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($listData)): ?>
                                            <?php foreach ($listData as $index => $row): ?>
                                                <tr>
                                                    <th scope="row"><?= $offset + $index + 1 ?></th>
                                                    <td><?= date('d-m-Y', strtotime($row['created_at'] ?? '')) ?></td>
                                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                                    <td>
                                                        <a href="tel:<?= htmlspecialchars($row['mobile']) ?>">
                                                            <?= htmlspecialchars($row['mobile']) ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="mailto:<?= htmlspecialchars($row['email']) ?>">
                                                            <?= htmlspecialchars($row['email']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['locality2']) ?></td>
                                                    <td><?= formatIndianNumber($row['areaInGaj']) ?></td>
                                                    <td>Rs <?= formatIndianNumber($row['exprectedPrice']) ?></td>
                                                    <td>
                                                        <?php
                                                        $interestedUsers = $row['interestedUsers'];
                                                        $interestedArray = json_decode($interestedUsers, true);
                                                        $interestedCount = is_array($interestedArray) ? count($interestedArray) : 0;
                                                        if ($interestedCount > 0):
                                                        ?>
                                                            <a
                                                                href="view_interested_users.php?property_id=<?= $row['id'] ?>"
                                                                data-bs-toggle="tooltip"
                                                                title="Number of interested buyers in this property! Click to show the list.">
                                                                <?= $interestedCount ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span
                                                                data-bs-toggle="tooltip"
                                                                title="Number of interested buyers in this property!">
                                                                0
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                                    <td class="d-flex flex-column flex-md-row gap-1">
                                                        <a href="view_sell_property.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">View</a>
                                                        <?php if ($row['done'] === 'yes'): ?>
                                                            <button class="btn btn-sm btn-secondary" disabled>Done</button>
                                                        <?php else: ?>
                                                            <a href="?action=mark_done&id=<?= $row['id'] ?>" class="btn btn-sm btn-dark" onclick="return confirm('Mark this as done?')">
                                                                Done
                                                            </a>
                                                        <?php endif; ?>
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

    <?php include '../comps/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>

    <script>
        document.getElementById('pageInput').addEventListener('input', function() {
            const max = <?= $totalPages ?>;
            if (this.value > max) this.value = max;
            if (this.value < 1) this.value = 1;
        });
    </script>
</body>

</html>