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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $areaInGaj = htmlspecialchars(trim($_POST['areaInGaj']));
    $address = htmlspecialchars(trim($_POST['address']));
    $locality2 = htmlspecialchars(trim($_POST['locality2']));
    $state = htmlspecialchars(trim($_POST['state']));
    $exprectedPrice = htmlspecialchars(trim($_POST['exprectedPrice']));
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile']);
    $userId = intval($_SESSION['user']['id'] ?? 0);
    $typeOfProperty = htmlspecialchars($_POST['typeOfProperty']);

    if (!$email || $userId === 0 || empty($areaInGaj) || empty($address) || empty($exprectedPrice) || empty($locality2) || empty($state) || !$typeOfProperty) {
        $_SESSION['error_msg'] = 'Invalid or missing input.';
        header("Location: sell_property.php");
        exit();
    }

    // Handle image uploads
    $imagePaths = [];
    if (!empty($_FILES['property_images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $maxImages = 10;
        $totalImages = count($_FILES['property_images']['name']);
        $uploadCount = min($totalImages, $maxImages);

        $skippedImages = 0;

        for ($i = 0; $i < $uploadCount; $i++) {
            $imageName = $_FILES['property_images']['name'][$i];
            $imageTmp = $_FILES['property_images']['tmp_name'][$i];
            $imageSize = $_FILES['property_images']['size'][$i];
            $imageError = $_FILES['property_images']['error'][$i];

            if ($imageError === 0) {
                if ($imageSize > 500 * 1024) {
                    $skippedImages++;
                    continue; // Skip images > 500KB
                }

                $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $newName = uniqid('img_', true) . '.' . $ext;
                    $path = 'uploads/property_images/' . $newName;
                    if (move_uploaded_file($imageTmp, $path)) {
                        $imagePaths[] = $path;
                    }
                }
            }
        }

        if ($totalImages > $maxImages) {
            $_SESSION['error_msg'] = 'Only 10 images were uploaded. Extra images were ignored.';
        }

        if ($skippedImages > 0) {
            $_SESSION['error_msg'] .= ' ' . $skippedImages . ' image(s) were larger than 500KB and not uploaded.';
        }
    }

    // Handle document upload
    $documentPath = '';
    if (!empty($_FILES['property_documents']['name'])) {
        $allowedDocs = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
        $docName = $_FILES['property_documents']['name'];
        $docTmp = $_FILES['property_documents']['tmp_name'];
        $docSize = $_FILES['property_documents']['size'];
        $docError = $_FILES['property_documents']['error'];

        if ($docError === 0 && $docSize <= 10 * 1024 * 1024) {
            $ext = strtolower(pathinfo($docName, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedDocs)) {
                $newName = uniqid('doc_', true) . '.' . $ext;
                $documentPath = 'uploads/property_documents/' . $newName;
                move_uploaded_file($docTmp, $documentPath);
            }
        }
    }

    if (listProperty($areaInGaj, $address, $exprectedPrice, $name, $email, $mobile, $imagePaths, $documentPath, $userId, $locality2, $state, $typeOfProperty)) {
        $_SESSION['success_msg'] = 'Property listed successfully!';
    } else {
        $_SESSION['error_msg'] = 'Failed to list property. Try again.';
    }

    header("Location: sell_property.php");
    exit();
}

$allLocalities = fetchAllLocalities();
?>

<?php
$pageTitle = "User Dashboard - Sell Property";
include '../comps/header.php';
?>

<body class="d-flex flex-column min-vh-100 app-background">
    <?php include '../comps/navbar.php'; ?>

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

                <!--Responsive Header-->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <a href="dashboard.php" class="btn btn-outline-light">Back</a>
                    <a href="" class="btn btn-outline-light">Reset Form</a>
                </div>

                <div class="card shadow border-danger">
                    <div class="card-header bg-danger text-white text-center">
                        <h4>Sell Property</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="typeOfProperty" class="form-label">Type of Property</label>
                                <select class="form-select" aria-label="Default select example" name="typeOfProperty">
                                    <option value="" selected>Select</option>
                                    <option value="1 BHK">1 BHK</option>
                                    <option value="2 BHK">2 BHK</option>
                                    <option value="3 BHK">3 BHK</option>
                                    <option value="4 BHK">4 BHK</option>
                                    <option value="House">House</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Bungalow">Bungalow</option>
                                    <option value="Pent House">Pent House</option>
                                    <option value="Shop">Shop</option>
                                    <option value="Land">Land</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="areaInGaj" class="form-label">Area (Sq. Yard)</label>
                                <input type="number" name="areaInGaj" class="form-control" id="areaInGaj" required>
                            </div>
                            <div class="mb-3">
                                <label for="exprectedPrice" class="form-label">Expected Price</label>
                                <input type="number" name="exprectedPrice" class="form-control" id="exprectedPrice" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Property Address</label>
                                <input type="text" name="address" class="form-control" id="address" placeholder="Enter your property address" required>
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
                            <input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
                            <input type="hidden" name="mobile" value="<?= htmlspecialchars($_SESSION['user']['mobile'] ?? '') ?>">

                            <div class="mb-3">
                                <label for="property_images" class="form-label">Upload Property Images (Max 10) <span class="text-muted">* File size must be less than 500KB</span></label>
                                <input type="file" name="property_images[]" class="form-control" id="property_images" accept="image/*" multiple required onchange="validateImageCount(this)">
                            </div>

                            <div class="mb-3">
                                <label for="property_documents" class="form-label">Upload Property Documents (Image/PDF) <span class="text-muted"> * File size must be less than 500KB</span></label>
                                <input type="file" name="property_documents" class="form-control" id="property_documents" accept="image/*,application/pdf" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="accuracyInfo" class="text-danger mt-2"></div>
            </div>
        </div>
    </main>

    <?php include '../comps/loan_calculator.php'; ?>
    <?php include '../comps/footer.php'; ?>

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