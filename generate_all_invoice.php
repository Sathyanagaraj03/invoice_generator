<?php
require_once('C:\xampp\htdocs\task\TCPDF-main\tcpdf.php'); 
include 'db_connection.php';

// Fetch all invoice items
$sql = "SELECT * FROM invoice";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("No invoices found!");
}

// Extend TCPDF for custom header and footer

class MYPDF extends TCPDF {
    public function Header() {
        
        $this->SetY(25);
        $this->SetX(140);
        $this->SetFont('helvetica', '', 33);
        $this->Cell(51, 10, 'Invoice', 0, 1, 'R'); 

        $this->SetFont('helvetica', '', 8);
        $this->SetX(135);
        date_default_timezone_set('Asia/Kolkata');
        $this->Cell(50, 7, 'DATE: ' . date('d-m-Y'), 0, 1, 'R'); 
        
        $this->SetX(135);
        global $invoice;
        $this->Cell(50, 5, 'INVOICE: ' ."invoice", 0, 1, 'R');
        $this->Ln(15);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(12, 50, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 12);
$pdf->AddPage();

// FROM & TO Section 
$pdf->SetX(12); 
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(20, 6, 'FROM:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(75, 6, 'Company Name', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(20, 6, 'TO:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 7);
$pdf->SetX(120); 
$pdf->Cell(75, 6, 'Client Name', 0, 1, 'L');

$pdf->SetFont('', '', 7);
$pdf->Cell(20, 5, '', 0, 0, 'L');
$pdf->Cell(75, 5, 'client email address', 0, 0, 'L'); 

$pdf->Cell(20, 5, '', 0, 0, 'L');
$pdf->SetX(120); 
$pdf->Cell(75, 5, 'client email address', 0, 1, 'L'); 

$pdf->Cell(20, 5, '', 0, 0, 'L');
$pdf->Cell(75, 5, 'Address 1', 0, 0, 'L'); 

$pdf->Cell(20, 5, '', 0, 0, 'L');
$pdf->SetX(120); 
$pdf->Cell(75, 5, 'Client Address 1', 0, 1, 'L'); 

$pdf->Cell(20, 5, '', 0, 0, 'L');
$pdf->Cell(75, 5, 'Address 2', 0, 0, 'L'); 
$pdf->Cell(20, 5, '', 0, 0, 'L');

$pdf->SetX(120); 
$pdf->Cell(75, 5, 'Client Address 2', 0, 1, 'L'); 
$pdf->Ln(7);

// TERMS & DUE DATE - One by One and Values Near 
$pdf->SetX(15); 
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(20, 5, 'TERMS:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(50, 5, 'Payment Terms', 0, 1, 'L');

$pdf->SetX(15); 
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(20, 5, 'DUE DATE:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(50, 5, 'Due Date', 0, 1, 'L');


// Table Header Function
function addTableHeader($pdf) {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Ln(3);
    $pdf->setX(15);
    
    $pdf->Cell(10, 8, 'ID', 1, 0, 'C');
    $pdf->Cell(78, 8, 'Item Description', 1, 0, 'C'); 
    $pdf->Cell(30, 8, 'Quantity', 1, 0, 'C');
    $pdf->Cell(29, 8, 'Price', 1, 0, 'C');
    $pdf->Cell(33, 8, 'Amount', 1, 1, 'C');
}

$item_count = 0;
$max_items_per_page = 15;
$invoice_amount = 0;


addTableHeader($pdf);
$pdf->SetFont('', '', 10);

while ($invoice = $result->fetch_assoc()) {
    if ($item_count == $max_items_per_page) {
        $pdf->AddPage();
        addTableHeader($pdf);
        $item_count = 0;
    }

    $pdf->setX(15);
    $pdf->Cell(10, 8, $invoice['id'], 1, 0, 'C');
    $pdf->Cell(78, 8, $invoice['item_description'], 1, 0, 'L');
    $pdf->Cell(30, 8, $invoice['quantity'], 1, 0, 'C');
    $pdf->Cell(29, 8, '$' . number_format($invoice['price'], 2), 1, 0, 'C');
    $pdf->Cell(33, 8, '$' . number_format($invoice['amount'], 2), 1, 1, 'C');
    
    $invoice_amount += $invoice['price'] * $invoice['quantity'];
    $item_count++;
}

// Ensure totals are at the last table's end
$pdf->SetFont('helvetica', '', 10);
$pdf->setX(122.5); 
$pdf->Cell(40, 6, 'Subtotal:', 0, 0, 'R'); 
$pdf->Cell(32, 8, '$' . number_format($invoice_amount, 2), 1, 1, 'C'); 

$pdf->setX(122.5);
$pdf->Cell(40, 6, 'Tax (10%):', 0, 0, 'R'); 
$tax = $invoice_amount * 0.1;
$pdf->Cell(32, 8, '$' . number_format($tax, 2), 1, 1, 'C'); 

$pdf->SetFont('helvetica', 'B', 12);
$pdf->setX(122.5); 
$pdf->Cell(40, 8, 'BALANCE DUE:', 0, 0, 'R'); 
$total_due = $invoice_amount + $tax;
$pdf->Cell(32, 8, '$' . number_format($total_due, 2), 1, 1, 'C'); 

$pdf->Ln(15);

// Notes Section 
$pdf->SetX(25); 
$pdf->SetFont('helvetica', '', 14);
$pdf->Cell(0, 8, 'Notes', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetX(25); 
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(168, 35, 'Enter notes and other special considerations here', 1, 'L'); 

$pdf->Output('Invoice_' . date('d-m-Y_His') . '.pdf', 'I');
?>
