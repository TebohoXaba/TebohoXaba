<?php
// Function to calculate the distance between two locations using the Google Distance Matrix API
function getDistance($origin, $destination) {
    $apiKey = 'api'; // Replace with your actual Google Maps API key
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . urlencode($origin) . "&destinations=" . urlencode($destination) . "&key=" . $apiKey;

    // Use cURL to fetch data from Google Maps API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // For testing only, avoid disabling SSL verification in production

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false; // Handle cURL errors
    }

    $data = json_decode($response, true);

    // Validate JSON response structure
    if (
        isset($data['rows'][0]['elements'][0]['distance']) &&
        $data['rows'][0]['elements'][0]['status'] === 'OK'
    ) {
        return [
            'distanceText' => $data['rows'][0]['elements'][0]['distance']['text'], // Distance as text (e.g., "1,397 km")
            'distanceValue' => $data['rows'][0]['elements'][0]['distance']['value'], // Distance in meters
        ];
    }

    return false; // Return false if API call fails or no valid data is returned
}

// Initialize variables
$result = null;
$error = null;
$shippingCost = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = trim($_POST['origin']); // Sanitize user input
    $destination = trim($_POST['destination']);

    if (empty($origin) || empty($destination)) {
        $error = "Both origin and destination must be provided.";
    } else {
        $result = getDistance($origin, $destination);
        if ($result) {
            // Calculate shipping cost: convert meters to kilometers and apply appropriate rate
            $distanceInKm = $result['distanceValue'] / 1000; // Convert meters to kilometers
            if ($distanceInKm < 30) {
                $shippingCost = $distanceInKm * 25; // 25 rands per km for less than 30 km
            } elseif ($distanceInKm < 50) {
                $shippingCost = $distanceInKm * 22; // 22 rands per km for less than 50 km
            } else {
                $shippingCost = $distanceInKm * 20; // 20 rands per km for more than 50 km
            }
        } else {
            $error = "Unable to calculate the distance. Please check your input or try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address Autocomplete with Distance and Shipping Cost</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB5cy0NoBdzweXFu0w0vjpr9itsGHTzSNk&libraries=places"></script>
    <style>
        .autocomplete-input {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Address Autocomplete with Distance and Shipping Cost</h1>
        <form method="POST">
            <div class="mb-3 autocomplete-input">
                <label for="origin" class="form-label">Origin</label>
                <input type="text" class="form-control" id="origin" name="origin" placeholder="Enter starting location" required>
            </div>
            <div class="mb-3 autocomplete-input">
                <label for="destination" class="form-label">Destination</label>
                <input type="text" class="form-control" id="destination" name="destination" placeholder="Enter destination" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>

        <?php if ($result): ?>
            <div class="alert alert-success mt-4">
                <p>The distance from <strong><?= htmlspecialchars($origin); ?></strong> to <strong><?= htmlspecialchars($destination); ?></strong> is <strong><?= $result['distanceText']; ?></strong>.</p>
                <p>The shipping cost is <strong>R <?= number_format($shippingCost, 2); ?></strong>.</p>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger mt-4">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function initAutocomplete() {
        const originInput = document.getElementById('origin');
        const destinationInput = document.getElementById('destination');
        
        const options = {
            fields: ['formatted_address'],
            types: ['geocode'],
            componentRestrictions: { country: 'za' } // Restrict to South Africa (country code: 'za')
        };

        new google.maps.places.Autocomplete(originInput, options);
        new google.maps.places.Autocomplete(destinationInput, options);
    }

    google.maps.event.addDomListener(window, 'load', initAutocomplete);
    </script>

</body>
</html>
