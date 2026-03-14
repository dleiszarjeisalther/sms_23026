<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Records Viewer</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <!-- Styles moved to /subsystem1/resources/css/index.css -->
</head>

<body>
    <div class="app-container">
        <?php require __DIR__ . '/../navigation_bar.php'; ?>

        <main class="main-content">
            <div class="container academic-page-container">
                <div class="premium-header" style="border-radius: 8px; margin-bottom: 2rem !important; flex-direction: column; align-items: flex-start;">
                    <h1 class="academic-records-title">Academic Records Viewer</h1>
                    <p class="academic-records-subtitle" style="margin-bottom: 0 !important;">View your historical academic data, schedules, and achievements.</p>
                </div>

                <div class="tabs">
                    <button class="tab-btn active" data-target="grades">Grades</button>
                    <button class="tab-btn" data-target="subjects">Enrolled Subjects</button>
                    <button class="tab-btn" data-target="schedules">Schedules</button>
                    <button class="tab-btn" data-target="accomplishments">Accomplishments</button>
                    <button class="tab-btn" data-target="organizations">Organizations</button>
                </div>

                <!-- GRADES TAB -->
                <div id="grades" class="tab-content active">
                    <h2 class="tab-title">Academic Grades</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Description</th>
                                <th>Units</th>
                                <th>Final Grade</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CS101</td>
                                <td>Introduction to Computing</td>
                                <td>3</td>
                                <td><span class="grade-badge grade-a">1.25</span></td>
                                <td>Passed</td>
                            </tr>
                            <tr>
                                <td>ENG101</td>
                                <td>Purposive Communication</td>
                                <td>3</td>
                                <td><span class="grade-badge grade-b">1.75</span></td>
                                <td>Passed</td>
                            </tr>
                            <tr>
                                <td>MATH101</td>
                                <td>Mathematics in the Modern World</td>
                                <td>3</td>
                                <td><span class="grade-badge grade-a">1.50</span></td>
                                <td>Passed</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SUBJECTS TAB -->
                <div id="subjects" class="tab-content">
                    <h2 class="tab-title">History of Subjects Taken</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Description</th>
                                <th>Semester Taken</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CS101</td>
                                <td>Introduction to Computing</td>
                                <td>1st Sem, 2023-2024</td>
                                <td>Completed</td>
                            </tr>
                            <tr>
                                <td>ENG101</td>
                                <td>Purposive Communication</td>
                                <td>1st Sem, 2023-2024</td>
                                <td>Completed</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SCHEDULES TAB -->
                <div id="schedules" class="tab-content">
                    <h2 class="tab-title">Current Schedule</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Monday</td>
                                <td>08:00 AM - 10:00 AM</td>
                                <td>CS102 - Programming 1</td>
                                <td>Lab 3</td>
                            </tr>
                            <tr>
                                <td>Wednesday</td>
                                <td>10:00 AM - 11:30 AM</td>
                                <td>PE102 - Rhythmic Activities</td>
                                <td>Gym</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ACCOMPLISHMENTS TAB -->
                <div id="accomplishments" class="tab-content">
                    <h2 class="tab-title">Achievements & Milestones</h2>
                    <div class="accomplishments-grid">

                        <div class="accomplishment-card warning">
                            <h3 class="accomplishment-title warning">Best in Quiz Bee</h3>
                            <p class="accomplishment-description">Awarded 1st Place in the Annual College of IT Quiz Bee Competition.</p>
                            <small class="accomplishment-date">Date: October 2023</small>
                        </div>

                        <div class="accomplishment-card info">
                            <h3 class="accomplishment-title info">Dean's Lister</h3>
                            <p class="accomplishment-description">Recognized for maintaining a GWA of 1.4 during the 1st Semester.</p>
                            <small class="accomplishment-date">Date: January 2024</small>
                        </div>

                    </div>
                </div>

                <!-- ORGANIZATIONS TAB -->
                <div id="organizations" class="tab-content">
                    <h2 class="tab-title">Affiliated Organizations</h2>
                    <div class="organization-card">
                        <h3 class="organization-title">Supreme Student Council (SSC)</h3>
                        <p class="organization-role"><strong>Role:</strong> President</p>
                        <p class="organization-description">Leading the student body in orchestrating collegiate events and representing student interests to the administration.</p>
                        <small class="organization-term">Term: 2024 - 2025</small>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Tab switching logic

            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active from all
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));

                    // Add active to clicked
                    btn.classList.add('active');
                    const targetId = btn.getAttribute('data-target');
                    document.getElementById(targetId).classList.add('active');
                });
            });
        });
    </script>
</body>

</html>