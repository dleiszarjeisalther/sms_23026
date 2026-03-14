<?php
session_start();

// Basic Router for Subsystem 1

require_once __DIR__ . '/models/config.php';

$basePath = BASE_URL;
$request = $_SERVER['REQUEST_URI'];

// Remove base path to get the route
if ($basePath !== '' && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}

// Remove query strings from URL (e.g. ?id=1)
$request = parse_url($request, PHP_URL_PATH);

// Clean up trailing and leading slashes
$request = trim($request, '/');

/**
 * Check if a student is enrolled
 */
function isStudentEnrolled($pdo, $studentId)
{
    $stmt = $pdo->prepare("SELECT enrollment_status FROM enrollments WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    if (!$stmt->execute([$studentId])) return false;
    $enrollment = $stmt->fetch();
    // Allow access if they have submitted an enrollment and it's not rejected
    return ($enrollment && $enrollment['enrollment_status'] !== 'Rejected');
}

// Route definitions
switch ($request) {
    case '':
    case 'home':
    case 'index.php':
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Student Admission System</title>
            <?php
            // Dynamic Absolute URL Generation
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $baseUrl = $protocol . "://" . $host . $basePath;
            $fullUrl = $protocol . "://" . $host . $_SERVER['REQUEST_URI'];

            // Image path - ensuring it's absolute for social scrapers
            $shareImageName = "2026-001_1772900483.jpg";
            $shareImagePath = "/" . $shareImageName;
            $shareImageUrl = $protocol . "://" . $host . $basePath . $shareImagePath;
            ?>
            <meta name="author" content="Lagarizz">
            <meta name="description" content="Secure Student Admission and ID Generation System - SIMS Academy">

            <!-- Open Graph / Facebook / Messenger -->
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?= $fullUrl ?>">
            <meta property="og:site_name" content="SIMS Academy">
            <meta property="og:title" content="subsystem1">
            <meta property="og:description" content="Student Admission and ID Generator">
            <meta property="og:image" content="<?= $shareImageUrl ?>">
            <meta property="og:image:secure_url" content="<?= $shareImageUrl ?>">
            <meta property="og:image:type" content="image/jpeg">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:alt" content="SIMS Student Profile Preview">

            <!-- Twitter -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:url" content="<?= $fullUrl ?>">
            <meta name="twitter:title" content="subsystem1">
            <meta name="twitter:description" content="Secure Student Admission and ID Generation System">
            <meta name="twitter:image" content="<?= $shareImageUrl ?>">
            <meta name="twitter:image:alt" content="SIMS Student Profile Preview">

            <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
        </head>

        <div class="app-container">
            <?php require __DIR__ . '/views/navigation_bar.php'; ?>
            <main class="main-content">
                <div class="container" style="text-align: center; margin-top: 5rem;">
                    <h1>Welcome to Student Admission System</h1>

                    <?php if (isset($_SESSION['enrollment_error'])): ?>
                        <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display: inline-block; border: 1px solid #fecaca;">
                            <?= htmlspecialchars($_SESSION['enrollment_error']) ?>
                            <?php unset($_SESSION['enrollment_error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                            <a href="<?= BASE_URL ?>/login" class="btn btn-primary" style="text-decoration: none;">Go to Login Portal</a>
                            <a href="<?= BASE_URL ?>/enrollment" class="btn btn-secondary" style="text-decoration: none; border: 1px solid var(--primary-color); color: var(--primary-color);">Go to Student Admission</a>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 2rem;">
                            <p style="font-size: 1.2rem; color: var(--text-muted);">You are logged in as <?= htmlspecialchars($_SESSION['user_type']) ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
        </body>

        </html>
<?php
        break;

    case 'login':
        // Load the universal login portal
        require __DIR__ . '/views/login.php';
        break;

    case 'admission':
        // Load the admission view and logic
        require __DIR__ . '/views/subsystem1/admission.php';
        break;

    case 'profile_update':
        // Load the profile update logic and view
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
            require_once __DIR__ . '/models/config.php';
            if (!isStudentEnrolled($pdo, $_SESSION['user_id'])) {
                $_SESSION['enrollment_error'] = "Access to Profile Update is restricted until your enrollment is officially Enrolled.";
                header('Location: ' . BASE_URL . '/home');
                exit;
            }
        }
        require __DIR__ . '/controllers/subsystem1/profile.controller.php';
        break;

    case 'academic_records':
        // Load the academic records view
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        if ($_SESSION['user_type'] === 'student') {
            require_once __DIR__ . '/models/config.php';
            if (!isStudentEnrolled($pdo, $_SESSION['user_id'])) {
                $_SESSION['enrollment_error'] = "Access to Academic Records is restricted until your enrollment is officially Enrolled.";
                header('Location: ' . BASE_URL . '/home');
                exit;
            }
        }
        require __DIR__ . '/views/subsystem1/academic_records.php';
        break;

    case 'student_status':
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        require __DIR__ . '/controllers/subsystem1/student_status.controller.php';
        break;

    case 'id_generator':
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        require __DIR__ . '/views/subsystem1/id_generator.php';
        break;

    case 'otp_verify':
        require __DIR__ . '/views/otp_verify.php';
        break;

    case 'security_management':
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        require __DIR__ . '/views/subsystem1/security_management.php';
        break;

    case 'my_id':
        // Student ID card view
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        require_once __DIR__ . '/models/config.php';
        if (!isStudentEnrolled($pdo, $_SESSION['user_id'])) {
            $_SESSION['enrollment_error'] = "Access to Your ID is restricted until your enrollment is officially Enrolled.";
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
        require __DIR__ . '/views/subsystem1/my_id.php';
        break;

    case 'enrollment':
        // Require login before accessing enrollment
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        // Load the subsystem 2 enrollment controller
        require __DIR__ . '/controllers/subsystem2/enrollment.controller.php';
        break;

    case 'logout':
        // Destroy session and redirect to login
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
        break;

    default:
        // Handle 404 Not Found
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>The page you requested does not exist: " . htmlspecialchars($request) . "</p>";
        echo "<p>Base Path: " . htmlspecialchars($basePath) . "</p>";
        break;
}
