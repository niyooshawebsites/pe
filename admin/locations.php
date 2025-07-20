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

// Handle deletion request
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    if (deleteLocality($id)) {
        $_SESSION['success_msg'] = 'Location deleted successfully!';
    } else {
        $_SESSION['error_msg'] = 'Error deleting location.';
    }

    header("Location: locations.php");
    exit();
}

// Handle adding location
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locality = htmlspecialchars(trim($_POST['locality']));

    if (!$locality) {
        $_SESSION['error_msg'] = 'Invalid or missing input fields.';
        header("Location: locations.php");
        exit();
    }

    if (addLocality($locality)) {
        $_SESSION['success_msg'] = 'Locality added successfully!';
    } else {
        $_SESSION['error_msg'] = 'Error adding locality. Please try again.';
    }

    header("Location: locations.php");
    exit();
}

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$totalRows = $conn->query("SELECT COUNT(*) FROM localities")->fetch_row()[0];
$totalPages = ceil($totalRows / $limit);

$localitiesData = fetchLocalities($limit, $offset);
?>

<?php
$pageTitle = "User Dashboard - Buy Property";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-5 col-lg-6">
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

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <a href="dashboard.php" class="btn btn-outline-light">Back</a>
                    <a href="" class="btn btn-outline-light">Reset Form</a>
                </div>

                <div class="card shadow border-primary">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Locations</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="locality" class="form-label">Location</label>
                                <input type="text" name="locality" class="form-control" id="locality" placeholder="Enter the service locations" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Add location</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center my-5">
            <div class="col-12 col-md-5 col-lg-6">
                <div class="card shadow border-danger">
                    <div class="card-header bg-danger text-white text-center">
                        <h5 class="mb-0">Your Service Locations</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light text-nowrap">
                                    <tr>
                                        <th>#</th>
                                        <th>Location</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($localitiesData)): ?>
                                        <?php foreach ($localitiesData as $index => $row): ?>
                                            <tr>
                                                <th scope="row"><?= $offset + $index + 1 ?></th>
                                                <td><?= htmlspecialchars($row['locality']) ?></td>
                                                <td class="text-center">
                                                    <a href="locations.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-dark"
                                                        onclick="return confirm('Are you sure you want to delete this location?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No locations found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <form method="get" class="m-2">
                            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <ul class="pagination mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
                                    </li>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                </ul>
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
    </main>

    <?php include '../comps/loan_calculator.php'; ?>
    <?php include '../comps/footer.php'; ?>
</body>

</html>