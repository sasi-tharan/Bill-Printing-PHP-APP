<?php
include 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $invoiceId = $_POST['id'];

    // Delete the invoice from the database
    $stmt = $con->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->bind_param("i", $invoiceId);

    if ($stmt->execute()) {
        echo 'success'; // Return success message to the front-end
    } else {
        echo 'error'; // Return error message to the front-end
    }

    $stmt->close();
    $con->close();
}
?>