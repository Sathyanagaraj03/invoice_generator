<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Fetch Invoices
    if ($action == 'fetch') {
        $result = $conn->query("SELECT * FROM invoice ");
        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        echo json_encode($invoices);
    }

    // Add Invoice
    elseif ($action == 'add') {
        $item_description = $_POST['item_description'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $amount = $quantity * $price; // Calculate total amount

        $conn->query("INSERT INTO invoice (item_description, quantity, price, amount) VALUES ('$item_description', '$quantity', '$price', '$amount')");
        echo "Invoice Added Successfully!";
    }

    // Update Invoice
    elseif ($action == 'update') {
        $id = $_POST['id'];
        $item_description = $_POST['item_description'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $amount = $quantity * $price; // Recalculate total amount

        $conn->query("UPDATE invoice SET item_description='$item_description', quantity='$quantity', price='$price', amount='$amount' WHERE id=$id");
        echo "Invoice $id Updated Successfully!";
    }

    // Delete Invoice (Soft Delete)
    elseif ($action == 'delete') {
        $id = $_POST['id'];
        $conn->query("delete from invoice WHERE id = $id");
        echo "Invoice $id Deleted Successfully!";
    }

    else {
        echo "Invalid Action!";
    }
}
?>
