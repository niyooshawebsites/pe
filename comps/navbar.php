<?php
$configFile =  require __DIR__ . '/../config/config.php';
?>

<?php $config = $configFile; ?>

<?php
$logoPath = '/admin/uploads/logo/logo.png';
$logoFullPath = $_SERVER['DOCUMENT_ROOT'] . $logoPath;

// Check if the logo file exists
$logoExists = file_exists($logoFullPath);

// Debugging output
if (!$logoExists) {
    error_log("Logo not found at: " . $logoFullPath);
}

?>

<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container-fluid">
        <!-- Brand -->
        <div class="navbar-brand mx-auto">
            <?php if ($logoExists): ?>
                <a href="/">
                    <img src="<?php echo $logoPath; ?>" alt="Logo" style="height: 60px;">
                </a>
            <?php else: ?>
                <a class="text-decoration-none fw-bold" href="/">
                    <?php echo $config['APP_NAME']; ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
            aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Offcanvas Menu -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
            aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>

            <div class="offcanvas-body">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <!--If logged in-->
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-primary" href="#">
                                <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                                    Welcome <span class="text-primary">ADMIN</span>,
                                <?php endif; ?>

                                <?php if ($_SESSION['user']['role'] == 'user'): ?>
                                    Welcome <span class="text-primary"><?= htmlspecialchars($_SESSION['user']['name']); ?></span>,
                                <?php endif; ?>
                            </a>
                        </li>

                        <!--If the role is user-->
                        <?php if ($_SESSION['user']['role'] == 'user'): ?>
                            <li class="nav-item">
                                <a class="btn btn-primary m-1" href="/user/dashboard.php">New Request</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-danger fw-bold blink-success" href="/user/all_listed_properties.php">ON SALE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-success fw-bold blink-success" href="/user/all_rented_properties.php">ON RENT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loanCalculatorModal">
                                    Loan Calculator
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/user/my_entries.php">My Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/profile.php">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/logout.php">logout</a>
                            </li>
                        <?php endif; ?>

                        <!--If the role is admin-->
                        <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/dashboard.php">My Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/locations.php">Locations</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Requests
                                </a>
                                <ul class="dropdown-menu bg-dark">
                                    <li>
                                        <a class="dropdown-item text-primary" href="/admin/buy_data.php">Buy Requests</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="/admin/sell_data.php">Sell Requests</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-warning" href="/admin/landLord_data.php">Landlord Requests</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-success" href="/admin/tenant_data.php">Tenant Requests</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/logo.php">Logo</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/profile.php">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/logout.php">logout</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>


                    <!--If not logged in-->
                    <?php if (!isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="https://niyooshawebsitesllp.in/product/property-expert/">SUBSCRIBE NOW FOR FREE</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mailto:<?php echo $config['ADMIN_EMAIL']; ?>"><i class="bi bi-envelope"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-" href="tel:<?php echo $config['ADMIN_MOBILE']; ?>"><i class="bi bi-telephone"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="/properties_available_for_sale.php">On SALE</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-success" href="/properties_available_for_rent.php">On RENT</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loanCalculatorModal">
                                Loan Calculator
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register.php">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-danger" id="installApp">APP <i class="bi bi-cloud-arrow-down-fill" style="font-size: 18px"></i> </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<?php include 'translate_widget.php'; ?>