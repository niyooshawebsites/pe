<?php
require_once __DIR__ . '/db.php';
function formatIndianNumber($number)
{
    $number = (string) $number;
    $decimal = '';

    // If there's a decimal part, separate it
    if (strpos($number, '.') !== false) {
        list($number, $decimal) = explode('.', $number);
        $decimal = '.' . $decimal;
    }

    $last3 = substr($number, -3);
    $rest = substr($number, 0, -3);

    if ($rest != '') {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        return $rest . ',' . $last3 . $decimal;
    } else {
        return $last3 . $decimal;
    }
}


function registerUser($name, $email, $mobile, $password, $role)
{
    global $conn;

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $checkEmail->close();
        return "Email is already registered.";
    }
    $checkEmail->close();

    // Check if mobile already exists
    $checkMobile = $conn->prepare("SELECT id FROM users WHERE mobile = ?");
    $checkMobile->bind_param("s", $mobile);
    $checkMobile->execute();
    $checkMobile->store_result();

    if ($checkMobile->num_rows > 0) {
        $checkMobile->close();
        return "Mobile number is already registered.";
    }
    $checkMobile->close();

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password, role) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        return "Database error: failed to prepare statement.";
    }

    $stmt->bind_param("sssss", $name, $email, $mobile, $password, $role);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return "Database error: failed to execute statement.";
    }
}


function loginUser($email, $password)
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        error_log("DB Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        // Double check password hash verification
        if (password_verify($password, $result['password'])) {
            // Optional: unset password hash before returning user array
            unset($result['password']);
            return $result;
        } else {
            error_log("Password mismatch for email: " . $email);
        }
    } else {
        error_log("No user found for email: " . $email);
    }

    return false;
}


function listProperty($areaInGaj, $address, $exprectedPrice, $name, $email, $mobile, $imagePaths, $documentPath, $userId, $locality2, $state, $typeOfProperty)
{
    global $conn;

    // Convert image array to string
    $imagePathsStr = implode(',', $imagePaths);

    $stmt = $conn->prepare("
        INSERT INTO listProperty 
        (areaInGaj, address, exprectedPrice, name, email, mobile, images, document, user_id, locality2, state, typeOfProperty) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    // Correct binding: userId is integer, others are strings
    $stmt->bind_param("ssssssssisss", $areaInGaj, $address, $exprectedPrice, $name, $email, $mobile, $imagePathsStr, $documentPath, $userId, $locality2, $state, $typeOfProperty);

    $success = $stmt->execute();
    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $success;
}

function buyProperty($areaInGaj, $budget, $name, $email, $mobile, $userId, $locality2, $state, $typeOfProperty)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO buyProperty (areaInGaj, budget, name, email, mobile, user_id, locality2, state, typeOfProperty)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    // Correct bind types (all strings except userId which is likely an integer)
    $stmt->bind_param(
        "sssssisss",
        $areaInGaj,
        $budget,
        $name,
        $email,
        $mobile,
        $userId,
        $locality2,
        $state,
        $typeOfProperty
    );

    $success = $stmt->execute();
    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $success;
}

function landLord($userId, $name, $email, $mobile, $address, $propertyToLet, $floor, $furniture, $rent, $tenantType, $food, $imagePaths, $locality2, $state)
{
    global $conn;

    // Convert image array to string
    $imagePathsStr = implode(',', $imagePaths);

    $stmt = $conn->prepare("
        INSERT INTO landLord (
            user_id, name, email, mobile, address, propertyToLet,
            floor, furniture, rent, tenantType, food, images, locality2, state
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    // Bind the parameters with correct data types
    $stmt->bind_param(
        "isssssisisssss", // i=int, s=string, d=decimal; rent is DECIMAL so s or d is acceptable
        $userId,
        $name,
        $email,
        $mobile,
        $address,
        $propertyToLet,
        $floor,
        $furniture,
        $rent,
        $tenantType,
        $food,
        $imagePathsStr,
        $locality2,
        $state
    );

    $success = $stmt->execute();
    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $success;
}


function tenant($userId, $name, $email, $mobile, $typeOfProperty, $budget, $typeOfTenant, $locality2, $state)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO tenant (user_id, name, email, mobile, typeOfProperty, budget, typeOfTenant, locality2, state)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param(
        "issssssss",
        $userId,
        $name,
        $email,
        $mobile,
        $typeOfProperty,
        $budget,
        $typeOfTenant,
        $locality2,
        $state
    );

    $success = $stmt->execute();
    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $success;
}

// Fetch paginated data
function listListedProperties($limit, $offset)
{
    global $conn;
    $sql = "SELECT * FROM listProperty ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fetch paginated data
function listPurchaseProperty($limit, $offset)
{
    global $conn;
    $sql = "SELECT * FROM buyProperty ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fetch paginated data
function listLandlordProperty($limit, $offset)
{
    global $conn;
    $sql = "SELECT * FROM landLord ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function listLandlordAndPublishedProperty($limit, $offset, $filters)
{
    global $conn;

    $where = "WHERE status = 'Published' AND done = 'no'";
    $params = [];
    $types = "";

    if (!empty($filters['max_rent'])) {
        $where .= " AND rent <= ?";
        $params[] = $filters['max_rent'];
        $types .= "i";
    }
    if (!empty($filters['locality2'])) {
        $where .= " AND locality2 LIKE ?";
        $params[] = "%" . $filters['locality2'] . "%";
        $types .= "s";
    }
    if (!empty($filters['property_type'])) {
        $where .= " AND propertyToLet = ?";
        $params[] = trim($filters['property_type']);
        $types .= "s";
    }
    if (!empty($filters['tenant_type'])) {
        $where .= " AND tenantType = ?";
        $params[] = trim($filters['tenant_type']);
        $types .= "s";
    }

    $sql = "SELECT * FROM landLord $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


// Fetch paginated data
function listTenantProperty($limit, $offset)
{
    global $conn;
    $sql = "SELECT * FROM tenant ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function listListedPropertiesForMapping()
{
    global $conn;
    $sql = "SELECT * FROM listProperty WHERE done = 'no' ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

function listListedAndPublishedProperties($limit, $offset, $filters = [])
{
    global $conn;

    $where = "WHERE status = 'Published' AND done = 'no'";
    $params = [];
    $types = "";

    if (!empty($filters['typeOfProperty'])) {
        $where .= " AND typeOfProperty LIKE ?";
        $params[] = "%" . $filters['typeOfProperty'] . "%";
        $types .= "s";
    }
    if (!empty($filters['min_area'])) {
        $where .= " AND areaInGaj >= ?";
        $params[] = $filters['min_area'];
        $types .= "i";
    }
    if (!empty($filters['max_area'])) {
        $where .= " AND areaInGaj <= ?";
        $params[] = $filters['max_area'];
        $types .= "i";
    }
    if (!empty($filters['min_price'])) {
        $where .= " AND exprectedPrice >= ?";
        $params[] = $filters['min_price'];
        $types .= "i";
    }
    if (!empty($filters['max_price'])) {
        $where .= " AND exprectedPrice <= ?";
        $params[] = $filters['max_price'];
        $types .= "i";
    }
    if (!empty($filters['locality2'])) {
        $where .= " AND locality2 LIKE ?";
        $params[] = "%" . $filters['locality2'] . "%";
        $types .= "s";
    }

    $sql = "SELECT * FROM listProperty $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);;
}

function listPurchasePropertyForAUser($userId, $limit, $offset)
{
    global $conn;

    $sql = "SELECT * FROM buyProperty WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }

    // 'i' for integer, 's' for string; assuming user_id is integer
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function listListedPropertyForAUser($userId, $limit, $offset)
{
    global $conn;

    $sql = "SELECT * FROM listProperty WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }

    // 'i' for integer, 's' for string; assuming user_id is integer
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function listLandlordPropertyForAUser($userId, $limit, $offset)
{
    global $conn;

    $sql = "SELECT * FROM landLord WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }

    // 'i' for integer, 's' for string; assuming user_id is integer
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function listTenantPropertyForAUser($userId, $limit, $offset)
{
    global $conn;

    $sql = "SELECT * FROM tenant WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }

    // 'i' for integer, 's' for string; assuming user_id is integer
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function countListedPropertyForAUser($userId)
{
    global $conn;

    $sql = "SELECT COUNT(*) as total FROM listProperty WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return 0;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result && $row = $result->fetch_assoc()) ? (int)$row['total'] : 0;
}

function countLandlordPropertyForAUser($userId)
{
    global $conn;

    $sql = "SELECT COUNT(*) as total FROM landLord WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return 0;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result && $row = $result->fetch_assoc()) ? (int)$row['total'] : 0;
}

function countTenantPropertyForAUser($userId)
{
    global $conn;

    $sql = "SELECT COUNT(*) as total FROM tenant WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return 0;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result && $row = $result->fetch_assoc()) ? (int)$row['total'] : 0;
}

function countBuyPropertyForAUser($userId)
{
    global $conn;

    $sql = "SELECT COUNT(*) as total FROM buyProperty WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return 0;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result && $row = $result->fetch_assoc()) ? (int)$row['total'] : 0;
}


function listPurchasePropertyForMapping()
{
    global $conn;
    $sql = "SELECT * FROM buyProperty WHERE done = 'no' ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// mark done for listing or selling properties
function markDone()
{
    global $conn;

    if (!isset($_GET['id'])) {
        die("Invalid request.");
    }

    $id = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("UPDATE listProperty SET done = 'yes' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        if ($stmt->affected_rows > 0) {
            // Redirect only if no previous output has been sent
            header("Location: /admin/sell_data.php");
            exit();
        } else {
            header("Location: /admin/sell_data.php");
            exit();
        }
    } catch (Exception $e) {
        die("Error updating record: " . $e->getMessage());
    }
}

function markDoneForBuyProperty()
{
    global $conn;

    if (!isset($_GET['id'])) {
        die("Invalid request.");
    }

    $id = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("UPDATE buyProperty SET done = 'yes' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        if ($stmt->affected_rows > 0) {
            // Redirect only if no previous output has been sent
            header("Location: /admin/buy_data.php");
            exit();
        } else {
            header("Location: /admin/buy_data.php");
            exit();
        }
    } catch (Exception $e) {
        die("Error updating record: " . $e->getMessage());
    }
}

function markDoneForLandlordProperty()
{
    global $conn;

    if (!isset($_GET['id'])) {
        die("Invalid request.");
    }

    $id = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("UPDATE landLord SET done = 'yes' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        if ($stmt->affected_rows > 0) {
            // Redirect only if no previous output has been sent
            header("Location: /admin/sell_data.php");
            exit();
        } else {
            header("Location: /admin/sell_data.php");
            exit();
        }
    } catch (Exception $e) {
        die("Error updating record: " . $e->getMessage());
    }
}

function resetUserPassword($userId, $newPassword, $name, $mobile)
{
    global $conn;

    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, name = ?, mobile = ? WHERE id = ?");
        $stmt->bind_param("sssi", $hashedPassword, $name, $mobile, $userId);
        $stmt->execute();

        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
    } catch (Exception $e) {
        error_log("Password and detail reset failed: " . $e->getMessage());
        return false;
    }
}

function changeStatusForPropertyOnSale($statusChange, $propertyId)
{
    global $conn;

    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE listProperty SET status = ? WHERE id = ?");

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters (s = string, i = integer)
    $stmt->bind_param("si", $statusChange, $propertyId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $success = $stmt->affected_rows > 0;
    // Close the statement
    $stmt->close();
    return $success;
}

function changeStatusForPropertyOnRent($statusChange, $propertyId)
{
    global $conn;

    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE landLord SET status = ? WHERE id = ?");

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters (s = string, i = integer)
    $stmt->bind_param("si", $statusChange, $propertyId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $success = $stmt->affected_rows > 0;
    // Close the statement
    $stmt->close();
    return $success;
}

function showInterestInProperty($propertyId, $userId)
{
    global $conn;

    // Step 1: Fetch existing interested_users
    $stmt = $conn->prepare("SELECT interestedUsers FROM listProperty WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $stmt->bind_result($interested_users_json);
    $stmt->fetch();
    $stmt->close();

    // Decode the existing JSON (if any)
    $interested_users = $interested_users_json ? json_decode($interested_users_json, true) : [];

    if (!is_array($interested_users)) {
        $interested_users = []; // fallback in case of bad JSON
    }

    // Step 2: Add user if not already present
    if (!in_array($userId, $interested_users)) {
        $interested_users[] = $userId;
        $updated_json = json_encode($interested_users);

        $stmt = $conn->prepare("UPDATE listProperty SET interestedUsers = ? WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // ✅ Correct variable: $propertyId
        $stmt->bind_param("si", $updated_json, $propertyId);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    // No update needed (user already in list)
    return false;
}

function showInterestInPropertyInLandlordProperty($propertyId, $userId)
{
    global $conn;

    // Step 1: Fetch existing interested_users
    $stmt = $conn->prepare("SELECT interestedUsers FROM landLord WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $stmt->bind_result($interested_users_json);
    $stmt->fetch();
    $stmt->close();

    // Decode the existing JSON (if any)
    $interested_users = $interested_users_json ? json_decode($interested_users_json, true) : [];

    if (!is_array($interested_users)) {
        $interested_users = []; // fallback in case of bad JSON
    }

    // Step 2: Add user if not already present
    if (!in_array($userId, $interested_users)) {
        $interested_users[] = $userId;
        $updated_json = json_encode($interested_users);

        $stmt = $conn->prepare("UPDATE landLord SET interestedUsers = ? WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // ✅ Correct variable: $propertyId
        $stmt->bind_param("si", $updated_json, $propertyId);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    // No update needed (user already in list)
    return false;
}

function showInterestInLandlordProperty($propertyId, $userId)
{
    global $conn;

    // Step 1: Fetch existing interested_users
    $stmt = $conn->prepare("SELECT interestedUsers FROM landLord WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $stmt->bind_result($interested_users_json);
    $stmt->fetch();
    $stmt->close();

    // Decode the existing JSON (if any)
    $interested_users = $interested_users_json ? json_decode($interested_users_json, true) : [];

    if (!is_array($interested_users)) {
        $interested_users = []; // fallback in case of bad JSON
    }

    // Step 2: Add user if not already present
    if (!in_array($userId, $interested_users)) {
        $interested_users[] = $userId;
        $updated_json = json_encode($interested_users);

        $stmt = $conn->prepare("UPDATE landLord SET interestedUsers = ? WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // ✅ Correct variable: $propertyId
        $stmt->bind_param("si", $updated_json, $propertyId);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    // No update needed (user already in list)
    return false;
}

function suggestProperties($limit, $offset, $type, $state, $locality2, $area, $budget)
{
    global $conn;

    // Allow 20% margin
    $areaMin = $area * 0.8;
    $areaMax = $area * 1.2;

    $budgetMin = $budget * 0.8;
    $budgetMax = $budget * 1.2;

    $query = "
        SELECT * 
        FROM listProperty 
        WHERE 
            typeOfProperty = ? 
            AND state = ? 
            AND locality2 = ? 
            AND areaInGaj BETWEEN ? AND ? 
            AND exprectedPrice BETWEEN ? AND ? 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($query);

    // 9 placeholders = 3 strings, 4 doubles, 2 integers
    $stmt->bind_param(
        "sssddddii",
        $type,          // string
        $state,         // string
        $locality2,     // string
        $areaMin,       // double
        $areaMax,       // double
        $budgetMin,     // double
        $budgetMax,     // double
        $limit,         // integer
        $offset         // integer
    );

    $stmt->execute();
    $result = $stmt->get_result();

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }

    return $properties;
}

function countSuggestedPropertiesForBuyers($type, $state, $locality, $area, $budget)
{
    global $conn;

    // Define flexible range for area matching
    $minArea = max(0, $area - 20);
    $maxArea = $area + 20;

    $sql = "SELECT COUNT(*) FROM listProperty 
            WHERE 
                status = 'Published' 
                AND done = 'no'
                AND typeOfProperty = ?
                AND state = ?
                AND locality2 LIKE ?
                AND CAST(areaInGaj AS UNSIGNED) BETWEEN ? AND ?
                AND CAST(exprectedPrice AS UNSIGNED) <= ?";

    $stmt = $conn->prepare($sql);
    $likeLocality = '%' . $locality . '%';
    $stmt->bind_param("sssiii", $type, $state, $likeLocality, $minArea, $maxArea, $budget);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count;
}

function suggestPropertiesForTenant($limit, $offset, $propertyType, $propertyState, $propertyLocality, $propertyTypeOfTenant, $propertyRent)
{
    global $conn;

    // Allow 20% margin on rent
    $rentMin = $propertyRent * 0.8;
    $rentMax = $propertyRent * 1.2;

    $query = "
        SELECT * 
        FROM landLord 
        WHERE 
            status = 'Published'
            AND done = 'no'
            AND propertyToLet = ? 
            AND state = ? 
            AND locality2 LIKE ? 
            AND rent BETWEEN ? AND ? 
            AND tenantType = ?
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $likeLocality = '%' . $propertyLocality . '%';

    $stmt->bind_param(
        "sssddsii",
        $propertyType,           // string
        $propertyState,          // string
        $likeLocality,           // string
        $rentMin,                // double
        $rentMax,                // double
        $propertyTypeOfTenant,   // string
        $limit,                  // int
        $offset                  // int
    );

    $stmt->execute();
    $result = $stmt->get_result();

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }

    return $properties;
}


function countSuggestedPropertiesForTenant($propertyType, $propertyState, $propertyLocality, $propertyTypeOfTenant, $propertyRent)
{
    global $conn;

    // Allow 20% margin on rent
    $rentMin = $propertyRent * 0.8;
    $rentMax = $propertyRent * 1.2;

    $query = "
        SELECT COUNT(*) as total
        FROM landLord 
        WHERE 
            status = 'Published'
            AND done = 'no'
            AND propertyToLet = ? 
            AND state = ? 
            AND locality2 LIKE ? 
            AND rent BETWEEN ? AND ? 
            AND tenantType = ?
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $likeLocality = '%' . $propertyLocality . '%';

    $stmt->bind_param(
        "sssdds",
        $propertyType,           // string
        $propertyState,          // string
        $likeLocality,           // string
        $rentMin,                // double
        $rentMax,                // double
        $propertyTypeOfTenant    // string
    );

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return (int)$row['total'];
}

function addLocality($locality)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO localities (locality)
        VALUES (?)
    ");

    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    // Correct bind types (all strings except userId which is likely an integer)
    $stmt->bind_param(
        "s",
        $locality
    );

    $success = $stmt->execute();
    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $success;
}

// Fetch paginated data
function fetchLocalities($limit, $offset)
{
    global $conn;
    $sql = "SELECT * FROM localities ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetchAllLocalities()
{
    global $conn;
    $sql = "SELECT * FROM localities ORDER BY id DESC";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function deleteLocality($id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM localities WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
