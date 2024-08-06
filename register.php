<?php
session_start();
include_once 'classes/Database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } 
    elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } 
    elseif (empty($username) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            $error_message = "A user with this email already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['username'] = $username;
                header("Location: checkout.php");
                exit;
            } else {
                $error_message = "Error: " . $stmt->errorInfo()[2];
            }
        }
    }
}
?>

<?php include('navbar.php'); ?>

<div class="container">
    <h1 class="register__heading">Register</h1>
    <form action="register.php" method="post" class="register__form">
        <?php if ($error_message): ?>
            <p class="register__error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <div class="register__field">
            <label for="username" class="register__label">Username:</label>
            <input type="text" id="username" name="username" class="register__input">
        </div>
        <div class="register__field">
            <label for="email" class="register__label">Email:</label>
            <input type="email" id="email" name="email" class="register__input">
        </div>
        <div class="register__field">
            <label for="password" class="register__label">Password:</label>
            <input type="password" id="password" name="password" class="register__input">
        </div>
        <button type="submit" class="btn btn--primary register__button">Register</button>
    </form>
</div>

<?php include('footer.php'); ?>