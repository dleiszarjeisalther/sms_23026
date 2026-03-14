<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Ensure only registrars can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Management - SIMS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <style>
        .security-container {
            padding: 2rem;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-locked {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-active {
            background-color: #dcfce7;
            color: #15803d;
        }
        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .btn-unlock {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-lock {
            background-color: #ef4444;
            color: white;
        }
        .btn-unlock:hover { opacity: 0.9; }
        .btn-lock:hover { opacity: 0.9; }
    </style>
</head>

<body>
    <div class="app-container">
        <?php require __DIR__ . '/../navigation_bar.php'; ?>

        <main class="main-content">
            <div class="security-container">
                <div class="premium-header">
                    <h2>Security Management</h2>
                    <p>Manage student account locks and security status</p>
                </div>

                <div class="card" style="margin-top: 2rem;">
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; align-items: center;">
                            <h3>Student Security Status</h3>
                            <input type="text" id="studentSearch" placeholder="Search by ID or Name..." class="filter-input-sm" style="max-width: 300px;">
                        </div>

                        <div class="status-table-container">
                            <table class="status-table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Full Name</th>
                                        <th>Login Attempts</th>
                                        <th>Status</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="securityTableBody">
                                    <!-- Populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= BASE_URL ?>/resources/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('securityTableBody');
            const searchInput = document.getElementById('studentSearch');

            function loadSecurityData() {
                fetch('<?= BASE_URL ?>/controllers/subsystem1/security.controller.php?action=list')
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            renderTable(res.data);
                        } else {
                            showToast(res.message, 'error');
                        }
                    })
                    .catch(err => showToast('Error loading security data.', 'error'));
            }

            function renderTable(data) {
                tableBody.innerHTML = '';
                data.forEach(student => {
                    const tr = document.createElement('tr');
                    const isLocked = student.is_locked == 1;
                    
                    tr.innerHTML = `
                        <td>${student.student_id}</td>
                        <td>${student.first_name} ${student.last_name}</td>
                        <td style="text-align: center;">${student.login_attempts}</td>
                        <td>
                            <span class="status-badge ${isLocked ? 'status-locked' : 'status-active'}">
                                ${isLocked ? 'Locked' : 'Active'}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <button class="action-btn ${isLocked ? 'btn-unlock' : 'btn-lock'}" 
                                    onclick="toggleLock('${student.student_id}', ${isLocked ? 0 : 1})">
                                ${isLocked ? 'Unlock Account' : 'Lock Account'}
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            }

            window.toggleLock = function(studentId, lockStatus) {
                const actionText = lockStatus ? 'lock' : 'unlock';
                if (!confirm(`Are you sure you want to ${actionText} this student's account?`)) return;

                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('is_locked', lockStatus);
                formData.append('action', 'toggle_lock');

                fetch('<?= BASE_URL ?>/controllers/subsystem1/security.controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        showToast(`Account successfully ${lockStatus ? 'locked' : 'unlocked'}.`, 'success');
                        loadSecurityData();
                    } else {
                        showToast(res.message, 'error');
                    }
                })
                .catch(err => showToast('Error performing security action.', 'error'));
            };

            // Simple search filter
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });

            loadSecurityData();
        });
    </script>
</body>

</html>
