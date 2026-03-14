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
    <title>Student ID Generator - SIMS</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- HTML Capture Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<body class="id-generator-page">
    <div class="app-container">
        <?php require __DIR__ . '/../navigation_bar.php'; ?>

        <main class="main-content">
            <div class="status-management-container">
                <div class="card">
                    <div class="premium-header">
                        <h2>Student ID Management</h2>
                    </div>
                    <div class="card-body">
                        <!-- Workspace Layout: Filters + Student List -->
                        <div class="status-filters-grid">
                            <div class="filter-item">
                                <label class="filter-label" for="idSectionFilter">Section Filter</label>
                                <select id="idSectionFilter" class="filter-input">
                                    <option value="">All Sections</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>
                            <div class="filter-item">
                                <label class="filter-label" for="idStudentSelect">Quick Search</label>
                                <select id="idStudentSelect" class="filter-input">
                                    <option value="">Search by ID or Name...</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label class="filter-label">&nbsp;</label>
                                <button id="btnClearFilter" class="btn-clear" title="Clear Filters">Clear Filters</button>
                            </div>
                        </div>

                        <!-- Main Workspace: Table and Preview Side by Side or Stacked -->
                        <div class="id-management-workspace">
                            <!-- Student List Side -->
                            <div class="student-list-pane" id="studentListContainer">
                                <div class="pane-header">
                                    <h3>Active Students</h3>
                                    <div class="pane-actions">
                                        <div class="search-input-wrapper">
                                            <input type="text" id="tableSearchInput" placeholder="Filter list..." class="filter-input-sm">
                                        </div>
                                        <button id="btnMassGenerate" class="status-btn">Generate Selected</button>
                                    </div>
                                </div>
                                <div class="status-table-container">
                                    <table class="status-table" id="studentTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                                                <th style="width: 150px;">Student ID</th>
                                                <th style="width: 200px;">Full Name</th>
                                                <th style="width: 100px;">Section</th>
                                                <th style="width: 120px;">Status</th>
                                                <th style="text-align: right; width: 120px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Populated via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Preview Side -->
                            <div class="id-preview-pane">
                                <div id="selectStudentPrompt" class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <p>Select a student to preview their ID card</p>
                                </div>

                                <div class="preview-card-container hidden" id="idCardPreviewContainer">
                                    <div class="premium-header">
                                        <h3>Card Preview</h3>
                                        <button id="btnGenerate" class="status-btn active">Print Card</button>
                                    </div>

                                    <div class="premium-id-card-wrapper">
                                        <!-- Front Card -->
                                        <div class="id-card id-front">
                                            <div class="id-card-inner">
                                                <div class="id-header">
                                                    <div class="school-logo">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z"></path>
                                                            <path d="M4.5 15.5c.5-1.5 2-2.5 4-2.5s3.5 1 4 2.5a5 5 0 01-8 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="school-name">
                                                        SIMS ACADEMY
                                                        <span>Metro City, Campus</span>
                                                    </div>
                                                </div>
                                                <div class="id-card-body">
                                                    <div class="photo-area">
                                                        <div class="photo-placeholder" id="previewPhoto">
                                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="details-area">
                                                        <div class="program-label" id="previewCourse">BS COMPUTER SCIENCE</div>
                                                        <div class="student-name" id="previewName">John Doe</div>
                                                        <div class="stats-grid">
                                                            <div class="stat-item">
                                                                <span class="stat-label">ID NO.</span>
                                                                <span class="stat-value" id="previewStudentId">2024-001</span>
                                                            </div>
                                                            <div class="stat-item">
                                                                <span class="stat-label">SECTION</span>
                                                                <span class="stat-value" id="previewSection">12231</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="id-footer" id="previewSY">
                                                    S.Y. 2024 - 2025
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Back Card -->
                                        <div class="id-card id-back">
                                            <div class="id-card-inner">
                                                <div class="emergency-section">
                                                    <div class="section-tag">In Case of Emergency</div>
                                                    <div class="contact-name" id="previewGuardian">Jane Doe</div>
                                                    <div class="contact-details">
                                                        <div class="detail-row">
                                                            <span class="label">Address</span>
                                                            <span class="value" id="previewAddress">123 Main St, Example City</span>
                                                        </div>
                                                        <div class="detail-row">
                                                            <span class="label">Contact</span>
                                                            <span class="value" id="previewTel">09123456789</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="barcode-section">
                                                    <div class="disclaimer">
                                                        Property of SIMS. If found, please return to any registrar's office.
                                                    </div>
                                                    <div id="qrcode" class="qr-code-wrapper"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-actions-footer">
                                        <button id="btnDownloadPDF" class="status-btn btn-secondary w-full">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            Generate ID
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= BASE_URL ?>/resources/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const studentSelect = document.getElementById('idStudentSelect');
            const sectionFilter = document.getElementById('idSectionFilter');
            const previewContainer = document.getElementById('idCardPreviewContainer');
            const prompt = document.getElementById('selectStudentPrompt');
            const btnGenerate = document.getElementById('btnGenerate');
            const qrcodeDiv = document.getElementById('qrcode');
            const studentListContainer = document.getElementById('studentListContainer');
            const studentTableBody = document.querySelector('#studentTable tbody');
            const selectAllCheckbox = document.getElementById('selectAll');
            const btnClearFilter = document.getElementById('btnClearFilter');
            const tableSearchInput = document.getElementById('tableSearchInput');
            const btnDownloadPDF = document.getElementById('btnDownloadPDF');

            let qrcode = new QRCode(qrcodeDiv, {
                width: 75,
                height: 75,
                colorDark: "#1e293b",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            // Tracking generated IDs locally for now (could save to DB later)
            let generatedIds = JSON.parse(localStorage.getItem('sims_generated_ids')) || {};

            function markAsGenerated(studentId) {
                generatedIds[studentId] = true;
                localStorage.setItem('sims_generated_ids', JSON.stringify(generatedIds));
                updateTableStatus(studentId);
            }

            function updateTableStatus(studentId) {
                const tr = document.querySelector(`tr[data-student-id="${studentId}"]`);
                if (tr) {
                    const statusCell = tr.querySelector('.status-cell');
                    const checkbox = tr.querySelector('.row-checkbox');

                    statusCell.innerHTML = '<span class="status-badge status-done">Generated</span>';
                    // Optional: Uncheck the box after generation to prevent double printing easily
                    checkbox.checked = false;
                }
            }

            // Load sections
            fetch('<?= BASE_URL ?>/controllers/subsystem1/id_generator.controller.php?action=sections')
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        res.data.forEach(section => {
                            if (section) {
                                const option = document.createElement('option');
                                option.value = section;
                                option.textContent = section;
                                sectionFilter.appendChild(option);
                            }
                        });
                        $(sectionFilter).select2({
                            placeholder: "All Sections",
                            allowClear: true,
                            width: '100%'
                        });
                    }
                });

            // Load student list
            function loadStudents(section = '') {
                studentListContainer.classList.add('hidden');
                previewContainer.classList.add('hidden');
                btnGenerate.classList.add('hidden');
                prompt.classList.remove('hidden');

                let url = '<?= BASE_URL ?>/controllers/subsystem1/id_generator.controller.php?action=list';
                if (section) url += `&section=${encodeURIComponent(section)}`;

                fetch(url)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            // Populate Dropdown
                            // Destroy old select2 before changing options
                            if ($(studentSelect).hasClass("select2-hidden-accessible")) {
                                $(studentSelect).select2('destroy');
                            }
                            studentSelect.innerHTML = '<option value="">Search by ID or Name...</option>';

                            studentTableBody.innerHTML = '';

                            res.data.forEach(student => {
                                // Dropdown option
                                const option = document.createElement('option');
                                option.value = student.student_id;
                                option.textContent = `${student.student_id} - ${student.first_name} ${student.last_name}`;
                                studentSelect.appendChild(option);

                                // Table Row
                                const isGenerated = generatedIds[student.student_id];
                                const tr = document.createElement('tr');
                                tr.dataset.studentId = student.student_id;
                                tr.innerHTML = `
                                    <td><input type="checkbox" class="row-checkbox" value="${student.student_id}"></td>
                                    <td>
                                        <div class="student-id-display">${student.student_id}</div>
                                        <div class="student-program-tag">${(student.program || 'N/A').toUpperCase()}</div>
                                    </td>
                                    <td>
                                        <div class="student-name-display">${student.last_name}, ${student.first_name}</div>
                                    </td>
                                    <td><span class="section-badge">${student.section || 'N/A'}</span></td>
                                    <td class="status-cell">
                                        ${isGenerated 
                                            ? '<span class="status-badge status-done">Generated</span>' 
                                            : '<span class="status-badge status-pending">Pending</span>'}
                                    </td>
                                    <td style="text-align: right;">
                                        <button class="status-btn preview-btn" data-id="${student.student_id}">Preview</button>
                                    </td>
                                `;
                                studentTableBody.appendChild(tr);
                            });

                            // Initialize Select2 after options are loaded
                            $(studentSelect).select2({
                                placeholder: "Search by ID or Name...",
                                allowClear: true,
                                width: '100%'
                            });

                            if (section) {
                                studentListContainer.classList.remove('hidden');
                                prompt.classList.add('hidden');
                            }
                        }
                    })
                    .catch(err => showToast('Error loading students.', 'error'));
            }

            // Initial Load (All students in dropdown, hide table)
            loadStudents();

            $(sectionFilter).on('change', function() {
                loadStudents(this.value);
            });

            // Table checkbox logic
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });

            // Table Search Filter
            tableSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = studentTableBody.querySelectorAll('tr');

                rows.forEach(row => {
                    const idText = row.cells[1].textContent.toLowerCase();
                    const nameText = row.cells[2].textContent.toLowerCase();

                    if (idText.includes(searchTerm) || nameText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            studentTableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('preview-btn')) {
                    const id = e.target.dataset.id;
                    $(studentSelect).val(id).trigger('change');
                }
            });

            btnClearFilter.addEventListener('click', function() {
                $(sectionFilter).val(null).trigger('change');
            });

            // Use jQuery event for Select2
            $(studentSelect).on('change', function() {
                const studentId = this.value;
                if (!studentId) {
                    previewContainer.classList.add('hidden');
                    btnGenerate.classList.add('hidden');

                    // Allow the table to still show if applicable, by only removing preview
                    return;
                }

                // Fetch student details
                fetch(`<?= BASE_URL ?>/controllers/subsystem1/id_generator.controller.php?action=details&id=${studentId}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            const s = res.data;

                            // Update Preview
                            document.getElementById('previewName').innerHTML = `${s.first_name} ${s.last_name}`;
                            document.getElementById('previewCourse').textContent = s.program.toUpperCase();
                            document.getElementById('previewStudentId').textContent = s.student_id;
                            document.getElementById('previewGuardian').textContent = s.guardian_name;
                            document.getElementById('previewAddress').textContent = s.address;
                            document.getElementById('previewTel').textContent = s.guardian_contact || s.contact_number;

                            // Placeholder for section (static for now as requested)
                            document.getElementById('previewSection').textContent = 'SEC-' + (Math.floor(Math.random() * 9000) + 1000);

                            // Update School Year Footer
                            document.getElementById('previewSY').textContent = s.academic_year ? `S.Y. ${s.academic_year}` : 'S.Y. 2024 - 2025';

                            // Update Photo if available
                            const photoContainer = document.getElementById('previewPhoto');
                            if (s.profile_image) {
                                photoContainer.innerHTML = `<img src="${s.profile_image}" alt="Student Photo">`;
                            } else {
                                photoContainer.innerHTML = `<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>`;
                            }

                            // Update QR
                            qrcode.clear();
                            qrcode.makeCode(s.student_id);

                            // Workspace logic: Show preview, keep list visible if width permits (CSS handles that)
                            previewContainer.classList.remove('hidden');
                            prompt.classList.add('hidden');

                            // Scroll to preview smoothly on smaller screens
                            if (window.innerWidth < 1024) {
                                previewContainer.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }

                            showToast('Student card ready.', 'success');
                        } else {
                            showToast(res.message, 'error');
                        }
                    })
                    .catch(err => showToast('Error fetching student details.', 'error'));
            });

            btnGenerate.addEventListener('click', () => {
                window.print();
            });

            // Download PDF
            btnDownloadPDF.addEventListener('click', () => {
                const studentId = studentSelect.value;
                if (!studentId) {
                    showToast('Please select a student first.', 'warning');
                    return;
                }

                // Open PDF in new tab
                window.open(`<?= BASE_URL ?>/controllers/subsystem1/id_generator_pdf.php?id=${studentId}`, '_blank');
                showToast('Generating PDF...', 'info');

                // Optionally mark as generated
                markAsGenerated(studentId);
            });

            document.getElementById('btnMassGenerate').addEventListener('click', () => {
                const checkedBoxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));
                if (checkedBoxes.length === 0) {
                    showToast('Please select at least one student from the table.', 'warning');
                    return;
                }

                // Collect all selected IDs and open a single combined PDF
                const ids = checkedBoxes.map(cb => cb.value).join(',');
                window.open(`<?= BASE_URL ?>/controllers/subsystem1/id_generator_pdf.php?ids=${encodeURIComponent(ids)}`, '_blank');
                showToast(`Generating ${checkedBoxes.length} ID card(s)...`, 'info');

                checkedBoxes.forEach(cb => {
                    markAsGenerated(cb.value);
                });
            });
        });
    </script>
</body>

</html>