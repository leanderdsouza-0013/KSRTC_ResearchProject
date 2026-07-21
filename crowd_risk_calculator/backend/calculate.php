<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

// Fetch requests safely via raw payload body or typical Form Data
$input = json_decode(file_get_contents('php://input'), true);
$destination = isset($input['destination']) ? trim($input['destination']) : (isset($_POST['destination']) ? trim($_POST['destination']) : '');
$slot = isset($input['slot']) ? trim($input['slot']) : (isset($_POST['slot']) ? trim($_POST['slot']) : '');

if (empty($destination) || empty($slot)) {
    echo json_encode(['error' => 'Please select both Destination and Time Slot parameters.']);
    exit;
}

$csv_path = '../frontend/csv/KSRTC_Merged_Final.csv';

if (!file_exists($csv_path)) {
    echo json_encode(['error' => 'Data source table not found.']);
    exit;
}

$result = null;

if (($handle = fopen($csv_path, "r")) !== FALSE) {
    $headers = fgetcsv($handle, 1000, ",");
    
    // Position lookup via structural mapping to guard against shifting columns
    $dest_idx = array_search('Destination', $headers);
    $slot_idx = array_search('Slot Label', $headers);
    $pop_idx = array_search('Population_Millions', $headers);
    $urban_idx = array_search('Urbanisation Level', $headers);
    $interstate_idx = array_search('Interstate_Flag', $headers);
    $time_score_idx = array_search('Time_Score', $headers);
    $risk_score_idx = array_search('Crowd_Risk_Score', $headers);
    $risk_level_idx = array_search('Crowd_Risk_Level', $headers);
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($data[$dest_idx] === $destination && $data[$slot_idx] === $slot) {
            $result = [
                'destination' => $data[$dest_idx],
                'slot' => $data[$slot_idx],
                'population' => $data[$pop_idx],
                'urbanisation_level' => $data[$urban_idx],
                'interstate_flag' => $data[$interstate_idx],
                'time_score' => $data[$time_score_idx],
                'crowd_risk_score' => round((float)$data[$risk_score_idx], 3),
                'crowd_risk_level' => $data[$risk_level_idx]
            ];
            break; // Stop iteration immediately upon finding target match 
        }
    }
    fclose($handle);
}

if ($result) {
    echo json_encode(['success' => true, 'data' => $result]);
} else {
    echo json_encode(['success' => false, 'error' => 'No historical data matches this specific schedule entry.']);
}
?>