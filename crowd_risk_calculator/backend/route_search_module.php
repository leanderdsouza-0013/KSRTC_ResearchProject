<?php
header('Content-Type: application/json');

$csv_path = '../frontend/csv/KSRTC_Merged_Final.csv';

if (!file_exists($csv_path)) {
    echo json_encode(['success' => false, 'error' => 'Data source CSV file not found.']);
    exit;
}

// ACTION 1: Populate Dropdowns (GET Request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $origins = [];
    $destinations = [];

    if (($handle = fopen($csv_path, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        $from_idx = array_search('From', $headers);
        $dest_idx = array_search('Destination', $headers);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (!empty(trim($data[$from_idx]))) {
                $origins[trim($data[$from_idx])] = true;
            }
            if (!empty(trim($data[$dest_idx]))) {
                $destinations[trim($data[$dest_idx])] = true;
            }
        }
        fclose($handle);
    }

    $from_list = array_keys($origins);
    $to_list = array_keys($destinations);
    sort($from_list);
    sort($to_list);

    echo json_encode([
        'success' => true,
        'origins' => $from_list,
        'destinations' => $to_list
    ]);
    exit;
}

// ACTION 2: Search Route Details (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $from = isset($input['from']) ? trim($input['from']) : '';
    $to = isset($input['to']) ? trim($input['to']) : '';

    if (empty($from) || empty($to)) {
        echo json_encode(['success' => false, 'error' => 'Please select both origin and destination points.']);
        exit;
    }

    $matches = [];

    if (($handle = fopen($csv_path, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        
        // Dynamically find header indices to prevent misalignment
        $from_idx = array_search('From', $headers);
        $dest_idx = array_search('Destination', $headers);
        $district_idx = array_search('District', $headers);
        $pop_idx = array_search('Population_Millions', $headers);
        $slot_idx = array_search('Slot Label', $headers);
        $risk_score_idx = array_search('Crowd_Risk_Score', $headers);
        $risk_level_idx = array_search('Crowd_Risk_Level', $headers);
        $service_idx = array_search('Service Class', $headers);
        $via_idx = array_search('Via Place', $headers);
        $time_idx = array_search('Time', $headers);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Case-insensitive matching to ensure accurate route filtering
            if (strcasecmp($data[$from_idx], $from) === 0 && strcasecmp($data[$dest_idx], $to) === 0) {
                $matches[] = [
                    'district' => $data[$district_idx],
                    'population' => $data[$pop_idx],
                    'time_slot' => $data[$slot_idx],
                    'crowd_risk_score' => round((float)$data[$risk_score_idx], 3),
                    'crowd_risk_level' => $data[$risk_level_idx],
                    'service_class' => $data[$service_idx],
                    'via' => $data[$via_idx],
                    'departure_time' => $data[$time_idx]
                ];
            }
        }
        fclose($handle);
    }

    if (!empty($matches)) {
        echo json_encode(['success' => true, 'data' => $matches]);
    } else {
        echo json_encode(['success' => false, 'error' => "No scheduled routes found traveling from '$from' to '$to'."]);
    }
    exit;
}
?>