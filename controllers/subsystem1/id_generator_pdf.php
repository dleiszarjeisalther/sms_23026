<?php
// Prevent any accidental output before PDF streaming
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Ensure only registrars can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
    if (ob_get_length()) ob_end_clean();
    die('Unauthorized access.');
}

// Support single id or comma-separated ids
$idsParam = $_GET['ids'] ?? $_GET['id'] ?? null;
if (!$idsParam) {
    if (ob_get_length()) ob_end_clean();
    die('Student ID is required.');
}

// Parse into array of IDs
$studentIds = array_filter(array_map('trim', explode(',', $idsParam)));
if (empty($studentIds)) {
    if (ob_get_length()) ob_end_clean();
    die('Student ID is required.');
}

// Database Connection & Configuration
require_once __DIR__ . '/../../models/config.php';
global $pdo;

// Build SVG logo as base64 data URI since Dompdf cannot render inline <svg> elements
$logoSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#059669"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z"></path><path d="M4.5 15.5c.5-1.5 2-2.5 4-2.5s3.5 1 4 2.5a5 5 0 01-8 0z"></path></svg>';
$logoBase64 = 'data:image/svg+xml;base64,' . base64_encode($logoSvg);

// QR context (reusable)
$qrContext = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);

/**
 * Helper to fetch remote images (e.g. QR code) with cURL fallback
 */
if (!function_exists('fetchRemoteData')) {
    function fetchRemoteData($url)
    {
        // Try file_get_contents first if allowed
        if (ini_get('allow_url_fopen')) {
            $context = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
            $data = @file_get_contents($url, false, $context);
            if ($data !== false && !empty($data)) return $data;
        }

        // Fallback to cURL
        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            $data = curl_exec($ch);
            curl_close($ch);
            if ($data !== false && !empty($data)) return $data;
        }

        return null;
    }
}

// Fetch all students and build card HTML for each
$cardsHtml = '';
$fetchedCount = 0;

foreach ($studentIds as $studentId) {
    $query = "SELECT s.*, e.academic_year, e.program as enrollment_program
              FROM students s 
              LEFT JOIN enrollments e ON s.student_id = e.student_id 
              WHERE s.student_id = :student_id 
              ORDER BY e.id DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['student_id' => $studentId]);
    $student = $stmt->fetch();

    if (!$student) continue;
    $fetchedCount++;

    // Prepare Data
    $fullName = mb_strtoupper($student['first_name'] . ' ' . $student['last_name']);
    $program = mb_strtoupper($student['program'] ?: $student['enrollment_program'] ?: 'N/A');
    $idNo = $student['student_id'];
    $section = 'SEC-' . (floor(time() / (60 * 60 * 24)) % 9000 + 1000);

    $syVal = trim($student['academic_year'] ?? '');
    $sy = !empty($syVal) ? "S.Y. $syVal" : "S.Y. 2024 - 2025";

    $guardian = mb_strtoupper($student['guardian_name'] ?: 'N/A');
    $address = $student['address'] ?: 'N/A';
    $contact = $student['guardian_contact'] ?: $student['contact_number'] ?: 'N/A';


    // QR Code
    $qrUrl = "http://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($idNo);
    $qrBase64 = '';
    $qrData = fetchRemoteData($qrUrl);
    if ($qrData) {
        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrData);
    }

    // Photo Processing
    $photoBase64 = '';
    if (!empty($student['profile_image'])) {
        $dbPath = $student['profile_image'];

        // Normalize the path by removing current base URL parts if present
        $cleanPath = $dbPath;
        if (defined('BASE_URL') && !empty(BASE_URL) && BASE_URL !== '/') {
            $baseForRegex = preg_quote(trim(BASE_URL, '/'), '/');
            $cleanPath = preg_replace('/^\/?' . $baseForRegex . '\//', '', $dbPath);
        }
        $cleanPath = ltrim($cleanPath, '/');

        // Robust base directory detection
        $baseDir = realpath(__DIR__ . '/../../'); // Root folder

        $possiblePaths = [
            $baseDir . '/' . $cleanPath,
            $baseDir . '/resources/' . $cleanPath,
            $baseDir . '/resources/uploads/profiles/' . basename($cleanPath),
            $_SERVER['DOCUMENT_ROOT'] . (defined('BASE_URL') ? BASE_URL : '') . '/' . $cleanPath,
            $_SERVER['DOCUMENT_ROOT'] . '/' . $cleanPath
        ];

        foreach ($possiblePaths as $path) {
            $path = str_replace(['\\', '//'], '/', $path);
            if (!empty($path) && file_exists($path) && !is_dir($path)) {
                $imgData = @file_get_contents($path);
                if ($imgData && strlen($imgData) > 0) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($imgData);
                    $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imgData);
                    break;
                }
            }
        }
    }

    // Fallback if no photo found or image loading failed
    if (empty($photoBase64)) {
        $fallbackIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>';
        $photoBase64 = 'data:image/svg+xml;base64,' . base64_encode($fallbackIcon);
    }

    // Build card pair (front + back) for this student
    $cardsHtml .= "
    <!-- Front Card - $idNo -->
    <div class='card front'>
        <div class='header-bar'>
            <img class='logo-icon' src='$logoBase64' alt='Logo'>
            <div class='school-info'>
                <h1 class='school-name'>SIMS ACADEMY</h1>
                <div class='school-campus'>Metro City, Campus</div>
            </div>
        </div>

        <div class='photo-box'>
            " . ($photoBase64 ? "<img src='$photoBase64'>" : "") . "
        </div>

        <div class='details-box'>
            <div class='program-tag'>$program</div>
            <div class='student-name-text'>$fullName</div>

            <div class='stat-container'>
                <div class='stat-group'>
                    <div class='stat-label'>ID NO.</div>
                    <div class='stat-value'>$idNo</div>
                </div>
                <div class='stat-group'>
                    <div class='stat-label'>SECTION</div>
                    <div class='stat-value'>$section</div>
                </div>
            </div>
        </div>

        <div class='front-footer'>
            <span class='footer-text'>$sy</span>
        </div>
    </div>

    <!-- Back Card - $idNo -->
    <div class='card back'>
        <div class='emergency-header'>
            <div class='emergency-tag'>In Case of Emergency</div>
            <div class='guardian-name'>$guardian</div>
        </div>

        <div class='info-section'>
            <div class='info-row'>
                <div class='info-label'>Address</div>
                <div class='info-value'>$address</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Contact</div>
                <div class='info-value'>$contact</div>
            </div>
        </div>

        <div class='barcode-section'>
            <div class='disclaimer-box'>
                Property of SIMS. If found, please return to any registrar's office.
            </div>
            <div class='qr-container'>
                " . ($qrBase64 ? "<img src='$qrBase64'>" : "") . "
            </div>
        </div>
    </div>
    ";
}

if ($fetchedCount === 0) {
    if (ob_get_length()) ob_end_clean();
    die('No student records found.');
}

// Determine page size: single student = portrait card size, multiple = A4
$isMultiple = $fetchedCount > 1;
$pageSize = $isMultiple ? 'A4 portrait' : '85.6mm 125mm';
$bodyPadding = $isMultiple ? '10mm' : '10mm 0';

// Full HTML
$html = "<!DOCTYPE html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <style>
        @page {
            margin: 0;
            size: $pageSize;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: $bodyPadding;
            font-family: 'Helvetica', sans-serif;
            background: #f8fafc;
        }
        .card {
            width: 80mm;
            height: 53mm;
            position: relative;
            overflow: hidden;
            margin: 0 auto 8mm auto;
            border-radius: 4.5mm;
            background: #ffffff;
            border: 0.1mm solid #e2e8f0;
        }

        /* ---- FRONT CARD ---- */
        .front {
            border-top: 2mm solid #059669;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .header-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 13mm;
            padding: 2.5mm 5mm;
            background: rgba(255, 255, 255, 0.8);
        }

        .logo-icon {
            display: inline-block;
            width: 8mm;
            height: 8mm;
            border-radius: 2mm;
            vertical-align: middle;
            margin-right: 2mm;
        }

        .school-info {
            display: inline-block;
            vertical-align: middle;
        }
        .school-name {
            font-size: 11pt;
            font-weight: 900;
            color: #1e293b;
            margin: 0;
            line-height: 1;
        }
        .school-campus {
            font-size: 5.5pt;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8mm;
            margin-top: 0.8mm;
        }

        .photo-box {
            position: absolute;
            top: 15mm;
            left: 5mm;
            width: 21mm;
            height: 23mm;
            border: 0.5mm solid #e2e8f0;
            border-radius: 3mm;
            background: #f1f5f9;
            overflow: hidden;
        }
        .photo-box img { width: 100%; height: 100%; display: block; }

        .details-box { position: absolute; top: 15.5mm; left: 29mm; width: 47mm; }
        .program-tag {
            font-size: 5.8pt;
            color: #059669;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3mm;
            margin-bottom: 0.5mm;
        }
        .student-name-text {
            font-size: 12.5pt;
            font-weight: 900;
            color: #1e293b;
            line-height: 1.1;
            margin-bottom: 3mm;
        }

        .stat-container { width: 100%; }
        .stat-group { display: inline-block; width: 48%; vertical-align: top; }
        .stat-label {
            font-size: 4.8pt;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
        }
        .stat-value {
            font-size: 8pt;
            font-weight: 700;
            color: #475569;
        }

        .front-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 6mm;
            background-color: #1e293b;
            color: #ffffff;
            text-align: center;
        }
        .footer-text {
            display: block;
            margin-top: 1.5mm;
            font-size: 6pt;
            font-weight: 700;
            letter-spacing: 1.5mm;
        }

        /* ---- BACK CARD ---- */
        .back {
            background-color: #1e293b;
            color: #ffffff;
            border: none;
        }

        .emergency-header {
            position: absolute;
            top: 5mm;
            left: 6mm;
        }
        .emergency-tag {
            font-size: 5.5pt;
            color: #34d399;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5mm;
            margin-bottom: 1.5mm;
        }
        .guardian-name {
            font-size: 9.5pt;
            font-weight: 700;
            color: #ffffff;
        }

        .info-section {
            position: absolute;
            top: 16mm;
            left: 6mm;
            width: 68mm;
        }
        .info-row { margin-bottom: 2.5mm; }
        .info-label {
            font-size: 4.8pt;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
        }
        .info-value {
            font-size: 8pt;
            font-weight: 600;
            color: #f8fafc;
            line-height: 1.3;
        }

        .barcode-section {
            position: absolute;
            bottom: 4mm;
            left: 6mm;
            width: 68mm;
            height: 18mm;
            border-top: 0.1mm solid rgba(255, 255, 255, 0.1);
            padding-top: 3mm;
        }
        .qr-container {
            float: right;
            background: #ffffff;
            width: 16mm;
            height: 16mm;
            border-radius: 2.5mm;
            padding: 1.5mm;
        }
        .qr-container img { width: 100%; height: 100%; }

        .disclaimer-box {
            float: left;
            width: 46mm;
            font-size: 4.5pt;
            color: rgba(255, 255, 255, 0.45);
            line-height: 1.4;
            padding-top: 3mm;
        }
    </style>
</head>
<body>
    $cardsHtml
</body>
</html>";

// Setup Dompdf
$dom_options = new Options();
$dom_options->set('isRemoteEnabled', true);
$dom_options->set('isHtml5ParserEnabled', true);
$dom_options->set('defaultFont', 'Helvetica');
$dom_options->set('dpi', 150);
$dompdf = new Dompdf($dom_options);

// Final safety check: Clean any stray output
if (ob_get_length()) ob_end_clean();

$dompdf->loadHtml($html);
$dompdf->render();

// Set appropriate headers for PDF
$filename = $isMultiple ? 'ID_Cards_Batch.pdf' : 'ID_Card_' . $studentIds[0] . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

echo $dompdf->output();
exit;
