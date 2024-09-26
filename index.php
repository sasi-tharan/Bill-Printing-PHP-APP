<?php
include 'db.php'; // Include your database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S & S Bakers</title>
    <link rel="stylesheet" href="styles.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }

    .container {
        display: flex;
    }

    .sidebar {
        width: 250px;
        background: #333;
        color: #fff;
        padding: 15px;
    }

    .sidebar h2 {
        text-align: center;
    }

    .sidebar nav ul {
        list-style: none;
    }

    .sidebar nav ul li {
        margin: 20px 0;
    }

    .sidebar nav ul li a {
        color: #fff;
        text-decoration: none;
        display: block;
        padding: 10px;
        transition: background 0.3s;
    }

    .sidebar nav ul li a:hover,
    .sidebar nav ul li a.active {
        background: #555;
    }

    .main-content {
        flex: 1;
        padding: 20px;
    }

    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cards {
        margin: 20px 0;
    }

    .card {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%; /* Full width of parent container */
        margin: 0; /* Remove any margins */
    }

    .recent-activity {
        margin-top: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .add-button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
        margin-bottom: 20px; /* Space between button and table */
    }

    .add-button:hover {
        background-color: #218838;
        transform: scale(1.05);
    }
</style>
<body>
    <div class="container">
        <main class="main-content">
            <header>
                <h1>Dashboard</h1>
                <!-- <div class="user-info">
                    <span>Welcome, Admin</span>
                    <button id="logout">Logout</button>
                </div> -->
            </header>

            <section class="cards">
                <div class="card">
                    <h3>Bills</h3>
                    <div class="table_section padding_infor_info">
                        <div class="table-responsive-sm">
                            <a href="sales.php">
                                <button class="add-button">Add</button>
                            </a>

                            <table id="sizeTable" class="display table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Bill No</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Total Cost</th>
                                        <th>Amount Paid</th>
                                        <th>Balance</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Database connection
                                    $conn = mysqli_connect("localhost", "root", "", "bakery_db");
                                    if (!$conn) {
                                        die("Connection failed: " . mysqli_connect_error());
                                    }

                                    // Query to fetch sales data
                                    $query = "SELECT * FROM sales";
                                    $query_run = mysqli_query($conn, $query);

                                    if (mysqli_num_rows($query_run) > 0) {
                                        foreach ($query_run as $invoice) {
                                            ?>
                                            <tr>
                                                <td><?=$invoice['invoice_number']?></td>
                                                <td><?=$invoice['invoice_date']?></td>
                                                <td><?=$invoice['cname']?></td>
                                                <td><?=$invoice['total_cost']?></td>
                                                <td><?=$invoice['amt_paid']?></td>
                                                <td><?=$invoice['balance']?></td>
                                                <td>
                                                <button type="button" value="<?=$invoice['id'];?>" class="printbtn btn btn-info btn-sm">Print</button>
                                                    <!-- <button type="button" value="<?=$invoice['id'];?>" class="editbtn btn btn-success btn-sm">Update</button> -->
                                                    <button type="button" value="<?=$invoice['id'];?>" class="deletebtn btn btn-danger btn-sm">Delete</button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>No records found</td></tr>";
                                    }
                                    mysqli_close($conn); // Close connection
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
            
        </main>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#sizeTable').DataTable(); // Initialize DataTable
        });
    </script>
    <script src="script.js"></script>

    <script>
    document.querySelectorAll('.printbtn').forEach(function(button) {
        button.addEventListener('click', function() {
            var invoiceId = this.value;

            // Redirect to the PHP script that generates the PDF
            window.location.href = "download_invoice.php?id=" + invoiceId;
        });
    });
</script>
<script>
    $(document).on('click', '.deletebtn', function() {
        var invoiceId = $(this).val(); // Get the invoice ID from the button value
        var confirmation = confirm('Are you sure you want to delete this invoice?');

        if (confirmation) {
            $.ajax({
                url: 'delete_invoice.php', // URL of the PHP script that handles deletion
                type: 'POST',
                data: { id: invoiceId }, // Send invoice ID to the back-end
                success: function(response) {
                    if (response == 'success') {
                        alert('Invoice deleted successfully.');
                        location.reload(); // Reload the page to reflect the changes
                    } else {
                        alert('Error deleting the invoice.');
                    }
                }
            });
        }
    });
</script>

</body>
</html>
