<?php 
include 'db_connection.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Details</title>

    <!-- Bootstrap, jQuery & DataTables -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
    body {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(to right, #f8f9fa, #e9ecef);
        text-align: center;
    }

    table {
        width: 80%;
        margin: 20px auto;
        border-collapse: collapse;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: center;
        transition: all 0.3s ease-in-out;
    }

    th {
        background-color: #6c757d;
        color: white;
        text-transform: uppercase;
        font-weight: bold;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #dcdcdc;
        transform: scale(1.02);
    }

    .btn {
        padding: 8px 12px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
        margin: 5px;
        font-size: 14px;
        transition: 0.3s ease-in-out;
    }

    .add {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 12px 18px;
        background-color: rgb(133, 13, 13);
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        transition: 0.3s ease-in-out;
    }

    .add:hover {
        background-color: #0056b3;
        transform: scale(1.1);
    }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Invoice Details</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal" onclick="openModal('add')">Add Invoice</button>

    <table id="invoiceTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Description</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="invoiceForm">
                    <input type="hidden" id="invoiceId">
                    <div class="mb-3">
                        <label>Item Description</label>
                        <input type="text" id="itemDescription" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Quantity</label>
                        <input type="number" id="quantity" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Price</label>
                        <input type="text" id="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Total Amount</label>
                        <input type="text" id="totalAmount" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadInvoices();
});

// Load Invoice Data
function loadInvoices() {
    $.ajax({
        url: 'action1.php',
        type: 'POST',
        data: { action: 'fetch' },
        dataType: 'json',
        cache: false,
        success: function(response) {
            let rows = '';
            let i = 1;
            response.forEach(invoice => {
                rows += `<tr>
                    <td>${i}</td>
                    <td>${invoice.item_description}</td>
                    <td>${invoice.quantity}</td>
                    <td>${invoice.price}</td>
                    <td>${invoice.amount}</td>
                    <td>
                        <a href="generate_invoice.php?id=${invoice.id}" class="btn btn-info">Download PDF</a>
                        <a href="view_invoice.php?id=${invoice.id}" class="btn btn-warning">View PDF</a>
                    </td>
                </tr>`;
                i++;
            });

            if ($.fn.DataTable.isDataTable('#invoiceTable')) {
                $('#invoiceTable').DataTable().clear().destroy();
            }

            $('#invoiceTable tbody').html(rows);

            $('#invoiceTable').DataTable({
                "pageLength": 5,
                "lengthMenu": [5, 10, 25, 50],
                "ordering": true,
                "searching": true
            });
        }
    });
}

// Open Modal for Add/Edit
window.openModal = function(mode, id = '', itemDescription = '', quantity = '', price = '', totalAmount = '') {
    $('#modalTitle').text(mode === 'add' ? 'Add Invoice' : 'Edit Invoice');
    $('#invoiceId').val(id);
    $('#itemDescription').val(itemDescription);
    $('#quantity').val(quantity);
    $('#price').val(price);
    $('#totalAmount').val(totalAmount);
    $('#invoiceModal').modal('show');
};

// Delete Invoice
window.deleteInvoice = function(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'action1.php',
                type: 'POST',
                data: { id, action: 'delete' },
                success: function(response) {
                    Swal.fire('Deleted!', response, 'success');
                    loadInvoices();
                }
            });
        }
    });
};
</script>

</body>
</html>
