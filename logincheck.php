
<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'smartmetering');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['pswd'];

    // Fetch user
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashedPassword);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $userId; // Set session
            header("Location: home.php"); // Redirect to dashboard
            exit();
        } else {
            header("Location: login.php?error=invalid"); // Redirect with error
            exit();
        }
    } else {
        header("Location: login.php?error=notfound"); // Redirect if no user found
        exit();
    }
}
?>
