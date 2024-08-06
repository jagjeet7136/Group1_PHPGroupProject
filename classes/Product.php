<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $description;
    public $long_description;
    public $price;
    public $category_id;
    public $category_name;
    public $image_url;
    public $sold_by;
    public $reviews;
    public $expected_delivery_date;
    public $dimensions;
    public $plant_type;
    public $origin_country;
    public $climate_conditions;
    public $alt_description;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($category_id = '', $sort_order = '') {
        $query = "SELECT * FROM " . $this->table_name;
        $conditions = [];

        if (!empty($category_id)) {
            $conditions[] = "category_id = :category_id";
        }

        if (!empty($sort_order)) {
            $order_by = ($sort_order == 'low_to_high') ? "price ASC" : "price DESC";
        } else {
            $order_by = "id ASC";
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY " . $order_by;

        $stmt = $this->conn->prepare($query);

        if (!empty($category_id)) {
            $stmt->bindParam(':category_id', $category_id);
        }

        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT p.*, c.name as category_name FROM " . $this->table_name . " p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->long_description = $row['long_description'];
        $this->price = $row['price'];
        $this->category_id = $row['category_id'];
        $this->category_name = $row['category_name'];
        $this->image_url = $row['image_url'];
        $this->sold_by = $row['sold_by'];
        $this->reviews = $row['reviews'];
        $this->expected_delivery_date = $row['expected_delivery_date'];
        $this->dimensions = $row['dimensions'];
        $this->plant_type = $row['plant_type'];
        $this->origin_country = $row['origin_country'];
        $this->climate_conditions = $row['climate_conditions'];
        $this->alt_description = $row['alt_description'];
    }
}
?>
