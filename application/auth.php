<?php
// ============================================================
// Application Layer: Authentication Handler
// File: application/auth.php
// ============================================================

require_once __DIR__ . '/config.php';

// Accept action from both POST (forms) and GET (logout link)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'register') {
    handleRegister();
} elseif ($action === 'login') {
    handleLogin();
} elseif ($action === 'logout') {
    handleLogout();
}

function handleRegister() {
    $db = getDB();

    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $college  = sanitize($_POST['college_name'] ?? '');
    $contact  = sanitize($_POST['contact_number'] ?? '');
    $age      = intval($_POST['age'] ?? 0);
    $role     = sanitize($_POST['role'] ?? '');
    $class    = sanitize($_POST['class_name'] ?? '');
    $subject  = sanitize($_POST['subject'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        setFlash('error', 'All required fields must be filled.');
        redirect(BASE_URL . '/presentation/register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Invalid email address.');
        redirect(BASE_URL . '/presentation/register.php');
    }

    if ($password !== $confirm) {
        setFlash('error', 'Passwords do not match.');
        redirect(BASE_URL . '/presentation/register.php');
    }

    if (strlen($password) < 6) {
        setFlash('error', 'Password must be at least 6 characters.');
        redirect(BASE_URL . '/presentation/register.php');
    }

    if (!in_array($role, ['student', 'teacher'])) {
        setFlash('error', 'Invalid role selected.');
        redirect(BASE_URL . '/presentation/register.php');
    }

    // Check duplicate email
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        setFlash('error', 'Email already registered.');
        redirect(BASE_URL . '/presentation/register.php');
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $uid    = generateUniqueId($role);

    // Insert user
    $stmt = $db->prepare("INSERT INTO users (unique_id, name, email, password, role, college_name, contact_number, age, is_approved) VALUES (?,?,?,?,?,?,?,?,'pending')");
    $stmt->bind_param("sssssssi", $uid, $name, $email, $hashed, $role, $college, $contact, $age);
    $stmt->execute();
    $user_id = $db->insert_id;

    // Insert role-specific record
    if ($role === 'student') {
        $stmt2 = $db->prepare("INSERT INTO students (user_id, class_name) VALUES (?,?)");
        $stmt2->bind_param("is", $user_id, $class);
        $stmt2->execute();
    } elseif ($role === 'teacher') {
        $stmt2 = $db->prepare("INSERT INTO teachers (user_id, subject) VALUES (?,?)");
        $stmt2->bind_param("is", $user_id, $subject);
        $stmt2->execute();
    }

    setFlash('success', 'Registration successful! Please wait for admin approval before logging in.');
    redirect(BASE_URL . '/presentation/login.php');
}

function handleLogin() {
    $db = getDB();
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Email and password are required.');
        redirect(BASE_URL . '/presentation/login.php');
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        setFlash('error', 'Invalid email or password.');
        redirect(BASE_URL . '/presentation/login.php');
    }

    if ($user['role'] !== 'admin' && $user['is_approved'] !== 'approved') {
        $msg = $user['is_approved'] === 'rejected'
            ? 'Your registration has been rejected by the admin.'
            : 'Your account is pending admin approval.';
        setFlash('error', $msg);
        redirect(BASE_URL . '/presentation/login.php');
    }

    // Set session
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['unique_id'] = $user['unique_id'];
    $_SESSION['name']      = $user['name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];

    // Role-based redirect
    $dashboards = [
        'student' => BASE_URL . '/presentation/student/dashboard.php',
        'teacher' => BASE_URL . '/presentation/teacher/dashboard.php',
        'admin'   => BASE_URL . '/presentation/admin/dashboard.php',
    ];
    redirect($dashboards[$user['role']]);
}

function handleLogout() {
    session_destroy();
    redirect(BASE_URL . '/presentation/login.php');
}
?>
