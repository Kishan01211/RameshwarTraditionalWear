<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/header.php';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Validation
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($message)) $errors[] = "Message is required";
    
    if (empty($errors)) {
        try {
            // Check if database connection exists
            if (!isset($pdo)) {
                throw new Exception("Database connection not available");
            }
            
            $stmt = $pdo->prepare("INSERT INTO contactus (name, email, phone, message) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$name, $email, $phone, $message]);
            
            if ($result) {
                $success_message = "Thank you for contacting us! We'll get back to you within 24 hours.";
                // Clear form data after successful submission
                $_POST = [];
            } else {
                $errors[] = "Failed to save your inquiry. Please try again.";
            }
        } catch (PDOException $e) {
            // Log the actual error for debugging (in production, don't show to user)
            error_log("Contact form PDO error: " . $e->getMessage());
            
            // Check if it's a table doesn't exist error
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Table") !== false) {
                $errors[] = "Database table missing. Please run the database migration script: add_contact_table.sql";
            } else {
                $errors[] = "Database error occurred. Please try again later.";
            }
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            $errors[] = "System error: " . $e->getMessage();
        }
    }
}
?>

<!-- Contact Page Title
<div class="row mb-4">
    <div class="col-12">
        <div class="bg-primary text-white text-center py-4 rounded">
            <h2 class="mb-2">Contact Us</h2>
            <p class="mb-0">Get in touch with Rameshwar Traditional Wear Rental</p>
        </div>
    </div>
</div> -->

<!-- Success/Error Messages -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Success!</strong> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Contact Information Cards -->
<div class="row mb-5">
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="contact-icon mb-3">
                    <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                </div>
                <h5 class="card-title text-primary">Visit Our Store</h5>
                <address class="mb-0">
                    Rameshwar Traditional Wear Rental,<br>
                    2nd Floor, Anand Libas,<br>
                    Railway Road,<br>
                    Rohtak - 124001,<br>
                    Haryana, India
                </address>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="contact-icon mb-3">
                    <i class="fas fa-envelope fa-2x text-primary"></i>
                </div>
                <h5 class="card-title text-primary">Email Us</h5>
                <p class="mb-1"><a href="mailto:rtwrs@gmail.com" class="text-decoration-none">rtwrs@gmail.com</a></p>
                <p class="mb-0"><a href="mailto:support@rtwrs.com" class="text-decoration-none">support@rtwrs.com</a></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="contact-icon mb-3">
                    <i class="fas fa-phone fa-2x text-primary"></i>
                </div>
                <h5 class="card-title text-primary">Call Us</h5>
                <p class="mb-1"><a href="tel:+917988766165" class="text-decoration-none">+91 79887 66165</a></p>
                <p class="mb-0"><a href="tel:+919876543210" class="text-decoration-none">+91 98765 43210</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Social Media Links -->
<div class="row mb-5">
    <div class="col-12 text-center">
        <h4 class="mb-4 text-primary">Follow Us</h4>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="https://www.facebook.com/rtwrs" class="btn btn-outline-primary" target="_blank">
                <i class="fab fa-facebook-f me-2"></i>Facebook
            </a>
            <a href="https://www.instagram.com/rtwrs_official/" class="btn btn-outline-danger" target="_blank">
                <i class="fab fa-instagram me-2"></i>Instagram
            </a>
            <a href="https://api.whatsapp.com/send?phone=917988766165" class="btn btn-outline-success" target="_blank">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a>
            <a href="https://twitter.com/rtwrs" class="btn btn-outline-info" target="_blank">
                <i class="fab fa-twitter me-2"></i>Twitter
            </a>
        </div>
    </div>
</div>

<!-- Contact Form -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">Send Us Your Message</h4>
                <p class="mb-0 mt-2 opacity-75">Have a question, need assistance with booking, or want to share feedback? We're here to help!</p>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" placeholder="+91 98765 43210">
                    </div>
                    <div class="mb-4">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Please provide detailed information about your inquiry..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Map Section -->
<div class="row mt-4 mb-0">
    <div class="col-12">
        <div class="card shadow border-0 mb-0">
            <div class="card-header bg-secondary text-white text-center">
                <h4 class="mb-0">Find Us</h4>
                <p class="mb-0 mt-2 opacity-75">Visit our showroom to experience our collection firsthand</p>
            </div>
            <div class="card-body p-0">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3719.628543861948!2d72.14717573449731!3d21.768237927845714!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395f5a7c2a002489%3A0x88ac1e64daa46b6a!2sRameshwar%20Traditional%20Wear!5e0!3m2!1sen!2sin!4v1713500000000!5m2!1sen!2sin"
                    width="100%"
                    height="100%"
                    style="border:0; min-height:420px; filter: grayscale(10%) contrast(1.1); background:transparent;"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Rameshwar Traditional Wear Location on Map">
                </iframe>
                <div class="text-center py-2">
                  <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener" href="https://www.google.com/maps/place/Rameshwar+Traditional+Wear/@21.7682379,72.1475299,17z/">
                    Open in Google Maps
                  </a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<?php require_once '../includes/footer.php'; ?>
<!-- End of container from header.php -->
