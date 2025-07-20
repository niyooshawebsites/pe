<?php
require __DIR__ . '/includes/session.php';
$config = require_once __DIR__ . '/config/config.php';
require 'includes/functions.php';

// printing errors for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/php-error.log');

// Validate property ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid property ID.";
    exit();
}

$propertyId = intval($_GET['id']);
global $conn;

// === Handle Interest Submission ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interestedBtn'])) {
    if (!isset($_SESSION['user']['id'])) {
        $_SESSION['error_msg'] = "Please login to show interest.";
        header("Location: /login.php?redirect=" . urlencode("/user/view_let_in_property.php?id=" . $propertyId));
        exit();
    }

    $userId = intval($_SESSION['user']['id']);

    // Get existing interested users
    $stmt = $conn->prepare("SELECT interestedUsers FROM landLord WHERE id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $stmt->bind_result($interestedUsersJSON);
    $stmt->fetch();
    $stmt->close();

    $interestedUsers = $interestedUsersJSON ? json_decode($interestedUsersJSON, true) : [];

    if (!is_array($interestedUsers)) {
        $interestedUsers = [];
    }

    if (!in_array($userId, $interestedUsers)) {
        $interestedUsers[] = $userId;
        $updatedJSON = json_encode($interestedUsers);

        $updateStmt = $conn->prepare("UPDATE landLord SET interestedUsers = ? WHERE id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("si", $updatedJSON, $propertyId);
            if ($updateStmt->execute()) {
                $_SESSION['success_msg'] = "Interest registered successfully!";
            } else {
                error_log("Update execute failed: " . $updateStmt->error);
                $_SESSION['error_msg'] = "Failed to register interest.";
            }
            $updateStmt->close();
        } else {
            error_log("Update prepare failed: " . $conn->error);
            $_SESSION['error_msg'] = "Server error. Try again.";
        }
    } else {
        $_SESSION['error_msg'] = "You have already shown interest in this property.";
    }

    header("Location: more_details_about_property_on_rent.php?id=" . $propertyId);
    exit();
}

// === Fetch Property ===
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

$isLoggedIn = isset($_SESSION['user']['id']);

// Check if user is already interested
$already_interested = false;

if ($isLoggedIn) {
    $userId = intval($_SESSION['user']['id']);

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
$pageTitle = "View Landlord Property";
include 'comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include 'comps/navbar.php'; ?>

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
                <a href="properties_available_for_rent.php" class="btn btn-outline-light mb-3">&larr; Back</a>
            </div>

            <div class="card shadow border-warning">
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
                        <div class="col-md-4 mb-2">
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

                    <!--Interested-->
                    <div class="mb-3">
                        <strong>Show your interest in the property:</strong><br>

                        <form method="POST" id="interestForm">
                            <input type="hidden" name="propertyId" value="<?= $propertyId ?>">

                            <input
                                type="submit"
                                name="interestedBtn"
                                id="interestedBtn"
                                class="<?= $already_interested ? 'btn btn-outline-secondary' : 'btn btn-outline-success' ?>"
                                value="<?= $already_interested ? 'You are already interested' : 'I am Interested' ?>"
                                <?= $already_interested ? 'disabled' : '' ?>>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'comps/footer.php'; ?>

    <script>
        const isLoggedIn = <?= json_encode($isLoggedIn) ?>;
        const propertyId = <?= json_encode($propertyId) ?>;

        document.getElementById('interestForm')?.addEventListener('submit', function(e) {
            if (!isLoggedIn) {
                e.preventDefault();
                const confirmLogin = confirm("You need to login to show interest. Do you want to login now?");
                if (confirmLogin) {
                    const redirectPath = `/user/view_let_in_property.php?id=${propertyId}`;
                    const encodedRedirect = encodeURIComponent(redirectPath);
                    window.location.href = `/login.php?redirect=${encodedRedirect}`;
                }
            }
        });
    </script>
</body>

</html>