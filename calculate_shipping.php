<?php
include 'header.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header('Location: login.php');
    exit;
}

// Function to calculate the distance between two locations using the Google Distance Matrix API
function getDistance($origin, $destination) {
    $apiKey = 'AIzaSyCRcZkXfDj0NzM0ADEqIz6ffudm53NMZuw'; // Replace with your actual Google Maps API key
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . urlencode($origin) . "&destinations=" . urlencode($destination) . "&key=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false;
    }

    $data = json_decode($response, true);

    if (
        isset($data['rows'][0]['elements'][0]['distance']) &&
        $data['rows'][0]['elements'][0]['status'] === 'OK'
    ) {
        return [
            'distanceText' => $data['rows'][0]['elements'][0]['distance']['text'],
            'distanceValue' => $data['rows'][0]['elements'][0]['distance']['value'],
        ];
    }

    return false;
}

$cost = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $businessAddress = "4604 Mhandzela St, Chiawelo, Soweto, South Africa";

    // Fetch distance to business and destination
    $originToBusiness = getDistance($origin, $businessAddress);
    $originToDestination = getDistance($origin, $destination);

    // Debugging: Check API Response and Distance
    if ($originToBusiness) {
        error_log('Distance to Business: ' . $originToBusiness['distanceValue'] . ' meters');
    }
    if ($originToDestination) {
        error_log('Distance to Destination: ' . $originToDestination['distanceValue'] . ' meters');
    }

    if ($originToBusiness && $originToDestination) {
        $distanceToBusiness_km = $originToBusiness['distanceValue'] / 1000;
        $distanceToDestination_km = $originToDestination['distanceValue'] / 1000;

        // Debugging: Log the calculated distance
        error_log('Distance to Business (km): ' . $distanceToBusiness_km);
        error_log('Distance to Destination (km): ' . $distanceToDestination_km);

        // Check if the distance from the origin to the business address exceeds 100 km
        if ($distanceToBusiness_km > 100) {
            $_SESSION['error'] = "The origin address is more than 100 km away from our business address (4604 Mhandzela St, Chiawelo, Soweto, South Africa). Please choose a closer location.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        // Determine the additional fee based on distance to the business address
        if ($distanceToDestination_km <= 5) {
            $additionalFee = 150;
        } elseif ($distanceToDestination_km <= 10) {
            $additionalFee = 200;
        } elseif ($distanceToDestination_km <= 20) {
            $additionalFee = 300;
        } elseif ($distanceToDestination_km <= 30) {
            $additionalFee = 400;
        } elseif ($distanceToDestination_km <= 40) {
            $additionalFee = 500;
        } elseif ($distanceToDestination_km <= 60) {
            $additionalFee = 600;
        } elseif ($distanceToDestination_km <= 80) {
            $additionalFee = 700;
        } elseif ($distanceToDestination_km <= 100) {
            $additionalFee = 800;
        } else {
            $additionalFee = 0; // No additional fee for distances beyond 100 km
        }

        // Calculate the shipping cost to the destination
        $shippingCost = $distanceToDestination_km * 20;

        // Total cost is the sum of the shipping cost and the additional fee
        $cost = $shippingCost + $additionalFee;
    } else {
        $cost = null;
    }
}
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Calculate Shipping Cost</h1>
        <p class="text-muted">Fill in the details below to calculate your shipping cost.</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger animate__animated animate__shakeX"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <form method="POST" class="shadow p-4 rounded bg-light">
                <div class="mb-3">
                    <label for="origin" class="form-label">Origin</label>
                    <input type="text" id="origin" name="origin" class="form-control" required placeholder="Enter origin location">
                </div>
                <div class="mb-3">
                    <label for="destination" class="form-label">Destination</label>
                    <input type="text" id="destination" name="destination" class="form-control" required placeholder="Enter destination location">
                </div>
                <button type="submit" class="btn btn-primary w-100">Calculate</button>
            </form>
        </div>
    </div>

    <?php if ($cost !== null): ?>
        <div class="row justify-content-center mt-5">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h3 class="card-title text-success">Estimated Shipping Cost</h3>
                        <p>From: <strong><?= htmlspecialchars($origin); ?></strong></p>
                        <p>To: <strong><?= htmlspecialchars($destination); ?></strong></p>
                        <p>Distance to Business: <strong><?= htmlspecialchars($originToBusiness['distanceText']); ?></strong></p>
                        <p>Distance to Destination: <strong><?= htmlspecialchars($originToDestination['distanceText']); ?></strong></p>
                        <p class="fs-5">Total Cost: <strong>ZAR <?= number_format($cost, 2); ?></strong></p>
                        <a href="make_appointment.php?origin=<?= urlencode($origin); ?>&destination=<?= urlencode($destination); ?>&distance_km=<?= $distanceToDestination_km; ?>&cost=<?= $cost; ?>&additional_fee=<?= $additionalFee; ?>" 
                            class="btn btn-success w-100 mt-3">Book Appointment</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCRcZkXfDj0NzM0ADEqIz6ffudm53NMZuw&libraries=places"></script>
<script>
    // Include the Google Maps API for Autocomplete
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

    // Initialize Autocomplete when the page loads
    google.maps.event.addDomListener(window, 'load', initAutocomplete);
</script>

<?php include 'footer.php'; ?>
