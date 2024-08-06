<?php
session_start();
include_once 'classes/Database.php';
include_once 'classes/Product.php';

if (!isset($_GET['id'])) {
    echo "No product ID provided.";
    exit;
}

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$product->id = $_GET['id'];
$product->readOne();

?>
<?php include('navbar.php'); ?>
<div class="container">
    <div class="product-details">
        <div class="product-details__image">
            <img src="<?php echo $product->image_url; ?>" alt="<?php echo $product->alt_description; ?>" class="product-details__img">
        </div>
        <div class="product-details__info">
            <h1 class="product-details__name"><?php echo $product->name; ?></h1>
            <p class="product-details__price">$<?php echo $product->price; ?></p>
            <p class="product-details__description"><?php echo $product->long_description; ?></p>
            <p class="product-details__sold-by"><strong>Sold by:</strong> <?php echo $product->sold_by; ?></p>
            <p class="product-details__reviews"><strong>Reviews:</strong> <?php echo $product->reviews; ?></p>
            <p class="product-details__delivery"><strong>Expected Delivery Date:</strong> <?php echo $product->expected_delivery_date; ?></p>

            <form class="product-details__form" action="add_to_cart.php" method="post">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="1" max="10" value="1">
                <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                <button type="submit" class="btn btn--primary">Add to Cart</button>
            </form>

            <div class="product-details__specs">
                <h2>Product Specifications</h2>
                <ul>
                    <li><strong>Dimensions:</strong> <?php echo $product->dimensions; ?></li>
                    <li><strong>Plant Type:</strong> <?php echo $product->plant_type; ?></li>
                    <li><strong>Category:</strong> <?php echo $product->category_name; ?></li>
                    <li><strong>Origin Country:</strong> <?php echo $product->origin_country; ?></li>
                    <li><strong>Climate Conditions:</strong> <?php echo $product->climate_conditions; ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
