<?php
// This view expects `$students` (array) and `$error` (string) from the controller.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Status</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <!-- Styles moved to <?= BASE_URL ?>/resources/css/index.css -->
</head>

<body class="student-status-page">
    <div class="app-container">
        <?php require __DIR__ . '/../navigation_bar.php'; ?>

        <main class="main-content">
            <div class="status-management-container">
                <div class="card">
                    <div class="premium-header">
                        <h2>Student Status Management</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php
                        $programs = [];
                        $statusOptions = ['Active', 'Dropped', 'Graduated', 'On Leave'];
                        $statusCounts = array_fill_keys($statusOptions, 0);
                        foreach ($students as $s) {
                            if (!in_array($s['program'], $programs)) {
                                $programs[] = $s['program'];
                            }
                            if (!empty($s['status']) && isset($statusCounts[$s['status']])) {
                                $statusCounts[$s['status']]++;
                            }
                        }
                        $allCount = count($students);
                        ?>

                        <div class="status-toolbar">
                            <button class="status-btn active" data-status="All">All (<?= $allCount ?>)</button>
                            <?php foreach ($statusOptions as $opt): ?>
                                <button class="status-btn" data-status="<?= htmlspecialchars($opt) ?>">
                                    <?= htmlspecialchars($opt) ?> (<?= $statusCounts[$opt] ?? 0 ?>)
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <div class="status-filters-grid">
                            <div class="filter-item">
                                <label class="filter-label">Search Student</label>
                                <input id="search" type="search" class="filter-input" placeholder="Search by ID or name...">
                            </div>
                            <div class="filter-item">
                                <label class="filter-label">Filter by Program</label>
                                <select id="filter_program" class="filter-input">
                                    <option value="All">All Programs</option>
                                    <option value="BS Computer Science">BS Computer Science</option>
                                    <option value="BS Information Technology">BS Information Technology</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label class="filter-label">Quick Status</label>
                                <select id="filter_status" class="filter-input">
                                    <option value="All">All Statuses</option>
                                    <option value="Active">Active</option>
                                    <option value="Dropped">Dropped</option>
                                    <option value="Graduated">Graduated</option>
                                    <option value="On Leave">On Leave</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <button id="clear_filters" class="status-btn" style="height: 48px; border-color: #cbd5e1;">Clear</button>
                            </div>
                        </div>

                        <div class="status-table-container">
                            <table class="status-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student Name</th>
                                        <th>Program</th>
                                        <th>Section</th>
                                        <th style="width: 180px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 3rem; color: #64748b;">
                                                No students found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $s):
                                            $rowClass = 'status-row-' . strtolower(str_replace(' ', '', $s['status']));
                                        ?>
                                            <tr class="<?= $rowClass ?>">
                                                <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($s['student_id']) ?></td>
                                                <td style="font-weight: 600;"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                                                <td>
                                                    <select class="status-select program-select" data-student-id="<?= htmlspecialchars($s['student_id']) ?>" style="width: 100%; border: none; background: transparent; font-weight: inherit; color: inherit; cursor: pointer;">
                                                        <?php
                                                        $progOptions = ['BS Computer Science', 'BS Information Technology'];
                                                        // Ensure current program is in options if not already
                                                        if (!in_array($s['program'], $progOptions)) {
                                                            array_unshift($progOptions, $s['program']);
                                                        }
                                                        foreach ($progOptions as $popt):
                                                        ?>
                                                            <option value="<?= htmlspecialchars($popt) ?>" <?= $s['program'] === $popt ? 'selected' : '' ?>><?= htmlspecialchars($popt) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td><span style="background: #f1f5f9; padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem;"><?= htmlspecialchars($s['section']) ?></span></td>
                                                <td>
                                                    <select class="status-select" data-student-id="<?= htmlspecialchars($s['student_id']) ?>">
                                                        <?php
                                                        $options = ['Active', 'Dropped', 'Graduated', 'On Leave'];
                                                        foreach ($options as $opt):
                                                        ?>
                                                            <option value="<?= $opt ?>" <?= $s['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            function attachStatusHandlers() {
                document.querySelectorAll('.status-select').forEach(function(sel) {
                    sel.addEventListener('change', function() {
                        var studentId = this.dataset.studentId;
                        var status = this.value;
                        var row = this.closest('tr');

                        var fd = new FormData();
                        fd.append('action', 'update_status');
                        fd.append('student_id', studentId);
                        fd.append('status', status);

                        fetch(window.location.pathname, {
                                method: 'POST',
                                body: fd
                            }).then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                if (data.status === 'success') {
                                    showToast(data.message, 'success');
                                    // Update row color
                                    row.className = 'status-row-' + status.toLowerCase().replace(/\s/g, '');
                                    filterRows();
                                } else {
                                    showToast(data.message || 'Update failed', 'error');
                                }
                            }).catch(function(err) {
                                showToast('Network error', 'error');
                            });
                    });
                });
            }

            function attachProgramHandlers() {
                document.querySelectorAll('.program-select').forEach(function(sel) {
                    sel.addEventListener('change', function() {
                        var studentId = this.dataset.studentId;
                        var program = this.value;

                        var fd = new FormData();
                        fd.append('action', 'update_program');
                        fd.append('student_id', studentId);
                        fd.append('program', program);

                        fetch(window.location.pathname, {
                                method: 'POST',
                                body: fd
                            }).then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                if (data.status === 'success') {
                                    showToast(data.message, 'success');
                                    filterRows();
                                } else {
                                    showToast(data.message || 'Update failed', 'error');
                                }
                            }).catch(function(err) {
                                showToast('Network error', 'error');
                            });
                    });
                });
            }

            attachStatusHandlers();
            attachProgramHandlers();

            // Filtering UI
            var searchEl = document.getElementById('search');
            var progEl = document.getElementById('filter_program');
            var statEl = document.getElementById('filter_status');
            var clearBtn = document.getElementById('clear_filters');

            function filterRows() {
                var q = (searchEl.value || '').trim().toLowerCase();
                var prog = progEl.value;
                var stat = statEl.value;

                document.querySelectorAll('.status-table tbody tr').forEach(function(tr) {
                    var id = (tr.querySelector('td:nth-child(1)') || {}).textContent || '';
                    var name = (tr.querySelector('td:nth-child(2)') || {}).textContent || '';

                    var progSel = tr.querySelector('.program-select');
                    var program = progSel ? progSel.value : '';

                    var statusSel = tr.querySelector('.status-select:not(.program-select)');
                    var status = statusSel ? statusSel.value : '';

                    var matches = true;
                    if (q && !(id.toLowerCase().includes(q) || name.toLowerCase().includes(q))) matches = false;
                    if (prog !== 'All' && program !== prog) matches = false;
                    if (stat !== 'All' && status !== stat) matches = false;

                    tr.style.display = matches ? '' : 'none';
                });
            }

            searchEl.addEventListener('input', filterRows);
            progEl.addEventListener('change', filterRows);
            statEl.addEventListener('change', function() {
                // clear active status button when manual select used
                document.querySelectorAll('.status-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                filterRows();
            });
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchEl.value = '';
                progEl.value = 'All';
                statEl.value = 'All';
                document.querySelectorAll('.status-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                document.querySelector('.status-btn[data-status="All"]').classList.add('active');
                filterRows();
            });

            // Status button behavior
            document.querySelectorAll('.status-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var status = this.dataset.status;
                    document.querySelectorAll('.status-btn').forEach(function(b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');
                    statEl.value = status === 'All' ? 'All' : status;
                    filterRows();
                });
            });

            // initial filter pass
            filterRows();
        });
    </script>
</body>

</html>