<?php
// Ensure session is started to access user_type
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navProfileImage = null;
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
    require_once __DIR__ . '/../models/config.php';
    require_once __DIR__ . '/../models/subsystem1/profile.model.php';
    $navProfileModel = new ProfileModel($pdo);
    $navDetails = $navProfileModel->getStudentDetails($_SESSION['user_id']);
    if ($navDetails && !empty($navDetails['profile_image'])) {
        $navProfileImage = $navDetails['profile_image'];
    }
}

// Detect current page for active highlight
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentRoute = trim(str_replace(BASE_URL, '', $currentPath), '/');
if ($currentRoute === '' || $currentRoute === 'index.php') {
    $currentRoute = 'home';
}

function isActive($route, $currentRoute)
{
    return ($route === $currentRoute) ? 'active' : '';
}
?>
<!-- Mobile Top Header (Visible only on mobile) -->
<div class="mobile-header-toggle">
    <button id="mobileSidebarToggle" class="sidebar-toggle-btn" aria-label="Open Sidebar" style="min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1.5em" width="1.5em" xmlns="http://www.w3.org/2000/svg">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>
    <h1>SIMS</h1>
</div>

<aside class="sidebar-nav" id="sidebarNav">
    <div class="sidebar-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="sidebar-title" style="font-size: 1.1rem; line-height: 1.2; word-wrap: break-word; overflow-wrap: break-word; max-width: 150px;">SIMS</h1>
        <button id="sidebarToggle" class="sidebar-toggle-btn" aria-label="Toggle Sidebar">
            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1.2em" width="1.2em" xmlns="http://www.w3.org/2000/svg">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>
    <nav class="sidebar-links">
        <a href="<?= BASE_URL ?>/home" class="nav-link <?= isActive('home', $currentRoute) ?>">
            <span class="nav-icon">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </span>
            <span class="nav-text">Home</span>
        </a>

        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'): ?>
            <?php
            // Enrollment check for sidebar
            $isEnrolled = false;
            if (isset($_SESSION['user_id'])) {
                $enrStmt = $pdo->prepare("SELECT enrollment_status FROM enrollments WHERE student_id = ? ORDER BY id DESC LIMIT 1");
                $enrStmt->execute([$_SESSION['user_id']]);
                $enrRow = $enrStmt->fetch();
                $isEnrolled = ($enrRow && $enrRow['enrollment_status'] !== 'Rejected');
            }
            ?>
            <!-- Subsystems Section -->
            <?php 
                include __DIR__ . '/navigation/subsystem1_navigation.php';
                include __DIR__ . '/navigation/subsystem2_navigation.php';
                include __DIR__ . '/navigation/subsystem3_navigation.php';
                include __DIR__ . '/navigation/subsystem4_navigation.php';
                include __DIR__ . '/navigation/subsystem5_navigation.php';
                include __DIR__ . '/navigation/subsystem6_navigation.php';
                include __DIR__ . '/navigation/subsystem7_navigation.php';
                include __DIR__ . '/navigation/subsystem8_navigation.php';
                include __DIR__ . '/navigation/subsystem9_navigation.php';
                include __DIR__ . '/navigation/subsystem10_navigation.php';
            ?>

            <a href="<?= BASE_URL ?>/logout" class="nav-link <?= isActive('logout', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'registrar'): ?>
            <!-- Registrar Navigation Section includes Subsystems 1-10 -->
            <?php 
                include __DIR__ . '/navigation/subsystem1_navigation.php'; 
                include __DIR__ . '/navigation/subsystem2_navigation.php';
                include __DIR__ . '/navigation/subsystem3_navigation.php';
                include __DIR__ . '/navigation/subsystem4_navigation.php';
                include __DIR__ . '/navigation/subsystem5_navigation.php';
                include __DIR__ . '/navigation/subsystem6_navigation.php';
                include __DIR__ . '/navigation/subsystem7_navigation.php';
                include __DIR__ . '/navigation/subsystem8_navigation.php';
                include __DIR__ . '/navigation/subsystem9_navigation.php';
                include __DIR__ . '/navigation/subsystem10_navigation.php';
            ?>

            <a href="<?= BASE_URL ?>/security_management" class="nav-link <?= isActive('security_management', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </span>
                <span class="nav-text">Security</span>
            </a>

            <a href="<?= BASE_URL ?>/logout" class="nav-link <?= isActive('logout', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        <?php else: ?>
            <!-- Not logged in -->
            <a href="<?= BASE_URL ?>/login" class="nav-link <?= isActive('login', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                </span>
                <span class="nav-text">Login</span>
            </a>
            <a href="<?= BASE_URL ?>/admission" class="nav-link <?= isActive('admission', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </span>
                <span class="nav-text">Register</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
        const sidebar = document.getElementById('sidebarNav');

        // Check local storage for initial state
        const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (isCollapsed && window.innerWidth > 768) {
            sidebar.classList.add('collapsed');
        }

        // Desktop Toggle inside sidebar
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                const isMobile = window.innerWidth <= 768;

                if (isMobile) {
                    sidebar.classList.remove('mobile-expanded'); // Close from inside
                } else {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
                }
            });
        }

        // Mobile Top Header Toggle
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', () => {
                sidebar.classList.add('mobile-expanded'); // Open from top header
            });
        }

        // Auto-close mobile sidebar when linking elsewhere
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-expanded');
                }
            });
        });

        // === Nav Group Expand/Collapse ===
        const groupToggles = document.querySelectorAll('.nav-group-toggle');
        groupToggles.forEach(toggle => {
            const groupId = toggle.dataset.group;
            const groupItems = document.getElementById('navGroup-' + groupId);

            // Auto-expand if any child is active
            const hasActiveChild = groupItems && groupItems.querySelector('.nav-link.active');
            const savedState = localStorage.getItem('navGroup_' + groupId);

            // Default: expanded if has active child or saved as expanded
            if (hasActiveChild || savedState === 'expanded' || savedState === null) {
                toggle.classList.add('expanded');
                if (groupItems) groupItems.style.maxHeight = groupItems.scrollHeight + 'px';
            } else {
                if (groupItems) groupItems.style.maxHeight = '0';
            }

            toggle.addEventListener('click', () => {
                const isExpanded = toggle.classList.toggle('expanded');
                if (groupItems) {
                    if (isExpanded) {
                        groupItems.style.maxHeight = groupItems.scrollHeight + 'px';
                    } else {
                        groupItems.style.maxHeight = '0';
                    }
                }
                localStorage.setItem('navGroup_' + groupId, isExpanded ? 'expanded' : 'collapsed');
            });
        });
    });
</script>