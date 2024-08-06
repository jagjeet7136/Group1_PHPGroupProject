<?php
session_start();
include_once 'classes/Database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>

<?php include('navbar.php'); ?>

<div class="container">
    <h1 class="login__heading">Login</h1>
    <form action="login.php" method="post" class="login__form">
        <?php if ($error_message): ?>
            <p class="login__error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <div class="login__field">
            <label for="email" class="login__label">Email:</label>
            <input type="email" id="email" name="email" class="login__input" required>
        </div>
        <div class="login__field">
            <label for="password" class="login__label">Password:</label>
            <input type="password" id="password" name="password" class="login__input" required>
        </div>
        <button type="submit" class="btn btn--primary login__button">Login</button>
    </form>
</div>

<?php include('footer.php'); ?>
