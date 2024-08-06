<?php
session_start();
include_once 'classes/Database.php';
include_once 'classes/Product.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$user_id = $_SESSION['user_id'];
$query = "SELECT c.*, p.name, p.description, p.price, p.image_url 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

$tax = $total_amount * 0.13;
$total_with_tax = $total_amount + $tax;

?>

<?php include('navbar.php'); ?>

<div class="-cart-container">
    <h1 class="cart__heading">Your Cart</h1>
    <div class="cart__items">
        <?php if (count($cart_items) > 0): ?>
            <ul class="cart__list">
                <?php foreach ($cart_items as $item): ?>
                    <li class="cart__item">
                        <div class="cart__item-wrapper">
                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="cart__item-image">
                            <div class="cart__item-details">
                                <h2 class="cart__item-name"><?php echo $item['name']; ?></h2>
                                <p class="cart__item-quantity">Quantity: <?php echo $item['quantity']; ?></p>
                                <p class="cart__item-price">Price: $<?php echo number_format($item['price'], 2); ?></p>
                                <form action="remove_from_cart.php" method="post" class="cart__item-form">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="btn btn--primary cart__item-remove">Remove</button>
                                </form>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="cart__summary">
                <p class="cart__summary-item">Subtotal: $<?php echo number_format($total_amount, 2); ?></p>
                <p class="cart__summary-item">Tax (13%): $<?php echo number_format($tax, 2); ?></p>
                <p class="cart__summary-item cart__summary-item--total">Total: $<?php echo number_format($total_with_tax, 2); ?></p>
            </div>
            <a href="checkout.php" class="btn btn--primary cart__checkout">Proceed to Checkout</a>
        <?php else: ?>
            <p class="cart__empty-message">Your cart is empty.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('footer.php'); ?>
