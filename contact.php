<?php include 'header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center text-primary">Contact Us</h2>

    <p class="lead text-justify">
        If you have any questions or inquiries, feel free to reach out to us. Our team at <strong>Z X Fleet Partners</strong> , Your reliable 3P Logistics Provider is ready to assist you with any information or support you need.
    </p>

    <h4 class="mt-4 text-success">Our Contact Information</h4>
    <ul class="list-group list-group-flush mb-4">
        <li class="list-group-item"><strong>Email:</strong> <a href="mailto:customer.service@zxfleet.co.za">customer.service@zxfleet.co.za</a></li>
        <li class="list-group-item"><strong>Phone/WhatsApp:</strong> <a href="tel:+27640085277">+27 64 008 5277</a></li>
    </ul>

    <h4 class="text-danger">Send Us a Message</h4>
    
    <?php 
    // Initialize variables to avoid errors if the form is submitted
    $successMessage = '';
    $errorMessage = '';

    // Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve the form input data
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $message = trim($_POST['message']);
        
        // Validate the form data
        if (empty($name) || empty($email) || empty($message)) {
            $errorMessage = "All fields are required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email format!";
        } else {
            // Send email (adjust the recipient address as needed)
            $to = "customer.service@zxfleet.co.za";
            $subject = "New Inquiry from $name";
            $body = "Name: $name\nEmail: $email\nMessage:\n$message";
            $headers = "From: $email\r\n" . "Reply-To: $email\r\n";

            // Try to send the email
            if (mail($to, $subject, $body, $headers)) {
                $successMessage = "Thank you for getting in touch! We will get back to you shortly.";
            } else {
                $errorMessage = "Oops! Something went wrong and we couldn't send your message.";
            }
        }
    }
    ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
    <?php elseif ($errorMessage): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <form action="contact.php" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="name" class="form-label">Your Name:</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Your Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Your Message:</label>
            <textarea id="message" name="message" class="form-control" rows="5" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>

<?php include 'footer.php'; ?>
