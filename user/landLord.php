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

// If not logged in or not a user, redirect to login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../logout.php');
    exit();
}

$config = require_once __DIR__ . '/../config/config.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize and validate inputs
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $propertyToLet = $_POST['propertyToLet'] ?? '';
    $floor = isset($_POST['floor']) ? (int) $_POST['floor'] : null;
    $furniture = $_POST['furniture'] ?? '';
    $rent = isset($_POST['rent']) ? (float) $_POST['rent'] : 0.0;
    $tenantType = $_POST['tenantType'] ?? '';
    $food = $_POST['food'] ?? '';
    $locality2 = $_POST['locality2'] ?? '';
    $state = $_POST['state'] ?? '';

    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile']);
    $userId = intval($_SESSION['user']['id'] ?? 0);

    // Handle image uploads
    $imagePaths = [];
    if (!empty($_FILES['property_images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $totalFiles = count($_FILES['property_images']['name']);
        $maxFiles = 10;

        for ($i = 0; $i < min($totalFiles, $maxFiles); $i++) {
            $imageName = $_FILES['property_images']['name'][$i];
            $imageTmp = $_FILES['property_images']['tmp_name'][$i];
            $imageSize = $_FILES['property_images']['size'][$i];
            $imageError = $_FILES['property_images']['error'][$i];

            // Check size <= 500 KB now
            if ($imageError === 0 && $imageSize <= 500 * 1024) {
                $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $newName = uniqid('img_', true) . '.' . $ext;
                    $path = 'uploads/property_images/' . $newName;
                    if (move_uploaded_file($imageTmp, $path)) {
                        $imagePaths[] = $path;
                    }
                }
            } else if ($imageSize > 500 * 1024) {
                $_SESSION['error_msg'] = 'Each image must be less than 500 KB.';
                break;
            }
        }

        // Show warning if more than max allowed
        if ($totalFiles > $maxFiles) {
            $_SESSION['error_msg'] = 'Only 10 images were uploaded. Extra images were ignored.';
        }
    }

    if (landLord($userId, $name, $email, $mobile, $address, $propertyToLet, $floor, $furniture, $rent, $tenantType, $food, $imagePaths, $locality2, $state)) {
        $_SESSION['success_msg'] = 'Property submitted successfully!';
    } else {
        $_SESSION['error_msg'] = 'Error submitting request. Please try again.';
    }

    header("Location: landLord.php");
    exit();
}

$allLocalities = fetchAllLocalities();
?>

<?php
$pageTitle = "User Dashboard - Landlord";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php' ?>

    <main class="flex-fill container py-4">

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">

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

                <div class="card shadow border-warning">
                    <div class="card-header bg-warning text-white text-center">
                        <h4>Landlord</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="propertyToLet" class="form-label">Property to let</label>
                                <select class="form-select" aria-label="Default select example" name="propertyToLet">
                                    <option value="" selected>Select</option>
                                    <option value="1 BHK">1 BHK</option>
                                    <option value="2 BHK">2 BHK</option>
                                    <option value="3 BHK">3 BHK</option>
                                    <option value="4 BHK">4 BHK</option>
                                    <option value="House">House</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Bungalow">Bungalow</option>
                                    <option value="Pent House">Pent House</option>
                                    <option value="Studio Appartment">Studio Appartment</option>
                                    <option value="Shop">Shop</option>
                                    <option value="Land">Land</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Location - Enter the full property address</label>
                                <input type="text" name="address" class="form-control" id="address" required>
                            </div>
                            <div class="mb-3">
                                <label for="locality2" class="form-label">Location</label>
                                <select class="form-select" aria-label="Default select example" name="locality2">
                                    <option value="" selected>Select</option>
                                    <?php foreach ($allLocalities as $locality): ?>
                                        <option value="<?= htmlspecialchars($locality['locality']) ?>">
                                            <?= htmlspecialchars($locality['locality']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <input type="hidden" name="state" class="form-control" id="state" placeholder="Enter the state" value="null" required>

                            <div class="mb-3">
                                <label for="floor" class="form-label">Floor</label>
                                <input type="number" name="floor" class="form-control" id="floor" placeholder="Enter 0 for ground floor" required>
                            </div>


                            <div class="mb-3">
                                <label for="room" class="form-label">Room Furniture</label>
                                <select class="form-select" aria-label="Default select example" name="furniture">
                                    <option value="" selected>Select</option>
                                    <option value="Furnished">Furnished</option>
                                    <option value="Semi Furnished">Semi Furnished</option>
                                    <option value="Unfurnished">Unfurnished</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="rent" class="form-label">Monthly Rent</label>
                                <input type="number" name="rent" class="form-control" id="rent" required inputmode="numeric">
                            </div>

                            <div class="mb-3">
                                <label for="tenantType" class="form-label">⁠Tenant Type</label>
                                <select class="form-select" aria-label="Default select example" name="tenantType">
                                    <option selected>Select</option>
                                    <option value="Family">Family</option>
                                    <option value="Unmarried">Unmarried</option>
                                    <option value="Commercial">Commercial</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="food" class="form-label">⁠Food</label>
                                <select class="form-select" aria-label="Default select example" name="food">
                                    <option selected>Select</option>
                                    <option value="Veg">Veg</option>
                                    <option value="Non-veg">Non-veg</option>
                                </select>
                            </div>

                            <!-- Hidden user info -->
                            <input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
                            <input type="hidden" name="mobile" value="<?= htmlspecialchars($_SESSION['user']['mobile'] ?? '') ?>">

                            <div class="mb-3">
                                <label for="property_images" class="form-label">Upload Property Images (Max 10)</label>
                                <input type="file" name="property_images[]" class="form-control" id="property_images" accept="image/*" multiple required onchange="validateImageCount(this)">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../comps/loan_calculator.php'; ?>
    <?php include '../comps/footer.php' ?>

    <script>
        function validateImageCount(input) {
            const maxImages = 10;
            const maxSizeKB = 500;

            if (input.files.length > maxImages) {
                alert("You can upload a maximum of 10 images.");
                input.value = '';
                return;
            }

            for (let i = 0; i < input.files.length; i++) {
                if (input.files[i].size > maxSizeKB * 1024) {
                    alert(`Image ${input.files[i].name} exceeds the 500 KB size limit.`);
                    input.value = '';
                    break;
                }
            }
        }
    </script>
</body>


</html>