<?php
session_start();
include("../includes/db_config.php");

// Ensure only farmers can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION["user_id"];
$message = "";

// Handle Order Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["input_id"])) {
    $input_id = $_POST["input_id"];
    $quantity = $_POST["quantity"];

    // Get the selected input's details securely
    $stmt = $conn->prepare("SELECT name, price, stock_quantity FROM farm_inputs WHERE id = ?");
    $stmt->bind_param("i", $input_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($name, $price, $stock_quantity);
        $stmt->fetch();

        if ($quantity > $stock_quantity) {
            $message = "<div class='alert alert-danger'>Not enough stock available.</div>";
        } else {
            $total_price = $quantity * $price;

            // Insert order into database
            $order_stmt = $conn->prepare("INSERT INTO orders (farmer_id, input_id, quantity, total_price, status) VALUES (?, ?, ?, ?, 'Pending')");
            $order_stmt->bind_param("iiid", $farmer_id, $input_id, $quantity, $total_price);
            
            if ($order_stmt->execute()) {
                // Reduce stock
                $update_stmt = $conn->prepare("UPDATE farm_inputs SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $update_stmt->bind_param("ii", $quantity, $input_id);
                $update_stmt->execute();

                $message = "<div class='alert alert-success'>Order placed successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Failed to place order. Try again.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid input selection.</div>";
    }
}

// Handle Order Cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cancel_order_id"])) {
    $order_id = $_POST["cancel_order_id"];

    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND farmer_id = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $order_id, $farmer_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Order cancelled successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to cancel order.</div>";
    }
}

// Fetch available farm inputs
$stmt = $conn->prepare("SELECT id, name, description, price, stock_quantity FROM farm_inputs WHERE stock_quantity > 0");
$stmt->execute();
$result = $stmt->get_result();

// Fetch orders for the logged-in farmer
$order_query = "SELECT o.id, f.name, o.quantity, o.total_price, o.order_date, o.status
                FROM orders o 
                JOIN farm_inputs f ON o.input_id = f.id 
                WHERE o.farmer_id = ? 
                ORDER BY o.order_date DESC";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $farmer_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Farm Inputs - AgriSmart Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <h2 class="text-center">Order Farm Inputs</h2>
        
        <?php echo $message; ?>

        <!-- Order Form -->
        <form action="order_inputs.php" method="POST">
        <div class="mb-3">
    <label class="form-label">Select Farm Input</label>
    <select name="input_id" class="form-control" required onchange="updateStock(this)">
        <option value="">-- Choose --</option>
        <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>" data-stock="<?php echo $row['stock_quantity']; ?>">
                <?php echo htmlspecialchars($row['name']) . " - " . htmlspecialchars($row['description']) . " - KSh " . number_format($row['price'], 2); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
<p id="stock_display" class="text-muted">Select an item to see stock.</p>
<div class="mb-3"></div>
            <p id="stock_display" class="text-muted">Select an item to see stock.</p>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Place Order</button>
        </form>

        <hr>

        <!-- Orders Table -->
        <h3 class="text-center mt-4">Your Orders</h3>
        <table class="table table-striped table-bordered mt-3">
            <thead class="table-success">
                <tr>
                    <th>Order ID</th>
                    <th>Farm Input</th>
                    <th>Quantity</th>
                    <th>Total Price (KSh)</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $order_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                    <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                    <td><?php echo number_format($order['total_price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                    <td>
                        <span class="badge 
                            <?php 
                                if ($order['status'] == 'Pending') echo 'bg-warning';
                                elseif ($order['status'] == 'Processed') echo 'bg-info';
                                else echo 'bg-success';
                            ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($order['status'] == 'Pending'): ?>
                            <form action="order_inputs.php" method="POST">
                                <input type="hidden" name="cancel_order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mt-3">
            <a href="farmer_dashboard.php" class="btn btn-secondary w-100">Back to Dashboard</a>
        </div>
    </div>

    <script>
    function updateStock(selectElement) {
        let selectedOption = selectElement.options[selectElement.selectedIndex];
        let stock = selectedOption.getAttribute("data-stock");

        if (stock !== null) {
            document.getElementById("stock_display").innerText = "Available Stock: " + stock;
        } else {
            document.getElementById("stock_display").innerText = "Select an item to see stock.";
        }
    }
</script>


</body>
</html>
