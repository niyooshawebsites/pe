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

// Redirect if not logged in or not a user
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
$tenantId = intval($_GET['tid']);
global $conn;

$stmt = $conn->prepare("SELECT * FROM landLord WHERE id = ?");
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

if (isset($_POST['interestedBtn'])) {

    $userId = $_SESSION['user']['id'];

    if (showInterestInPropertyInLandlordProperty($propertyId, $userId)) {
        $_SESSION['success_msg'] = 'Thank you for showing your interest';
    } else {
        $_SESSION['error_msg'] = 'Error submitting request. Please try again.';
    }

    header("Location: view_landLord_property_suggestion.php?id=$propertyId&tid=$tenantId");
    exit();
}


$already_interested = false;

if (isset($_SESSION['user']['id']) && isset($propertyId)) {
    $userId = $_SESSION['user']['id'];

    $stmt = $conn->prepare("SELECT interestedUsers FROM landLord WHERE id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $stmt->bind_result($interested_users_json);
    $stmt->fetch();
    $stmt->close();

    $interested_users = $interested_users_json ? json_decode($interested_users_json, true) : [];
    if (!is_array($interested_users)) {
        $interested_users = [];
    }

    $already_interested = in_array($userId, $interested_users);
}
?>

<?php
$pageTitle = "User Dashboard - View Landlord Property";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

    <main class="flex-fill py-4">
        <div class="container">
            <a href="view_tenant_property.php?id=<?= $tenantId ?>" class="btn btn-outline-light mb-3">&larr; Back</a>

            <div class="card shadow">
                <div class="card-header bg-warning text-white text-center">
                    <h4 class="mb-0">Property Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <strong>Property Type:</strong> <?= htmlspecialchars($property['propertyToLet']) ?>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Floor:</strong> <?= htmlspecialchars($property['floor']) ?>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Furniture:</strong> <?= htmlspecialchars($property['furniture']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <strong>Tenant Type:</strong> <?= htmlspecialchars($property['tenantType']) ?>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Food:</strong> <?= htmlspecialchars($property['food']) ?>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Rent:</strong> ₹<?= formatIndianNumber($property['rent']) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 mb-2">
                            <strong>Location:</strong> <?= htmlspecialchars($property['locality2']) ?>
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

                    <div class="mb-3">
                        <strong>Show your interest in the property:</strong><br>
                        <form method="POST">
                            <input type="hidden" name="propertyId" value="<?= $propertyId ?>">
                            <input type="submit" name="interestedBtn"
                                class="<?= $already_interested ? 'btn btn-outline-secondary' : 'btn btn-outline-success' ?>"
                                value="<?= $already_interested ? 'You are already interested' : 'I am Interested' ?>"
                                <?= $already_interested ? 'disabled' : '' ?>>
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