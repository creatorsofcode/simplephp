<?php
require_once __DIR__ . '/includes/Security.php';
header('Content-Type: application/json');

// Honeypot: hidden field only bots fill in. Pretend success without sending.
if (!empty($_POST['website'])) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully.'
    ]);
    exit;
}

// Rate limit contact-form submissions per IP to curb spam/abuse.
$ip = simplephp_client_ip();
$rateKey = 'contact_form_' . $ip;
$rateStatus = simplephp_rate_limit_status($rateKey, 5, 600);
if (!$rateStatus['allowed']) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'errors' => ['general' => 'Too many messages sent. Please try again in a few minutes.']
    ]);
    exit;
}
simplephp_rate_limit_hit($rateKey, 5, 600, 600);

// Get site data to get recipient email
$data = simplephp_json_read(SIMPLEPHP_DATA_DIR . '/content.json', ['site' => [], 'pages' => []]);
$siteData = $data['site'];
// Try to get email from contact page, otherwise use site email
$contactPage = $data['pages']['contact'] ?? [];
$recipientEmail = $contactPage['email'] ?? $siteData['email'] ?? 'info@simplephp.org';

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if(empty($name)){
    $errors['name'] = 'Name is required';
}

if(empty($email)){
    $errors['email'] = 'Email is required';
} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors['email'] = 'Please enter a valid email address';
}

if(empty($message)){
    $errors['message'] = 'Message is required';
} elseif(strlen($message) < 10){
    $errors['message'] = 'Message must be at least 10 characters long';
}

// If there are errors, return them
if(!empty($errors)){
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);
    exit;
}

// Prepare email
$subject = "Contact Form Submission from " . htmlspecialchars($name);
$emailBody = "Name: " . htmlspecialchars($name) . "\n";
$emailBody .= "Email: " . htmlspecialchars($email) . "\n";
$emailBody .= "Message:\n" . htmlspecialchars($message) . "\n";

$headers = "From: " . htmlspecialchars($email) . "\r\n";
$headers .= "Reply-To: " . htmlspecialchars($email) . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
$mailSent = @mail($recipientEmail, $subject, $emailBody, $headers);

if($mailSent){
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errors' => ['general' => 'Failed to send email. Please try again later.']
    ]);
}
?>
