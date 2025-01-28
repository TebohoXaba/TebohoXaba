<?php
require 'vendor/autoload.php';

// Generate Invoice PDF using mPDF
$mpdf = new \Mpdf\Mpdf();
$html = "
<style>
    body {
        font-family: 'Arial', sans-serif;
        color: #333;
        margin: 0;
        padding: 0;
    }
    .invoice-container {
        max-width: 800px;
        margin: 30px auto;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    .header {
        background-color: darkslategrey;
        color: white;
        padding: 20px;
        text-align: center;
    }
    .header h1 {
        margin: 0;
        font-size: 26px;
        font-weight: bold;
    }
    .header p {
        margin: 5px 0;
        font-size: 16px;
    }
    .branding {
        display: flex;
        justify-content: space-between;
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 2px solid darkslategrey;
    }
    .branding img {
        height: 60px;
    }
    .branding .company-info {
        text-align: right;
    }
    .branding .company-info h3 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }
    .branding .company-info p {
        margin: 3px 0;
        font-size: 14px;
        color: #555;
    }
    .invoice-details {
        padding: 20px;
    }
    .invoice-details h2 {
        font-size: 20px;
        margin-bottom: 10px;
        border-bottom: 2px solid darkslategrey;
        padding-bottom: 5px;
    }
    .invoice-details p {
        margin: 5px 0;
        font-size: 14px;
    }
    .details-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .details-table th, .details-table td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .details-table th {
        background-color: darkslategrey;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
    .details-table td {
        font-size: 14px;
    }
    .total {
        text-align: right;
        padding: 10px;
        margin: 20px 0;
        font-size: 18px;
        font-weight: bold;
    }
    .footer {
        background: #f8f9fa;
        padding: 20px;
        text-align: center;
        font-size: 12px;
        color: #555;
    }
    .footer a {
        color: #007BFF;
        text-decoration: none;
    }
</style>

<div class='invoice-container'>
    <div class='header'>
        <h1>Invoice</h1>
        <p>Payment Confirmation</p>
        <p>Date: " . date('Y-m-d') . "</p>
    </div>
    
    <div class='branding'>
        <img src='https://i.ibb.co/B4YGVpt/ZX-logo-black.png' style='height: 60px; width: 150px;' alt='Logo'>
        <div class='company-info'>
            <h3>Shipping Company</h3>
            <p>4604 Mhandzela street</p>
            <p>Soweto, South Africa</p>
            <p>Email: zxfleetpartners@gmail.com</p>
        </div>
    </div>
    
    <div class='invoice-details'>
        <h2>Customer Information</h2>
        <p><strong>Name:</strong> </p>
        <p><strong>Email:</strong> </p>
    </div>
    
    <table class='details-table'>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount (R)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Shipping Fee</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    
    <div class='total'>
        Total Paid: R 
    </div>
    
    <div class='footer'>
        <p>If you have any questions, please contact our support team at <a href='mailto:support@shipping.com'>zxfleetpartners@gmail.com</a>.</p>
        <p>Shipping Team, © " . date('Y') . "</p>
    </div>
</div>
";

$mpdf->WriteHTML($html);
$mpdf->Output('example.pdf', 'D'); // Outputs the PDF for download
?>
