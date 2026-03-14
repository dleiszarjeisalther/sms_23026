<?php
/**
 * Subsystem 1 Navigation
 * Handles both Student and Registrar views
 */

// Subsystem 1 for Student
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'): 
    // Enrollment check (logic from original navigation_bar.php)
    $isEnrolled = false;
    if (isset($_SESSION['user_id'])) {
        $enrStmt = $pdo->prepare("SELECT enrollment_status FROM enrollments WHERE student_id = ? ORDER BY id DESC LIMIT 1");
        $enrStmt->execute([$_SESSION['user_id']]);
        $enrRow = $enrStmt->fetch();
        $isEnrolled = ($enrRow && $enrRow['enrollment_status'] !== 'Rejected');
    }
?>
    <div class="nav-group">
        <button class="nav-group-toggle" data-group="subsystem1">
            <span class="nav-group-icon">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
            </span>
            <span class="nav-group-text">Subsystem 1</span>
            <span class="nav-group-arrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="nav-group-items" id="navGroup-subsystem1">
            <a href="<?= BASE_URL ?>/profile_update" class="nav-link sub-link <?= isActive('profile_update', $currentRoute) ?> <?= !$isEnrolled ? 'restricted-link' : '' ?>" style="text-decoration: none;" <?= !$isEnrolled ? 'title="Enrolled status required"' : '' ?>>
                <?php if ($navProfileImage): ?>
                    <img src="<?= htmlspecialchars($navProfileImage) ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; <?= !$isEnrolled ? 'filter: grayscale(1); opacity: 0.6;' : '' ?>">
                <?php else: ?>
                    <span class="nav-icon">
                        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                <?php endif; ?>
                <span class="nav-text">Profile Update</span>
                <?php if (!$isEnrolled): ?>
                    <span style="margin-left: auto; font-size: 0.7rem; color: #ef4444; background: #fee2e2; padding: 2px 4px; border-radius: 4px;">Locked</span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/academic_records" class="nav-link sub-link <?= isActive('academic_records', $currentRoute) ?> <?= !$isEnrolled ? 'restricted-link' : '' ?>" <?= !$isEnrolled ? 'title="Enrolled status required"' : '' ?>>
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </span>
                <span class="nav-text">Academic Records</span>
                <?php if (!$isEnrolled): ?>
                    <span style="margin-left: auto; font-size: 0.7rem; color: #ef4444; background: #fee2e2; padding: 2px 4px; border-radius: 4px;">Locked</span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/my_id" class="nav-link sub-link <?= isActive('my_id', $currentRoute) ?> <?= !$isEnrolled ? 'restricted-link' : '' ?>" <?= !$isEnrolled ? 'title="Enrolled status required"' : '' ?>>
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <circle cx="8" cy="12" r="2"></circle>
                        <path d="M14 10h4"></path>
                        <path d="M14 14h4"></path>
                    </svg>
                </span>
                <span class="nav-text">My ID</span>
                <?php if (!$isEnrolled): ?>
                    <span style="margin-left: auto; font-size: 0.7rem; color: #ef4444; background: #fee2e2; padding: 2px 4px; border-radius: 4px;">Locked</span>
                <?php endif; ?>
            </a>
        </div>
    </div>

<?php 
// Subsystem 1 for Registrar
elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'registrar'): ?>
    <!-- Navigation group for Subsystem 1 - Registrar Access -->
    <div class="nav-group">
        <button class="nav-group-toggle" data-group="subsystem1">
            <span class="nav-group-icon">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
            </span>
            <span class="nav-group-text">Subsystem 1</span>
            <span class="nav-group-arrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="nav-group-items" id="navGroup-subsystem1">
            <a href="<?= BASE_URL ?>/student_status" class="nav-link sub-link <?= isActive('student_status', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </span>
                <span class="nav-text">Student Status</span>
            </a>
            <a href="<?= BASE_URL ?>/id_generator" class="nav-link sub-link <?= isActive('id_generator', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                        <line x1="7" y1="8" x2="17" y2="8"></line>
                        <line x1="7" y1="12" x2="17" y2="12"></line>
                        <line x1="7" y1="16" x2="12" y2="16"></line>
                    </svg>
                </span>
                <span class="nav-text">ID Generator</span>
            </a>
        </div>
    </div>
<?php endif; ?>
