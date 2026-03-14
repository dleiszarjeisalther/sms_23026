<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Ensure only students can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Student ID - SIMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- HTML Capture Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .my-id-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .my-id-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .my-id-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-primary, #1e293b);
            margin: 0 0 0.3rem 0;
        }

        .my-id-header p {
            color: var(--text-muted, #64748b);
            font-size: 0.9rem;
            margin: 0;
        }

        .id-card-showcase {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        /* Flip card container */
        .flip-card-scene {
            perspective: 1200px;
            width: 380px;
            height: 240px;
        }

        .flip-card-inner-wrap {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
        }

        .flip-card-inner-wrap.flipped {
            transform: rotateY(180deg);
        }

        .flip-card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .flip-card-back {
            transform: rotateY(180deg);
        }

        .card-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 0.5rem;
        }

        .card-actions .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.25s ease;
            font-family: 'Outfit', sans-serif;
        }

        .btn-flip {
            background: var(--surface-elevated, #f1f5f9);
            color: var(--text-primary, #334155);
            border: 1px solid var(--border-color, #e2e8f0) !important;
        }

        .btn-flip:hover {
            background: var(--surface-hover, #e2e8f0);
            transform: translateY(-1px);
        }

        .btn-save-png {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-save-png:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
        }

        .btn-save-png:active {
            transform: translateY(0);
        }

        .btn-save-png svg,
        .btn-flip svg {
            width: 18px;
            height: 18px;
        }

        /* Loading state */
        .id-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 4rem 2rem;
            color: var(--text-muted, #64748b);
        }

        .id-loading .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color, #e2e8f0);
            border-top-color: var(--primary-color, #6366f1);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .id-error-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted, #94a3b8);
        }

        .id-error-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .id-error-state p {
            font-size: 0.95rem;
        }

        @media (max-width: 480px) {
            .flip-card-scene {
                width: 320px;
                height: 200px;
            }

            .flip-card-scene .id-card {
                width: 320px;
                height: 200px;
            }

            .my-id-container {
                padding: 1rem 0.5rem;
            }
        }
    </style>
</head>

<body class="id-generator-page">
    <div class="app-container">
        <?php require __DIR__ . '/../navigation_bar.php'; ?>

        <main class="main-content">
            <div class="status-management-container">
                <div class="my-id-container">
                    <div class="premium-header" style="border-radius: 8px; margin-bottom: 2rem !important; flex-direction: column;">
                        <h2>My Student ID</h2>
                        <p>View your student identification card and save it as an image</p>
                    </div>

                    <!-- Loading State -->
                    <div class="id-loading" id="idLoading">
                        <div class="spinner"></div>
                        <p>Loading your ID card...</p>
                    </div>

                    <!-- Error State -->
                    <div class="id-error-state hidden" id="idError">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p id="idErrorMessage">Unable to load your ID card. Please try again later.</p>
                    </div>

                    <!-- ID Card Display -->
                    <div class="id-card-showcase hidden" id="idShowcase">
                        <!-- Flip Card -->
                        <div class="flip-card-scene">
                            <div class="flip-card-inner-wrap" id="flipCardInner">
                                <!-- Front -->
                                <div class="flip-card-face flip-card-front">
                                    <div class="id-card id-front" id="visibleFront">
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
                                                    <div class="program-label" id="previewCourse">PROGRAM</div>
                                                    <div class="student-name" id="previewName">Student Name</div>
                                                    <div class="stats-grid">
                                                        <div class="stat-item">
                                                            <span class="stat-label">ID NO.</span>
                                                            <span class="stat-value" id="previewStudentId">—</span>
                                                        </div>
                                                        <div class="stat-item">
                                                            <span class="stat-label">SECTION</span>
                                                            <span class="stat-value" id="previewSection">—</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="id-footer" id="previewSY">S.Y. 2024 - 2025</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Back -->
                                <div class="flip-card-face flip-card-back">
                                    <div class="id-card id-back" id="visibleBack">
                                        <div class="id-card-inner">
                                            <div class="emergency-section">
                                                <div class="section-tag">In Case of Emergency</div>
                                                <div class="contact-name" id="previewGuardian">—</div>
                                                <div class="contact-details">
                                                    <div class="detail-row">
                                                        <span class="label">Address</span>
                                                        <span class="value" id="previewAddress">—</span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="label">Contact</span>
                                                        <span class="value" id="previewTel">—</span>
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
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="card-actions">
                            <button class="btn-action btn-flip" id="btnFlip">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="17 1 21 5 17 9"></polyline>
                                    <path d="M3 11V9a4 4 0 014-4h14"></path>
                                    <polyline points="7 23 3 19 7 15"></polyline>
                                    <path d="M21 13v2a4 4 0 01-4 4H3"></path>
                                </svg>
                                Flip Card
                            </button>
                            <button class="btn-action btn-save-png" id="btnSavePng">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Save as PNG
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="<?= BASE_URL ?>/resources/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loading = document.getElementById('idLoading');
            const errorState = document.getElementById('idError');
            const errorMessage = document.getElementById('idErrorMessage');
            const showcase = document.getElementById('idShowcase');
            const flipCardInner = document.getElementById('flipCardInner');
            const btnFlip = document.getElementById('btnFlip');
            const btnSavePng = document.getElementById('btnSavePng');
            const qrcodeDiv = document.getElementById('qrcode');

            let qrcode = new QRCode(qrcodeDiv, {
                width: 75,
                height: 75,
                colorDark: "#1e293b",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            // Flip button
            btnFlip.addEventListener('click', () => {
                flipCardInner.classList.toggle('flipped');
            });

            // Fetch logged-in student data
            fetch('<?= BASE_URL ?>/controllers/subsystem1/my_id.controller.php?action=details')
                .then(res => res.json())
                .then(res => {
                    loading.classList.add('hidden');

                    if (res.status === 'success') {
                        const s = res.data;

                        document.getElementById('previewName').textContent = `${s.first_name} ${s.last_name}`;
                        document.getElementById('previewCourse').textContent = (s.program || 'N/A').toUpperCase();
                        document.getElementById('previewStudentId').textContent = s.student_id;
                        document.getElementById('previewSection').textContent = s.section || 'N/A';
                        document.getElementById('previewGuardian').textContent = s.guardian_name || '—';
                        document.getElementById('previewAddress').textContent = s.address || '—';
                        document.getElementById('previewTel').textContent = s.guardian_contact || s.contact_number || '—';
                        document.getElementById('previewSY').textContent = s.academic_year ? `S.Y. ${s.academic_year}` : 'S.Y. 2024 - 2025';

                        const photoContainer = document.getElementById('previewPhoto');
                        if (s.profile_image) {
                            photoContainer.innerHTML = `<img src="${s.profile_image}" alt="Student Photo">`;
                        }

                        qrcode.clear();
                        qrcode.makeCode(s.student_id);

                        showcase.classList.remove('hidden');
                        showToast('ID card loaded successfully.', 'success');
                    } else {
                        errorMessage.textContent = res.message || 'Unable to load your ID card.';
                        errorState.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    loading.classList.add('hidden');
                    errorState.classList.remove('hidden');
                    console.error('Error fetching ID card data:', err);
                });

            /**
             * Convert all SVG elements inside a container to inline <img> with data URI.
             * This is necessary because html2canvas cannot render inline SVGs.
             */
            function convertSvgsToImages(container) {
                const svgs = container.querySelectorAll('svg');
                svgs.forEach(svg => {
                    const svgData = new XMLSerializer().serializeToString(svg);
                    const svgBlob = new Blob([svgData], {
                        type: 'image/svg+xml;charset=utf-8'
                    });
                    const url = URL.createObjectURL(svgBlob);

                    const img = document.createElement('img');
                    img.src = url;
                    img.width = svg.getAttribute('width') || svg.getBoundingClientRect().width || 32;
                    img.height = svg.getAttribute('height') || svg.getBoundingClientRect().height || 32;
                    img.style.display = 'inline-block';
                    img.style.verticalAlign = 'middle';

                    svg.parentNode.replaceChild(img, svg);
                });
            }

            /**
             * Convert all canvas elements inside a container to inline <img> with data URI.
             * This is necessary because cloning a canvas does not clone its contents.
             */
            function convertCanvasesToImages(sourceContainer, targetContainer) {
                const sourceCanvases = sourceContainer.querySelectorAll('canvas');
                const targetCanvases = targetContainer.querySelectorAll('canvas');

                for (let i = 0; i < sourceCanvases.length && i < targetCanvases.length; i++) {
                    const srcCanvas = sourceCanvases[i];
                    const targetCanvas = targetCanvases[i];

                    const img = document.createElement('img');
                    img.src = srcCanvas.toDataURL('image/png');
                    img.width = srcCanvas.width;
                    img.height = srcCanvas.height;
                    img.style.cssText = targetCanvas.style.cssText;

                    targetCanvas.parentNode.replaceChild(img, targetCanvas);
                }
            }

            /**
             * Apply computed styles inline so html2canvas can read them
             */
            function inlineAllStyles(source, target) {
                const sourceChildren = source.querySelectorAll('*');
                const targetChildren = target.querySelectorAll('*');

                // Inline styles on the root
                const rootComputed = getComputedStyle(source);
                for (let i = 0; i < rootComputed.length; i++) {
                    const prop = rootComputed[i];
                    target.style.setProperty(prop, rootComputed.getPropertyValue(prop));
                }

                for (let i = 0; i < sourceChildren.length && i < targetChildren.length; i++) {
                    const computed = getComputedStyle(sourceChildren[i]);
                    for (let j = 0; j < computed.length; j++) {
                        const prop = computed[j];
                        targetChildren[i].style.setProperty(prop, computed.getPropertyValue(prop));
                    }
                }
            }

            // Save as PNG
            btnSavePng.addEventListener('click', () => {
                btnSavePng.disabled = true;
                const originalText = btnSavePng.innerHTML;
                btnSavePng.textContent = 'Generating...';

                // Clone the front and back cards
                const frontCard = document.getElementById('visibleFront');
                const backCard = document.getElementById('visibleBack');

                const frontClone = frontCard.cloneNode(true);
                const backClone = backCard.cloneNode(true);

                // Apply all computed styles inline
                inlineAllStyles(frontCard, frontClone);
                inlineAllStyles(backCard, backClone);

                // Convert canvases to images (Transfer content from source)
                convertCanvasesToImages(frontCard, frontClone);
                convertCanvasesToImages(backCard, backClone);

                // Convert SVGs to <img> in clones
                convertSvgsToImages(frontClone);
                convertSvgsToImages(backClone);

                // Build a capture container
                const captureContainer = document.createElement('div');
                captureContainer.style.cssText = `
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 24px;
                    padding: 24px;
                    background: #f8fafc;
                    border-radius: 16px;
                    position: fixed;
                    left: 0;
                    top: 0;
                    z-index: -9999;
                    font-family: 'Outfit', sans-serif;
                `;

                // Reset flip-related transforms on clones
                frontClone.style.position = 'relative';
                frontClone.style.backfaceVisibility = 'visible';
                frontClone.style.transform = 'none';
                frontClone.style.width = '380px';
                frontClone.style.height = '240px';

                backClone.style.position = 'relative';
                backClone.style.backfaceVisibility = 'visible';
                backClone.style.transform = 'none';
                backClone.style.width = '380px';
                backClone.style.height = '240px';

                captureContainer.appendChild(frontClone);
                captureContainer.appendChild(backClone);
                document.body.appendChild(captureContainer);

                // Wait for images (profile photo, SVG replacements, Canvas replacements) to load
                const images = captureContainer.querySelectorAll('img');
                const imagePromises = Array.from(images).map(img => {
                    if (img.complete) return Promise.resolve();
                    return new Promise(resolve => {
                        img.onload = resolve;
                        img.onerror = resolve;
                    });
                });

                Promise.all(imagePromises).then(() => {
                    setTimeout(() => {
                        html2canvas(captureContainer, {
                            scale: 3,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: '#f8fafc',
                            logging: false
                        }).then(canvas => {
                            document.body.removeChild(captureContainer);

                            const link = document.createElement('a');
                            const studentId = document.getElementById('previewStudentId').textContent;
                            link.download = `Student_ID_${studentId}.png`;
                            link.href = canvas.toDataURL('image/png');
                            link.click();

                            btnSavePng.disabled = false;
                            btnSavePng.innerHTML = originalText;
                            showToast('ID card saved as PNG!', 'success');
                        }).catch(err => {
                            if (document.body.contains(captureContainer)) {
                                document.body.removeChild(captureContainer);
                            }
                            btnSavePng.disabled = false;
                            btnSavePng.innerHTML = originalText;
                            showToast('Failed to generate PNG. Please try again.', 'error');
                            console.error('html2canvas error:', err);
                        });
                    }, 500);
                });
            });
        });
    </script>
</body>

</html>