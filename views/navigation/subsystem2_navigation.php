<?php
/**
 * Subsystem 2 Navigation
 * Handles Student view
 */

// Subsystem 2 for Student
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'): ?>
    <div class="nav-group">
        <button class="nav-group-toggle" data-group="subsystem2">
            <span class="nav-group-icon">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </span>
            <span class="nav-group-text">Subsystem 2</span>
            <span class="nav-group-arrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="nav-group-items" id="navGroup-subsystem2">
            <a href="<?= BASE_URL ?>/enrollment" class="nav-link sub-link <?= isActive('enrollment', $currentRoute) ?>">
                <span class="nav-icon">
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </span>
                <span class="nav-text">Enrollment</span>
            </a>
        </div>
    </div>

<?php 
// Subsystem 2 for Registrar
elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'registrar'): ?>
    <!-- Navigation group for Subsystem 2 - Registrar Access -->
    <div class="nav-group">
        <button class="nav-group-toggle" data-group="subsystem2">
            <span class="nav-group-icon">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </span>
            <span class="nav-group-text">Subsystem 2</span>
            <span class="nav-group-arrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </button>
        <div class="nav-group-items" id="navGroup-subsystem2">
            <a href="<?= BASE_URL ?>/module1_2" class="nav-link sub-link <?= isActive('module1_2', $currentRoute) ?>">
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
