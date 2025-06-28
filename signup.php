<?php
// signup.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['txt']) ? trim($_POST['txt']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($name) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'smartmetering2');

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Insert user data into the database
    $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        header("Location: login.php"); // Redirect to the login page
        exit();
    } else {
        header("Location: signup.php?error=failed"); // Redirect back with an error (optional)
        exit();
    }
    

    $stmt->close();
    $conn->close();
}
?>