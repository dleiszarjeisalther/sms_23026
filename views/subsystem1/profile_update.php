<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: <?= BASE_URL ?>/login");
    exit();
}

$message = $message ?? '';
$error = $error ?? '';
$studentDetails = $studentDetails ?? [];
// Filter out "N/A" values from existing student records
$studentDetails = array_map(function($val) {
    return (is_string($val) && strtoupper(trim($val)) === 'N/A') ? '' : $val;
}, $studentDetails);
$updateHistory = $updateHistory ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information Update</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <!-- Styles moved to <?= BASE_URL ?>/resources/css/index.css -->
</head>

<body>

    <div class="app-container">
        <?php require __DIR__ . '/../navigation_bar.php'; ?>

        <main class="main-content">
            <div class="container" style="margin-top: 2rem;">
                <form action="<?= BASE_URL ?>/profile_update" method="POST" enctype="multipart/form-data">
                <div class="profile-container">
                    <!-- Sidebar / Profile Summary -->
                    <div class="profile-sidebar">
                        <div class="col-span-4 flex flex-col items-center">
                            <div class="photo-placeholder w-40 h-48 rounded-xl mb-3 bg-gray-50 overflow-hidden" id="photoContainer" style="position: relative; display: flex; align-items: center; justify-content: center; border: 2px dashed var(--border-color);">
                                <i class="fa-solid fa-user text-6xl text-gray-300 <?= !empty($studentDetails['profile_image']) ? 'hidden' : '' ?>" id="photoIcon" aria-hidden="true"></i>
                                <img id="photoPreview" class="w-full h-full object-cover <?= empty($studentDetails['profile_image']) ? 'hidden' : '' ?>" src="<?= !empty($studentDetails['profile_image']) ? htmlspecialchars($studentDetails['profile_image']) : '' ?>" alt="Uploaded student photo">
                            </div>
                            <input type="file" id="photoInput" name="profile_image" class="hidden" accept="image/*" onchange="handlePhotoUpload(this)" aria-label="Upload student photo">
                            <button type="button" class="btn btn-primary text-sm ripple" style="width: auto; padding: 0.5rem 1rem;" onclick="document.getElementById('photoInput').click()">
                                <i class="fa-solid fa-upload" aria-hidden="true"></i> Upload Photo
                            </button>
                            <p class="text-xs text-gray-500 mt-2">Max 2MB (JPG, PNG)</p>
                            <div class="error-message mt-2" id="photoError" style="display: none;">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>Photo is required</span>
                            </div>

                            <!-- Photo Requirements -->
                            <div class="photo-requirements w-full mt-4" style="text-align: left; font-size: 0.85rem;">
                                <h4 style="margin-bottom: 0.5rem; color: var(--text-main); font-weight: 600;"><i class="fa-solid fa-camera"></i> Photo Requirements</h4>
                                <ul style="list-style: none; padding: 0; color: var(--text-muted);">
                                    <li><i class="fa-solid fa-check text-green-500 mr-1"></i> Recent 2x2 ID picture</li>
                                    <li><i class="fa-solid fa-check text-green-500 mr-1"></i> White/plain background</li>
                                    <li><i class="fa-solid fa-check text-green-500 mr-1"></i> No eyeglasses/sunglasses</li>
                                    <li><i class="fa-solid fa-check text-green-500 mr-1"></i> Neutral expression</li>
                                    <li><i class="fa-solid fa-check text-green-500 mr-1"></i> Face clearly visible</li>
                                </ul>
                            </div>
                        </div>

                        <h2 style="color: var(--text-main); margin-top: 1.5rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($studentDetails['first_name'] . ' ' . $studentDetails['last_name']) ?></h2>
                        <p style="color: var(--text-muted); font-weight: 500; font-size: 1.1rem; margin-bottom: 1.5rem;">ID: <?= htmlspecialchars($studentDetails['student_id']) ?></p>

                        <div style="text-align: left; background: var(--bg-lighter); padding: 1rem; border-radius: 4px;">
                            <p style="margin-bottom: 0.5rem;"><strong>Program:</strong> <?= htmlspecialchars($studentDetails['program']) ?></p>
                            <p style="margin-bottom: 0.5rem;"><strong>Gender:</strong> <?= ucfirst(htmlspecialchars($studentDetails['gender'])) ?></p>
                            <p><strong>DOB:</strong> <?= htmlspecialchars($studentDetails['date_of_birth']) ?></p>
                        </div>
                    </div>

                    <!-- Update Form -->
                    <div class="profile-form-container">
                        <div class="card shadow-premium border-0 overflow-hidden">
                            <div class="premium-header">
                                <h2>Personal Information Update</h2>
                            </div>
                            <div class="card-body">

                            <!-- Image upload moved to sidebar for better UX -->

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact_number">Contact Number</label>
                                    <input type="tel" id="contact_number" name="contact_number" value="<?= htmlspecialchars($studentDetails['contact_number']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email_address">Email Address</label>
                                    <input type="email" id="email_address" name="email_address" value="<?= htmlspecialchars($studentDetails['email_address']) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Current Address</label>
                                <textarea id="address" name="address" required><?= htmlspecialchars($studentDetails['address']) ?></textarea>
                            </div>

                            <h4 style="margin: 1.5rem 0 1rem; color: var(--text-main);">Guardian Details</h4>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="guardian_name">Guardian Name</label>
                                    <input type="text" id="guardian_name" name="guardian_name" value="<?= htmlspecialchars($studentDetails['guardian_name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="guardian_relation">Relationship to Guardian</label>
                                    <select id="guardian_relation" name="guardian_relation" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; background-color: var(--bg-lighter);" required>
                                        <?php $relation = $studentDetails['guardian_relation'] ?? ''; ?>
                                        <option value="" <?= $relation === '' ? 'selected' : '' ?>>Select Relationship</option>
                                        <option value="Parent" <?= $relation === 'Parent' ? 'selected' : '' ?>>Parent</option>
                                        <option value="Guardian" <?= $relation === 'Guardian' ? 'selected' : '' ?>>Guardian</option>
                                    </select>
                                </div>
                            </div>

                                <div class="form-group">
                                    <label for="guardian_contact">Guardian Contact Number</label>
                                    <input type="tel" id="guardian_contact" name="guardian_contact" value="<?= htmlspecialchars($studentDetails['guardian_contact'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="guardian_address">Guardian Address</label>
                                <textarea id="guardian_address" name="guardian_address" required><?= htmlspecialchars($studentDetails['guardian_address'] ?? '') ?></textarea>
                            </div>

                            <h4 style="margin: 1.5rem 0 1rem; color: var(--text-main); border-bottom: 1px solid var(--bg-color); padding-bottom: 0.5rem;">Change Password</h4>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Leave blank if you don't want to change your password.</p>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <div class="password-input-group">
                                        <input type="password" id="new_password" name="new_password" placeholder="Min 8 characters" oninput="checkRegPasswordStrength(this.value)">
                                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new_password', this)" aria-label="Toggle password visibility">
                                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg class="eye-closed" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div id="password-strength" class="password-strength-meter" style="margin-top: 5px; height: 4px; background: #eee; border-radius: 2px; overflow: hidden; display: none;">
                                        <div id="strength-bar" style="height: 100%; width: 0; transition: width 0.3s, background-color 0.3s;"></div>
                                    </div>
                                    <p id="password-hint" style="font-size: 0.75rem; margin-top: 5px; color: var(--text-muted); display: none;"></p>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <div class="password-input-group">
                                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password">
                                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="Toggle password visibility">
                                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg class="eye-closed" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem;">Save Changes</button>
                        </div>
                    </div>
                </form>

                <!-- History Tracking -->
                <div class="history-container">
                    <h3 style="color: var(--primary-color); border-bottom: 2px solid var(--bg-color); padding-bottom: 0.5rem;">Update History</h3>

                    <?php if (empty($updateHistory)): ?>
                        <p style="margin-top: 1rem; color: var(--text-muted); font-style: italic;">No updates have been made yet.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($updateHistory as $record): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date"><?= date('F j, Y, g:i a', strtotime($record['updated_at'])) ?> by <?= htmlspecialchars($record['updated_by']) ?></div>
                                    <div class="timeline-content">
                                        <strong>Changed: </strong> <span style="font-family: monospace; color: var(--primary-color);"><?= htmlspecialchars($record['field_changed']) ?></span><br>
                                        <span style="color: var(--text-muted); font-size: 0.85em;">From:</span> <em><?= htmlspecialchars(substr($record['old_value'], 0, 100)) ?><?= strlen($record['old_value']) > 100 ? '...' : '' ?></em>
                                        <span style="color: var(--text-muted); font-size: 0.85em; margin-left: 10px;">To:</span> <strong><?= htmlspecialchars(substr($record['new_value'], 0, 100)) ?><?= strlen($record['new_value']) > 100 ? '...' : '' ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <!-- Toast JS -->
    <script src="<?= BASE_URL ?>/resources/js/toast.js"></script>

    <!-- Trigger Toasts from PHP Session Variables -->
    <?php if (!empty($message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast(<?= json_encode($message) ?>, 'success');
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast(<?= json_encode($error) ?>, 'error');
            });
        </script>
    <?php endif; ?>

    <script>
        function checkRegPasswordStrength(password) {
            const meter = document.getElementById('password-strength');
            const bar = document.getElementById('strength-bar');
            const hint = document.getElementById('password-hint');

            if (!password) {
                meter.style.display = 'none';
                hint.style.display = 'none';
                return;
            }

            meter.style.display = 'block';
            hint.style.display = 'block';

            const checks = {
                length: password.length >= 8 && password.length <= 64,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*]/.test(password)
            };

            let strength = 0;
            if (checks.length) strength++;
            if (checks.uppercase && checks.lowercase) strength++;
            if (checks.number) strength++;
            if (checks.special) strength++;

            const colors = ['#ef4444', '#f59e0b', '#10b981', '#059669'];
            const widths = ['25%', '50%', '75%', '100%'];
            
            bar.style.width = widths[strength - 1] || '0%';
            bar.style.backgroundColor = colors[strength - 1] || '#eee';

            const missing = [];
            if (!checks.length) missing.push(password.length < 8 ? '8+ chars' : 'max 64 chars');
            if (!checks.uppercase) missing.push('uppercase');
            if (!checks.lowercase) missing.push('lowercase');
            if (!checks.number) missing.push('number');
            if (!checks.special) missing.push('special (!@#$%^&*)');

            if (missing.length > 0) {
                hint.textContent = 'Missing: ' + missing.join(', ');
                hint.style.color = '#ef4444';
            } else {
                hint.textContent = 'Strong password';
                hint.style.color = '#10b981';
            }
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }

        function handlePhotoUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                if (!file.type.startsWith('image/')) {
                    showToast('Please upload an image file', 'error');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    showToast('File too large. Maximum 2MB allowed.', 'error');
                    return;
                }

                const reader = new FileReader();

                reader.onerror = () => {
                    showToast('Error reading file. Please try again.', 'error');
                };

                reader.onload = (e) => {
                    document.getElementById('photoPreview').src = e.target.result;
                    document.getElementById('photoPreview').classList.remove('hidden');
                    document.getElementById('photoIcon').classList.add('hidden');
                    document.getElementById('photoError').style.display = 'none';
                    
                    showToast('Photo selected for upload!', 'success');
                };

                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>