<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>Home - E-commerce</title>
</head>
<body>
    <nav class="navbar">
            <a href="index.php" class="navbar__brand">
                <img src="images/logo.png" alt="E-commerce Logo" class="navbar__logo">
            </a>
            <ul class="navbar__menu">
                <li class="navbar__item"><a href="index.php" class="navbar__link">Home</a></li>
                <li class="navbar__item"><a href="cart.php" class="navbar__link">Cart</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="navbar__item navbar__item--username">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></li>

                    <li class="navbar__item"><a href="logout.php" class="navbar__link">Logout</a></li>
                <?php else: ?>
                    <li class="navbar__item"><a href="login.php" class="navbar__link">Login</a></li>
                    <li class="navbar__item"><a href="register.php" class="navbar__link">Register</a></li>
                <?php endif; ?>
            </ul>
    </nav>
