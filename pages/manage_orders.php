<?php
session_start();
include("../includes/db_config.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "supplier") {
    header("Location: login.php");
    exit();
}

// Fetch all pending and processed orders
$sql = "SELECT orders.id, users.full_name AS farmer_name, farm_inputs.name AS input_name, 
               orders.quantity, orders.total_price, orders.status, orders.order_date
        FROM orders
        JOIN users ON orders.farmer_id = users.id  -- Changed 'farmers' to 'users'
        JOIN farm_inputs ON orders.input_id = farm_inputs.id
        WHERE users.role = 'farmer'  -- Ensure we only fetch farmers
        ORDER BY orders.order_date DESC";

$result = $conn->query($sql);

// Update order status
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["order_id"], $_POST["status"])) {
    $order_id = $_POST["order_id"];
    $new_status = $_POST["status"];

    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        // TODO: Add SMS notification here
        header("Location: manage_orders.php?success=Order Updated");
        exit();
    } else {
        header("Location: manage_orders.php?error=Update Failed");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - AgriSmart Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="supplier_dashboard.php">AgriSmart Hub</a>
            <a href="logout.php" class="btn btn-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Manage Orders</h2>

        <?php if (isset($_GET["success"])): ?>
            <div class="alert alert-success text-center"><?php echo $_GET["success"]; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET["error"])): ?>
            <div class="alert alert-danger text-center"><?php echo $_GET["error"]; ?></div>
        <?php endif; ?>

        <table class="table table-bordered mt-4">
            <thead class="table-success">
                <tr>
                    <th>#</th>
                    <th>Farmer</th>
                    <th>Farm Input</th>
                    <th>Quantity</th>
                    <th>Total Price (KSh)</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo htmlspecialchars($row["farmer_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["input_name"]); ?></td>
                        <td><?php echo $row["quantity"]; ?></td>
                        <td><?php echo number_format($row["total_price"], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo ($row["status"] == 'Delivered') ? 'success' : (($row["status"] == 'Processed') ? 'warning' : 'secondary'); ?>">
                                <?php echo $row["status"]; ?>
                            </span>
                        </td>
                        <td><?php echo date("d M Y, H:i", strtotime($row["order_date"])); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <select name="status" class="form-select">
                                    <option value="Pending" <?php if ($row["status"] == "Pending") echo "selected"; ?>>Pending</option>
                                    <option value="Processed" <?php if ($row["status"] == "Processed") echo "selected"; ?>>Processed</option>
                                    <option value="Delivered" <?php if ($row["status"] == "Delivered") echo "selected"; ?>>Delivered</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm mt-2">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mt-3">
            <a href="supplier_dashboard.php" class="btn btn-secondary w-100">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
