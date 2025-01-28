<?php
header('Content-Type: application/json');

if (!isset($_GET['origin']) || !isset($_GET['destination'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$origin = urlencode($_GET['origin']);
$destination = urlencode($_GET['destination']);

// Your Google Maps API Key
$apiKey = 'AIzaSyB5cy0NoBdzweXFu0w0vjpr9itsGHTzSNk';

// Google Maps Distance Matrix API URL
$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origin&destinations=$destination&key=$apiKey";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['status'] === 'OK') {
    $rows = $data['rows'][0]['elements'][0];
    if ($rows['status'] === 'OK') {
        $distanceInMeters = $rows['distance']['value'] ?? 0; // Handle missing values
        $distanceInKm = $distanceInMeters / 1000;
        $cost = $distanceInKm * 20; // Cost calculation

        echo json_encode([
            'success' => true,
            'distance' => round($distanceInKm, 2),
            'cost' => round($cost, 2)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Could not calculate distance.',
            'debug' => $rows // Log the specific response for debugging
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error with Distance Matrix API',
        'debug' => $data // Log the API response for debugging
    ]);
}
?>
