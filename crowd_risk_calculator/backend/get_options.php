<?php
header('Content-Type: application/json');

$csv_path = '../frontend/csv/KSRTC_Merged_Final.csv';

if (!file_exists($csv_path)) {
    echo json_encode(['error' => 'CSV file not found at path: ' . $csv_path]);
    exit;
}

$destinations = [];
$slots = [];

if (($handle = fopen($csv_path, "r")) !== FALSE) {
    // Read headers dynamically to identify index maps accurately
    $headers = fgetcsv($handle, 1000, ",");
    $dest_idx = array_search('Destination', $headers);
    $slot_idx = array_search('Slot Label', $headers);
    
    if ($dest_idx === false || $slot_idx === false) {
        echo json_encode(['error' => 'Required columns not found in CSV.']);
        exit;
    }
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (isset($data[$dest_idx]) && trim($data[$dest_idx]) !== '') {
            $destinations[trim($data[$dest_idx])] = true;
        }
        if (isset($data[$slot_idx]) && trim($data[$slot_idx]) !== '') {
            $slots[trim($data[$slot_idx])] = true;
        }
    }
    fclose($handle);
}

$dest_list = array_keys($destinations);
$slot_list = array_keys($slots);

// Alpha sorting for destinations
sort($dest_list);

// Chronological custom sorting for Time Slots
$defined_slots = ["2 AM - 6 AM", "6 AM - 10 AM", "10 AM - 2 PM", "2 PM - 6 PM", "6 PM - 10 PM", "10 PM - 2 AM"];
usort($slot_list, function($a, $b) use ($defined_slots) {
    $pos_a = array_search($a, $defined_slots);
    $pos_b = array_search($b, $defined_slots);
    $pos_a = ($pos_a === false) ? 999 : $pos_a;
    $pos_b = ($pos_b === false) ? 999 : $pos_b;
    return $pos_a - $pos_b;
});

echo json_encode([
    'destinations' => $dest_list,
    'slots' => $slot_list
]);
?>