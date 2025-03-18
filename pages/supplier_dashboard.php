<?php 
session_start();
include("../includes/db_config.php");

// Ensure user is logged in as a supplier
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "supplier") {
    header("Location: login.php");
    exit();
}

$supplier_id = intval($_SESSION["user_id"]);

// Fetch order statistics
$order_stats_sql = "SELECT 
                        COUNT(*) AS total_orders,
                        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_orders,
                        SUM(CASE WHEN status = 'Processed' THEN 1 ELSE 0 END) AS processed_orders,
                        SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) AS delivered_orders
                    FROM orders WHERE supplier_id = ?";
$stmt = $conn->prepare($order_stats_sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$order_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch recent orders
$recent_orders_sql = "SELECT users.full_name AS farmer_name, farm_inputs.name AS input_name, 
                             orders.quantity, orders.status, orders.order_date
                      FROM orders 
                      JOIN users ON orders.farmer_id = users.id 
                      JOIN farm_inputs ON orders.input_id = farm_inputs.id
                      WHERE farm_inputs.supplier_id = ?
                      ORDER BY orders.order_date DESC LIMIT 5";
$stmt = $conn->prepare($recent_orders_sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$recent_orders = $stmt->get_result();
$stmt->close();

// Fetch best-selling farm inputs
$best_sellers_sql = "SELECT farm_inputs.name, SUM(orders.quantity) AS total_sold
                     FROM orders
                     JOIN farm_inputs ON orders.input_id = farm_inputs.id
                     WHERE farm_inputs.supplier_id = ?
                     GROUP BY farm_inputs.name
                     ORDER BY total_sold DESC LIMIT 5";
$stmt = $conn->prepare($best_sellers_sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$best_sellers = $stmt->get_result();
$stmt->close();

// Fetch low-stock alerts
$low_stock_sql = "SELECT id, name, stock_quantity FROM farm_inputs WHERE stock_quantity < 10 AND supplier_id = ? ORDER BY stock_quantity ASC";
$stmt = $conn->prepare($low_stock_sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$low_stock = $stmt->get_result();
$stmt->close();

// Fetch all farm inputs added by this supplier
$all_inputs_sql = "SELECT id, name, description, price, stock_quantity FROM farm_inputs WHERE supplier_id = ?";
$stmt = $conn->prepare($all_inputs_sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$all_inputs = $stmt->get_result();
$stmt->close();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle adding farm inputs
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_input"])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $name = htmlspecialchars(trim($_POST["name"]));
    $description = htmlspecialchars(trim($_POST["description"]));
    $price = floatval($_POST["price"]);
    $stock_quantity = intval($_POST["stock_quantity"]);

    if (!empty($name) && !empty($description) && $price > 0 && $stock_quantity >= 0) {
        $insert_sql = "INSERT INTO farm_inputs (name, description, price, stock_quantity, supplier_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssdii", $name, $description, $price, $stock_quantity, $supplier_id);
        
        if ($stmt->execute()) {
            header("Location: supplier_dashboard.php?success=Farm Input Added");
        } else {
            header("Location: supplier_dashboard.php?error=Failed to Add Input");
        }
        $stmt->close();
        exit();
    } else {
        header("Location: supplier_dashboard.php?error=Invalid Input Data");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - AgriSmart Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="supplier_dashboard.php">AgriSmart Hub</a>
            <a href="logout.php" class="btn btn-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center">Supplier Dashboard</h2>

        <!-- Order Statistics -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-secondary">
                    <div class="card-body text-center">
                        <h5>Total Orders</h5>
                        <h3><?php echo $order_stats['total_orders']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <h5>Pending Orders</h5>
                        <h3><?php echo $order_stats['pending_orders']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h5>Delivered Orders</h5>
                        <h3><?php echo $order_stats['delivered_orders']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Farm Input Management -->
        <div class="mt-5">
            <h4>Manage Farm Inputs</h4>
            <table class="table table-bordered table-striped">
                <thead class="table-success">
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price (KSh)</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $all_inputs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["description"]); ?></td>
                            <td><?php echo number_format($row["price"], 2); ?></td>
                            <td><?php echo $row["stock_quantity"]; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Farm Input -->
        <div class="mt-5">
            <h4>Add New Farm Input</h4>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-3">
                    <label>Name:</label>
                    <input type="text" name="name" required class="form-control">
                </div>
                <div class="mb-3">
                    <label>Description:</label>
                    <textarea name="description" required class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Price (KSh):</label>
                    <input type="number" name="price" step="0.01" required class="form-control">
                </div>
                <div class="mb-3">
                    <label>Stock Quantity:</label>
                    <input type="number" name="stock_quantity" required class="form-control">
                </div>
                <button type="submit" name="add_input" class="btn btn-success">Add Input</button>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
