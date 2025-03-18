<?php
session_start();
include("../includes/db_config.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION["user_id"];

// Fetch farmer's orders
$sql = "SELECT orders.id, farm_inputs.name, orders.quantity, orders.total_price, orders.status, orders.order_date 
        FROM orders 
        JOIN farm_inputs ON orders.input_id = farm_inputs.id 
        WHERE orders.farmer_id = ?
        ORDER BY orders.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status - AgriSmart Hub</title>
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
        <h2 class="text-center">Your Orders</h2>

        <table class="table table-bordered mt-4">
            <thead class="table-success">
                <tr>
                    <th>#</th>
                    <th>Farm Input</th>
                    <th>Quantity</th>
                    <th>Total Price (KSh)</th>
                    <th>Status</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo $row["quantity"]; ?></td>
                            <td><?php echo number_format($row["total_price"], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($row["status"] == 'Delivered') ? 'success' : (($row["status"] == 'Processed') ? 'warning' : 'secondary'); ?>">
                                    <?php echo $row["status"]; ?>
                                </span>
                            </td>
                            <td><?php echo date("d M Y, H:i", strtotime($row["order_date"])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-3">
            <a href="farmer_dashboard.php" class="btn btn-secondary w-100">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
