<?php
/**
 * Subsystem 10 Navigation
 */

// Subsystem 10 for Student
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'): ?>
    <!-- Navigation group for Subsystem 10 - Student Access -->
    <div class="nav-group">
        <button class="nav-group-toggle" data-group="subsystem10">
            <span class="nav-group-icon">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
            </span>
            <span class="nav-group-text">Subsystem 10</span>
            <span class="nav-group-arrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="nav-group-items" id="navGroup-subsystem10">
            <a href="<?= BASE_URL ?>/module1_10" class="nav-link sub-link <?= isActive('module1_10', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </span>
                <span class="nav-text">Module #1</span>
            </a>
        </div>
    </div>

<?php 
// Subsystem 10 for Registrar
elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'registrar'): ?>
    <!-- Navigation group for Subsystem 10 - Registrar Access -->
    <div class="nav-group">
        <button class="nav-group-toggle" data-group="subsystem10">
            <span class="nav-group-icon">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
            </span>
            <span class="nav-group-text">Subsystem 10</span>
            <span class="nav-group-arrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="nav-group-items" id="navGroup-subsystem10">
            <a href="<?= BASE_URL ?>/module1_10_reg" class="nav-link sub-link <?= isActive('module1_10_reg', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </span>
                <span class="nav-text">Module #1</span>
            </a>
        </div>
    </div>
<?php endif; ?>