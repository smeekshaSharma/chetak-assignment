<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product CRUD</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .modal-dialog {
            max-width: 600px;
        }
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Product Management</h1>
        <div id="alertContainer"></div> 
        <button class="btn btn-primary mb-3" id="addProductBtn">Add Product</button>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="productTable">
            <thead class="table-light">
    <tr>
        <th>ID</th>
        <th><a href="#" class="sort" data-sort="name" data-order="{{ request('order', 'asc') }}">Name</a></th>
        <th><a href="#" class="sort" data-sort="price" data-order="{{ request('order', 'asc') }}">Price</a></th>
        <th><a href="#" class="sort" data-sort="quantity" data-order="{{ request('order', 'asc') }}">Quantity</a></th>
        <th>Description</th>
        <th>Actions</th>
    </tr>
</thead>
                <tbody>
                    @foreach($products as $product)
                    <tr data-id="{{ $product->id }}">
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>₹ {{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ $product->description }}</td>
                        <td>
                            <button class="btn btn-success btn-sm editBtn">Edit</button>
                            <button class="btn btn-danger btn-sm deleteBtn">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Add/Update Product Form -->
        <div class="modal fade" id="productFormModal" tabindex="-1" aria-labelledby="productFormModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productFormModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <form id="productForm">
    <input type="hidden" id="id">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" required minlength="2" maxlength="255">
        <div class="invalid-feedback">Please enter a valid name between 2 and 255 characters.</div>
    </div>
    <div class="mb-3">
        <label for="price" class="form-label">Price</label>
        <input type="number" class="form-control" id="price" step="0.01" required>
        <div class="invalid-feedback">Please enter a valid price.</div>
    </div>
    <div class="mb-3">
        <label for="quantity" class="form-label">Quantity</label>
        <input type="number" class="form-control" id="quantity" required min="1" max="10000">
        <div class="invalid-feedback">Please enter a valid quantity between 1 and 10,000.</div>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <input type="text" class="form-control" id="description" required minlength="5" maxlength="500">
        <div class="invalid-feedback">Please enter a description between 5 and 500 characters.</div>
    </div>
</form>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveProductBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#addProductBtn').click(function() {
        $('#productFormModalLabel').text('Add Product');
        $('#productForm')[0].reset();
        $('#id').val('');
        $('#productFormModal').modal('show');
    });

    // Save product
    $('#saveProductBtn').click(function() {
        // Clear previous validation messages
        $('#productForm').removeClass('was-validated');
        $('#productForm .form-control').removeClass('is-invalid');

        // Check if form is valid
        if ($('#productForm')[0].checkValidity() === false) {
            $('#productForm').addClass('was-validated');
            return;
        }

        var id = $('#id').val();
        var name = $('#name').val();
        var price = $('#price').val();
        var quantity = $('#quantity').val();
        var description = $('#description').val();

        var method = id ? 'PUT' : 'POST';
        var url = id ? '/products/' + id : '/products';
        var data = {
            name: name,
            price: price,
            quantity: quantity,
            description: description
        };

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function() {
                showAlert('Product saved successfully!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1000); 
            },
            error: function() {
                showAlert('An error occurred while saving the product.', 'danger');
            }
        });
    });

    // Edit product
    $('#productTable').on('click', '.editBtn', function() {
        var row = $(this).closest('tr');
        var id = row.data('id');
        $.ajax({
            url: '/products/' + id,
            method: 'GET',
            success: function(product) {
                $('#productFormModalLabel').text('Edit Product');
                $('#id').val(product.id);
                $('#name').val(product.name);
                $('#price').val(product.price);
                $('#quantity').val(product.quantity);
                $('#description').val(product.description);
                $('#productFormModal').modal('show');
            }
        });
    });

    // Delete product
    $('#productTable').on('click', '.deleteBtn', function() {
        var row = $(this).closest('tr');
        var id = row.data('id');
        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: '/products/' + id,
                method: 'DELETE',
                success: function() {
                    showAlert('Product deleted successfully!', 'success');
                    // Update the table by reloading the page
                    setTimeout(function() {
                        location.reload();
                    }, 1000); 
                },
                error: function() {
                    showAlert('An error occurred while deleting the product.', 'danger');
                }
            });
        }
    });

    // Handle sorting
    $('.sort').click(function(e) {
        e.preventDefault();
        var sortBy = $(this).data('sort');
        var sortOrder = $(this).data('order') === 'desc' ? 'asc' : 'desc';
        $(this).data('order', sortOrder);
        window.location.href = `?sort_by=${sortBy}&order=${sortOrder}`;
    });

    // Function to show alert messages
    function showAlert(message, type) {
        var alertHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                        message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
        $('#alertContainer').html(alertHTML);
    }
});


    </script>
</body>
</html>