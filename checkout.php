<?php
require('libs/fpdf186/fpdf.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('navbar.php');
include_once 'classes/Database.php';

$database = new Database();
$db = $database->getConnection();

$provinces = [
    'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick', 'Newfoundland and Labrador', 
    'Nova Scotia', 'Ontario', 'Prince Edward Island', 'Quebec', 'Saskatchewan'
];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $province = trim($_POST['province']);
    $card_type = trim($_POST['card_type']);
    $card_number = trim($_POST['card_number']);
    $cvv = trim($_POST['cvv']);
    $expiry = trim($_POST['expiry']);

    if (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters long.';
    }

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Phone number must be exactly 10 digits.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($address)) {
        $errors[] = 'Address is required.';
    }

    if (empty($city)) {
        $errors[] = 'City is required.';
    }

    if (!preg_match('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/', $postal_code)) {
        $errors[] = 'Postal code must follow the pattern A1A1A1.';
    }

    if (!in_array($province, $provinces)) {
        $errors[] = 'Invalid province selected.';
    }

    if (!in_array($card_type, ['Debit', 'Credit'])) {
        $errors[] = 'Invalid card type selected.';
    }

    if (!preg_match('/^[0-9]{16}$/', $card_number)) {
        $errors[] = 'Card number must be exactly 16 digits.';
    }

    if (!preg_match('/^[0-9]{3}$/', $cvv)) {
        $errors[] = 'CVV must be exactly 3 digits.';
    }

    if (!preg_match('/^(0[1-9]|1[0-2])[0-9]{2}$/', $expiry)) {
        $errors[] = 'Expiry must follow the format MMYY.';
    } else {
        $currentYear = (int) date('y');
        $currentMonth = (int) date('m');
        $expiryMonth = (int) substr($expiry, 0, 2);
        $expiryYear = (int) substr($expiry, 2, 2);
        if ($expiryYear < $currentYear || ($expiryYear === $currentYear && $expiryMonth < $currentMonth)) {
            $errors[] = 'Expiry date cannot be in the past.';
        }
    }

    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];

        $query = "SELECT c.*, p.name, p.description, p.price, p.image_url 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
            // ob_start();

            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetTitle('Order Summary');

            $pdf->Image('images/logo.png', 10, 10, 30);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'The Plant Store', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 10, '108 University Avenue', 0, 1, 'C');
            $pdf->Cell(0, 10, 'Waterloo, ON, N2J2W2', 0, 1, 'C');
            $pdf->Cell(0, 10, 'Phone: (123) 456-7890', 0, 1, 'C');
            $pdf->Cell(0, 10, 'Email: contact@theplantstore.com', 0, 1, 'C');
            $pdf->Ln(20);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'User Information', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);

            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(0, 8, '', 0, 1);

            $pdf->Cell(50, 8, 'Name:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, htmlspecialchars($name), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Phone:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, htmlspecialchars($phone), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Email:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, htmlspecialchars($email), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Address:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, htmlspecialchars($address), 1, 1, 'L');

            $pdf->Cell(50, 8, 'City:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, htmlspecialchars($city), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Postal Code:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, htmlspecialchars($postal_code), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Province:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, htmlspecialchars($province), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Country:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, 'Canada', 1, 1, 'L');

            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Products Purchased', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);

            $totalPrice = 0;

            foreach ($products as $product) {
                $pdf->SetFillColor(230, 230, 230);
                $pdf->Cell(0, 8, '', 0, 1);

                $pdf->Cell(0, 8, $product['name'], 1, 1, 'L', true);

                $pdf->Cell(50, 8, 'Quantity:', 1, 0, 'L');
                $pdf->Cell(140, 8, htmlspecialchars($product['quantity']), 1, 1, 'L');

                $pdf->Cell(50, 8, 'Price:', 1, 0, 'L');
                $pdf->Cell(140, 8, '$' . number_format($product['price'], 2), 1, 1, 'L');

                $totalPrice += $product['quantity'] * $product['price'];
            }

            $pdf->Ln(10);

            $taxRate = 0.13;
            $tax = $totalPrice * $taxRate;
            $totalWithTax = $totalPrice + $tax;

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Summary', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);

            $pdf->SetFillColor(240, 240, 240);

            $pdf->Cell(0, 8, '', 0, 1);

            $pdf->Cell(50, 8, 'Total Price:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, '$' . number_format($totalPrice, 2), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Tax (13%):', 1, 0, 'L', true);
            $pdf->Cell(140, 8, '$' . number_format($tax, 2), 1, 1, 'L');

            $pdf->Cell(50, 8, 'Total with Tax:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, '$' . number_format($totalWithTax, 2), 1, 1, 'L');

            $deliveryDate = date('F j, Y', strtotime('+7 days'));
            $pdf->Cell(50, 8, 'Estimated Delivery Date:', 1, 0, 'L', true);
            $pdf->Cell(140, 8, $deliveryDate, 1, 1, 'L');

            ob_clean();
            $pdfFileName = 'order_summary_' . time() . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $pdfFileName . '"');
            $pdf->Output('D', $pdfFileName);

            $query = "DELETE FROM cart WHERE user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            echo "<script>setTimeout(function() { window.location.href = 'index.php'; }, 1000);</script>";
            exit;
        } else {
            $errors[] = 'Failed to retrieve cart products. Please try again.';
        }
    }
}
?>

<div class="container">
<?php
    if (!empty($errors)) {
        echo '<div class="error-messages">';
        foreach ($errors as $error) {
            echo '<p class="error checkout-error">' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
    }
    ?>
    <h1 class="checkout-title">Checkout</h1>
    <form id="checkout-form" class="checkout-form" action="checkout.php" method="post">
    <div class="form-group checkout-form-group">
        <label for="name" class="checkout-label">Name:</label>
        <input type="text" id="name" class="checkout-input" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="phone" class="checkout-label">Phone:</label>
        <input type="text" id="phone" class="checkout-input" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="email" class="checkout-label">Email:</label>
        <input type="email" id="email" class="checkout-input" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="address" class="checkout-label">Address:</label>
        <input type="text" id="address" class="checkout-input" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="city" class="checkout-label">City:</label>
        <input type="text" id="city" class="checkout-input" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="postal_code" class="checkout-label">Postal Code:</label>
        <input type="text" id="postal_code" class="checkout-input" name="postal_code" value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="province" class="checkout-label">Province:</label>
        <select id="province" class="checkout-select" name="province">
            <?php foreach ($provinces as $prov): ?>
                <option value="<?php echo $prov; ?>" <?php echo (isset($_POST['province']) && $_POST['province'] == $prov) ? 'selected' : ''; ?>><?php echo $prov; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group checkout-form-group">
        <label for="country" class="checkout-label">Country:</label>
        <input type="text" id="country" class="checkout-input" name="country" value="Canada" readonly>
    </div>
    <div class="form-group checkout-form-group">
        <label for="card_type" class="checkout-label">Card Type:</label>
        <select id="card_type" class="checkout-select" name="card_type">
            <option value="Credit" <?php echo (isset($_POST['card_type']) && $_POST['card_type'] == 'Credit') ? 'selected' : ''; ?>>Credit</option>
            <option value="Debit" <?php echo (isset($_POST['card_type']) && $_POST['card_type'] == 'Debit') ? 'selected' : ''; ?>>Debit</option>
        </select>
    </div>
    <div class="form-group checkout-form-group">
        <label for="card_number" class="checkout-label">Card Number:</label>
        <input type="text" id="card_number" class="checkout-input" name="card_number" value="<?php echo htmlspecialchars($_POST['card_number'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="cvv" class="checkout-label">CVV:</label>
        <input type="text" id="cvv" class="checkout-input" name="cvv" value="<?php echo htmlspecialchars($_POST['cvv'] ?? ''); ?>">
    </div>
    <div class="form-group checkout-form-group">
        <label for="expiry" class="checkout-label">Expiry Date(MMYY):</label>
        <input type="text" id="expiry" class="checkout-input" name="expiry" value="<?php echo htmlspecialchars($_POST['expiry'] ?? ''); ?>">
    </div>
    <button type="submit" class="btn btn-primary checkout-btn">Place Order</button>
</form>


</div>

<?php include('footer.php'); ?>
