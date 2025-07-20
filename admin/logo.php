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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/logo/';
    $allowed = ['png'];
    $maxSize = 500 * 1024; // 500 KB
    $errors = [];

    // --- Handle Mobile Logos (max 2) ---
    if (!empty($_FILES['mobile_logos']['name'][0])) {
        $totalMobileImages = count($_FILES['mobile_logos']['name']);
        $uploadCount = min($totalMobileImages, 2); // Only 2 allowed

        for ($i = 0; $i < $uploadCount; $i++) {
            $name = $_FILES['mobile_logos']['name'][$i];
            $tmp = $_FILES['mobile_logos']['tmp_name'][$i];
            $size = $_FILES['mobile_logos']['size'][$i];
            $error = $_FILES['mobile_logos']['error'][$i];

            if ($error === 0) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $errors[] = "Invalid file type for Mobile Logo: $name";
                    continue;
                }
                if ($size > $maxSize) {
                    $errors[] = "Mobile Logo '$name' exceeds 500KB.";
                    continue;
                }

                // Sanitize filename
                $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($name));
                $targetPath = $uploadDir . $safeName;

                move_uploaded_file($tmp, $targetPath);
            }
        }

        if ($totalMobileImages > 2) {
            $errors[] = "Only 2 mobile logos allowed. Extra files ignored.";
        }
    }

    // --- Handle Web Logo (only 1) ---
    if (isset($_FILES['web_logo']) && $_FILES['web_logo']['error'] === 0) {
        $name = $_FILES['web_logo']['name'];
        $tmp = $_FILES['web_logo']['tmp_name'];
        $size = $_FILES['web_logo']['size'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid file type for Web Logo.";
        } elseif ($size > $maxSize) {
            $errors[] = "Web Logo '$name' exceeds 500KB.";
        } else {
            // Sanitize filename
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($name));
            $targetPath = $uploadDir . $safeName;

            move_uploaded_file($tmp, $targetPath);
        }
    }

    if (empty($errors)) {
        $_SESSION['success_msg'] = "Logos uploaded successfully!";
    } else {
        $_SESSION['error_msg'] = implode(' ', $errors);
    }

    header("Location: logo.php");
    exit();
}

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

                <div class="card shadow border-primary">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Logo</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="mobile_logos" class="form-label">Upload Mobile App Logos (Max 2, under 500KB each)</label>
                                <input type="file" name="mobile_logos[]" class="form-control" id="mobile_logos" accept="image/*" multiple required>
                            </div>

                            <div class="mb-3">
                                <label for="web_logo" class="form-label">Upload Web App Logo (Max 1, under 500KB)</label>
                                <input type="file" name="web_logo" class="form-control" id="web_logo" accept="image/*" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Upload</button>
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
        document.getElementById('mobile_logos').addEventListener('change', function() {
            const maxFiles = 2;
            const maxSize = 500 * 1024; // 500 KB
            const files = this.files;

            if (files.length > maxFiles) {
                alert('Only 2 mobile logos are allowed.');
                this.value = '';
                return;
            }

            for (let i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    alert(`File "${files[i].name}" exceeds the 500KB size limit.`);
                    this.value = '';
                    break;
                }
            }
        });

        document.getElementById('web_logo').addEventListener('change', function() {
            const maxSize = 500 * 1024;
            const file = this.files[0];
            if (file && file.size > maxSize) {
                alert(`Web logo exceeds the 500KB size limit.`);
                this.value = '';
            }
        });
    </script>


</body>


</html>