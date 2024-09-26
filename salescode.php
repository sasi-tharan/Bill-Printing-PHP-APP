<?php
require 'fpdf186/fpdf.php';
include 'db.php'; // Include your database connection file

if (isset($_POST['submit'])) {
                // Get form data for sales
                $invoice_date = mysqli_real_escape_string($con, $_POST['invoice_date']);
                $customer_name = mysqli_real_escape_string($con, $_POST['cname']);
                $invoice_number = mysqli_real_escape_string($con, $_POST['invoice_number']); // Get invoice number
                $total_cost = (float) mysqli_real_escape_string($con, $_POST['total_cost']);
                $amt_paid = (float) mysqli_real_escape_string($con, $_POST['amt_paid']);
                $balance = (float) mysqli_real_escape_string($con, $_POST['balance']);

                // Debugging output
                error_log("invoice_date: $invoice_date, customer_name: $customer_name, invoice_number: $invoice_number, total_cost: $total_cost, amt_paid: $amt_paid, balance: $balance");

                // Insert sales data into the 'sales' table
                $query = "INSERT INTO sales (invoice_date, cname, invoice_number, total_cost, amt_paid, balance) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($con, $query)) {
                    mysqli_stmt_bind_param($stmt, 'ssddds', $invoice_date, $customer_name, $invoice_number, $total_cost, $amt_paid, $balance);
                    mysqli_stmt_execute($stmt);
                    $invoice_id = mysqli_insert_id($con); // Get the last inserted invoice ID
                    mysqli_stmt_close($stmt);
                }

                // Prepare the sales_items insert query if product data is provided
                if (!empty($_POST['description']) && !empty($_POST['unit_price']) && !empty($_POST['qty']) && !empty($_POST['total'])) {
                    $description = $_POST['description'];
                    $unit_price = $_POST['unit_price'];
                    $qty = $_POST['qty'];
                    $total = $_POST['total'];

                    $item_query = "INSERT INTO sales_items (invoice_id, description, unit_price, qty, total) VALUES (?, ?, ?, ?, ?)";
                    if ($item_stmt = mysqli_prepare($con, $item_query)) {
                        for ($i = 0; $i < count($description); $i++) {
                            mysqli_stmt_bind_param($item_stmt, 'isddd', $invoice_id, $description[$i], $unit_price[$i], $qty[$i], $total[$i]);
                            mysqli_stmt_execute($item_stmt);
                        }
                        mysqli_stmt_close($item_stmt);
                    }
                }

    class PDF extends FPDF
    {
        private $info; // To hold invoice information

        public function setInfo($info)
        {
            $this->info = $info; // Store info for later use
        }

        public function Header()
        {
            // Display the logo on the left
            $this->Image('logo.png', 10, 6, 60, 40); // Width is 40, height is 20

            // Set the position to the right of the logo
            $this->SetX(50); // Move to the right after the logo

            // Calculate the available width for centering
            $totalWidth = 210; // Total width of the PDF (A4 size)
            $availableWidth = $totalWidth - 40; // Total width minus left margin (10) and logo width (30)

            // Center the company name by calculating the X position
            $this->SetX(($totalWidth - $availableWidth) / 2); // Center it
            $this->SetFont('Arial', 'B', 14);
            $this->Cell($availableWidth, 10, 'S & S BAKERS', 0, 1, 'C'); // Center the company name

            // Center the address
            $this->SetX(($totalWidth - $availableWidth) / 2); // Center it
            $this->SetFont('Arial', '', 12);
            $this->Cell($availableWidth, 7, 'No. 18, Main Street, Watagoda', 0, 1, 'C'); // Center the address

            // Center the telephone
            $this->SetX(($totalWidth - $availableWidth) / 2); // Center it
            $this->Cell($availableWidth, 7, 'Tel: 0777878488', 0, 1, 'C'); // Center the teleph
        }

        public function DateInvoice()
        {
            // Add Invoice details on the right
            $this->SetFont('Arial', '', 12);
            $this->SetY(20); // Adjust Y position for the date
            $this->SetX(-60); // Align to the right
            $this->Cell(0, 10, 'Date: ' . date('d.m.Y'), 0, 1, 'L'); // Set current date
            $this->SetX(-60);
            $this->Cell(0, 10, 'Bill No: ' . $this->info['invoice_number'], 0, 1, 'L'); // Use dynamic bill number from info

            // Add border around the date section
            $this->Rect(10, 6, 190, 39 + 20, 'D');
        }

        public function CustomerDetails()
        {
            // Add some space before customer details (reduced line break before customer details)
            $this->Ln(2); // Reduced line break before customer details

            // Set font for customer name
            $this->SetFont('Arial', '', 16);

            // Get the customer name from the info array
            $customerName = $this->info['cname'];

            // Move the Y position slightly down for the customer name
            $this->SetY($this->GetY() + 5); // Adjust this value for how much down you want

            // Prepare the full line with the customer name
            $this->Cell(0, 10, 'Mr/Mrs. ' . $customerName, 0, 1, 'L');

            // Move down slightly for better spacing before the body content
            $this->Ln(5); // Adjust this value for the gap before the body
        }

        public function Body($products_info)
        {
            // Additional space before the body
            $this->Ln(10); // Adjust this value to increase or decrease space before the body

            // Table Headers
            $this->SetY($this->GetY()); // Set Y to the current position
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(20, 10, 'Qty', 1, 0, 'C');
            $this->Cell(100, 10, 'Description', 1, 0, 'C');
            $this->Cell(30, 10, 'Price', 1, 0, 'C');
            $this->Cell(40, 10, 'Amount', 1, 1, 'C'); // End of row

            // Calculate available space for the rows before hitting the footer
            $startY = $this->GetY(); // Get the Y position where the table starts
            $availableSpace = $this->h - $this->bMargin - $startY - 30; // Total available space for the table, leaving space for footer

            $rowHeight = 10; // Height of each row
            $maxRows = floor($availableSpace / $rowHeight); // Max number of rows that fit before the footer

            // Table Rows with Product Data
            $this->SetFont('Arial', '', 12);
            $numProducts = count($products_info);
            $rowsDisplayed = 0;

            foreach ($products_info as $row) {
                $this->CheckPageBreak($rowHeight); // Ensure there is enough space for the next row
                $this->Cell(20, 10, $row["qty"], 1, 0, 'C');
                $this->Cell(100, 10, $row["name"], 1, 0);
                $this->Cell(30, 10, number_format($row["price"], 2), 1, 0, 'R');
                $this->Cell(40, 10, number_format($row["total"], 2), 1, 1, 'R');
                $rowsDisplayed++;
            }

            // Fill the remaining rows with empty cells to reach the footer
            for ($i = $rowsDisplayed; $i < $maxRows; $i++) {
                $this->CheckPageBreak($rowHeight); // Ensure enough space for the empty row
                $this->Cell(20, 10, '', 1, 0);
                $this->Cell(100, 10, '', 1, 0);
                $this->Cell(30, 10, '', 1, 0);
                $this->Cell(40, 10, '', 1, 1);
            }

            // Check if there is enough space for the total row before the footer
            if ($this->GetY() + $rowHeight + 10 > $this->PageBreakTrigger) {
                // If not enough space, add a new page
                $this->AddPage($this->CurOrientation);
            }

            // Total Row
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(150, 10, 'Total', 1, 0, 'R');
            $this->Cell(40, 10, number_format($this->info['total_amt'], 2), 1, 1, 'R');

        }

// Method to check if page break is needed
        public function CheckPageBreak($height)
        {
            // If the height of the next row will exceed the page height, add a new page
            if ($this->GetY() + $height > $this->PageBreakTrigger - 30) { // Leave space for footer
                $this->AddPage($this->CurOrientation);
                // Optionally, re-add the table headers
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(20, 10, 'Qty', 1, 0, 'C');
                $this->Cell(100, 10, 'Description', 1, 0, 'C');
                $this->Cell(30, 10, 'Price', 1, 0, 'C');
                $this->Cell(40, 10, 'Amount', 1, 1, 'C'); // End of header row
            }
        }
        public function Footer()
        {
            // Set Y position for the footer
            $this->SetY(-30);

            // Draw an empty box in the footer
            $this->SetLineWidth(0.5); // Set the line width for the box
            $this->Rect(10, $this->GetY(), 190, 20); // Draw a rectangle (x, y, width, height)
            // Adjust the coordinates and dimensions as needed
        }

    }

    // Prepare the info array before using it
    $info = [
        "cname" => $customer_name,
        "invoice_number" => $invoice_number,
        "invoice_date" => $invoice_date,
        "total_amt" => $total_cost,
        "amount_paid" => $amt_paid,
        "balance" => $balance,
    ];

    $pdf = new PDF();
    $pdf->setInfo($info); // Now this method is defined
    $pdf->AddPage();
    $pdf->DateInvoice(); // No need to pass info anymore
    $pdf->CustomerDetails(); // No need to pass info anymore

    // Prepare product info for the PDF
    $products_info = [];
    for ($i = 0; $i < count($description); $i++) {
        $products_info[] = [
            "name" => $description[$i],
            "price" => $unit_price[$i],
            "qty" => $qty[$i],
            "total" => $total[$i],
        ];
    }

    $pdf->Body($products_info);
    $pdf->Output('I', 'invoice_' . $invoice_id . '.pdf');
}
