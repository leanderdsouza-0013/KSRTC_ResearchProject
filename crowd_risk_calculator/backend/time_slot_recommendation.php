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
            if (!empty(trim($data[$from_idx]))) $origins[trim($data[$from_idx])] = true;
            if (!empty(trim($data[$dest_idx]))) $destinations[trim($data[$dest_idx])] = true;
        }
        fclose($handle);
    }

    $from_list = array_keys($origins);
    $to_list = array_keys($destinations);
    sort($from_list);
    sort($to_list);

    echo json_encode(['success' => true, 'origins' => $from_list, 'destinations' => $to_list]);
    exit;
}

// ACTION 2: Generate Chronological Pairs (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $from = isset($input['from']) ? trim($input['from']) : '';
    $to = isset($input['to']) ? trim($input['to']) : '';

    if (empty($from) || empty($to)) {
        echo json_encode(['success' => false, 'error' => 'Please select both origin and destination.']);
        exit;
    }

    // Direct chronological rotation map
    $chronological_map = [
        '2 AM - 6 AM'   => '6 AM - 10 AM',
        '6 AM - 10 AM'  => '10 AM - 2 PM',
        '10 AM - 2 PM'  => '2 PM - 6 PM',
        '2 PM - 6 PM'   => '6 PM - 10 PM',
        '6 PM - 10 PM'  => '10 PM - 2 AM',
        '10 PM - 2 AM'  => '2 AM - 6 AM'
    ];

    $route_slots = [];
    $district = '';
    $population = '';

    if (($handle = fopen($csv_path, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        
        $from_idx = array_search('From', $headers);
        $dest_idx = array_search('Destination', $headers);
        $district_idx = array_search('District', $headers);
        $pop_idx = array_search('Population_Millions', $headers);
        $slot_idx = array_search('Slot Label', $headers);
        $score_idx = array_search('Crowd_Risk_Score', $headers);
        $level_idx = array_search('Crowd_Risk_Level', $headers);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (strcasecmp($data[$from_idx], $from) === 0 && strcasecmp($data[$dest_idx], $to) === 0) {
                $slot_name = trim($data[$slot_idx]);
                $slot_name = preg_replace('/\s+/', ' ', $slot_name); // Normalize spacing
                
                if (empty($district)) {
                    $district = trim($data[$district_idx]);
                    $population = trim($data[$pop_idx]);
                }

                if (!isset($route_slots[$slot_name])) {
                    $route_slots[$slot_name] = [
                        'time_slot' => $slot_name,
                        'crowd_risk_score' => round((float)$data[$score_idx], 3),
                        'crowd_risk_level' => trim($data[$level_idx])
                    ];
                }
            }
        }
        fclose($handle);
    }

    if (empty($route_slots)) {
        echo json_encode(['success' => false, 'error' => "No operational data found for route: $from to $to."]);
        exit;
    }

    // Build the pair comparison matrix sequentially
    $comparisons = [];
    $order = ['2 AM - 6 AM', '6 AM - 10 AM', '10 AM - 2 PM', '2 PM - 6 PM', '6 PM - 10 PM', '10 PM - 2 AM'];
    
    foreach ($order as $current_slot) {
        if (isset($route_slots[$current_slot])) {
            $next_slot_name = $chronological_map[$current_slot];
            
            if (isset($route_slots[$next_slot_name])) {
                $next_slot_data = $route_slots[$next_slot_name];
            } else {
                // Next slot is not actively scheduled in the dataset for this route
                $next_slot_data = [
                    'time_slot' => $next_slot_name,
                    'crowd_risk_score' => 'N/A',
                    'crowd_risk_level' => 'No Scheduled Bus'
                ];
            }

            $comparisons[] = [
                'original' => $route_slots[$current_slot],
                'next_available' => $next_slot_data
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'from' => $from,
        'to' => $to,
        'district' => $district,
        'population' => $population,
        'comparisons' => $comparisons
    ]);
    exit;
}
?>