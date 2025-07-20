<?php
require __DIR__ . '/../includes/session.php';

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit();
}

// Validate property ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid property ID.";
    exit();
}

$propertyId = intval($_GET['id']);
global $conn;

$stmt = $conn->prepare("SELECT * FROM listProperty WHERE id = ?");
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Property not found.";
    exit();
}

$property = $result->fetch_assoc();
$images = explode(',', $property['images']);
$documentPath = $property['document'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize and validate inputs
    $statusChange = trim($_POST['statusChange']);

    if (!$statusChange) {
        $_SESSION['error_msg'] = 'Invalid or missing input fields.';
        header("Location: view_sell_property.php?id=$propertyId");
        exit();
    }

    if (changeStatusForPropertyOnSale($statusChange, $propertyId)) {
        $_SESSION['success_msg'] = 'Status changed successfully!';
    } else {
        $_SESSION['error_msg'] = 'Error submitting request. Please try again.';
    }

    header("Location: view_sell_property.php?id=$propertyId");
    exit();
}
?>

<?php
$pageTitle = "Admin Dashboard - View Sell Property";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill py-4">
        <div class="container">

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

            <div class="d-flex justify-content-between">
                <a href="sell_data.php" class="btn btn-outline-light mb-3">&larr; Back</a>
                <form method="post" class="row row-cols-lg-auto g-3 align-items-center">
                    <div class="col-12">
                        <select class="form-select" id="statusChange" name="statusChange" aria-label="Select status">
                            <option selected>Select</option>
                            <option value="Pending">Pending</option>
                            <option value="Published">Published</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Change status</button>
                    </div>
                </form>
            </div>

            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0">Property Details</h4>
                </div>

                <div class="card-body">
                    <!-- Property Info -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-2">
                            <strong>Name:</strong> <?= htmlspecialchars($property['name']) ?>
                        </div>
                        <div class="col-12 col-md-4 mb-2">
                            <strong>Mobile:</strong> <a href="tel:<?= htmlspecialchars($property['mobile']) ?>"><?= htmlspecialchars($property['mobile']) ?></a>
                        </div>
                        <div class="col-12 col-md-4 mb-2">
                            <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($property['email']) ?>"><?= htmlspecialchars($property['email']) ?></a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <strong>Property Type:</strong> <?= htmlspecialchars($property['typeOfProperty']) ?>
                        </div>
                        <div class="col-12 col-md-4 mb-2">
                            <strong>Area(Sq. Yard):</strong> <?= formatIndianNumber($property['areaInGaj']) ?>
                        </div>
                        <div class="col-12 col-md-4 mb-2">
                            <strong>Expected Price: </strong>Rs <?= formatIndianNumber($property['exprectedPrice']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-2">
                            <strong>Location:</strong> <?= htmlspecialchars($property['locality2']) ?>
                        </div>
                        <div class="col-12 col-md-8 mb-2">
                            <strong>Property Address:</strong> <?= htmlspecialchars($property['address']) ?>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="mb-4">
                        <strong>Property Images:</strong>
                        <div class="row">
                            <?php foreach ($images as $img): ?>
                                <?php if (!empty(trim($img))): ?>
                                    <div class="col-6 col-sm-4 col-md-3 mb-3">
                                        <a href="/user/uploads/property_images/<?= basename($img) ?>" data-lightbox="property-gallery" data-title="Click image to close">
                                            <img src="/user/uploads/property_images/<?= basename($img) ?>" class="img-fluid rounded border property-image" />
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Document -->
                    <div class="mb-3">
                        <strong>Property Document:</strong><br>
                        <?php if (!empty($documentPath)): ?>
                            <a href="/user/uploads/property_documents/<?= basename($documentPath) ?>" class="btn btn-outline-danger mt-2" download>
                                Download Document
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No document uploaded.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../comps/footer.php'; ?>
</body>


</html>