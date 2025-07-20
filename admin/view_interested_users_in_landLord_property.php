<?php
require __DIR__ . '/../includes/session.php';

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit();
}

if (!isset($_GET['property_id'])) {
    die('Invalid request.');
}

$propertyId = (int) $_GET['property_id'];

// Fetch interestedUsers from listProperty
$stmt = $conn->prepare("SELECT propertyToLet, rent, locality2, state, interestedUsers FROM landLord WHERE id = ?");
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die('Property not found.');
}

$interestedArray = json_decode($row['interestedUsers'], true);

if (!is_array($interestedArray) || count($interestedArray) === 0) {
    echo "<p>No users have shown interest in this property.</p>";
    exit();
}

// Build placeholders and bind values
$placeholders = implode(',', array_fill(0, count($interestedArray), '?'));
$types = str_repeat('s', count($interestedArray)); // assuming user_id is varchar
$stmt = $conn->prepare("SELECT id, name, email, mobile FROM users WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$interestedArray);
$stmt->execute();
$usersResult = $stmt->get_result();
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

// Pagination Setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalUsers = count($users);
$totalPages = ceil($totalUsers / $limit);

// Slice users for current page
$users = array_slice($users, $offset, $limit);
?>

<?php
$pageTitle = "Admin Dashboard - Interested Users Data";
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
                        <a href="landLord_data.php" class="btn btn-outline-light">Back</a>
                    </div>

                    <!-- Data Table Card -->
                    <div class="card shadow border-warning">
                        <div class="card-header bg-warning text-white text-center">
                            <h5 class="mb-0">Interested Tenants</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($users)): ?>
                                            <?php foreach ($users as $index => $user): ?>
                                                <tr>
                                                    <th scope="row"><?= $offset + $index + 1 ?></th>
                                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                                    <td><a href="mailto:<?= htmlspecialchars($user['email']) ?>" data-bs-toggle="tooltip" title="Email now!"><?= htmlspecialchars($user['email']) ?></a></td>
                                                    <td><a href="tel:<?= htmlspecialchars($user['mobile']) ?>" data-bs-toggle="tooltip" title="Call now!"><?= htmlspecialchars($user['mobile']) ?></a></td>

                                                    <?php
                                                    $name = htmlspecialchars($user['name']);
                                                    $typeOfProperty = htmlspecialchars($row['propertyToLet']);
                                                    $rent = formatIndianNumber($row['rent']);
                                                    $mobile = htmlspecialchars($user['mobile']);
                                                    $locality = htmlspecialchars($row['locality2']);

                                                    // Build the message
                                                    $plainMessage = "Hi $name 👋,\n\nHere are the property details you showed interest in:\n\n🏡 *Property Type:* $typeOfProperty\n 💰 *Monthy Rent:* ₹$rent \n📍 *Location:* $locality\n\nLet me know if you'd like more details or a visit.";

                                                    // Encode for URL
                                                    $message = urlencode($plainMessage);

                                                    // Proper WhatsApp phone link
                                                    $clientPhone = '91' . $mobile;
                                                    $whatsappUrl = "https://wa.me/{$clientPhone}?text={$message}";
                                                    ?>
                                                    <td>
                                                        <a href="<?= $whatsappUrl ?>" target="_blank" data-bs-toggle="tooltip" title="Message on WhatsApp">
                                                            <i class="bi bi-whatsapp text-success mx-1" style="font-size: 25px"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="12" class="text-center">No interested users found.</td>
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
                                        <button type="submit" class="btn btn-outline-warning">Go</button>
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
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
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