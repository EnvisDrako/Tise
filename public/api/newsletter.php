<?php
// Newsletter subscription API endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email'])) {
        throw new Exception('Email is required');
    }
    
    $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        throw new Exception('Invalid email address');
    }
    
    // Include database connection
    require_once '../../includes/db_connect.php';
    
    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->fetch()) {
        echo json_encode([
            'success' => false, 
            'message' => 'This email is already subscribed to our newsletter'
        ]);
        exit();
    }
    
    // Insert new subscriber
    $insertStmt = $pdo->prepare("
        INSERT INTO newsletter_subscribers (email, subscribed_at, ip_address, user_agent) 
        VALUES (?, NOW(), ?, ?)
    ");
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $insertStmt->execute([$email, $ipAddress, $userAgent]);
    
    // Log the subscription
    error_log("Newsletter subscription: $email from $ipAddress");
    
    // Send welcome email (placeholder - implement actual email sending)
    // sendWelcomeEmail($email);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for subscribing to our newsletter!'
    ]);
    
} catch (PDOException $e) {
    error_log("Newsletter subscription database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred. Please try again later.'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

function sendWelcomeEmail($email) {
    // Placeholder for email sending functionality
    // This would integrate with your email service provider
    // Example: SendGrid, Mailgun, PHPMailer, etc.
    
    $subject = "Welcome to EduPortal Newsletter!";
    $message = "
        <html>
        <head>
            <title>Welcome to EduPortal</title>
        </head>
        <body>
            <h2>Welcome to EduPortal Newsletter!</h2>
            <p>Thank you for subscribing to our newsletter. You'll receive updates about:</p>
            <ul>
                <li>New courses and programs</li>
                <li>Educational tips and resources</li>
                <li>Success stories from our students</li>
                <li>Special offers and events</li>
            </ul>
            <p>Stay tuned for valuable content!</p>
            <p>Best regards,<br>The EduPortal Team</p>
        </body>
        </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: EduPortal <noreply@eduportal.com>',
        'Reply-To: info@eduportal.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Uncomment to send actual email
    // mail($email, $subject, $message, implode("\r\n", $headers));
}
?>