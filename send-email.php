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
// Checked and recorded atomically under one lock (simplephp_rate_limit_attempt)
// so concurrent requests can't both slip through a separate check-then-record step.
$ip = simplephp_client_ip();
$rateKey = 'contact_form_' . $ip;
$rateResult = simplephp_rate_limit_attempt($rateKey, 5, 600, 600);
if (!$rateResult['allowed']) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'errors' => ['general' => 'Too many messages sent. Please try again in a few minutes.']
    ]);
    exit;
}

// Get site data to get recipient email
$data = simplephp_json_read(SIMPLEPHP_DATA_DIR . '/content.json', ['site' => [], 'pages' => []]);
$siteData = $data['site'];
// Try to get email from contact page, otherwise use site email
$contactPage = $data['pages']['contact'] ?? [];
$recipientEmail = $contactPage['email'] ?? $siteData['email'] ?? 'info@simplephp.org';

$errors = [];
$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

// Validation (reject-with-feedback, not silent truncation)
if(empty($name)){
    $errors['name'] = 'Name is required';
} elseif(strlen($name) > 200){
    $errors['name'] = 'Name is too long (max 200 characters)';
}

if(empty($email)){
    $errors['email'] = 'Email is required';
} elseif(strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)){ // 254 = max valid email length per RFC 5321
    $errors['email'] = 'Please enter a valid email address';
}

if(empty($message)){
    $errors['message'] = 'Message is required';
} elseif(strlen($message) < 10){
    $errors['message'] = 'Message must be at least 10 characters long';
} elseif(strlen($message) > 5000){
    $errors['message'] = 'Message is too long (max 5000 characters)';
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

// Strip any stray CR/LF before anything derived from user input touches a
// mail header - defends against header injection (extra Bcc:/To: lines etc)
// even though FILTER_VALIDATE_EMAIL already rejects them in $email.
$headerSafeName = str_replace(["\r", "\n"], '', $name);
$headerSafeEmail = str_replace(["\r", "\n"], '', $email);

// Prepare email
$subject = "Contact Form Submission from " . htmlspecialchars($name);
$emailBody = "Name: " . htmlspecialchars($name) . "\n";
$emailBody .= "Email: " . htmlspecialchars($email) . "\n";
$emailBody .= "Message:\n" . htmlspecialchars($message) . "\n";

// From: must be an address the sending server actually controls - using the
// visitor's own address here caused spoofing/deliverability problems (fails
// SPF/DKIM) and let visitor-controlled input dictate an auth-sensitive header.
// The visitor's address goes in Reply-To instead, where it belongs.
$siteHost = preg_replace('/[^a-zA-Z0-9.\-]/', '', (string) ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost'));
if ($siteHost === '') {
    $siteHost = 'localhost';
}
$fromAddress = 'no-reply@' . $siteHost;

$headers = "From: " . $headerSafeName . " <" . $fromAddress . ">\r\n";
$headers .= "Reply-To: " . $headerSafeName . " <" . $headerSafeEmail . ">\r\n";
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
