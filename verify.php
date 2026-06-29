<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'username', 'password', 'database_name');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}

$token = $conn->real_escape_string($_GET['token']);
$email = $conn->real_escape_string($_GET['email']);

// Find user with token and email
$result = $conn->query("SELECT id FROM users WHERE email = '$email' AND verification_token = '$token' AND verified = 0");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Mark as verified
    $conn->query("UPDATE users SET verified = 1, verification_token = NULL WHERE id = {$user['id']}");
    
    echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired verification token']);
}

$conn->close();
?>