<?php
require 'fpdf186/fpdf.php';
include 'db.php'; // Include your database connection file

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

// Check if the invoice ID is provided
if (isset($_GET['id'])) {
    $invoice_id = $_GET['id'];

    // Fetch invoice data based on the invoice ID from your database
    // Example query to fetch invoice info
    // Assuming your database returns the following fields
    $sql = "SELECT * FROM sales WHERE id = $invoice_id";
    $result = mysqli_query($con, $sql);
    $invoice = mysqli_fetch_assoc($result);

    // Prepare the info array
    $info = [
        "cname" => $invoice['cname'],
        "invoice_number" => $invoice['invoice_number'],
        "invoice_date" => $invoice['invoice_date'],
        "total_amt" => $invoice['total_cost'],
    ];

    $pdf = new PDF();
    $pdf->setInfo($info);
    $pdf->AddPage();
    $pdf->DateInvoice();
    $pdf->CustomerDetails();

    // Fetch product details
    $sql_products = "SELECT * FROM sales_items WHERE invoice_id = $invoice_id";
    $result_products = mysqli_query($con, $sql_products);
    $products_info = [];
    while ($product = mysqli_fetch_assoc($result_products)) {
        $products_info[] = [
            "name" => $product['description'],
            "price" => $product['unit_price'],
            "qty" => $product['qty'],
            "total" => $product['total'],
        ];
    }

    $pdf->Body($products_info);
    $pdf->Output('D', 'invoice_' . $invoice_id . '.pdf'); // 'D' for download
    exit();
} else {
    echo 'Invoice ID is missing.';
}
