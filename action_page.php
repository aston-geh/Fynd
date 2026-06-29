<?php
header('Content-Type: application/json');

// Email configuration
$from_email = 'noreply@fynd.com'; // Change to your email

// Connect to database
$conn = new mysqli('localhost', 'username', 'password', 'database_name');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$data = json_decode(file_get_contents("php://input"), true);
$email = $conn->real_escape_string($data['email']);
$password = $data['password'];

// Check if user exists
$result = $conn->query("SELECT id, password, verified FROM users WHERE email = '$email'");

if ($result->num_rows > 0) {
    // Email exists - check password
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        if ($user['verified'] == 1) {
            // Password correct and email verified
            $token = bin2hex(random_bytes(32));
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");
            
            echo json_encode([
                'success' => true,
                'userId' => $user['id'],
                'token' => $token,
                'message' => 'Logged in successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Please verify your email first',
                'needsVerification' => true
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    }
} else {
    // Email doesn't exist - create account and send verification
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $verificationToken = bin2hex(random_bytes(16));
    
    if ($conn->query("INSERT INTO users (email, password, verification_token, verified, created_at) 
                     VALUES ('$email', '$hashedPassword', '$verificationToken', 0, NOW())")) {
        
        $userId = $conn->insert_id;
        
        // Send verification email
        $verificationLink = "https://aston-geh.github.io/Fynd/verify.php?token=$verificationToken&email=$email";
        
        $subject = "Verify your Fynd account";
        $message = "
        <html>
        <body>
            <h2>Welcome to Fynd!</h2>
            <p>Click the link below to verify your email:</p>
            <a href='$verificationLink'>Verify Email</a>
            <p>Or copy this link: $verificationLink</p>
        </body>
        </html>";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $from_email . "\r\n";
        
        if (mail($email, $subject, $message, $headers)) {
            echo json_encode([
                'success' => true,
                'userId' => $userId,
                'message' => 'Account created! Check your email to verify.',
                'needsVerification' => true
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Account created but email failed to send'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account']);
    }
}

$conn->close();
?>
