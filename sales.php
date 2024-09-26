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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        flex-direction: column;
        margin: 20px;
    }

    .main-content {
        padding: 20px;
        background: #fff;
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

    .table-section {
        margin-top: 20px;
    }
</style>
<body>
    
    <div class="container">
        <div class="main-content">
            <h3 class="text-center">Add New Invoice</h3>
            <a href="index.php" class="btn btn-primary btn-sm mb-3"><i class="fa fa-back"></i> Back</a>
            <form action="salescode.php" method="POST">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="invoice_date">Date</label>
                        <input type="date" name="invoice_date" class="form-control" id="invoice_date" required>
                    </div>
                     <div class="form-group col-md-6">
                        <label for="invoice_date">Bill No</label>
                        <input type="text" name="invoice_number" class="form-control" id="invoice_number" >
                    </div> 
                    <div class="form-group col-md-6">
                        <label for="cname">Customer Name</label>
                        <input type="text" name="cname" class="form-control" id="cname" placeholder="Enter Customer Name" required>
                    </div>
                </div>

                <div class="table-section">
                    <h5>Product Details</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product Description</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Add</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody id="tbl">
                            <tr>
                                <td><input class="description form-control" type="text" name="description[]"></td>
                                <td><input class="price form-control" type="number" name="unit_price[]"></td>
                                <td><input class="qty form-control" type="number" name="qty[]"></td>
                                <td><input class="total form-control" type="number" name="total[]" readonly></td>
                                <td><button type="button" class="btn btn-success add">+</button></td>
                                <td><button type="button" class="btn btn-danger rmv">-</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="total_cost">Total Amount</label>
                        <input type="number" class="form-control" name="total_cost" id="total_cost" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="amt_paid">Amount Paid</label>
                        <input type="number" name="amt_paid" class="form-control" id="amt_paid" placeholder="Enter Amount Paid" onchange="GetGrandTotal()" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="balance">Balance</label>
                        <input type="number" class="form-control" id="balance" name="balance" value="0" readonly>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" name="submit" class="btn btn-primary">Proceed</button>
                </div>
            </form>
        </div>
    </div>

   
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            function calculateTotal() {
                let total = 0;
                $('#tbl tr').each(function() {
                    const price = parseFloat($(this).find('.price').val()) || 0;
                    const qty = parseInt($(this).find('.qty').val()) || 0;
                    const rowTotal = price * qty;
                    $(this).find('.total').val(rowTotal.toFixed(2));
                    total += rowTotal;
                });
                $('#total_cost').val(total.toFixed(2));
                GetGrandTotal();
            }

            window.GetGrandTotal = function() {
                const totalCost = parseFloat($('#total_cost').val()) || 0;
                const amtPaid = parseFloat($('#amt_paid').val()) || 0;
                const balance = totalCost - amtPaid;
                $('#balance').val(balance.toFixed(2));
            };

            $(document).on('click', '.add', function() {
                const newRow = `<tr>
                                    <td><input class="description form-control" type="text" name="description[]"></td>
                                    <td><input class="price form-control" type="number" name="unit_price[]"></td>
                                    <td><input class="qty form-control" type="number" name="qty[]"></td>
                                    <td><input class="total form-control" type="number" name="total[]" readonly></td>
                                    <td><button type="button" class="btn btn-success add">+</button></td>
                                    <td><button type="button" class="btn btn-danger rmv">-</button></td>
                                </tr>`;
                $('#tbl').append(newRow);
            });

            $(document).on('click', '.rmv', function() {
                $(this).closest('tr').remove();
                calculateTotal(); // Recalculate totals
            });

            $(document).on('input', '.price, .qty', function() {
                calculateTotal(); // Recalculate totals on input change
            });
        });
    </script>
</html>





 <!-- jQuery -->
 