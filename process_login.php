<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo "<script>alert('All fields are required');</script>";
        echo "<script>window.location.replace('login.php');</script>";
        exit();
    }

    $servername = "localhost";
    $username_db = "root"; 
    $password_db = ""; 
    $dbname = "account"; 
    

    $conn = new mysqli($servername, $username_db, $password_db, $dbname, 3306); // Specify port 3306 here

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user data based on username
    $sql = "SELECT * FROM user WHERE email = ? ";
    $stmt = $conn->prepare("SELECT * FROM user where email = ? ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        var_dump($user);
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            echo "<script>alert('Login successful. ');</script>";
            echo "<script>window.location.replace('welcome.php');</script>";
        } else {
            echo "<script>alert('Invalid username or password');</script>";
            echo "<script>window.location.replace('login.php');</script>";
        }
    } else {
        echo "<script>alert('Invalid username or password');</script>";
        echo "<script>window.location.replace('login.php');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
