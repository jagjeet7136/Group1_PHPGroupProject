<?php
session_start();
include_once 'classes/Database.php';
include_once 'classes/Product.php';
include_once 'classes/Category.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$category = new Category($db);

$categories = $category->read();
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : '';

$stmt = $product->read($filter_category, $sort_order);
?>

<?php include('navbar.php'); ?>

<div class="container">
<div class="hero">
        <img src="images/hero-images.jpg" alt="Hero Image" class="hero__image">
        <div class="hero__text">
            <h1 class="heading-primary">Welcome to Our Plant Store</h1>
            <p class="hero__description">Discover a variety of plants to brighten your home and garden</p>
        </div>
    </div>
    <form method="GET" class="filter-form">
        <div class="filter-form__group">
            <label for="category" class="filter-form__label">Category:</label>
            <select name="category" id="category" class="filter-form__select">
                <option value="">All</option>
                <?php while ($row = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo $filter_category == $row['id'] ? 'selected' : ''; ?>><?php echo $row['name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="filter-form__group">
            <label for="sort" class="filter-form__label">Sort by:</label>
            <select name="sort" id="sort" class="filter-form__select">
                <option value="">None</option>
                <option value="low_to_high" <?php echo $sort_order == 'low_to_high' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="high_to_low" <?php echo $sort_order == 'high_to_low' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </div>
        <button type="submit" class="btn btn--primary filter-form__button">Apply Filters</button>
    </form>

    <div class="products">
        <?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            echo "<div class='product'>";
            echo "<a href='product.php?id={$id}'>";
            echo "<img src='{$image_url}' alt='{$alt_description}' class='product__image'>";
            echo '<h2 class="product__name">' . $name . '</h2>';
            echo '<p class="product__description">' . $description . '</p>';
            echo '<p class="product__price">$' . $price . '</p>';
            echo "</a>";
            echo "</div>";
        }
        ?>
    </div>
</div>

<?php include('footer.php'); ?>
