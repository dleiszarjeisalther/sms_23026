<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment System - Secure Login</title>

    <!-- Security Headers (Meta equivalents) -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    <meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera()">

    <!-- Content Security Policy - Fixed spacing -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com   https://cdnjs.cloudflare.com  ; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com   https://cdnjs.cloudflare.com  ; font-src https://fonts.gstatic.com   https://cdnjs.cloudflare.com  ; img-src 'self' data: blob:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';">

    <script src="https://cdn.tailwindcss.com  "></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css  " rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <!-- Unified CSS linked above -->
</head>

<body class="min-h-screen">
    <?php 
    $studentDetails = $studentDetails ?? []; 
    // Filter out "N/A" values from existing student records
    $studentDetails = array_map(function($val) {
        return (is_string($val) && strtoupper(trim($val)) === 'N/A') ? '' : $val;
    }, $studentDetails);
    ?>
    <!-- Session Timeout Warning -->
    <div id="sessionWarning" class="session-warning">
        <i class="fa-solid fa-clock mr-2"></i>
        Your session will expire in <span id="sessionCountdown">5:00</span> minutes.
        <button onclick="app.extendSession()" class="ml-4 px-4 py-1 bg-orange-600 text-white rounded-lg text-sm hover:bg-orange-700 transition-colors">
            Stay Logged In
        </button>
    </div>

    <!-- Animated Background -->
    <div class="bg-pattern"></div>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <!-- Help Overlay -->
    <div class="help-overlay" id="helpOverlay" onclick="app.toggleHelp()"></div>

    <!-- Help Panel (Slide-out) -->
    <div class="help-panel" id="helpPanel">
        <div class="help-panel-header">
            <h3>
                <i class="fa-solid fa-headset"></i>
                Quick Help
            </h3>
            <button class="help-close-btn" onclick="app.toggleHelp()" aria-label="Close help panel">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="help-panel-content">
            <div class="help-section">
                <h4><i class="fa-solid fa-circle-question"></i> Getting Started</h4>
                <p>Welcome to the Student Enrollment System. Follow these steps to complete your enrollment:</p>
                <div class="help-item">
                    <i class="fa-solid fa-1"></i>
                    <div class="help-item-content">
                        <strong>Personal Information</strong>
                        <span>Fill in your details and upload a photo</span>
                    </div>
                </div>
                <div class="help-item">
                    <i class="fa-solid fa-2"></i>
                    <div class="help-item-content">
                        <strong>Subject Selection</strong>
                        <span>Choose 9-18 credits of courses</span>
                    </div>
                </div>
                <div class="help-item">
                    <i class="fa-solid fa-3"></i>
                    <div class="help-item-content">
                        <strong>Validation</strong>
                        <span>Wait for registrar approval</span>
                    </div>
                </div>
            </div>

            <div class="help-section">
                <h4><i class="fa-solid fa-lightbulb"></i> Tips</h4>
                <div class="help-item">
                    <i class="fa-solid fa-check-circle"></i>
                    <div class="help-item-content">
                        <strong>Required Fields</strong>
                        <span>Fields marked with * are mandatory</span>
                    </div>
                </div>
                <div class="help-item">
                    <i class="fa-solid fa-camera"></i>
                    <div class="help-item-content">
                        <strong>Photo Requirements</strong>
                        <span>2x2 ID picture with white background</span>
                    </div>
                </div>
            </div>

            <div class="help-section">
                <h4><i class="fa-solid fa-clock"></i> Support Hours</h4>
                <p>Our support team is available:</p>
                <div class="help-item">
                    <i class="fa-solid fa-calendar"></i>
                    <div class="help-item-content">
                        <strong>Monday - Friday</strong>
                        <span>8:00 AM - 5:00 PM</span>
                    </div>
                </div>
                <div class="help-item">
                    <i class="fa-solid fa-calendar-day"></i>
                    <div class="help-item-content">
                        <strong>Saturday</strong>
                        <span>9:00 AM - 12:00 PM</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="help-panel-footer">
            <button class="help-contact-btn ripple" onclick="app.contactSupport()">
                <i class="fa-solid fa-phone"></i>
                Contact Support
            </button>
        </div>
    </div>

    <!-- View Student Modal -->
    <div id="viewStudentModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="viewStudentModalTitle">
        <div class="modal-content large">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                <h3 class="text-2xl font-bold text-gray-800" id="viewStudentModalTitle">Student Details</h3>
                <button onclick="app.closeViewStudentModal()" class="text-gray-400 hover:text-gray-600 transition-all hover:rotate-90" aria-label="Close student details">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <div class="student-detail-grid">
                <!-- Left: Photo -->
                <div>
                    <div class="student-photo-large mb-4">
                        <img id="viewStudentPhoto" class="hidden" alt="Student photo" />
                        <i id="viewStudentPhotoIcon" class="fa-solid fa-user text-8xl text-gray-300"></i>
                    </div>
                    <div class="text-center">
                        <span id="viewStudentStatus" class="status-badge pending">
                            <i class="fa-regular fa-clock"></i> Pending
                        </span>
                    </div>
                </div>

                <!-- Right: Info -->
                <div>
                    <!-- Personal Info -->
                    <div class="student-info-section">
                        <h4 class="font-bold text-lg text-green-900 mb-4 border-b border-green-200 pb-2">
                            <i class="fa-solid fa-user mr-2"></i>Personal Information
                        </h4>
                        <div class="info-row">
                            <span class="info-label">Student ID:</span>
                            <span class="info-value font-mono font-semibold" id="viewStudentId">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value font-semibold text-lg" id="viewStudentName">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value" id="viewStudentEmail">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value" id="viewStudentPhone">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date of Birth:</span>
                            <span class="info-value" id="viewStudentDOB">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Gender:</span>
                            <span class="info-value" id="viewStudentGender">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value" id="viewStudentAddress">-</span>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="student-info-section">
                        <h4 class="font-bold text-lg text-green-900 mb-4 border-b border-green-200 pb-2">
                            <i class="fa-solid fa-phone-volume mr-2"></i>Emergency Contact
                        </h4>
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value" id="viewEmergencyName">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value" id="viewEmergencyPhone">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Relationship:</span>
                            <span class="info-value" id="viewEmergencyRelation">-</span>
                        </div>
                    </div>

                    <!-- Academic Info -->
                    <div class="student-info-section">
                        <h4 class="font-bold text-lg text-green-900 mb-4 border-b border-green-200 pb-2">
                            <i class="fa-solid fa-graduation-cap mr-2"></i>Academic Information
                        </h4>
                        <div class="info-row">
                            <span class="info-label">Student Type:</span>
                            <span class="info-value font-semibold" id="viewStudentType">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Program:</span>
                            <span class="info-value font-semibold" id="viewStudentProgram">-</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Credits:</span>
                            <span class="info-value font-bold text-green-600" id="viewStudentCredits">-</span>
                        </div>
                    </div>

                    <!-- Subjects -->
                    <div class="student-info-section">
                        <h4 class="font-bold text-lg text-green-900 mb-4 border-b border-green-200 pb-2">
                            <i class="fa-solid fa-book mr-2"></i>Enrolled Subjects
                        </h4>
                        <div id="viewStudentSubjects" class="subject-list-compact">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                <button onclick="app.closeViewStudentModal()" class="btn-secondary ripple">
                    <i class="fa-solid fa-xmark mr-2"></i>Close
                </button>
                <button onclick="app.printStudentDetails()" class="btn-outline ripple">
                    <i class="fa-solid fa-print mr-2"></i>Print
                </button>
                <button onclick="app.editCurrentStudent()" class="btn-primary ripple">
                    <i class="fa-solid fa-pen-to-square mr-2"></i>Edit
                </button>
            </div>
        </div>
    </div>

    <!-- LOGIN PAGE -->
    <?php if (!$isLoggedIn): ?>
        <div id="loginPage" class="login-container">
            <div class="login-box">
                <!-- Logo -->
                <div class="login-logo">
                    <i class="fa-solid fa-graduation-cap text-4xl text-white"></i>
                </div>

                <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Student Enrollment System</h1>
                <p class="text-center text-gray-500 mb-6">Sign in to manage your enrollment</p>



                <!-- Login Form -->
                <form id="loginForm" class="login-form active" action="<?= BASE_URL ?>/enrollment" method="POST">
                    <?php if (!empty($error) && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
                        <div style="color: red; margin-bottom: 10px;"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <input type="hidden" name="action" value="login">
                    <div class="input-group">
                        <label for="loginStudentId">Student ID</label>
                        <i class="fa-solid fa-id-card input-icon"></i>
                        <input type="text" id="loginStudentId" name="student_id" placeholder="Your Student ID" required value="<?= htmlspecialchars($_POST['student_id'] ?? $_COOKIE['remembered_student'] ?? '') ?>" <?= !empty($error) && isset($_POST['action']) && $_POST['action'] === 'login' ? 'class="input-error"' : '' ?>>
                    </div>

                    <div class="input-group">
                        <label for="loginPassword">Password</label>
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required autocomplete="current-password" <?= !empty($error) && isset($_POST['action']) && $_POST['action'] === 'login' ? 'class="input-error"' : '' ?>>
                        <button type="button" class="password-toggle" onclick="app.toggleLoginPassword('loginPassword')" aria-label="Toggle password visibility">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="rememberMe" name="remember_me" class="checkbox-custom w-4 h-4" <?= isset($_COOKIE['remembered_student']) ? 'checked' : '' ?>>
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-green-600 hover:text-green-800 font-medium" onclick="app.showForgotPassword()">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Sign In
                    </button>
                </form>





                <!-- Security Badges -->
                <div class="security-badges">
                    <div class="security-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>256-bit SSL</span>
                    </div>
                    <div class="security-badge">
                        <i class="fa-solid fa-lock"></i>
                        <span>Secure Login</span>
                    </div>
                    <div class="security-badge">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Privacy Protected</span>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- MAIN APPLICATION (Visible because logged in) -->
        <div class="app-container" id="mainApp">
            <?php require __DIR__ . '/../navigation_bar.php'; ?>
            <div class="main-content" style="width: 100%;">

                <!-- Progress Steps -->
                <div class="max-w-7xl mx-auto px-8 py-6">
                    <div class="flex" id="progressSteps" role="tablist" aria-label="Enrollment steps">
                        <div class="step-arrow active flex-1 py-3 px-6 text-center font-semibold text-sm" data-step="1" role="tab" aria-selected="true" tabindex="0">
                            1. Personal Info
                        </div>
                        <div class="step-arrow flex-1 py-3 px-6 text-center font-semibold text-sm text-gray-600 ml-1" data-step="2" role="tab" aria-selected="false" tabindex="-1">
                            2. Subject Selection
                        </div>
                        <div class="step-arrow flex-1 py-3 px-6 text-center font-semibold text-sm text-gray-600 ml-1" data-step="3" role="tab" aria-selected="false" tabindex="-1">
                            3. Validation
                        </div>
                        <div class="step-arrow flex-1 py-3 px-6 text-center font-semibold text-sm text-gray-600 ml-1" data-step="4" role="tab" aria-selected="false" tabindex="-1">
                            4. Status
                        </div>
                    </div>
                </div>

                <!-- Main Content Container -->
                <main class="max-w-7xl mx-auto px-8 pb-12">

                    <!-- STEP 1: Personal Information -->
                    <div id="step1" class="page-section active" role="tabpanel" aria-labelledby="step1-tab">
                        <div class="bg-white/80 backdrop-blur-lg border-2 border-green-400 rounded-2xl p-8 shadow-xl">

                            <!-- Account Created Success Message -->
                            <div id="accountCreatedMsg" class="account-success hidden">
                                <i class="fa-solid fa-circle-check"></i>
                                <h3 class="text-xl font-bold text-green-800 mb-2">Account Created Successfully!</h3>
                                <p class="text-green-700">Welcome to the Student Enrollment System. Please complete your enrollment profile below.</p>
                            </div>

                            <!-- Edit Mode Banner -->
                            <div id="editModeBanner" class="edit-mode-banner" role="status" aria-live="polite">
                                <i class="fa-solid fa-pen-to-square text-2xl"></i>
                                <div>
                                    <h3 class="font-bold text-lg">Editing Student Record</h3>
                                    <p class="text-sm">You are editing <span id="editingStudentId" class="font-mono font-bold"></span>.
                                        <button onclick="app.cancelEdit()" class="text-red-600 underline hover:text-red-800 ml-2 font-medium">Cancel Edit</button>
                                    </p>
                                </div>
                            </div>

                            <!-- Validation Summary -->
                            <div id="step1ValidationSummary" class="validation-summary" role="alert" aria-live="assertive">
                                <strong>Please fix the following errors:</strong>
                                <ul id="step1ErrorList"></ul>
                            </div>

                            <!-- Student Type Selection -->
                            <div class="mb-8">
                                <h2 class="section-title text-lg">Student Type</h2>
                                <div class="student-type-toggle">
                                    <div class="student-type-option selected" onclick="app.selectStudentType('new')" id="type-new">
                                        <i class="fa-solid fa-user-graduate"></i>
                                        <span class="label">New Student</span>
                                        <span class="sublabel">First time enrollment</span>
                                    </div>
                                    <div class="student-type-option" onclick="app.selectStudentType('old')" id="type-old">
                                        <i class="fa-solid fa-user-check"></i>
                                        <span class="label">Old Student</span>
                                        <span class="sublabel">Returning/Continuing student</span>
                                    </div>
                                </div>
                                <input type="hidden" id="studentType" value="new">
                            </div>

                            <div class="mb-8">
                                <h2 class="section-title text-lg">Personal Information</h2>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-8 space-y-4">
                                        <!-- Full Name -->
                                        <div class="grid grid-cols-12 items-start">
                                            <label class="col-span-3 text-sm font-medium text-gray-700 required pt-2" for="inputName">Full Name</label>
                                            <div class="col-span-9">
                                                <input
                                                    type="text"
                                                    class="form-input bg-gray-100"
                                                    id="inputName"
                                                    name="full_name"
                                                    value="<?= htmlspecialchars(($studentDetails['first_name'] ?? '') . ' ' . ($studentDetails['last_name'] ?? '')) ?>"
                                                    readonly>
                                            </div>
                                        </div>

                                        <!-- Email (Read-only for logged in users) -->
                                        <div class="grid grid-cols-12 items-start">
                                            <label class="col-span-3 text-sm font-medium text-gray-700 required pt-2" for="inputEmail">Email Address</label>
                                            <div class="col-span-9">
                                                <input
                                                    type="email"
                                                    class="form-input bg-gray-100"
                                                    id="inputEmail"
                                                    name="email_address"
                                                    value="<?= htmlspecialchars($studentDetails['email_address'] ?? '') ?>"
                                                    readonly
                                                    required>
                                                <p class="text-xs text-gray-500 mt-1">Email is linked to your account and cannot be changed</p>
                                            </div>
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="grid grid-cols-12 items-start">
                                            <label class="col-span-3 text-sm font-medium text-gray-700 required pt-2" for="inputPhone">Phone Number</label>
                                            <div class="col-span-6">
                                                <input
                                                    type="tel"
                                                    class="form-input"
                                                    id="inputPhone"
                                                    name="contact_number"
                                                    value="<?= htmlspecialchars($studentDetails['contact_number'] ?? '') ?>"
                                                    placeholder="Enter phone number"
                                                    onblur="app.validatePhoneNumber(true)"
                                                    required>
                                                <div class="error-message" id="phoneError">
                                                    <i class="fa-solid fa-circle-exclamation"></i>
                                                    <span>Phone number is required</span>
                                                </div>
                                                <div class="success-message" id="phoneSuccess">
                                                    <i class="fa-solid fa-check-circle"></i>
                                                    <span>Valid phone number</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Date of Birth -->
                                        <div class="grid grid-cols-12 items-start">
                                            <label class="col-span-3 text-sm font-medium text-gray-700 required pt-2" for="inputDOB">Date of Birth</label>
                                            <div class="col-span-5">
                                                <input
                                                    type="date"
                                                    class="form-input"
                                                    id="inputDOB"
                                                    name="date_of_birth"
                                                    value="<?= htmlspecialchars($studentDetails['date_of_birth'] ?? '') ?>"
                                                    onblur="app.validateField('inputDOB', 'date')"
                                                    onchange="app.validateField('inputDOB', 'date')"
                                                    required>
                                                <div class="error-message" id="inputDOB-error">
                                                    <i class="fa-solid fa-circle-exclamation"></i>
                                                    <span>You must be at least 16 years old</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Gender - Fixed accessibility -->
                                        <div class="grid grid-cols-12 items-start">
                                            <label class="col-span-3 text-sm font-medium text-gray-700 required pt-2">Gender</label>
                                            <div class="col-span-9">
                                                <div class="gender-options" role="radiogroup" aria-label="Gender selection">
                                                    <label class="gender-option">
                                                        <input type="radio" name="gender" value="male" <?= strtolower($studentDetails['gender'] ?? '') === 'male' ? 'checked' : '' ?> required>
                                                        <i class="fa-solid fa-mars text-blue-500"></i>
                                                        <span>Male</span>
                                                    </label>
                                                    <label class="gender-option">
                                                        <input type="radio" name="gender" value="female" <?= strtolower($studentDetails['gender'] ?? '') === 'female' ? 'checked' : '' ?>>
                                                        <i class="fa-solid fa-venus text-pink-500"></i>
                                                        <span>Female</span>
                                                    </label>
                                                    <label class="gender-option">
                                                        <input type="radio" name="gender" value="Other" <?= !in_array(strtolower($studentDetails['gender'] ?? ''), ['male', 'female']) ? 'checked' : '' ?>>
                                                        <i class="fa-solid fa-genderless text-purple-500"></i>
                                                        <span>Other</span>
                                                    </label>
                                                </div>
                                                <div class="error-message" id="gender-error">
                                                    <i class="fa-solid fa-circle-exclamation"></i>
                                                    <span>Please select your gender</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div class="grid grid-cols-12 items-start">
                                            <label class="col-span-3 text-sm font-medium text-gray-700 required pt-2" for="inputAddress">Address</label>
                                            <div class="col-span-9">
                                                <input
                                                    type="text"
                                                    class="form-input"
                                                    id="inputAddress"
                                                    name="address"
                                                    value="<?= htmlspecialchars($studentDetails['address'] ?? '') ?>"
                                                    placeholder="123 University Ave, City, Province"
                                                    onblur="app.validateField('inputAddress', 'address')"
                                                    oninput="app.clearFieldError('inputAddress')"
                                                    required>
                                                <div class="error-message" id="inputAddress-error">
                                                    <i class="fa-solid fa-circle-exclamation"></i>
                                                    <span>Address is required (minimum 10 characters)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Photo Upload -->
                                    <div class="col-span-4 flex flex-col items-center">
                                        <div class="photo-placeholder w-40 h-48 rounded-xl mb-3 bg-gray-50 overflow-hidden" id="photoContainer">
                                            <?php $existingPhoto = $studentDetails['profile_image'] ?? ''; ?>
                                            <i class="fa-solid fa-user text-6xl text-gray-300 <?= $existingPhoto ? 'hidden' : '' ?>" id="photoIcon" aria-hidden="true"></i>
                                            <img id="photoPreview" class="w-full h-full object-cover <?= $existingPhoto ? '' : 'hidden' ?>" src="<?= htmlspecialchars($existingPhoto) ?>" alt="Uploaded student photo" />
                                        </div>
                                        <input type="file" id="photoInput" class="hidden" accept="image/*" onchange="app.handlePhotoUpload(this)" aria-label="Upload student photo">
                                        <button type="button" class="btn-primary text-sm ripple" onclick="document.getElementById('photoInput').click()">
                                            <i class="fa-solid fa-upload" aria-hidden="true"></i> Upload Photo
                                        </button>
                                        <p class="text-xs text-gray-500 mt-2">Max 2MB (JPG, PNG)</p>
                                        <div class="error-message mt-2" id="photoError">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            <span>Photo is required</span>
                                        </div>

                                        <!-- Photo Requirements -->
                                        <div class="photo-requirements w-full">
                                            <h4><i class="fa-solid fa-camera"></i> Photo Requirements</h4>
                                            <ul>
                                                <li><i class="fa-solid fa-check"></i> Recent 2x2 ID picture</li>
                                                <li><i class="fa-solid fa-check"></i> White/plain background</li>
                                                <li><i class="fa-solid fa-check"></i> No eyeglasses/sunglasses</li>
                                                <li><i class="fa-solid fa-check"></i> Neutral expression</li>
                                                <li><i class="fa-solid fa-check"></i> Face clearly visible</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Section -->
                            <div class="emergency-contact-section">
                                <h3><i class="fa-solid fa-phone-volume"></i> Emergency Contact Information</h3>
                                <div class="grid grid-cols-12 gap-6">
                                    <!-- Emergency Contact Name -->
                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 required mb-2" for="inputEmergencyName">Full Name</label>
                                        <input
                                            type="text"
                                            class="form-input"
                                            id="inputEmergencyName"
                                            name="guardian_name"
                                            value="<?= htmlspecialchars($studentDetails['guardian_name'] ?? '') ?>"
                                            placeholder="Enter emergency contact name"
                                            onblur="app.validateField('inputEmergencyName', 'name')"
                                            oninput="app.clearFieldError('inputEmergencyName')"
                                            required>
                                        <div class="error-message" id="inputEmergencyName-error">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            <span>Emergency contact name is required</span>
                                        </div>
                                    </div>

                                    <!-- Emergency Contact Phone -->
                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 required mb-2" for="inputEmergencyPhone">Phone Number</label>
                                        <div class="phone-input-wrapper">
                                            <input
                                                type="tel"
                                                class="form-input"
                                                id="inputEmergencyPhone"
                                                name="guardian_contact"
                                                value="<?= htmlspecialchars($studentDetails['guardian_contact'] ?? '') ?>"
                                                placeholder="912 345 6789"
                                                oninput="app.formatEmergencyPhone(this)"
                                                onblur="app.validateEmergencyPhone()"
                                                required>
                                        </div>
                                        <div class="error-message" id="inputEmergencyPhone-error">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            <span>Valid phone number required</span>
                                        </div>
                                    </div>

                                    <!-- Relationship -->
                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 required mb-2" for="inputEmergencyRelation">Relationship</label>
                                        <div class="relative">
                                            <?php $_selectedRelation = $studentDetails['guardian_relation'] ?? $studentDetails['emergency_relation'] ?? $studentDetails['relation'] ?? ''; ?>
                                            <select
                                                class="form-input appearance-none bg-white"
                                                id="inputEmergencyRelation"
                                                name="guardian_relation"
                                                onchange="app.validateField('inputEmergencyRelation', 'select')"
                                                required>
                                                <option value="" <?= $_selectedRelation === '' ? 'selected' : '' ?>>Select Relationship</option>
                                                <option value="Parent" <?= $_selectedRelation === 'Parent' ? 'selected' : '' ?>>Parent</option>
                                                <option value="Guardian" <?= $_selectedRelation === 'Guardian' ? 'selected' : '' ?>>Guardian</option>
                                            </select>
                                            <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-gray-400 text-xs pointer-events-none" aria-hidden="true"></i>
                                        </div>
                                        <div class="error-message" id="inputEmergencyRelation-error">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            <span>Please select relationship</span>
                                        </div>
                                    </div>

                                    <!-- Guardian Address -->
                                    <div class="col-span-12">
                                        <label class="block text-sm font-medium text-gray-700 required mb-2" for="inputEmergencyAddress">Guardian Address</label>
                                        <textarea
                                            class="form-input"
                                            id="inputEmergencyAddress"
                                            name="guardian_address"
                                            placeholder="Same as student address or new address..."
                                            onblur="app.validateField('inputEmergencyAddress', 'address')"
                                            oninput="app.clearFieldError('inputEmergencyAddress')"
                                            required><?= htmlspecialchars($studentDetails['guardian_address'] ?? '') ?></textarea>
                                        <div class="error-message" id="inputEmergencyAddress-error">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            <span>Guardian address is required</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-8 mt-8">
                                <h2 class="section-title text-lg">Program & Enrollment Details</h2>
                                <div class="grid grid-cols-2 gap-8">
                                    <div class="space-y-4">
                                        <!-- Program -->
                                        <div class="grid grid-cols-12 items-start">
                                            <label class="col-span-5 text-sm font-medium text-gray-700 required pt-2" for="inputProgram">Select Program</label>
                                            <div class="col-span-7 relative">
                                                <select
                                                    class="form-input appearance-none bg-white"
                                                    id="inputProgram"
                                                    onchange="app.validateField('inputProgram', 'select')"
                                                    required>
                                                    <option value="">Select Program</option>
                                                    <option value="BS Computer Science">BS Computer Science</option>
                                                    <option value="BS Information Technology">BS Information Technology</option>
                                                </select>
                                                <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-gray-400 text-xs pointer-events-none" aria-hidden="true"></i>
                                                <div class="error-message" id="inputProgram-error">
                                                    <i class="fa-solid fa-circle-exclamation"></i>
                                                    <span>Please select a program</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Preferred Start Date REMOVED -->
                                    </div>

                                </div>
                            </div>



                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer hover:scale-105 transition-transform" id="termsContainer">
                                        <input type="checkbox" class="checkbox-custom" id="termsCheck" onchange="app.validateTerms()" required>
                                        <span class="text-sm text-gray-700">I agree to the <a href="#" class="text-green-600 underline hover:text-green-800" onclick="event.preventDefault(); app.showTermsModal();">Terms & Conditions</a></span>
                                    </label>
                                    <div class="error-message inline-flex" id="termsError">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        <span>You must agree to the terms</span>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <button type="button" class="btn-secondary ripple" onclick="app.saveDraft()">Save & Exit</button>
                                    <button type="button" class="btn-secondary hidden ripple" id="btnCancelEdit" onclick="app.cancelEdit()">Cancel</button>
                                    <button type="button" class="btn-primary ripple" id="btnStep1Action" onclick="app.validateStep1AndProceed()">
                                        Next <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Subject Selection - SEMESTER DROPDOWN REMOVED -->
                    <div id="step2" class="page-section" role="tabpanel" aria-labelledby="step2-tab">
                        <div class="bg-white/80 backdrop-blur-lg border-2 border-green-400 rounded-2xl p-6 shadow-xl">

                            <!-- Credit Validation Warning -->
                            <div id="creditValidationWarning" class="validation-summary" role="alert" aria-live="assertive">
                                <strong><i class="fa-solid fa-circle-exclamation mr-2" aria-hidden="true"></i>Credit Requirements Not Met:</strong>
                                <ul>
                                    <li id="creditErrorText">You must select between 9 and 18 credits</li>
                                </ul>
                            </div>

                            <div id="conflictWarning" class="conflict-warning" role="alert" aria-live="polite">
                                <i class="fa-solid fa-triangle-exclamation mr-2" aria-hidden="true"></i>
                                <span id="conflictText">Schedule conflicts detected!</span>
                            </div>

                            <div class="grid grid-cols-12 gap-6">
                                <!-- Left Sidebar - SEMESTER FILTER REMOVED -->
                                <div class="col-span-3">
                                    <h2 class="section-title text-lg">Filter Subjects</h2>
                                    <div class="space-y-4">
                                        <!-- SEMESTER DROPDOWN REMOVED FROM HERE -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Type:</label>
                                            <div class="space-y-2">
                                                <label class="flex items-center gap-2 cursor-pointer hover:translate-x-1 transition-transform">
                                                    <input type="checkbox" class="checkbox-custom filter-type" value="General" checked onchange="app.filterSubjects()">
                                                    <span class="text-sm text-gray-700">General Ed</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer hover:translate-x-1 transition-transform">
                                                    <input type="checkbox" class="checkbox-custom filter-type" value="Core" checked onchange="app.filterSubjects()">
                                                    <span class="text-sm text-gray-700">Core Courses</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer hover:translate-x-1 transition-transform">
                                                    <input type="checkbox" class="checkbox-custom filter-type" value="Elective" onchange="app.filterSubjects()">
                                                    <span class="text-sm text-gray-700">Electives</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2" for="searchInput">Search:</label>
                                            <div class="flex">
                                                <input type="text" id="searchInput" class="form-input rounded-r-none border-r-0" placeholder="Search..." onkeyup="app.filterSubjects()" aria-label="Search subjects">
                                                <button class="bg-green-600 text-white px-3 rounded-r hover:bg-green-700 transition-all hover:scale-105" aria-label="Search">
                                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-gray-200 credit-progress-container">
                                            <div class="mb-2 flex justify-between text-sm font-medium">
                                                <span>Credits:</span>
                                                <span id="creditDisplay" class="credit-warning">0 / 18</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="18">
                                                <div id="creditBar" class="bg-red-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Min: 9 | Max: 18</p>
                                            <div id="creditStatusDetail" class="text-xs mt-2 font-medium text-red-600">
                                                <i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>Need 9 more credits minimum
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Center Table -->
                                <div class="col-span-6">
                                    <h2 class="section-title text-lg">Select Your Subjects</h2>
                                    <div class="overflow-hidden rounded-t-xl border border-green-200 shadow-lg">
                                        <table class="subject-table w-full" role="grid" aria-label="Available subjects">
                                            <thead>
                                                <tr>
                                                    <th class="w-16 text-center" scope="col">Select</th>
                                                    <th scope="col">Subject</th>
                                                    <th scope="col">Schedule</th>
                                                    <th class="w-16 text-center" scope="col">Cr</th>
                                                </tr>
                                            </thead>
                                            <tbody id="subjectTableBody">
                                                <!-- Populated by JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="flex justify-center gap-2 mt-4">
                                        <button class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs transition-all hover:scale-110" onclick="app.changePage(-1)" aria-label="Previous page">
                                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                                        </button>
                                        <span id="pageIndicator" class="flex items-center text-sm font-medium px-4">Page 1 of 1</span>
                                        <button class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs transition-all hover:scale-110" onclick="app.changePage(1)" aria-label="Next page">
                                            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Right Sidebar -->
                                <div class="col-span-3">
                                    <div class="summary-box rounded-xl">
                                        <h3 class="font-bold text-gray-800 mb-4 text-lg border-b border-green-300 pb-2">Summary</h3>
                                        <div class="space-y-2 mb-4 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Student:</span>
                                                <span class="font-medium text-gray-800" id="summaryName">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">ID:</span>
                                                <span class="font-medium text-gray-800" id="summaryId">-</span>
                                            </div>
                                        </div>
                                        <div class="border-t border-green-300 pt-4 mb-4">
                                            <h4 class="font-semibold text-gray-800 mb-2">Selected (<span id="selectedCount">0</span>)</h4>
                                            <ul class="space-y-1 text-sm max-h-40 overflow-y-auto" id="selectedSubjectsList">
                                                <li class="text-gray-400 italic text-xs">No subjects selected</li>
                                            </ul>
                                        </div>
                                        <div class="border-t border-green-300 pt-4 mb-4">
                                            <div class="flex justify-between items-center">
                                                <span class="font-semibold text-gray-800">Total Credits:</span>
                                                <span class="font-bold text-xl text-red-600" id="totalCredits">0</span>
                                            </div>
                                            <div id="creditStatusMsg" class="text-xs mt-1 text-red-600 font-medium">
                                                <i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>Minimum 9 credits required
                                            </div>
                                        </div>
                                        <button class="btn-outline w-full text-sm mb-2 ripple" onclick="app.clearSelection()">Clear All</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-center gap-4 mt-8 pt-6 border-t border-gray-200">
                                <button class="btn-secondary ripple" onclick="app.prevStep()">Back</button>
                                <button class="btn-primary ripple" onclick="app.validateStep2AndProceed()" id="btnStep2Continue">
                                    Continue
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Validation & Approval -->
                    <div id="step3" class="page-section" role="tabpanel" aria-labelledby="step3-tab">
                        <div class="bg-white/80 backdrop-blur-lg border-2 border-green-400 rounded-2xl p-6 shadow-xl">
                            <div class="grid grid-cols-12 gap-6">
                                <!-- Left -->
                                <div class="col-span-3">
                                    <h2 class="section-title text-lg">Student Information</h2>
                                    <div class="border-2 border-green-500 p-4 mb-4 rounded-xl bg-green-50 shadow-lg">
                                        <div class="flex gap-4 mb-4">
                                            <div class="photo-placeholder w-20 h-20 rounded-xl bg-white overflow-hidden shadow-md">
                                                <img id="valPhotoPreview" class="w-full h-full object-cover hidden" alt="Student" />
                                                <i class="fa-solid fa-user text-4xl text-gray-400" id="valPhotoIcon" aria-hidden="true"></i>
                                            </div>
                                            <div class="text-sm">
                                                <div class="mb-1">
                                                    <span class="font-medium text-gray-700">Name:</span><br>
                                                    <span class="text-gray-900 font-semibold" id="valName">-</span>
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-700">ID:</span>
                                                    <span class="text-gray-900 font-mono" id="valId">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-sm mb-1">
                                            <span class="font-medium text-gray-700">Program:</span>
                                            <span class="text-gray-900" id="valProgram">-</span>
                                        </div>
                                        <div class="text-sm">
                                            <span class="font-medium text-gray-700">Phone:</span>
                                            <span class="text-gray-900" id="valPhone">-</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 mb-2">Notes:</h3>
                                        <textarea class="form-input w-full h-24 text-sm" id="validationNotes" placeholder="Add validation notes..." aria-label="Validation notes"></textarea>
                                    </div>
                                </div>

                                <!-- Center -->
                                <div class="col-span-6">
                                    <h2 class="section-title text-lg">Load & Compliance</h2>
                                    <div class="compliance-box mb-6 shadow-lg">
                                        <div class="compliance-item" id="itemLoad">
                                            <i class="fa-regular fa-circle text-gray-400" id="iconLoad" aria-hidden="true"></i>
                                            <span class="font-medium text-gray-800">Current Load: <span id="loadCredits">0</span> Credits</span>
                                        </div>
                                        <div class="compliance-item" id="itemMin">
                                            <i class="fa-regular fa-circle text-gray-400" id="iconMin" aria-hidden="true"></i>
                                            <span class="font-medium text-gray-800">Minimum Credits Met (9)</span>
                                        </div>
                                        <div class="compliance-item" id="itemMax">
                                            <i class="fa-regular fa-circle text-gray-400" id="iconMax" aria-hidden="true"></i>
                                            <span class="font-medium text-gray-800">Max Credits Not Exceeded (18)</span>
                                        </div>
                                        <div class="compliance-item" id="itemProgram">
                                            <i class="fa-regular fa-circle text-gray-400" id="iconProgram" aria-hidden="true"></i>
                                            <span class="font-medium text-gray-800">Program Requirements</span>
                                            <span class="ml-auto text-sm font-medium text-orange-600" id="programComplianceStatus">Pending</span>
                                        </div>
                                    </div>

                                    <h2 class="section-title text-lg">Subject Validation</h2>
                                    <div class="overflow-hidden rounded-t-xl border border-green-200 shadow-lg">
                                        <table class="subject-table text-sm" role="grid" aria-label="Subject validation">
                                            <thead>
                                                <tr>
                                                    <th class="w-12" scope="col">Sel</th>
                                                    <th scope="col">Subject</th>
                                                    <th scope="col">Schedule</th>
                                                    <th class="w-16 text-center" scope="col">Cr</th>
                                                    <th class="w-24" scope="col">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="valTableBody">
                                                <!-- Populated by JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Right -->
                                <div class="col-span-3">
                                    <div class="border-2 border-green-500 p-4 rounded-xl bg-green-50 shadow-lg">
                                        <h3 class="font-bold text-gray-800 mb-4 text-lg border-b border-green-300 pb-2">Registrar</h3>
                                        <div class="mb-4">
                                            <label class="block text-xs font-medium text-gray-700 mb-1" for="registrarComments">Comments:</label>
                                            <div class="bg-white border border-green-300 rounded-lg p-2 text-sm min-h-[100px]" contenteditable="true" id="registrarComments" role="textbox" aria-multiline="true"></div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-lg border border-green-300 hover:bg-green-200 transition-all hover:scale-105" onclick="app.setRegistrarStatus('approved')">Approved</button>
                                            <button class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg border border-yellow-300 hover:bg-yellow-200 transition-all hover:scale-105" onclick="app.setRegistrarStatus('pending')">Pending Docs</button>
                                            <button class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded-lg border border-red-300 hover:bg-red-200 transition-all hover:scale-105" onclick="app.setRegistrarStatus('rejected')">Rejected</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-center gap-4 mt-8 pt-6 border-t border-gray-200">
                                <button class="btn-danger ripple" onclick="app.rejectEnrollment()" id="btnReject">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i> Deny
                                </button>
                                <button class="btn-secondary ripple" onclick="app.prevStep()">Back</button>
                                <button class="btn-success ripple" onclick="app.approveEnrollment()" id="btnApprove">
                                    <i class="fa-solid fa-check" aria-hidden="true"></i> Approve
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Status & Registrar Report - SEMESTER DROPDOWN REMOVED -->
                    <div id="step4" class="page-section" role="tabpanel" aria-labelledby="step4-tab">
                        <div class="bg-white/80 backdrop-blur-lg border-2 border-green-400 rounded-2xl p-8 shadow-xl">

                            <!-- Status Banner -->
                            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 flex justify-between items-center shadow-lg">
                                <div>
                                    <span class="text-sm text-gray-600">Current Status:</span>
                                    <span id="currentStatusBadge" class="status-badge validated ml-2">
                                        <i class="fa-solid fa-check-circle" aria-hidden="true"></i> Validated
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm text-gray-600">Student Type:</span>
                                    <span class="text-sm font-bold text-green-800 ml-2" id="statusStudentType">New Student</span>
                                </div>
                            </div>

                            <div class="mb-8">
                                <h2 class="section-title text-xl">Enrollment Status</h2>
                                <div class="flex justify-between items-start">
                                    <div class="flex gap-6">
                                        <div class="photo-placeholder w-24 h-24 rounded-xl bg-gray-100 overflow-hidden shadow-lg">
                                            <img id="statusPhotoPreview" class="w-full h-full object-cover hidden" alt="Student photo" />
                                            <i class="fa-solid fa-user text-5xl text-gray-300" id="statusPhotoIcon" aria-hidden="true"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 mb-1" id="statusName">-</h3>
                                            <p class="text-sm text-gray-700 mb-1"><span class="font-medium">ID:</span> <span id="statusId" class="font-mono font-semibold">-</span></p>
                                            <p class="text-sm text-gray-700 mb-1"><span class="font-medium">Program:</span> <span id="statusProgram">-</span></p>
                                            <p class="text-sm text-gray-700 mb-1"><span class="font-medium">Phone:</span> <span id="statusPhone">-</span></p>
                                            <p class="text-sm text-gray-700 mb-1"><span class="font-medium">Gender:</span> <span id="statusGender">-</span></p>
                                            <p class="text-sm text-gray-700"><span class="font-medium">Credits:</span> <span id="statusCredits" class="font-semibold">-</span></p>
                                        </div>
                                    </div>
                                    <!-- SEMESTER DROPDOWN REMOVED FROM HERE -->
                                </div>
                            </div>

                            <!-- Status Timeline -->
                            <div class="mb-10 px-4">
                                <div class="grid grid-cols-4 gap-6 relative" id="statusTimeline">
                                    <!-- Pending -->
                                    <div class="status-box pending p-4 rounded-xl relative" id="boxPending">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="status-icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></div>
                                            <span class="font-bold text-lg text-green-900">Pending</span>
                                        </div>
                                        <div class="text-sm text-green-800">
                                            <p class="font-medium mb-1">Submitted:</p>
                                            <p id="pendingDate">-</p>
                                        </div>
                                        <div class="arrow-connector text-green-400" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></div>
                                    </div>

                                    <!-- Validated -->
                                    <div class="status-box future p-4 rounded-xl relative" id="boxValidated">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="status-icon"><i class="fa-solid fa-check" aria-hidden="true"></i></div>
                                            <span class="font-bold text-lg text-gray-600">Validated</span>
                                        </div>
                                        <div class="text-sm">
                                            <p class="font-medium mb-1 opacity-90">Reviewed:</p>
                                            <p class="font-bold" id="validatedDate">-</p>
                                        </div>
                                        <div class="arrow-connector text-gray-400" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></div>
                                    </div>

                                    <!-- Approved -->
                                    <div class="status-box future p-4 rounded-xl relative" id="boxApproved">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="status-icon"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></div>
                                            <span class="font-bold text-lg text-gray-600">Approved</span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <p class="font-medium mb-1">Status:</p>
                                            <p>Waiting for approval</p>
                                        </div>
                                        <div class="arrow-connector text-gray-400" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></div>
                                    </div>

                                    <!-- Enrolled -->
                                    <div class="status-box future p-4 rounded-xl" id="boxEnrolled">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="status-icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                                            <span class="font-bold text-lg text-gray-600">Enrolled</span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <p class="font-medium mb-1">Status:</p>
                                            <p>Waiting for validation</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h2 class="section-title text-xl mb-4">Subject Overview</h2>
                                <div class="flex gap-6">
                                    <div class="flex-1">
                                        <table class="subject-table text-sm" role="grid" aria-label="Enrolled subjects">
                                            <thead>
                                                <tr>
                                                    <th class="w-1/3" scope="col">Subject</th>
                                                    <th class="w-1/3" scope="col">Schedule</th>
                                                    <th class="w-16 text-center" scope="col">Credits</th>
                                                    <th class="w-28" scope="col">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="statusTableBody">
                                                <!-- Populated by JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="w-64">
                                        <div class="border-2 border-green-500 p-4 rounded-xl bg-green-50 min-h-[150px] shadow-lg">
                                            <h3 class="font-bold text-gray-800 mb-3 text-sm border-b border-green-300 pb-2">Registrar Notes:</h3>
                                            <p class="text-sm text-gray-700" id="statusRegistrarNotes">Enrollment validated. Awaiting approval.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Registrar Summary Report -->
                            <div class="registrar-report">
                                <div class="registrar-report-header">
                                    <h3><i class="fa-solid fa-file-lines mr-2"></i>Enrollment Summary Report for Registrar</h3>
                                    <button class="btn-outline text-sm" onclick="app.printRegistrarReport()">
                                        <i class="fa-solid fa-print mr-2"></i>Print Report
                                    </button>
                                </div>

                                <div class="report-meta">
                                    <div class="report-meta-item">
                                        <div class="report-meta-label">Enrollment Date</div>
                                        <div class="report-meta-value" id="reportDate">-</div>
                                    </div>
                                    <div class="report-meta-item">
                                        <div class="report-meta-label">Student Type</div>
                                        <div class="report-meta-value" id="reportStudentType">-</div>
                                    </div>
                                    <div class="report-meta-item">
                                        <div class="report-meta-label">Total Credits</div>
                                        <div class="report-meta-value" id="reportCredits">-</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6 mt-6">
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-3">Student Information</h4>
                                        <table class="w-full text-sm">
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Student ID:</td>
                                                <td class="py-2 font-medium" id="reportStudentId">-</td>
                                            </tr>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Full Name:</td>
                                                <td class="py-2 font-medium" id="reportStudentName">-</td>
                                            </tr>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Gender:</td>
                                                <td class="py-2 font-medium" id="reportGender">-</td>
                                            </tr>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Date of Birth:</td>
                                                <td class="py-2 font-medium" id="reportDOB">-</td>
                                            </tr>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Program:</td>
                                                <td class="py-2 font-medium" id="reportProgram">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-3">Emergency Contact</h4>
                                        <table class="w-full text-sm">
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Name:</td>
                                                <td class="py-2 font-medium" id="reportEmergencyName">-</td>
                                            </tr>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Phone:</td>
                                                <td class="py-2 font-medium" id="reportEmergencyPhone">-</td>
                                            </tr>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Relationship:</td>
                                                <td class="py-2 font-medium" id="reportEmergencyRelation">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="font-semibold text-gray-800 mb-3">Selected Subjects</h4>
                                    <table class="w-full text-sm border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50">
                                                <th class="text-left py-2 px-3 border border-gray-200">Code</th>
                                                <th class="text-left py-2 px-3 border border-gray-200">Subject Name</th>
                                                <th class="text-center py-2 px-3 border border-gray-200">Credits</th>
                                                <th class="text-left py-2 px-3 border border-gray-200">Schedule</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reportSubjectsTable">
                                            <!-- Populated by JS -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-green-50 font-bold">
                                                <td colspan="2" class="py-2 px-3 border border-gray-200 text-right">Total Credits:</td>
                                                <td class="py-2 px-3 border border-gray-200 text-center" id="reportTotalCredits">0</td>
                                                <td class="py-2 px-3 border border-gray-200"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="mt-6 flex justify-between items-center pt-4 border-t border-gray-200">
                                    <div class="text-sm text-gray-600">
                                        <p>Report generated by: <span class="font-medium">Enrollment System</span></p>
                                        <p>Date: <span id="reportGeneratedDate">-</span></p>
                                    </div>
                                    <div class="flex gap-3">
                                        <button class="btn-secondary text-sm" onclick="app.exportRegistrarReport()">
                                            <i class="fa-solid fa-download mr-2"></i>Export PDF
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-center gap-4 mt-8 pt-6 border-t border-gray-200">
                                <button class="btn-secondary ripple" onclick="app.prevStep()">Back</button>
                                <button class="btn-primary ripple" onclick="app.finishEnrollment()">
                                    Finish Enrollment <i class="fa-solid fa-check text-sm ml-2" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </main>

                <!-- Floating Action Button - CENTERED with Label -->
                <div class="fab-container">
                    <span class="fab-label">Need Help?</span>
                    <button class="fab ripple" onclick="app.toggleHelp()" aria-label="Open Help">
                        <i class="fa-solid fa-question" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <script>
            /**
             * Student Enrollment System - FIXED VERSION
             * Removed: Intake, Prepared Date, Enrollment Count, Payment System, Preferred Start Date, ALL SEMESTER references
             * Added: Emergency Contact, Gender, Student Type (New/Old), Registrar Report, Photo Requirements
             * Fixed: CSP spacing, accessibility, CSS typos, error handling
             */
            class EnrollmentSystem {
                constructor() {
                    this.currentStep = 1;
                    this.selectedSubjects = new Map();
                    this.allSubjects = [];
                    this.filteredSubjects = [];
                    this.currentPage = 1;
                    this.itemsPerPage = 5;
                    this.phoneNumber = '';
                    this.emergencyPhoneNumber = '';
                    this.enrollmentId = null;
                    this.studentData = null;
                    this.validationErrors = {};
                    this.passwordValid = false;
                    this.passwordMatch = false;
                    this.enrollmentStatus = 'pending';
                    this.uploadedPhotoData = null;
                    this.currentViewingStudent = null;
                    this.isEditMode = false;
                    this.editingStudentId = null;
                    this.nextStudentIdCounter = 6;
                    this.helpOpen = false;
                    this.studentType = 'new';

                    // Authentication
                    this.currentUser = null;
                    this.sessionTimeout = null;
                    this.sessionWarningTimeout = null;
                    this.sessionCountdownInterval = null;
                    this.isLoggedIn = false;
                    this.csrfToken = null;

                    this.users = [];

                    // Mock Data for enrolled students
                    this.enrolledStudents = [{
                        id: '2024-001',
                        name: 'Juan Dela Cruz',
                        email: 'juan@university.edu',
                        phone: '+63 912 345 6789',
                        dob: '2000-05-15',
                        gender: 'Male',
                        address: '123 Main St, Manila',
                        studentType: 'new',
                        program: 'BSCS',
                        status: 'Enrolled',
                        date: '2024-01-15',
                        photo: null,
                        emergencyName: 'Maria Dela Cruz',
                        emergencyPhone: '+63 912 345 6788',
                        emergencyRelation: 'Parent',
                        subjects: [{
                                code: 'CSC110',
                                name: 'Intro to Programming',
                                credits: 3,
                                schedule: 'Mon/Wed 10:00-11:30'
                            },
                            {
                                code: 'MATH108',
                                name: 'Calculus I',
                                credits: 3,
                                schedule: 'Tue/Thu 11:00-12:30'
                            },
                            {
                                code: 'ENG101',
                                name: 'English Composition',
                                credits: 3,
                                schedule: 'Mon/Wed/Fri 9:00-10:00'
                            }
                        ]
                    }];

                    this.subjectsData = [{
                            id: 's1',
                            code: 'ENG101',
                            name: 'English Composition',
                            credits: 3,
                            type: 'General',
                            instructor: 'Dr. Smith',
                            schedule: 'Mon/Wed/Fri 9:00-10:00'
                        },
                        {
                            id: 's2',
                            code: 'MATH108',
                            name: 'Calculus I',
                            credits: 3,
                            type: 'Core',
                            instructor: 'Prof. Johnson',
                            schedule: 'Tue/Thu 11:00-12:30'
                        },
                        {
                            id: 's3',
                            code: 'BIO150',
                            name: 'Intro to Biology',
                            credits: 3,
                            type: 'Core',
                            instructor: 'Dr. Lee',
                            schedule: 'Tue/Thu 9:00-10:30'
                        },
                        {
                            id: 's4',
                            code: 'CSC110',
                            name: 'Intro to Programming',
                            credits: 3,
                            type: 'Core',
                            instructor: 'Prof. Williams',
                            schedule: 'Mon/Wed 10:00-11:30'
                        },
                        {
                            id: 's5',
                            code: 'HIST201',
                            name: 'World History',
                            credits: 3,
                            type: 'General',
                            instructor: 'Dr. Adams',
                            schedule: 'Tue/Thu 1:00-2:30'
                        },
                        {
                            id: 's6',
                            code: 'ART101',
                            name: 'Intro to Art',
                            credits: 3,
                            type: 'Elective',
                            instructor: 'Prof. Davis',
                            schedule: 'Fri 2:00-5:00'
                        },
                        {
                            id: 's7',
                            code: 'PHY101',
                            name: 'Physics I',
                            credits: 4,
                            type: 'Core',
                            instructor: 'Dr. Einstein',
                            schedule: 'Mon/Wed/Fri 8:00-9:00'
                        },
                        {
                            id: 's8',
                            code: 'PSY101',
                            name: 'Psychology',
                            credits: 3,
                            type: 'General',
                            instructor: 'Dr. Freud',
                            schedule: 'Mon/Wed 2:00-3:30'
                        }
                    ];

                    this.init();
                }

                init() {
                    this.allSubjects = [...this.subjectsData];
                    this.filteredSubjects = [...this.subjectsData];
                    this.renderSubjectTable();
                    this.setupEventListeners();
                    this.setupAccessibility();
                    this.checkExistingSession();
                    this.generateCSRFToken();
                    this.setupAutoSave();

                    if (this.isLoggedIn) {
                        this.loadProfileData();
                    }
                }

                // ==================== STUDENT TYPE SELECTION ====================

                selectStudentType(type) {
                    this.studentType = type;
                    document.getElementById('studentType').value = type;

                    // Update UI
                    document.querySelectorAll('.student-type-option').forEach(el => {
                        el.classList.remove('selected');
                    });
                    document.getElementById(`type-${type}`).classList.add('selected');

                    // Update student ID prefix based on type
                    this.updateStudentIdDisplay();

                    this.showToast(`${type === 'new' ? 'New' : 'Old'} student selected`, 'success');
                }

                // ==================== EMERGENCY CONTACT PHONE ====================

                formatEmergencyPhone(input) {
                    let value = input.value.replace(/\D/g, '');
                    value = value.substring(0, 11);

                    let formattedValue = '';
                    if (value.length > 0) {
                        formattedValue = value.substring(0, 4);
                        if (value.length > 4) {
                            formattedValue += ' ' + value.substring(4, 7);
                        }
                        if (value.length > 7) {
                            formattedValue += ' ' + value.substring(7, 11);
                        }
                    }

                    input.value = formattedValue;
                    this.emergencyPhoneNumber = value;

                    document.getElementById('inputEmergencyPhone-error').classList.remove('show');
                    input.classList.remove('field-error');
                }

                validateEmergencyPhone() {
                    const input = document.getElementById('inputEmergencyPhone');

                    // Relaxed validation: accept any non-empty input value. Use stored emergencyPhoneNumber if available,
                    // otherwise derive from the input's current value so server-rendered values are recognized.
                    const raw = (this.emergencyPhoneNumber || input.value || '').toString().trim();
                    const digits = (input.value || raw).replace(/\D/g, '');

                    if (raw.length > 0) {
                        this.emergencyPhoneNumber = digits;
                        input.classList.remove('field-error');
                        input.classList.add('field-valid');
                        document.getElementById('inputEmergencyPhone-error').classList.remove('show');
                        delete this.validationErrors['inputEmergencyPhone'];
                        return true;
                    } else {
                        input.classList.remove('field-valid');
                        input.classList.add('field-error');
                        document.getElementById('inputEmergencyPhone-error').classList.add('show');
                        this.validationErrors['inputEmergencyPhone'] = 'Phone number is required';
                        return false;
                    }
                }

                // ==================== REGISTRAR REPORT ====================

                generateRegistrarReport() {
                    const subjects = Array.from(this.selectedSubjects.values());
                    const totalCredits = subjects.reduce((sum, s) => sum + s.credits, 0);

                    // Basic Info
                    document.getElementById('reportDate').textContent = new Date().toLocaleDateString();
                    document.getElementById('reportStudentType').textContent = this.studentType === 'new' ? 'New Student' : 'Old Student';
                    document.getElementById('reportCredits').textContent = totalCredits;

                    // Student Info
                    document.getElementById('reportStudentId').textContent = this.profileData?.academicInfo?.studentId || 'PENDING';
                    document.getElementById('reportStudentName').textContent = document.getElementById('inputName').value;
                    document.getElementById('reportGender').textContent = document.querySelector('input[name="gender"]:checked')?.value || '-';
                    document.getElementById('reportDOB').textContent = this.formatDate(document.getElementById('inputDOB').value);
                    document.getElementById('reportProgram').textContent = document.getElementById('inputProgram').value;

                    // Emergency Contact
                    document.getElementById('reportEmergencyName').textContent = document.getElementById('inputEmergencyName').value || '-';
                    document.getElementById('reportEmergencyPhone').textContent = this.getFullEmergencyPhone() || '-';
                    document.getElementById('reportEmergencyRelation').textContent = document.getElementById('inputEmergencyRelation').value || '-';

                    // Subjects Table
                    const tbody = document.getElementById('reportSubjectsTable');
                    if (subjects.length > 0) {
                        tbody.innerHTML = subjects.map(s => `
                        <tr>
                            <td class="py-2 px-3 border border-gray-200">${s.code}</td>
                            <td class="py-2 px-3 border border-gray-200">${s.name}</td>
                            <td class="py-2 px-3 border border-gray-200 text-center">${s.credits}</td>
                            <td class="py-2 px-3 border border-gray-200">${s.schedule}</td>
                        </tr>
                    `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="py-2 px-3 border border-gray-200 text-center text-gray-500">No subjects selected</td></tr>';
                    }

                    document.getElementById('reportTotalCredits').textContent = totalCredits;
                    document.getElementById('reportGeneratedDate').textContent = new Date().toLocaleString();
                }

                getFullEmergencyPhone() {
                    const input = document.getElementById('inputEmergencyPhone');
                    return input ? input.value : (this.emergencyPhoneNumber || '');
                }

                printRegistrarReport() {
                    window.print();
                }

                exportRegistrarReport() {
                    this.showToast('Report exported as PDF', 'success');
                }

                finishEnrollment() {
                    this.showToast('Enrollment completed successfully!', 'success');
                    this.triggerConfetti();
                    setTimeout(() => {
                        this.goToStep(1);
                        this.resetForm();
                    }, 2000);
                }

                // ==================== SECURITY & AUTH (same as before) ====================

                generateCSRFToken() {
                    const array = new Uint8Array(32);
                    crypto.getRandomValues(array);
                    this.csrfToken = Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
                    sessionStorage.setItem('csrf_token', this.csrfToken);
                }

                async hashPassword(password) {
                    const encoder = new TextEncoder();
                    const data = encoder.encode(password);
                    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                    const hashArray = Array.from(new Uint8Array(hashBuffer));
                    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                }

                sanitizeInput(input) {
                    if (typeof input !== 'string') return '';
                    const div = document.createElement('div');
                    div.textContent = input;
                    return div.innerHTML;
                }

                stripNA(value) {
                    if (typeof value !== 'string') return value;
                    return value.trim().toUpperCase() === 'N/A' ? '' : value;
                }

                // ==================== PROFILE MANAGEMENT ====================

                saveProfileData() {
                    if (this.currentUser) {
                        const gender = document.querySelector('input[name="gender"]:checked')?.value || '';

                        const profile = {
                            userId: this.currentUser.id,
                            personalInfo: {
                                name: document.getElementById('inputName').value,
                                email: document.getElementById('inputEmail').value,
                                phone: this.getFullPhoneNumber(),
                                dob: document.getElementById('inputDOB').value,
                                gender: gender,
                                address: document.getElementById('inputAddress').value
                            },
                            academicInfo: {
                                studentType: this.studentType,
                                program: document.getElementById('inputProgram').value,
                                studentId: this.profileData?.academicInfo?.studentId || 'PENDING'
                            },
                            emergencyContact: {
                                name: document.getElementById('inputEmergencyName').value,
                                phone: this.getFullEmergencyPhone(),
                                relation: document.getElementById('inputEmergencyRelation').value
                            },
                            photo: this.uploadedPhotoData,
                            updatedAt: new Date().toISOString()
                        };

                        try {
                            sessionStorage.setItem(`profile_${this.currentUser.id}`, JSON.stringify(profile));
                            this.profileData = profile;
                            return true;
                        } catch (e) {
                            console.error('Error saving profile:', e);
                            return false;
                        }
                    }
                    return false;
                }

                loadProfileData() {
                    if (this.currentUser) {
                        try {
                            const stored = sessionStorage.getItem(`profile_${this.currentUser.id}`);
                            if (stored) {
                                this.profileData = JSON.parse(stored);
                                this.populateProfileForm();
                                return true;
                            }
                        } catch (e) {
                            console.error('Error loading profile:', e);
                        }
                    }
                    return false;
                }

                populateProfileForm() {
                    if (!this.profileData) return;

                    const p = this.profileData;
                    if (p.personalInfo) {
                        document.getElementById('inputName').value = this.stripNA(p.personalInfo.name) || '';
                        document.getElementById('inputEmail').value = this.stripNA(p.personalInfo.email) || '';

                        if (p.personalInfo.phone) {
                            const digits = p.personalInfo.phone.replace(/\D/g, '');
                            this.phoneNumber = digits;
                            if (digits.length === 11) {
                                document.getElementById('inputPhone').value =
                                    digits.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
                            } else {
                                document.getElementById('inputPhone').value = this.stripNA(p.personalInfo.phone);
                            }
                        }

                        document.getElementById('inputDOB').value = this.stripNA(p.personalInfo.dob) || '';
                        document.getElementById('inputAddress').value = this.stripNA(p.personalInfo.address) || '';

                        // Set gender
                        if (p.personalInfo.gender) {
                            const genderVal = this.stripNA(p.personalInfo.gender);
                            const genderRadio = document.querySelector(`input[name="gender"][value="${genderVal.toLowerCase()}"]`);
                            if (genderRadio) genderRadio.checked = true;
                        }
                    }

                    if (p.academicInfo) {
                        this.studentType = p.academicInfo.studentType || 'new';
                        this.selectStudentType(this.studentType);
                        document.getElementById('inputProgram').value = this.stripNA(p.academicInfo.program) || '';

                    }

                    if (p.emergencyContact) {
                        document.getElementById('inputEmergencyName').value = this.stripNA(p.emergencyContact.name) || '';
                        document.getElementById('inputEmergencyRelation').value = this.stripNA(p.emergencyContact.relation) || '';

                        if (p.emergencyContact.phone) {
                            const digits = p.emergencyContact.phone.replace(/\D/g, '');
                            this.emergencyPhoneNumber = digits;
                            if (digits.length === 11) {
                                document.getElementById('inputEmergencyPhone').value =
                                    digits.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
                            } else {
                                document.getElementById('inputEmergencyPhone').value = this.stripNA(p.emergencyContact.phone);
                            }
                        }
                    }

                    if (p.photo) {
                        this.uploadedPhotoData = p.photo;
                        document.getElementById('photoPreview').src = p.photo;
                        document.getElementById('photoPreview').classList.remove('hidden');
                        document.getElementById('photoIcon').classList.add('hidden');
                    }

                    this.updateUserDisplay();
                }

                setupAutoSave() {
                    setInterval(() => {
                        if (this.isLoggedIn && this.currentStep === 1) {
                            if (document.getElementById('inputName').value) {
                                this.saveProfileData();
                            }
                        }
                    }, 30000);
                }

                // ==================== AUTHENTICATION ====================

                async switchLoginTab(tab) {
                    document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.login-form').forEach(f => f.classList.remove('active'));

                    document.getElementById(`tab-${tab}`).classList.add('active');
                    document.getElementById(`${tab}Form`).classList.add('active');
                }

                toggleLoginPassword(fieldId) {
                    const field = document.getElementById(fieldId);
                    const btn = field.nextElementSibling;
                    const icon = btn.querySelector('i');

                    if (field.type === 'password') {
                        field.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        field.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }

                checkPasswordStrength(password) {
                    const checks = {
                        length: password.length >= 8,
                        uppercase: /[A-Z]/.test(password),
                        lowercase: /[a-z]/.test(password),
                        number: /[0-9]/.test(password),
                        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
                    };

                    let strength = 0;
                    if (checks.length) strength++;
                    if (checks.uppercase && checks.lowercase) strength++;
                    if (checks.number) strength++;
                    if (checks.special) strength++;

                    for (let i = 1; i <= 4; i++) {
                        const seg = document.getElementById(`regStrength${i}`);
                        seg.className = 'strength-segment';
                        if (i <= strength) {
                            seg.classList.add('active');
                            if (strength <= 2) seg.classList.add('weak');
                            else if (strength === 3) seg.classList.add('medium');
                            else seg.classList.add('strong');
                        }
                    }

                    const hint = document.getElementById('regPasswordHint');
                    const missing = [];
                    if (!checks.length) missing.push('8+ chars');
                    if (!checks.uppercase) missing.push('uppercase');
                    if (!checks.lowercase) missing.push('lowercase');
                    if (!checks.number) missing.push('number');
                    if (!checks.special) missing.push('special char');

                    if (missing.length > 0) {
                        hint.textContent = 'Missing: ' + missing.join(', ');
                        hint.style.color = '#dc2626';
                    } else {
                        hint.textContent = 'Strong password';
                        hint.style.color = '#16a34a';
                    }

                    return strength === 4;
                }

                async handleLogin(e) {
                    e.preventDefault();

                    const btn = document.getElementById('btnLogin');
                    btn.classList.add('btn-loading');
                    btn.disabled = true;

                    try {
                        const email = document.getElementById('loginEmail').value.trim().toLowerCase();
                        const password = document.getElementById('loginPassword').value;

                        if (!email || !password) {
                            this.showToast('Please enter email and password', 'error');
                            return;
                        }

                        const user = this.users.find(u => u.email.toLowerCase() === email);

                        if (!user) {
                            this.showToast('Invalid email or password', 'error');
                            return;
                        }

                        const hashedPassword = await this.hashPassword(password);
                        if (hashedPassword !== user.password) {
                            await new Promise(resolve => setTimeout(resolve, 100));
                            this.showToast('Invalid email or password', 'error');
                            return;
                        }

                        user.lastLogin = new Date().toISOString();

                        this.currentUser = {
                            id: user.id,
                            name: user.name,
                            email: user.email,
                            role: user.role
                        };

                        this.isLoggedIn = true;

                        const sessionData = {
                            user: this.currentUser,
                            csrfToken: this.csrfToken,
                            expires: Date.now() + (30 * 60 * 1000)
                        };
                        sessionStorage.setItem('enrollment_session', JSON.stringify(sessionData));

                        this.showToast(`Welcome back, ${user.name}!`, 'success');
                        this.enterApplication();
                    } finally {
                        btn.classList.remove('btn-loading');
                        btn.disabled = false;
                    }
                }

                async handleRegister(e) {
                    e.preventDefault();

                    const btn = document.getElementById('btnRegister');
                    btn.classList.add('btn-loading');
                    btn.disabled = true;

                    try {
                        const name = document.getElementById('regName').value.trim();
                        const email = document.getElementById('regEmail').value.trim().toLowerCase();
                        const password = document.getElementById('regPassword').value;
                        const confirmPassword = document.getElementById('regConfirmPassword').value;

                        if (!this.checkPasswordStrength(password)) {
                            this.showToast('Please create a stronger password', 'error');
                            return;
                        }

                        if (password !== confirmPassword) {
                            this.showToast('Passwords do not match', 'error');
                            return;
                        }

                        if (this.users.find(u => u.email.toLowerCase() === email)) {
                            this.showToast('An account with this email already exists', 'error');
                            return;
                        }

                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(email)) {
                            this.showToast('Please enter a valid email address', 'error');
                            return;
                        }

                        const hashedPassword = await this.hashPassword(password);
                        const newUser = {
                            id: 'USR-' + crypto.randomUUID(),
                            name: this.sanitizeInput(name),
                            email: email,
                            password: hashedPassword,
                            role: 'student',
                            createdAt: new Date().toISOString(),
                            lastLogin: new Date().toISOString()
                        };

                        this.users.push(newUser);

                        this.currentUser = {
                            id: newUser.id,
                            name: newUser.name,
                            email: newUser.email,
                            role: newUser.role
                        };

                        this.isLoggedIn = true;

                        const sessionData = {
                            user: this.currentUser,
                            csrfToken: this.csrfToken,
                            expires: Date.now() + (30 * 60 * 1000)
                        };
                        sessionStorage.setItem('enrollment_session', JSON.stringify(sessionData));

                        this.showToast('Account created successfully!', 'success');
                        document.getElementById('accountCreatedMsg').classList.remove('hidden');
                        this.enterApplication();
                    } finally {
                        btn.classList.remove('btn-loading');
                        btn.disabled = false;
                    }
                }

                enterApplication() {
                    document.getElementById('loginPage').classList.add('hidden');
                    document.getElementById('mainApp').classList.remove('hidden');

                    this.updateUserDisplay();
                    document.getElementById('inputEmail').value = this.currentUser.email;
                    document.getElementById('inputName').value = this.currentUser.name;

                    this.loadProfileData();
                    this.startSessionTimer();
                    this.updateStudentIdDisplay();
                }

                updateUserDisplay() {
                    if (!this.currentUser) return;

                    const formName = document.getElementById('inputName')?.value;
                    const displayName = formName || this.currentUser.name;

                    const initials = displayName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

                    // Removed so it uses PHP session variables instead
                    // document.getElementById('userAvatar').textContent = initials;
                    // document.getElementById('userNameDisplay').textContent = displayName;
                    // document.getElementById('dropdownUserName').textContent = displayName;
                    // document.getElementById('dropdownUserEmail').textContent = this.currentUser.email;

                    this.currentUser.name = displayName;
                }

                startSessionTimer() {
                    this.clearSessionTimers();

                    const session = JSON.parse(sessionStorage.getItem('enrollment_session') || '{}');
                    if (!session.expires) return;

                    const timeLeft = session.expires - Date.now();
                    const warningTime = 5 * 60 * 1000;

                    if (timeLeft > warningTime) {
                        this.sessionWarningTimeout = setTimeout(() => {
                            this.showSessionWarning();
                        }, timeLeft - warningTime);
                    } else if (timeLeft > 0) {
                        this.showSessionWarning();
                    }

                    this.sessionTimeout = setTimeout(() => {
                        this.logout();
                        this.showToast('Session expired. Please log in again.', 'error');
                    }, timeLeft);
                }

                clearSessionTimers() {
                    if (this.sessionTimeout) clearTimeout(this.sessionTimeout);
                    if (this.sessionWarningTimeout) clearTimeout(this.sessionWarningTimeout);
                    if (this.sessionCountdownInterval) clearInterval(this.sessionCountdownInterval);
                }

                showSessionWarning() {
                    const warning = document.getElementById('sessionWarning');
                    warning.classList.add('show');

                    let secondsLeft = 5 * 60;
                    const countdownEl = document.getElementById('sessionCountdown');

                    this.sessionCountdownInterval = setInterval(() => {
                        secondsLeft--;
                        if (secondsLeft <= 0) {
                            clearInterval(this.sessionCountdownInterval);
                        } else {
                            const mins = Math.floor(secondsLeft / 60);
                            const secs = secondsLeft % 60;
                            countdownEl.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
                        }
                    }, 1000);
                }

                extendSession() {
                    const session = JSON.parse(sessionStorage.getItem('enrollment_session') || '{}');
                    if (session.expires) {
                        session.expires = Date.now() + (30 * 60 * 1000);
                        sessionStorage.setItem('enrollment_session', JSON.stringify(session));
                    }

                    this.clearSessionTimers();
                    document.getElementById('sessionWarning').classList.remove('show');
                    this.startSessionTimer();
                    this.showToast('Session extended', 'success');
                }

                checkExistingSession() {
                    const session = JSON.parse(sessionStorage.getItem('enrollment_session') || '{}');

                    if (session.user && session.expires > Date.now() && session.csrfToken) {
                        this.csrfToken = session.csrfToken;
                        this.currentUser = session.user;
                        this.isLoggedIn = true;
                        this.enterApplication();
                    } else {
                        sessionStorage.removeItem('enrollment_session');
                    }
                }

                logout() {
                    if (this.isLoggedIn) {
                        this.saveProfileData();
                    }

                    this.clearSessionTimers();
                    sessionStorage.removeItem('enrollment_session');
                    sessionStorage.removeItem('csrf_token');

                    this.currentUser = null;
                    this.isLoggedIn = false;
                    this.currentStep = 1;
                    this.selectedSubjects.clear();
                    this.resetForm();
                    this.generateCSRFToken();

                    document.getElementById('mainApp').classList.add('hidden');
                    document.getElementById('loginPage').classList.remove('hidden');
                    document.getElementById('userDropdown').classList.remove('show');

                    this.switchLoginTab('login');
                    this.showToast('Logged out successfully', 'success');
                }

                toggleUserMenu() {
                    document.getElementById('userDropdown').classList.toggle('show');
                }

                viewProfile() {
                    document.getElementById('userDropdown').classList.remove('show');

                    if (this.currentUser) {
                        const gender = document.querySelector('input[name="gender"]:checked')?.value || 'Not specified';

                        const tempStudent = {
                            id: this.profileData?.academicInfo?.studentId || 'PENDING',
                            name: this.stripNA(document.getElementById('inputName').value) || this.currentUser.name,
                            email: this.stripNA(document.getElementById('inputEmail').value) || this.currentUser.email,
                            phone: this.stripNA(this.getFullPhoneNumber()) || 'Not provided',
                            dob: this.stripNA(document.getElementById('inputDOB').value) || 'Not set',
                            gender: gender,
                            address: this.stripNA(document.getElementById('inputAddress').value) || 'Not provided',
                            studentType: this.studentType === 'new' ? 'New Student' : 'Old Student',
                            program: this.stripNA(document.getElementById('inputProgram').value) || 'Not selected',
                            status: this.enrollmentStatus === 'enrolled' ? 'Enrolled' : 'In Progress',
                            photo: this.uploadedPhotoData,
                            emergencyName: this.stripNA(document.getElementById('inputEmergencyName').value) || 'Not provided',
                            emergencyPhone: this.stripNA(this.getFullEmergencyPhone()) || 'Not provided',
                            emergencyRelation: this.stripNA(document.getElementById('inputEmergencyRelation').value) || 'Not specified',
                            subjects: Array.from(this.selectedSubjects.values()).map(s => ({
                                code: s.code,
                                name: s.name,
                                credits: s.credits,
                                schedule: s.schedule
                            }))
                        };

                        this.currentViewingStudent = tempStudent;

                        document.getElementById('viewStudentId').textContent = tempStudent.id;
                        document.getElementById('viewStudentName').textContent = tempStudent.name;
                        document.getElementById('viewStudentEmail').textContent = tempStudent.email;
                        document.getElementById('viewStudentPhone').textContent = tempStudent.phone;
                        document.getElementById('viewStudentDOB').textContent = this.formatDate(tempStudent.dob);
                        document.getElementById('viewStudentGender').textContent = tempStudent.gender;
                        document.getElementById('viewStudentAddress').textContent = tempStudent.address;
                        document.getElementById('viewStudentType').textContent = tempStudent.studentType;
                        document.getElementById('viewStudentProgram').textContent = tempStudent.program;

                        const totalCredits = tempStudent.subjects.reduce((sum, s) => sum + s.credits, 0);
                        document.getElementById('viewStudentCredits').textContent = totalCredits;

                        const statusBadge = document.getElementById('viewStudentStatus');
                        statusBadge.className = 'status-badge ' + (tempStudent.status === 'Enrolled' ? 'enrolled' : 'pending');
                        statusBadge.innerHTML = this.getStatusIcon(tempStudent.status) + ' ' + tempStudent.status;

                        document.getElementById('viewEmergencyName').textContent = tempStudent.emergencyName;
                        document.getElementById('viewEmergencyPhone').textContent = tempStudent.emergencyPhone;
                        document.getElementById('viewEmergencyRelation').textContent = tempStudent.emergencyRelation;

                        const photoEl = document.getElementById('viewStudentPhoto');
                        const photoIcon = document.getElementById('viewStudentPhotoIcon');
                        if (tempStudent.photo) {
                            photoEl.src = tempStudent.photo;
                            photoEl.classList.remove('hidden');
                            photoIcon.classList.add('hidden');
                        } else {
                            photoEl.classList.add('hidden');
                            photoIcon.classList.remove('hidden');
                        }

                        const subjectsContainer = document.getElementById('viewStudentSubjects');
                        if (tempStudent.subjects.length > 0) {
                            subjectsContainer.innerHTML = tempStudent.subjects.map(sub => `
                            <div class="subject-item">
                                <div>
                                    <div class="font-semibold text-sm">${sub.code} - ${sub.name}</div>
                                    <div class="text-xs text-gray-500">${sub.schedule}</div>
                                </div>
                                <span class="text-sm font-bold text-green-600">${sub.credits} cr</span>
                            </div>
                        `).join('');
                        } else {
                            subjectsContainer.innerHTML = '<p class="text-gray-500 text-sm italic">No subjects selected yet</p>';
                        }

                        const modal = document.getElementById('viewStudentModal');
                        modal.classList.add('show');

                        const editBtn = modal.querySelector('button[onclick="app.editCurrentStudent()"]');
                        if (editBtn) editBtn.style.display = 'none';

                        this.announceToScreenReader(`Viewing profile for ${tempStudent.name}`);
                    }
                }

                showForgotPassword() {
                    this.showToast('Please contact support to reset your password', 'info');
                }

                showTerms() {
                    alert('Terms of Service:\n\nBy using this system, you agree to abide by all university policies and procedures regarding enrollment and conduct.');
                }

                showPrivacy() {
                    alert('Privacy Policy:\n\nYour personal information is protected and will only be used for enrollment purposes.');
                }

                socialLogin(provider) {
                    this.showToast(`${provider} login coming soon!`, 'info');
                }

                setupEventListeners() {
                    // Use passive listeners where possible for better performance
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            if (this.helpOpen) {
                                this.toggleHelp();
                            }
                            const viewModal = document.getElementById('viewStudentModal');
                            if (viewModal.classList.contains('show')) {
                                this.closeViewStudentModal();
                            }
                            document.getElementById('userDropdown').classList.remove('show');
                        }
                    }, {
                        passive: true
                    });

                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('.user-menu')) {
                            document.getElementById('userDropdown').classList.remove('show');
                        }
                    });
                }

                setupAccessibility() {
                    this.updateStepIndicators();
                }

                updateStepIndicators() {
                    const arrows = document.querySelectorAll('.step-arrow');
                    arrows.forEach((arrow, idx) => {
                        const stepNum = idx + 1;
                        arrow.setAttribute('aria-label', `Step ${stepNum}: ${arrow.textContent.trim()}`);
                        arrow.setAttribute('tabindex', stepNum <= this.currentStep ? '0' : '-1');
                    });
                }

                // ==================== HELP PANEL ====================

                toggleHelp() {
                    this.helpOpen = !this.helpOpen;
                    const panel = document.getElementById('helpPanel');
                    const overlay = document.getElementById('helpOverlay');

                    if (this.helpOpen) {
                        panel.classList.add('open');
                        overlay.classList.add('show');
                        document.body.style.overflow = 'hidden';
                    } else {
                        panel.classList.remove('open');
                        overlay.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                }

                contactSupport() {
                    this.showToast('Connecting to support... Call +63 2 8123 4567', 'info');
                    setTimeout(() => {
                        this.toggleHelp();
                    }, 2000);
                }

                // ==================== STUDENT ID ====================

                generateNewStudentId() {
                    const year = new Date().getFullYear();
                    const prefix = this.studentType === 'new' ? 'NEW' : 'OLD';
                    const id = `${year}-${prefix}-${String(this.nextStudentIdCounter).padStart(3, '0')}`;
                    this.nextStudentIdCounter++;
                    return id;
                }

                updateStudentIdDisplay() {
                    // Method retained for logical consistency, but display element removed
                }

                // ==================== EDIT MODE ====================

                enterEditMode(student) {
                    this.isEditMode = true;
                    this.editingStudentId = student.id;
                    this.currentViewingStudent = student;

                    const banner = document.getElementById('editModeBanner');
                    banner.classList.add('show');
                    document.getElementById('editingStudentId').textContent = student.id;

                    document.getElementById('accountCreatedMsg').classList.add('hidden');
                    document.getElementById('btnCancelEdit').classList.remove('hidden');
                    document.getElementById('btnStep1Action').innerHTML = 'Save Changes <i class="fa-solid fa-save text-sm ml-2" aria-hidden="true"></i>';

                    document.getElementById('inputName').value = this.stripNA(student.name);
                    document.getElementById('inputEmail').value = this.stripNA(student.email);
                    document.getElementById('inputAddress').value = this.stripNA(student.address);
                    document.getElementById('inputDOB').value = this.stripNA(student.dob);

                    // Set gender
                    if (student.gender) {
                        const genderVal = this.stripNA(student.gender);
                        const genderRadio = document.querySelector(`input[name="gender"][value="${genderVal.toLowerCase()}"]`);
                        if (genderRadio) genderRadio.checked = true;
                    }

                    // Set student type
                    if (student.studentType) {
                        const typeVal = this.stripNA(student.studentType);
                        const type = typeVal.toLowerCase().includes('new') ? 'new' : 'old';
                        this.selectStudentType(type);
                    }

                    document.getElementById('inputProgram').value = this.stripNA(student.program);


                    // Emergency contact
                    if (student.emergencyName) {
                        document.getElementById('inputEmergencyName').value = this.stripNA(student.emergencyName);
                    }
                    if (student.emergencyPhone) {
                        const digits = student.emergencyPhone.replace(/\D/g, '');
                        this.emergencyPhoneNumber = digits;
                        if (digits.length === 11) {
                            document.getElementById('inputEmergencyPhone').value =
                                digits.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
                        } else {
                            document.getElementById('inputEmergencyPhone').value = this.stripNA(student.emergencyPhone);
                        }
                    }
                    if (student.emergencyRelation) {
                        document.getElementById('inputEmergencyRelation').value = this.stripNA(student.emergencyRelation);
                    }

                    if (student.phone) {
                        const digits = student.phone.replace(/\D/g, '');
                        this.phoneNumber = digits;
                        if (digits.length === 11) {
                            document.getElementById('inputPhone').value =
                                digits.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
                        } else {
                            document.getElementById('inputPhone').value = this.stripNA(student.phone);
                        }
                    }

                    if (student.photo) {
                        this.uploadedPhotoData = student.photo;
                        document.getElementById('photoPreview').src = student.photo;
                        document.getElementById('photoPreview').classList.remove('hidden');
                        document.getElementById('photoIcon').classList.add('hidden');
                    }

                    this.selectedSubjects.clear();
                    if (student.subjects && student.subjects.length > 0) {
                        student.subjects.forEach(sub => {
                            const match = this.allSubjects.find(s => s.code === sub.code);
                            if (match) {
                                this.selectedSubjects.set(match.id, match);
                            }
                        });
                    }

                    this.renderSubjectTable();
                    this.updateSummaryPanel();

                    this.validationErrors = {};
                    this.clearValidationSummary('step1');

                    this.goToStep(1);

                    this.showToast(`Editing student ${student.id}`, 'info');
                    this.announceToScreenReader(`Now editing student ${student.id}`);
                }

                cancelEdit() {
                    this.isEditMode = false;
                    this.editingStudentId = null;
                    this.currentViewingStudent = null;

                    document.getElementById('editModeBanner').classList.remove('show');
                    document.getElementById('btnCancelEdit').classList.add('hidden');
                    document.getElementById('btnStep1Action').innerHTML = 'Next <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>';

                    this.resetForm();


                    this.showToast('Edit cancelled', 'info');
                    this.announceToScreenReader('Edit mode cancelled. Starting new enrollment.');
                }

                saveEditChanges() {
                    const studentIndex = this.enrolledStudents.findIndex(s => s.id === this.editingStudentId);
                    if (studentIndex === -1) {
                        this.showToast('Student not found', 'error');
                        return;
                    }

                    const validations = [
                        this.validateField('inputName', 'name'),
                        this.validatePhoneNumber(true),
                        this.validateField('inputDOB', 'date'),
                        this.validateField('inputAddress', 'address'),
                        this.validateField('inputProgram', 'select'),
                        this.validateField('inputEmergencyName', 'name'),
                        this.validateEmergencyPhone(),
                        this.validateField('inputEmergencyRelation', 'select')
                    ];

                    // Validate gender
                    const genderSelected = document.querySelector('input[name="gender"]:checked');
                    if (!genderSelected) {
                        document.getElementById('gender-error').classList.add('show');
                        validations.push(false);
                    } else {
                        document.getElementById('gender-error').classList.remove('show');
                    }

                    const photoPreview = document.getElementById('photoPreview');
                    const photoValid = !photoPreview.classList.contains('hidden');
                    if (!photoValid) {
                        document.getElementById('photoError').classList.add('show');
                        this.validationErrors['photo'] = 'Photo is required';
                    } else {
                        document.getElementById('photoError').classList.remove('show');
                        delete this.validationErrors['photo'];
                    }

                    const allValid = validations.every(v => v === true) && photoValid;

                    if (!allValid) {
                        this.showValidationSummary('step1');
                        this.showToast('Please fix all validation errors', 'error');
                        return;
                    }

                    const originalId = this.editingStudentId;
                    const gender = document.querySelector('input[name="gender"]:checked')?.value;

                    const updatedStudent = {
                        ...this.enrolledStudents[studentIndex],
                        id: originalId,
                        name: this.sanitizeInput(document.getElementById('inputName').value),
                        email: document.getElementById('inputEmail').value,
                        phone: this.getFullPhoneNumber(),
                        dob: document.getElementById('inputDOB').value,
                        gender: gender,
                        address: this.sanitizeInput(document.getElementById('inputAddress').value),
                        studentType: this.studentType === 'new' ? 'New Student' : 'Old Student',
                        program: document.getElementById('inputProgram').value,
                        photo: this.uploadedPhotoData,
                        emergencyName: document.getElementById('inputEmergencyName').value,
                        emergencyPhone: this.getFullEmergencyPhone(),
                        emergencyRelation: document.getElementById('inputEmergencyRelation').value,
                        subjects: Array.from(this.selectedSubjects.values()).map(s => ({
                            code: s.code,
                            name: s.name,
                            credits: s.credits,
                            schedule: s.schedule
                        })),
                        date: new Date().toISOString().split('T')[0]
                    };

                    this.enrolledStudents[studentIndex] = updatedStudent;

                    this.isEditMode = false;
                    this.editingStudentId = null;
                    this.currentViewingStudent = null;

                    document.getElementById('editModeBanner').classList.remove('show');
                    document.getElementById('btnCancelEdit').classList.add('hidden');
                    document.getElementById('btnStep1Action').innerHTML = 'Next <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>';

                    this.resetForm();

                    this.showToast('Student record updated successfully!', 'success');
                    this.announceToScreenReader(`Student ${originalId} updated successfully`);
                    this.goToStep(4);
                }

                resetForm() {
                    document.getElementById('inputName').value = '';
                    document.getElementById('inputEmail').value = this.currentUser ? this.currentUser.email : '';
                    document.getElementById('inputPhone').value = '';
                    document.getElementById('inputDOB').value = '';

                    // Reset gender
                    document.querySelectorAll('input[name="gender"]').forEach(el => el.checked = false);
                    document.getElementById('gender-error').classList.remove('show');

                    document.getElementById('inputAddress').value = '';
                    document.getElementById('inputProgram').value = '';

                    // Reset emergency contact
                    document.getElementById('inputEmergencyName').value = '';
                    document.getElementById('inputEmergencyPhone').value = '';
                    document.getElementById('inputEmergencyRelation').value = '';
                    this.emergencyPhoneNumber = '';

                    // Reset student type to new
                    this.selectStudentType('new');



                    this.uploadedPhotoData = null;
                    document.getElementById('photoPreview').classList.add('hidden');
                    document.getElementById('photoIcon').classList.remove('hidden');

                    document.getElementById('termsCheck').checked = false;

                    document.querySelectorAll('.form-input').forEach(input => {
                        input.classList.remove('field-error', 'field-valid');
                    });
                    document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));
                    document.querySelectorAll('.success-message').forEach(el => el.classList.remove('show'));

                    this.selectedSubjects.clear();
                    this.renderSubjectTable();
                    this.updateSummaryPanel();

                    this.phoneNumber = '';

                    this.validationErrors = {};
                }

                // ==================== TOAST NOTIFICATIONS ====================

                showToast(message, type = 'info') {
                    const container = document.getElementById('toastContainer');
                    const toast = document.createElement('div');
                    toast.className = `toast ${type}`;
                    toast.setAttribute('role', 'status');
                    toast.setAttribute('aria-live', 'polite');
                    toast.innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-circle-exclamation' : 'fa-info-circle'}" aria-hidden="true"></i>
                        <span>${this.sanitizeInput(message)}</span>
                    </div>
                `;
                    container.appendChild(toast);

                    setTimeout(() => toast.classList.add('show'), 100);
                    setTimeout(() => {
                        toast.classList.remove('show');
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                }

                announceToScreenReader(message) {
                    const announcer = document.createElement('div');
                    announcer.setAttribute('role', 'status');
                    announcer.setAttribute('aria-live', 'polite');
                    announcer.className = 'sr-only';
                    announcer.textContent = message;
                    document.body.appendChild(announcer);
                    setTimeout(() => announcer.remove(), 1000);
                }

                // ==================== VIEW STUDENT MODAL ====================

                viewStudent(id) {
                    const student = this.enrolledStudents.find(s => s.id === id);
                    if (!student) {
                        this.showToast('Student not found', 'error');
                        return;
                    }

                    this.currentViewingStudent = student;

                    document.getElementById('viewStudentId').textContent = student.id;
                    document.getElementById('viewStudentName').textContent = student.name;
                    document.getElementById('viewStudentEmail').textContent = student.email;
                    document.getElementById('viewStudentPhone').textContent = student.phone;
                    document.getElementById('viewStudentDOB').textContent = this.formatDate(student.dob);
                    document.getElementById('viewStudentGender').textContent = student.gender || '-';
                    document.getElementById('viewStudentAddress').textContent = student.address;
                    document.getElementById('viewStudentType').textContent = student.studentType || 'New Student';
                    document.getElementById('viewStudentProgram').textContent = student.program;

                    const totalCredits = student.subjects.reduce((sum, s) => sum + s.credits, 0);
                    document.getElementById('viewStudentCredits').textContent = totalCredits;

                    const statusBadge = document.getElementById('viewStudentStatus');
                    statusBadge.className = 'status-badge ' + student.status.toLowerCase();
                    statusBadge.innerHTML = this.getStatusIcon(student.status) + ' ' + student.status;

                    // Emergency contact
                    document.getElementById('viewEmergencyName').textContent = student.emergencyName || '-';
                    document.getElementById('viewEmergencyPhone').textContent = student.emergencyPhone || '-';
                    document.getElementById('viewEmergencyRelation').textContent = student.emergencyRelation || '-';

                    const photoEl = document.getElementById('viewStudentPhoto');
                    const photoIcon = document.getElementById('viewStudentPhotoIcon');
                    if (student.photo) {
                        photoEl.src = student.photo;
                        photoEl.classList.remove('hidden');
                        photoIcon.classList.add('hidden');
                    } else {
                        photoEl.classList.add('hidden');
                        photoIcon.classList.remove('hidden');
                    }

                    const subjectsContainer = document.getElementById('viewStudentSubjects');
                    if (student.subjects.length > 0) {
                        subjectsContainer.innerHTML = student.subjects.map(sub => `
                        <div class="subject-item">
                            <div>
                                <div class="font-semibold text-sm">${sub.code} - ${sub.name}</div>
                                <div class="text-xs text-gray-500">${sub.schedule}</div>
                            </div>
                            <span class="text-sm font-bold text-green-600">${sub.credits} cr</span>
                        </div>
                    `).join('');
                    } else {
                        subjectsContainer.innerHTML = '<p class="text-gray-500 text-sm italic">No subjects enrolled</p>';
                    }

                    const modal = document.getElementById('viewStudentModal');
                    modal.classList.add('show');

                    const editBtn = modal.querySelector('button[onclick="app.editCurrentStudent()"]');
                    if (editBtn) editBtn.style.display = 'inline-flex';

                    this.showToast(`Viewing student ${id}`, 'info');
                }

                closeViewStudentModal() {
                    document.getElementById('viewStudentModal').classList.remove('show');
                    this.currentViewingStudent = null;
                }

                editCurrentStudent() {
                    if (this.currentViewingStudent) {
                        this.closeViewStudentModal();
                        this.enterEditMode(this.currentViewingStudent);
                    }
                }

                printStudentDetails() {
                    window.print();
                }

                getStatusIcon(status) {
                    switch (status) {
                        case 'Enrolled':
                            return '<i class="fa-solid fa-check-circle" aria-hidden="true"></i>';
                        case 'Validated':
                            return '<i class="fa-solid fa-check" aria-hidden="true"></i>';
                        case 'Pending':
                            return '<i class="fa-regular fa-clock" aria-hidden="true"></i>';
                        default:
                            return '<i class="fa-regular fa-clock" aria-hidden="true"></i>';
                    }
                }

                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }

                // ==================== FIELD VALIDATION ====================

                validateField(fieldId, type) {
                    const field = document.getElementById(fieldId);
                    const errorEl = document.getElementById(`${fieldId}-error`);
                    const successEl = document.getElementById(`${fieldId}-success`);
                    const value = field.value.trim();

                    let isValid = false;
                    let errorMsg = '';

                    switch (type) {
                        case 'name':
                            isValid = value.length >= 2 && /^[a-zA-Z\s\-']+$/.test(value);
                            errorMsg = 'Full name is required (minimum 2 characters, letters only)';
                            break;

                        case 'email':
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            isValid = emailRegex.test(value);
                            errorMsg = 'Please enter a valid email address';
                            break;

                        case 'address':
                            isValid = value.length >= 10;
                            errorMsg = 'Address is required (minimum 10 characters)';
                            break;

                        case 'select':
                            isValid = value !== '';
                            errorMsg = 'Please select an option';
                            break;

                        case 'date':
                            if (!value) {
                                isValid = false;
                                errorMsg = 'Date of birth is required';
                            } else {
                                const birthDate = new Date(value);
                                const today = new Date();
                                const age = today.getFullYear() - birthDate.getFullYear();
                                const monthDiff = today.getMonth() - birthDate.getMonth();
                                const dayDiff = today.getDate() - birthDate.getDate();

                                const actualAge = monthDiff < 0 || (monthDiff === 0 && dayDiff < 0) ? age - 1 : age;

                                isValid = actualAge >= 16 && actualAge <= 100;
                                errorMsg = 'You must be between 16 and 100 years old';
                            }
                            break;
                    }

                    if (isValid) {
                        field.classList.remove('field-error');
                        field.classList.add('field-valid');
                        if (errorEl) errorEl.classList.remove('show');
                        if (successEl) successEl.classList.add('show');
                        delete this.validationErrors[fieldId];
                    } else {
                        field.classList.remove('field-valid');
                        field.classList.add('field-error');
                        if (errorEl) {
                            errorEl.querySelector('span').textContent = errorMsg;
                            errorEl.classList.add('show');
                        }
                        if (successEl) successEl.classList.remove('show');
                        this.validationErrors[fieldId] = errorMsg;
                    }

                    return isValid;
                }

                clearFieldError(fieldId) {
                    const field = document.getElementById(fieldId);
                    const errorEl = document.getElementById(`${fieldId}-error`);

                    field.classList.remove('field-error');
                    if (errorEl) errorEl.classList.remove('show');
                }

                // ==================== PHONE VALIDATION ====================

                formatPhoneNumber(input) {
                    let value = input.value.replace(/\D/g, '');
                    value = value.substring(0, 11);

                    let formattedValue = '';
                    if (value.length > 0) {
                        formattedValue = value.substring(0, 4);
                        if (value.length > 4) {
                            formattedValue += ' ' + value.substring(4, 7);
                        }
                        if (value.length > 7) {
                            formattedValue += ' ' + value.substring(7, 11);
                        }
                    }

                    input.value = formattedValue;
                    this.phoneNumber = value;

                    document.getElementById('phoneError').classList.remove('show');
                    document.getElementById('phoneSuccess').classList.remove('show');
                    input.classList.remove('field-error', 'field-valid');
                }

                validatePhoneNumber(showSuccess = false) {
                    const input = document.getElementById('inputPhone');
                    const errorEl = document.getElementById('phoneError');
                    const successEl = document.getElementById('phoneSuccess');

                    // Relaxed validation: just check if it's not empty since it's required
                    if (input.value.trim().length > 0) {
                        // Keep phoneNumber updated just in case other parts of the script use it
                        this.phoneNumber = input.value.trim();
                        input.classList.remove('field-error');
                        input.classList.add('field-valid');
                        errorEl.classList.remove('show');
                        if (showSuccess) successEl.classList.add('show');
                        delete this.validationErrors['inputPhone'];
                        return true;
                    } else {
                        input.classList.remove('field-valid');
                        input.classList.add('field-error');
                        errorEl.classList.add('show');
                        successEl.classList.remove('show');
                        this.validationErrors['inputPhone'] = 'Phone number is required';
                        return false;
                    }
                }

                // ==================== STEP 1 VALIDATION ====================

                validateStep1AndProceed() {
                    this.saveProfileData();

                    if (this.isEditMode) {
                        this.saveEditChanges();
                        return;
                    }

                    const validations = [
                        this.validateField('inputName', 'name'),
                        this.validatePhoneNumber(true),
                        this.validateField('inputDOB', 'date'),
                        this.validateField('inputAddress', 'address'),
                        this.validateField('inputProgram', 'select'),
                        this.validateField('inputEmergencyName', 'name'),
                        this.validateEmergencyPhone(),
                        this.validateField('inputEmergencyRelation', 'select')
                    ];

                    // Validate gender
                    const genderSelected = document.querySelector('input[name="gender"]:checked');
                    if (!genderSelected) {
                        document.getElementById('gender-error').classList.add('show');
                        validations.push(false);
                    } else {
                        document.getElementById('gender-error').classList.remove('show');
                    }

                    const photoPreview = document.getElementById('photoPreview');
                    const photoValid = !photoPreview.classList.contains('hidden');
                    if (!photoValid) {
                        document.getElementById('photoError').classList.add('show');
                        this.validationErrors['photo'] = 'Photo is required';
                    } else {
                        document.getElementById('photoError').classList.remove('show');
                        delete this.validationErrors['photo'];
                    }

                    const allValid = validations.every(v => v === true) && photoValid;

                    if (!allValid) {
                        this.showValidationSummary('step1');
                        this.showToast('Please fix all validation errors', 'error');
                        this.announceToScreenReader('Form has errors. Please review the highlighted fields.');
                        return;
                    }

                    this.clearValidationSummary('step1');
                    this.goToStep(2);
                }

                showValidationSummary(stepId) {
                    const summary = document.getElementById(`${stepId}ValidationSummary`);
                    const list = document.getElementById(`${stepId}ErrorList`);

                    if (summary && list) {
                        list.innerHTML = Object.values(this.validationErrors)
                            .map(err => `<li>${this.sanitizeInput(err)}</li>`)
                            .join('');
                        summary.classList.add('show');
                    }
                }

                clearValidationSummary(stepId) {
                    const summary = document.getElementById(`${stepId}ValidationSummary`);
                    if (summary) summary.classList.remove('show');
                }

                validateTerms() {
                    const checkbox = document.getElementById('termsCheck');
                    const errorEl = document.getElementById('termsError');

                    if (checkbox.checked) {
                        errorEl.classList.remove('show');
                        return true;
                    } else {
                        errorEl.classList.add('show');
                        return false;
                    }
                }

                showTermsModal() {
                    alert('Terms & Conditions:\n\n1. All information provided must be accurate.\n2. Students must adhere to university policies and code of conduct.\n3. The university reserves the right to cancel enrollment for violation of terms.');
                }

                // ==================== STEP 2 VALIDATION ====================

                validateStep2AndProceed() {
                    const subjects = Array.from(this.selectedSubjects.values());
                    const credits = subjects.reduce((sum, s) => sum + s.credits, 0);

                    const warningEl = document.getElementById('creditValidationWarning');

                    if (credits < 9) {
                        warningEl.classList.add('show');
                        document.getElementById('creditErrorText').textContent =
                            `You have only ${credits} credits. Minimum 9 credits required.`;
                        this.showToast(`Need ${9 - credits} more credits minimum`, 'error');
                        this.announceToScreenReader(`Credit requirement not met. You have ${credits} credits, but need at least 9.`);
                        return;
                    }

                    if (credits > 18) {
                        warningEl.classList.add('show');
                        document.getElementById('creditErrorText').textContent =
                            `You have ${credits} credits. Maximum 18 credits allowed.`;
                        this.showToast(`Remove ${credits - 18} credits to proceed`, 'error');
                        this.announceToScreenReader(`Credit limit exceeded. You have ${credits} credits, but maximum is 18.`);
                        return;
                    }

                    warningEl.classList.remove('show');

                    // Submit to PHP Controller using fetch
                    const formData = new FormData();
                    formData.append('action', 'enroll');

                    const programVal = document.getElementById('inputProgram') ? document.getElementById('inputProgram').value : '';
                    formData.append('program', programVal);

                    // Also append personal info that might have been updated
                    formData.append('contact_number', document.getElementById('inputPhone')?.value || '');
                    formData.append('address', document.getElementById('inputAddress')?.value || '');
                    formData.append('guardian_name', document.getElementById('inputEmergencyName')?.value || '');
                    formData.append('guardian_contact', document.getElementById('inputEmergencyPhone')?.value || '');
                    formData.append('guardian_relation', document.getElementById('inputEmergencyRelation')?.value || '');
                    formData.append('guardian_address', document.getElementById('inputEmergencyAddress')?.value || '');

                    // Append the profile image if selected
                    const photoInput = document.getElementById('photoInput');
                    if (photoInput && photoInput.files[0]) {
                        formData.append('profile_image', photoInput.files[0]);
                    }

                    // Append the selected subjects
                    if (this.selectedSubjects) {
                        const subjectsData = JSON.stringify(Array.from(this.selectedSubjects.values()));
                        formData.append('subjects', subjectsData);
                    }

                    fetch('<?= BASE_URL ?>/enrollment', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            this.showToast('Enrollment submitted successfully!', 'success');
                            // Proceed to Validation (Step 3) instead of redirecting
                            this.goToStep(3);
                        })
                        .catch(error => {
                            console.error('Error submitting enrollment:', error);
                            this.showToast('Failed to submit enrollment.', 'error');
                            // Fallback to step 3 if there's an error for demo purposes
                            this.goToStep(3);
                        });
                }

                // ==================== NAVIGATION ====================

                goToStep(step) {
                    if (step < 1 || step > 4) return;

                    if (this.isEditMode && step > 2) {
                        this.showToast('Please save changes before proceeding', 'warning');
                        return;
                    }

                    if (step > this.currentStep + 1 && !this.isEditMode) {
                        this.showToast('Please complete the current step first', 'warning');
                        return;
                    }

                    document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
                    document.getElementById(`step${step}`).classList.add('active');

                    const arrows = document.querySelectorAll('.step-arrow');
                    arrows.forEach((arrow, idx) => {
                        arrow.classList.remove('active', 'completed');
                        arrow.style.pointerEvents = 'none';
                        arrow.setAttribute('aria-selected', 'false');
                        arrow.setAttribute('tabindex', '-1');
                        if (idx + 1 < step) {
                            arrow.classList.add('completed');
                            arrow.style.pointerEvents = 'auto';
                        } else if (idx + 1 === step) {
                            arrow.classList.add('active');
                            arrow.setAttribute('aria-selected', 'true');
                            arrow.setAttribute('tabindex', '0');
                        }
                    });

                    this.currentStep = step;
                    this.updateStepIndicators();

                    if (step === 2) this.updateSummaryPanel();
                    else if (step === 3) this.loadValidationData();
                    else if (step === 4) {
                        this.loadStatusData();
                        this.generateRegistrarReport();
                    }

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    this.announceToScreenReader(`Step ${step} of 4. ${this.getStepName(step)}`);
                }

                getStepName(step) {
                    const names = {
                        1: 'Personal Information',
                        2: 'Subject Selection',
                        3: 'Validation',
                        4: 'Status'
                    };
                    return names[step] || '';
                }

                nextStep() {
                    this.goToStep(this.currentStep + 1);
                }

                prevStep() {
                    this.goToStep(this.currentStep - 1);
                }

                // ==================== PHOTO UPLOAD ====================

                async handlePhotoUpload(input) {
                    if (input.files && input.files[0]) {
                        const file = input.files[0];

                        if (!file.type.startsWith('image/')) {
                            this.showToast('Please upload an image file', 'error');
                            return;
                        }

                        if (file.size > 2 * 1024 * 1024) {
                            this.showToast('File too large. Maximum 2MB allowed.', 'error');
                            return;
                        }

                        const reader = new FileReader();

                        reader.onerror = () => {
                            this.showToast('Error reading file. Please try again.', 'error');
                        };

                        reader.onload = (e) => {
                            this.uploadedPhotoData = e.target.result;

                            document.getElementById('photoPreview').src = e.target.result;
                            document.getElementById('photoPreview').classList.remove('hidden');
                            document.getElementById('photoIcon').classList.add('hidden');
                            document.getElementById('photoError').classList.remove('show');
                            delete this.validationErrors['photo'];

                            this.saveProfileData();

                            this.showToast('Photo uploaded successfully!', 'success');
                        };

                        try {
                            reader.readAsDataURL(file);
                        } catch (e) {
                            this.showToast('Error processing image. Please try a different file.', 'error');
                        }
                    }
                }

                // ==================== SUBJECT SELECTION ====================

                renderSubjectTable() {
                    const tbody = document.getElementById('subjectTableBody');
                    tbody.innerHTML = '';

                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    const pageItems = this.filteredSubjects.slice(start, end);

                    pageItems.forEach(sub => {
                        const isSelected = this.selectedSubjects.has(sub.id);
                        const row = document.createElement('tr');
                        row.className = isSelected ? 'bg-green-50' : '';
                        row.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="checkbox-custom" 
                                ${isSelected ? 'checked' : ''} 
                                onchange="app.toggleSubject('${sub.id}', this)"
                                aria-label="Select ${sub.code} - ${sub.name}">
                        </td>
                        <td>
                            <div class="font-medium text-gray-900">${sub.code} - ${sub.name}</div>
                            <div class="text-xs text-gray-500">${sub.instructor}</div>
                        </td>
                        <td class="text-gray-700 text-sm">${sub.schedule}</td>
                        <td class="text-center font-medium">${sub.credits}</td>
                    `;
                        tbody.appendChild(row);
                    });

                    const totalPages = Math.ceil(this.filteredSubjects.length / this.itemsPerPage) || 1;
                    document.getElementById('pageIndicator').textContent = `Page ${this.currentPage} of ${totalPages}`;
                }

                toggleSubject(id, checkbox) {
                    const subject = this.allSubjects.find(s => s.id === id);
                    if (checkbox.checked) {
                        this.selectedSubjects.set(id, subject);
                    } else {
                        this.selectedSubjects.delete(id);
                    }
                    this.renderSubjectTable();
                    this.updateSummaryPanel();
                    this.checkConflicts();

                    const credits = Array.from(this.selectedSubjects.values()).reduce((sum, s) => sum + s.credits, 0);
                    if (credits >= 9 && credits <= 18) {
                        document.getElementById('creditValidationWarning').classList.remove('show');
                    }

                    this.announceToScreenReader(`${subject.name} ${checkbox.checked ? 'selected' : 'deselected'}. Total credits: ${credits}`);
                }

                filterSubjects() {
                    const search = document.getElementById('searchInput').value.toLowerCase();
                    const types = Array.from(document.querySelectorAll('.filter-type:checked')).map(cb => cb.value);

                    this.filteredSubjects = this.allSubjects.filter(sub => {
                        const matchesSearch = sub.name.toLowerCase().includes(search) ||
                            sub.code.toLowerCase().includes(search);
                        const matchesType = types.includes(sub.type);
                        return matchesSearch && matchesType;
                    });

                    this.currentPage = 1;
                    this.renderSubjectTable();
                }

                changePage(dir) {
                    const totalPages = Math.ceil(this.filteredSubjects.length / this.itemsPerPage) || 1;
                    const newPage = this.currentPage + dir;
                    if (newPage >= 1 && newPage <= totalPages) {
                        this.currentPage = newPage;
                        this.renderSubjectTable();
                    }
                }

                updateSummaryPanel() {
                    document.getElementById('summaryName').textContent =
                        document.getElementById('inputName').value || 'Guest';


                    const subjects = Array.from(this.selectedSubjects.values());
                    const totalCredits = subjects.reduce((sum, s) => sum + s.credits, 0);

                    const creditDisplay = document.getElementById('creditDisplay');
                    const totalCreditsEl = document.getElementById('totalCredits');
                    const creditBar = document.getElementById('creditBar');
                    const statusMsg = document.getElementById('creditStatusMsg');
                    const statusDetail = document.getElementById('creditStatusDetail');

                    creditDisplay.textContent = `${totalCredits} / 18`;
                    totalCreditsEl.textContent = totalCredits;

                    creditBar.parentElement.setAttribute('aria-valuenow', totalCredits);

                    const pct = Math.min((totalCredits / 18) * 100, 100);
                    creditBar.style.width = `${pct}%`;

                    if (totalCredits < 9) {
                        creditDisplay.className = 'credit-warning';
                        totalCreditsEl.className = 'font-bold text-xl text-red-600';
                        creditBar.className = 'bg-red-600 h-3 rounded-full transition-all duration-500';
                        statusMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>Need ${9 - totalCredits} more credits minimum`;
                        statusMsg.className = 'text-xs mt-1 text-red-600 font-medium';
                        statusDetail.innerHTML = `<i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>Need ${9 - totalCredits} more credits minimum`;
                        statusDetail.className = 'text-xs mt-2 font-medium text-red-600';
                    } else if (totalCredits > 18) {
                        creditDisplay.className = 'credit-warning';
                        totalCreditsEl.className = 'font-bold text-xl text-red-600';
                        creditBar.className = 'bg-red-600 h-3 rounded-full transition-all duration-500';
                        statusMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>Remove ${totalCredits - 18} credits (max 18)`;
                        statusMsg.className = 'text-xs mt-1 text-red-600 font-medium';
                        statusDetail.innerHTML = `<i class="fa-solid fa-circle-exclamation mr-1" aria-hidden="true"></i>Remove ${totalCredits - 18} credits`;
                        statusDetail.className = 'text-xs mt-2 font-medium text-red-600';
                    } else {
                        creditDisplay.className = 'credit-success';
                        totalCreditsEl.className = 'font-bold text-xl text-green-600';
                        creditBar.className = 'bg-green-600 h-3 rounded-full transition-all duration-500';
                        statusMsg.innerHTML = `<i class="fa-solid fa-check-circle mr-1" aria-hidden="true"></i>Credit requirement met`;
                        statusMsg.className = 'text-xs mt-1 text-green-600 font-medium';
                        statusDetail.innerHTML = `<i class="fa-solid fa-check-circle mr-1" aria-hidden="true"></i>Valid credit load`;
                        statusDetail.className = 'text-xs mt-2 font-medium text-green-600';
                    }

                    document.getElementById('selectedCount').textContent = subjects.length;

                    const list = document.getElementById('selectedSubjectsList');
                    if (subjects.length === 0) {
                        list.innerHTML = '<li class="text-gray-400 italic text-xs">No subjects selected</li>';
                    } else {
                        list.innerHTML = subjects.map(s => `
                        <li class="flex justify-between text-sm">
                            <span>${s.code}</span>
                            <span class="text-gray-500">${s.credits}cr</span>
                        </li>
                    `).join('');
                    }
                }

                checkConflicts() {
                    const count = this.selectedSubjects.size;
                    const warning = document.getElementById('conflictWarning');
                    if (count > 5) {
                        document.getElementById('conflictText').textContent =
                            "Heavy course load detected. Ensure you can manage the schedule.";
                        warning.classList.add('show');
                    } else {
                        warning.classList.remove('show');
                    }
                }

                clearSelection() {
                    this.selectedSubjects.clear();
                    this.renderSubjectTable();
                    this.updateSummaryPanel();
                    document.getElementById('creditValidationWarning').classList.remove('show');
                    this.showToast('All selections cleared', 'info');
                }

                // ==================== STEP 3: VALIDATION ====================

                loadValidationData() {
                    const valPhotoPreview = document.getElementById('valPhotoPreview');
                    const valPhotoIcon = document.getElementById('valPhotoIcon');

                    const currentPhoto = document.getElementById('photoPreview');

                    if (this.uploadedPhotoData) {
                        valPhotoPreview.src = this.uploadedPhotoData;
                        valPhotoPreview.classList.remove('hidden');
                        valPhotoIcon.classList.add('hidden');
                    } else if (currentPhoto && !currentPhoto.classList.contains('hidden')) {
                        valPhotoPreview.src = currentPhoto.src;
                        valPhotoPreview.classList.remove('hidden');
                        valPhotoIcon.classList.add('hidden');
                    } else {
                        valPhotoPreview.classList.add('hidden');
                        valPhotoIcon.classList.remove('hidden');
                    }

                    document.getElementById('valName').textContent =
                        document.getElementById('inputName').value;

                    document.getElementById('valProgram').textContent =
                        document.getElementById('inputProgram').value;
                    document.getElementById('valPhone').textContent = this.getFullPhoneNumber() || '-';

                    const subjects = Array.from(this.selectedSubjects.values());
                    const credits = subjects.reduce((sum, s) => sum + s.credits, 0);

                    document.getElementById('loadCredits').textContent = credits;

                    this.updateComplianceItem('Load', true, `${credits} credits`);
                    this.updateComplianceItem('Min', credits >= 9, 'Minimum 9 credits');
                    this.updateComplianceItem('Max', credits <= 18, 'Maximum 18 credits');
                    this.updateComplianceItem('Program', document.getElementById('inputProgram').value !== '', 'Program selected');

                    const tbody = document.getElementById('valTableBody');
                    tbody.innerHTML = subjects.map(s => `
                    <tr>
                        <td class="text-center"><i class="fa-solid fa-check text-green-600" aria-hidden="true"></i></td>
                        <td class="font-medium">${s.code} - ${s.name}</td>
                        <td class="text-sm text-gray-600">${s.schedule}</td>
                        <td class="text-center">${s.credits}</td>
                        <td><span class="text-green-600 font-medium text-sm"><i class="fa-solid fa-check" aria-hidden="true"></i> Valid</span></td>
                    </tr>
                `).join('');
                }

                updateComplianceItem(type, isValid, text) {
                    const item = document.getElementById(`item${type}`);
                    const icon = document.getElementById(`icon${type}`);

                    if (isValid) {
                        item.classList.add('verified');
                        item.classList.remove('error');
                        icon.className = 'fa-solid fa-check-circle check-icon';
                    } else {
                        item.classList.remove('verified');
                        item.classList.add('error');
                        icon.className = 'fa-solid fa-circle-xmark error-icon';
                    }
                }

                setRegistrarStatus(status) {
                    const statusEl = document.getElementById('programComplianceStatus');
                    const item = document.getElementById('itemProgram');
                    const icon = document.getElementById('iconProgram');

                    if (status === 'approved') {
                        statusEl.textContent = 'Approved';
                        statusEl.className = 'ml-auto text-sm font-medium text-green-600';
                        item.classList.add('verified');
                        icon.className = 'fa-solid fa-check-circle check-icon';
                        this.enrollmentStatus = 'validated';
                    } else if (status === 'rejected') {
                        statusEl.textContent = 'Rejected';
                        statusEl.className = 'ml-auto text-sm font-medium text-red-600';
                        item.classList.remove('verified');
                        icon.className = 'fa-solid fa-circle-xmark error-icon';
                    } else {
                        statusEl.textContent = 'Pending';
                        statusEl.className = 'ml-auto text-sm font-medium text-orange-600';
                        item.classList.remove('verified');
                        icon.className = 'fa-regular fa-circle text-gray-400';
                    }
                }

                approveEnrollment() {
                    const notes = document.getElementById('validationNotes').value;
                    this.enrollmentStatus = 'validated';
                    this.showToast('Enrollment approved! Proceed to status.', 'success');

                    document.getElementById('boxValidated').classList.remove('future');
                    document.getElementById('boxValidated').classList.add('active');
                    document.getElementById('validatedDate').textContent = new Date().toLocaleDateString();

                    setTimeout(() => this.goToStep(4), 500);
                }

                rejectEnrollment() {
                    const notes = document.getElementById('validationNotes').value;
                    if (!notes.trim()) {
                        this.showToast('Please provide rejection notes', 'error');
                        document.getElementById('validationNotes').focus();
                        return;
                    }
                    this.showToast('Enrollment rejected', 'error');
                }

                // ==================== STEP 4: STATUS ====================

                loadStatusData() {
                    const statusPhotoPreview = document.getElementById('statusPhotoPreview');
                    const statusPhotoIcon = document.getElementById('statusPhotoIcon');

                    if (this.uploadedPhotoData) {
                        statusPhotoPreview.src = this.uploadedPhotoData;
                        statusPhotoPreview.classList.remove('hidden');
                        statusPhotoIcon.classList.add('hidden');
                    } else {
                        statusPhotoPreview.classList.add('hidden');
                        statusPhotoIcon.classList.remove('hidden');
                    }

                    document.getElementById('statusName').textContent =
                        document.getElementById('inputName').value;

                    document.getElementById('statusProgram').textContent =
                        document.getElementById('inputProgram').value;
                    document.getElementById('statusPhone').textContent = this.getFullPhoneNumber() || '-';

                    const gender = document.querySelector('input[name="gender"]:checked')?.value || '-';
                    document.getElementById('statusGender').textContent = gender;

                    document.getElementById('statusStudentType').textContent =
                        this.studentType === 'new' ? 'New Student' : 'Old Student';

                    const subjects = Array.from(this.selectedSubjects.values());
                    const credits = subjects.reduce((sum, s) => sum + s.credits, 0);
                    document.getElementById('statusCredits').textContent = credits;

                    const badge = document.getElementById('currentStatusBadge');
                    badge.className = 'status-badge validated ml-2';
                    badge.innerHTML = '<i class="fa-solid fa-check-circle" aria-hidden="true"></i> Validated';

                    document.getElementById('boxPending').classList.add('pending');
                    document.getElementById('pendingDate').textContent = new Date().toLocaleDateString();

                    document.getElementById('boxValidated').classList.remove('future');
                    document.getElementById('boxValidated').classList.add('active');
                    document.getElementById('validatedDate').textContent = new Date().toLocaleDateString();

                    document.getElementById('boxApproved').classList.remove('future');
                    document.getElementById('boxApproved').classList.add('active');
                    document.querySelector('#boxApproved .text-sm').innerHTML = '<p class="font-medium mb-1">Status:</p><p class="font-bold">Approved</p>';

                    document.getElementById('boxEnrolled').classList.remove('future');
                    document.getElementById('boxEnrolled').classList.add('active');
                    document.querySelector('#boxEnrolled .font-bold').textContent = 'Enrolled';
                    document.querySelector('#boxEnrolled .text-sm').innerHTML = '<p class="font-medium mb-1">Status:</p><p class="font-bold text-green-100">Officially Enrolled</p>';

                    const tbody = document.getElementById('statusTableBody');
                    tbody.innerHTML = subjects.map(s => `
                    <tr>
                        <td class="font-medium text-gray-900">${s.code} - ${s.name}</td>
                        <td class="text-gray-700">${s.schedule}</td>
                        <td class="text-center font-medium">${s.credits}</td>
                        <td><span class="text-green-600 font-medium text-sm"><i class="fa-solid fa-check" aria-hidden="true"></i> Validated</span></td>
                    </tr>
                `).join('');
                }

                // ==================== UTILITIES ====================

                getFullPhoneNumber() {
                    const input = document.getElementById('inputPhone');
                    return input ? input.value : (this.phoneNumber || '');
                }

                saveDraft() {
                    this.saveProfileData();

                    const data = {
                        name: document.getElementById('inputName').value,
                        step: this.currentStep,
                        timestamp: new Date().toISOString()
                    };
                    localStorage.setItem('enrollmentDraft', JSON.stringify(data));
                    this.showToast('Draft saved!', 'success');
                    this.announceToScreenReader('Draft saved successfully');
                }

                loadDraft() {
                    const draft = localStorage.getItem('enrollmentDraft');
                    if (draft) {
                        try {
                            const data = JSON.parse(draft);
                            if (data.name) document.getElementById('inputName').value = data.name;
                            this.showToast('Draft loaded!', 'success');
                        } catch (e) {
                            console.error('Error loading draft:', e);
                        }
                    }
                }

                triggerConfetti() {
                    const colors = ['#16a34a', '#22c55e', '#4ade80', '#86efac', '#15803d'];
                    for (let i = 0; i < 50; i++) {
                        setTimeout(() => {
                            const confetti = document.createElement('div');
                            confetti.className = 'confetti';
                            confetti.style.left = Math.random() * 100 + 'vw';
                            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                            confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                            confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                            document.body.appendChild(confetti);
                            setTimeout(() => confetti.remove(), 4000);
                        }, i * 30);
                    }
                }
            }

            // Initialize App
            const app = new EnrollmentSystem();

            window.addEventListener('load', () => {
                app.loadDraft();
            });

            window.addEventListener('beforeunload', (e) => {
                if (app.isLoggedIn && document.getElementById('inputName').value) {
                    app.saveDraft();
                }
            });
            <?php endif; ?>
        </script>
</body>

</html>