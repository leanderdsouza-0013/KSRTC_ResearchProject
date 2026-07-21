<?php
/**
 * Smart Data Download Handler - Nested Folder Matrix Fixed
 */

$possible_paths = [
    __DIR__ . '/KSRTC_Merged_Final.csv',                                      // Look inside local folder (frontend/csv/)
    __DIR__ . '/../KSRTC_Merged_Final.csv',                                   // Look one folder up (frontend/)
    __DIR__ . '/../../KSRTC_Merged_Final.csv',                                // Look two folders up (crowd_risk_calculator/)
    __DIR__ . '/../../../merged/KSRTC_Merged_Final.csv',                      // Look three folders up inside main master merged/
];

$filepath = null;

foreach ($possible_paths as $path) {
    if (file_exists($path) && is_readable($path) && !is_dir($path)) {
        $filepath = $path;
        break;
    }
}

if ($filepath !== null) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    
    readfile($filepath);
    exit;
} else {
    http_response_code(404);
    echo "<div style='font-family: \"Montserrat\", -apple-system, sans-serif; padding: 40px 20px; max-width: 650px; margin: 60px auto; background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-align: center;'>";
    echo "<h2 style='color: #dc3545; font-size: 24px; margin-bottom: 12px;'>File Synchronization Gap Detected</h2>";
    echo "<p style='color: #333333; font-size: 15px; margin-bottom: 25px;'>The web layer script was unable to safely retrieve <strong>KSRTC_Merged_Final.csv</strong>.</p>";
    
    echo "<div style='background: #f8f9fa; border: 1px solid #e9ecef; padding: 18px; border-radius: 6px; text-align: left; font-size: 13px; font-family: monospace; color: #495057; margin-bottom: 25px; line-height: 1.6;'>";
    echo "<strong>Active Script File Path:</strong> " . htmlspecialchars(__FILE__) . "<br><br>";
    echo "<strong>Scanned System Matrix Targets:</strong><br>";
    foreach ($possible_paths as $path) {
        $resolved_status = file_exists($path) ? 'Readable' : 'Not Found';
        echo "<span style='color: #dc3545;'>✕</span> " . htmlspecialchars($path) . " — <strong>(" . $resolved_status . ")</strong><br>";
    }
    echo "</div>";
    
    echo "<a href='index.php' style='display: inline-block; margin-top: 25px; background: #009E60; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;'>Return to Dashboard</a>";
    echo "</div>";
}
?>