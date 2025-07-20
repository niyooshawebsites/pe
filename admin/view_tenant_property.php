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

// Validate property ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid property ID.";
    exit();
}

$propertyId = intval($_GET['id']);
global $conn;

$stmt = $conn->prepare("SELECT * FROM tenant WHERE id = ?");
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Property not found.";
    exit();
}

$property = $result->fetch_assoc();


// message on whatsapp
$name = htmlspecialchars($_SESSION['user']['name']);
$typeOfProperty = htmlspecialchars($property['typeOfProperty']);
$budget = formatIndianNumber($property['budget']);
$mobile = htmlspecialchars($property['mobile']);
$locality = htmlspecialchars($property['locality2']);

// Build the message
$plainMessage = "Hi $name 👋,\n\nYou want to take a property on rent with the following details:\n\n🏡 *Property Type:* $typeOfProperty\n📐 💰 *Monthly Budget:* ₹$budget\n📍 *Location:* $locality\n\nWe have a few properties that matches your requirements.\n\nLet me know when can we have a conversation or a visit.";

// Encode for URL
$message = urlencode($plainMessage);

// Proper WhatsApp phone link
$clientPhone = '91' . $mobile;
$whatsappUrl = "https://wa.me/{$clientPhone}?text={$message}";

$propertyType = $property['typeOfProperty'];
$propertyState = $property['state'];
$propertyLocality = $property['locality2'];
$propertyTypeOfTenant = $property['typeOfTenant'];
$propertyRent = $property['budget'];

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Total rows for pagination
$totalRows = countSuggestedPropertiesForTenant($propertyType, $propertyState, $propertyLocality, $propertyTypeOfTenant, $propertyRent);
$totalPages = ceil($totalRows / $limit);

// Fetch the paginated records
$landLordData = suggestPropertiesForTenant($limit, $offset, $propertyType, $propertyState, $propertyLocality, $propertyTypeOfTenant, $propertyRent);
?>

<?php
$pageTitle = "Admin Dashboard - View Tenant Property";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill">
        <div class="container py-4">
            <a href="tenant_data.php" class="btn btn-outline-light mb-3">&larr; Back</a>

            <div class="card shadow border-success">
                <div class="card-header bg-success text-white text-center">
                    <h5 class="mb-0">Property Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2"><strong>Name:</strong> <?= htmlspecialchars($property['name']) ?></div>
                        <div class="col-md-4 mb-2"><strong>Mobile:</strong> <a href="tel:<?= htmlspecialchars($property['mobile']) ?>"><?= htmlspecialchars($property['mobile']) ?></a></div>
                        <div class="col-md-4 mb-2"><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($property['email']) ?>"><?= htmlspecialchars($property['email']) ?></a></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 mb-2"><strong>Property Type:</strong> <?= htmlspecialchars($property['typeOfProperty']) ?></div>
                        <div class="col-md-4 mb-2"><strong>Tenant Type:</strong> <?= htmlspecialchars($property['typeOfTenant']) ?></div>
                        <div class="col-md-4 mb-2"><strong>Monthly Budget:</strong> Rs <?= formatIndianNumber($property['budget']) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 mb-2"><strong>Location:</strong> <?= htmlspecialchars($property['locality2']) ?></div>
                        <div class="col-md-8"><strong>Message:</strong>
                            <a href="<?= $whatsappUrl ?>" target="_blank" data-bs-toggle="tooltip" title="Message on WhatsApp">
                                <i class="bi bi-whatsapp text-success mx-1" style="font-size: 25px"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12">

                    <div class="card shadow border-warning">
                        <div class="card-header bg-warning text-white text-center">
                            <h5 class="mb-0 text-dark">Suggested Properties</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Location</th>
                                            <th>Property Type</th>
                                            <th>Tenant Type</th>
                                            <th>Furniture</th>
                                            <th>Floor</th>
                                            <th>Food</th>
                                            <th>Rent</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($landLordData)): ?>
                                            <?php foreach ($landLordData as $index => $row): ?>
                                                <tr>
                                                    <th scope="row"><?= $index + 1 ?></th>
                                                    <td><?= date('d-m-Y', strtotime($row['created_at'] ?? '')) ?></td>
                                                    <td><?= htmlspecialchars($row['locality2']) ?></td>
                                                    <td><?= htmlspecialchars($row['propertyToLet']) ?></td>
                                                    <td><?= htmlspecialchars($row['tenantType']) ?></td>
                                                    <td><?= htmlspecialchars($row['furniture']) ?></td>
                                                    <td><?= formatIndianNumber($row['floor']) ?></td>
                                                    <td><?= htmlspecialchars($row['food']) ?></td>
                                                    <td><?= formatIndianNumber($row['rent']) ?></td>
                                                    <td>
                                                        <a href="view_landLord_property_suggestion.php?id=<?= $row['id'] ?>&tid=<?= $propertyId; ?>" class="btn btn-sm btn-warning">View</a>
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
                                            <a class="page-link" href="?page=<?= $page - 1 ?>" tabindex="-1">Prev</a>
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
                                        <button type="submit" class="btn btn-outline-warning text-dark">Go</button>
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