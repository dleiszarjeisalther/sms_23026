<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admission Registration</title>

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    <meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera=()">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com   https://cdnjs.cloudflare.com  ; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com   https://cdnjs.cloudflare.com  ; font-src https://fonts.gstatic.com   https://cdnjs.cloudflare.com  ; img-src 'self' data: blob:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
</head>

<body class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-10">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800">Student Admission System</h1>
        </header>

        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b">
                <h2 class="text-xl font-semibold">Register New Student</h2>
            </div>

            <div class="px-6 py-8">
                <!-- Chevron Progress Bar -->
                <div class="chevron-progress" id="chevronProgress">
                    <div class="chevron-step active" data-step="1">1. Personal Info</div>
                    <div class="chevron-step" data-step="2">2. Contact</div>
                    <div class="chevron-step" data-step="3">3. Summary</div>
                </div>

                <form id="registrationForm" novalidate>
                    <div id="formMessage" style="margin-bottom: 1rem; font-weight: 500; font-size: 1.1rem; text-align: center;"></div>

                    <!-- STEP 1: Personal Info -->
                    <section class="wizard-step active" data-step="1">
                        <h2 class="wizard-step-title">Personal Information</h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="form-row grid-3">
                            <div class="form-group">
                                <label for="dobMonth">Month of Birth</label>
                                <select id="dobMonth" name="dobMonth" required>
                                    <option value="">Month</option>
                                    <option value="01">January</option>
                                    <option value="02">February</option>
                                    <option value="03">March</option>
                                    <option value="04">April</option>
                                    <option value="05">May</option>
                                    <option value="06">June</option>
                                    <option value="07">July</option>
                                    <option value="08">August</option>
                                    <option value="09">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dobDay">Day</label>
                                <select id="dobDay" name="dobDay" required>
                                    <option value="">Day</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?= $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dobYear">Year</label>
                                <select id="dobYear" name="dobYear" required>
                                    <option value="">Year</option>
                                    <?php for ($y = 2011; $y >= 1994; $y--): ?>
                                        <option value="<?= $y; ?>"><?= $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="program">Program</label>
                                <select id="program" name="program" required>
                                    <option value="">Select Program</option>
                                    <option value="BS Computer Science">BS Computer Science</option>
                                    <option value="BS Information Technology">BS Information Technology</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- STEP 2: Contact Info -->
                    <section class="wizard-step" data-step="2">
                        <h2 class="wizard-step-title">Contact & Address</h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactNumber">Contact Number</label>
                                <div class="phone-input-wrapper" style="display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden; background-color: var(--bg-main);">
                                    <span class="country-code" aria-hidden="true" style="background-color: #d1fae5; color: var(--primary-color); padding: 0.75rem 1rem; font-weight: 600; font-size: 0.95rem; border-right: 1px solid var(--border-color);">PH +63</span>
                                    <input type="tel" id="contactNumber" name="contactNumber" placeholder="9XX XXX XXXX" style="flex: 1; border: none; padding: 0.75rem 1rem; outline: none; background: transparent; width: 100%; box-sizing: border-box;" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="emailAddress">Email Address</label>
                                <input type="email" id="emailAddress" name="emailAddress" placeholder="student@example.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Current Address</label>
                            <textarea id="address" name="address" placeholder="Enter full address" required></textarea>
                        </div>
                    </section>



                    <!-- STEP 3: Summary -->
                    <section class="wizard-step" data-step="3">
                        <h2 class="wizard-step-title">Review & Submit</h2>
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Please review your information before completing the registration.</p>

                        <div class="summary-grid">
                            <div class="summary-card">
                                <h3>Personal details</h3>
                                <div class="summary-item">
                                    <span class="summary-label">Full Name</span>
                                    <span class="summary-value" id="sum-name"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Date of Birth</span>
                                    <span class="summary-value" id="sum-dob"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Gender</span>
                                    <span class="summary-value" id="sum-gender"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Program</span>
                                    <span class="summary-value" id="sum-program"></span>
                                </div>
                            </div>

                            <div class="summary-card">
                                <h3>Contact Details</h3>
                                <div class="summary-item">
                                    <span class="summary-label">Contact Number</span>
                                    <span class="summary-value" id="sum-contact"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Email Address</span>
                                    <span class="summary-value" id="sum-email"></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Current Address</span>
                                    <span class="summary-value" id="sum-address"></span>
                                </div>
                            </div>
                        </div>


                    </section>

                    <!-- Wizard Controls -->
                    <div class="wizard-footer">
                        <button type="button" class="btn btn-secondary" id="btnPrev" style="visibility: hidden;">Back</button>
                        <button type="button" class="btn btn-primary" id="btnNext">Continue</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit" style="display: none; background-color: #10b981;">Complete Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('registrationForm');
            const steps = document.querySelectorAll('.wizard-step');
            const btnPrev = document.getElementById('btnPrev');
            const btnNext = document.getElementById('btnNext');
            const btnSubmit = document.getElementById('btnSubmit');
            const chevronSteps = document.querySelectorAll('.chevron-step');
            const formInputs = document.querySelectorAll('#registrationForm input[required], #registrationForm select[required], #registrationForm textarea[required]');
            const messageDiv = document.getElementById('formMessage');

            let currentStep = 1;
            const totalSteps = 3; // Reduced to 3 steps

            // Automatically load saved form data from localStorage
            function loadFormData() {
                const savedData = JSON.parse(localStorage.getItem('admission_formDraft')) || {};
                formInputs.forEach(input => {
                    if (savedData[input.name]) {
                        input.value = savedData[input.name];
                    }
                });
            }

            // Save form data to localStorage
            function saveFormData() {
                const draft = {};
                formInputs.forEach(input => {
                    draft[input.name] = input.value;
                });
                localStorage.setItem('admission_formDraft', JSON.stringify(draft));
            }

            function validateCurrentStep() {
                const activeStep = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
                // If it's the summary step, no required inputs to validate
                if (currentStep === 3) return true;

                const inputs = activeStep.querySelectorAll('input[required], select[required], textarea[required]');

                let isValid = true;
                inputs.forEach(input => {
                    if (!input.value.trim() || (input.type === 'email' && !input.checkValidity())) {
                        isValid = false;
                        input.style.borderColor = 'red';
                    } else {
                        input.style.borderColor = 'var(--border-color)';
                    }
                });
                return isValid;
            }

            function populateSummary() {
                document.getElementById('sum-name').textContent = `${document.getElementById('firstName').value} ${document.getElementById('lastName').value}`;

                const monthSelect = document.getElementById('dobMonth');
                const monthText = monthSelect.options[monthSelect.selectedIndex]?.text || '';
                document.getElementById('sum-dob').textContent = `${monthText} ${document.getElementById('dobDay').value}, ${document.getElementById('dobYear').value}`;

                const genderSelect = document.getElementById('gender');
                document.getElementById('sum-gender').textContent = genderSelect.options[genderSelect.selectedIndex]?.text || '';

                const programSelect = document.getElementById('program');
                document.getElementById('sum-program').textContent = programSelect.options[programSelect.selectedIndex]?.text || '';

                document.getElementById('sum-contact').textContent = document.getElementById('contactNumber').value;
                document.getElementById('sum-email').textContent = document.getElementById('emailAddress').value;
                document.getElementById('sum-address').textContent = document.getElementById('address').value;
            }

            function updateUI() {
                // Toggle active steps
                steps.forEach(step => {
                    step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
                });

                // Update chevron indicator
                chevronSteps.forEach((chevron, index) => {
                    const stepNum = index + 1;
                    if (stepNum < currentStep) {
                        chevron.classList.add('completed');
                        chevron.classList.remove('active');
                    } else if (stepNum === currentStep) {
                        chevron.classList.add('active');
                        chevron.classList.remove('completed');
                    } else {
                        chevron.classList.remove('active');
                        chevron.classList.remove('completed');
                    }
                });

                // Populate summary if entering step 3
                if (currentStep === 3) {
                    populateSummary();
                }

                // Toggle Buttons
                btnPrev.style.visibility = currentStep === 1 ? 'hidden' : 'visible';

                if (currentStep === totalSteps) {
                    btnNext.style.display = 'none';
                    btnSubmit.style.display = 'block';
                } else {
                    btnNext.style.display = 'block';
                    btnSubmit.style.display = 'none';
                }

                // Clear errors on navigation
                messageDiv.innerText = '';

                // Save current step to localStorage
                localStorage.setItem('admission_currentStep', currentStep);

                // Auto-scroll to top of form smoothly to guide user
                document.querySelector('.card').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            btnNext.addEventListener('click', () => {
                if (validateCurrentStep()) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        updateUI();
                    }
                } else {
                    showToast('Please fill out all required fields on this step.', 'warning');
                }
            });

            btnPrev.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateUI();
                }
            });

            // Re-bind input events to clear red borders on typing and auto-save
            formInputs.forEach(input => {
                input.addEventListener('input', () => {
                    input.style.borderColor = 'var(--border-color)';
                    saveFormData(); // Save on every keystroke
                });
                input.addEventListener('change', saveFormData);
            });

            // Form Submit Logic
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!validateCurrentStep()) {
                    showToast('Please ensure all fields are correctly filled.', 'error');
                    return;
                }

                var formData = new FormData(this);
                btnSubmit.disabled = true;
                btnSubmit.textContent = "Registering...";

                fetch('<?= BASE_URL ?>/controllers/subsystem1/admission.controller.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        // response format is status|info1|info2|...
                        var parts = data.split('|');
                        var status = parts[0];
                        var info = parts.slice(1).join('|'); // keep full message if error
                        var userId = parts[1] || 'Unknown';
                        var password = parts[2] || '';
                        var emailSent = parts[3] || 'false';

                        if (status.trim() === 'success') {
                            // Clear localStorage on success
                            localStorage.removeItem('admission_formDraft');
                            localStorage.removeItem('admission_currentStep');

                            var studentEmail = document.getElementById('emailAddress').value;

                            var emailNoticeHtml = emailSent === '1' ?
                                `<p style="color: #10b981; font-size: 0.95rem; margin-top: 1rem;">✓ An email containing these credentials has been sent to ${studentEmail}.</p>` :
                                `<p style="color: #d97706; font-size: 0.95rem; margin-top: 1rem;">⚠ Could not send email. Please copy these credentials now!</p>`;

                            showToast('Registration successful!', 'success');
                            // Switch to a success view
                            form.innerHTML = `
                            <div style="text-align: center; padding: 2rem;">
                                <h2 style="color: #10b981; margin-bottom: 1rem;">Registration Complete!</h2>
                                <p style="font-size: 1.1rem; color: var(--text-main);">The student has been successfully registered.</p>
                                
                                <div style="background-color: var(--bg-lighter); padding: 1.5rem; border-radius: 8px; margin-top: 2rem; border: 1px solid var(--border-color);">
                                    <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--text-muted);">We sent your user id:</p>
                                    <h1 style="font-size: 2.2rem; color: var(--primary-color); margin-bottom: 1rem; letter-spacing: 2px;">${userId}</h1>
                                    
                                    <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: 0.5rem;">Password is <strong style="color: var(--text-main); font-family: monospace; font-size: 1.3rem; background: #e0e0e0; padding: 2px 8px; border-radius: 4px;">${password}</strong> as your credential to login.</p>
                                    ${emailNoticeHtml}
                                </div>

                                <div style="margin-top: 2.5rem; display: flex; gap: 1rem; justify-content: center;">
                                    <button type="button" class="btn btn-secondary" onclick="location.reload()">Register Another</button>
                                    <a href="<?= BASE_URL ?>/login" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Proceed to Login</a>
                                </div>
                            </div>
                        `;
                        } else {
                            showToast('Error: ' + info, 'error');
                            btnSubmit.disabled = false;
                            btnSubmit.textContent = "Complete Registration";
                        }
                    })
                    .catch(error => {
                        showToast('Network Error: ' + error.message, 'error');
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = "Complete Registration";
                    });
            });

            // Init UI
            loadFormData();
            updateUI();
        });
    </script>
    <script src="<?= BASE_URL ?>/resources/js/toast.js"></script>
</body>

</html>