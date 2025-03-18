<?php
session_start();
include("../includes/db_config.php");

// Check if farmer is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION["user_id"];

// Fetch farmer's name securely
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();
$farmer_name = htmlspecialchars($farmer["full_name"]);

// Fetch order summary
$order_summary_sql = "SELECT 
                        COUNT(*) AS total_orders,
                        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_orders,
                        SUM(CASE WHEN status = 'Processed' THEN 1 ELSE 0 END) AS processed_orders,
                        SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) AS delivered_orders
                      FROM orders WHERE farmer_id = ?";
$stmt = $conn->prepare($order_summary_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$order_summary = $stmt->get_result()->fetch_assoc();

// Fetch recent orders
$recent_orders_sql = "SELECT farm_inputs.name AS input_name, orders.quantity, orders.status, orders.order_date
                      FROM orders 
                      JOIN farm_inputs ON orders.input_id = farm_inputs.id
                      WHERE orders.farmer_id = ?
                      ORDER BY orders.order_date DESC LIMIT 5";
$stmt = $conn->prepare($recent_orders_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$recent_orders = $stmt->get_result();

// Fetch low-stock farm inputs
$low_stock_sql = "SELECT name, stock_quantity FROM farm_inputs WHERE stock_quantity < 10 ORDER BY stock_quantity ASC";
$low_stock = $conn->query($low_stock_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - AgriSmart Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="farmer_dashboard.php">AgriSmart Hub</a>
            <a href="logout.php" class="btn btn-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Welcome, <?php echo $farmer_name; ?> (Farmer)</h2>

        <!-- Order Summary -->
        <div class="row mt-4">
            <?php
            $summary_cards = [
                ["Total Orders", "primary", $order_summary['total_orders']],
                ["Pending Orders", "warning", $order_summary['pending_orders']],
                ["Processed Orders", "info", $order_summary['processed_orders']],
                ["Delivered Orders", "success", $order_summary['delivered_orders']]
            ];
            foreach ($summary_cards as $card) {
                echo "<div class='col-md-3'>
                        <div class='card text-white bg-{$card[1]}'>
                            <div class='card-body text-center'>
                                <h5>{$card[0]}</h5>
                                <h3>{$card[2]}</h3>
                            </div>
                        </div>
                      </div>";
            }
            ?>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-4">
                <a href="order_inputs.php" class="btn btn-primary w-100">Order Farm Inputs</a>
            </div>
            <div class="col-md-4">
                <a href="order_status.php" class="btn btn-warning w-100">Check Order Status</a>
            </div>
            <div class="col-md-4">
                <a href="profile.php" class="btn btn-info w-100">View Profile</a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mt-5">
            <h4>Recent Orders</h4>
            <table class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>Farm Input</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["input_name"]); ?></td>
                            <td><?php echo $row["quantity"]; ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $row["status"] == 'Pending' ? 'bg-warning' : 
                                               ($row["status"] == 'Processed' ? 'bg-info' : 'bg-success'); ?>">
                                    <?php echo $row["status"]; ?>
                                </span>
                            </td>
                            <td><?php echo date("d M Y", strtotime($row["order_date"])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Low Stock Alerts -->
        <div class="mt-5">
            <h4 class="text-danger">Low Stock Alerts</h4>
            <table class="table table-bordered">
                <thead class="table-danger">
                    <tr>
                        <th>Farm Input</th>
                        <th>Stock Left</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $low_stock->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo $row["stock_quantity"]; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Notifications -->
        <div class="mt-5">
            <h4 class="text-primary">Notifications</h4>
            <ul class="list-group">
                <?php
                if ($order_summary['pending_orders'] > 0) {
                    echo "<li class='list-group-item text-warning'>You have {$order_summary['pending_orders']} pending orders.</li>";
                }
                if ($order_summary['processed_orders'] > 0) {
                    echo "<li class='list-group-item text-info'>You have {$order_summary['processed_orders']} orders in processing.</li>";
                }
                if ($order_summary['delivered_orders'] > 0) {
                    echo "<li class='list-group-item text-success'>You have {$order_summary['delivered_orders']} delivered orders.</li>";
                }
                if ($low_stock->num_rows > 0) {
                    echo "<li class='list-group-item text-danger'>Some farm inputs are running low in stock.</li>";
                }
                ?>
            </ul>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
