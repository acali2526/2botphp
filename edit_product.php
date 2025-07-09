<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="public/css/output.css" rel="stylesheet">
</head>
<body class="flex">

    <?php include '_sidebar.php'; ?>

    <main class="flex-grow p-6">
        <h1 class="text-3xl font-bold mb-4">Edit Product</h1>

        <div id="formMessageContainer" class="mb-4">
            <!-- Messages will be displayed here -->
        </div>

        <form id="editProductForm" class="space-y-4 max-w-xl">
            <input type="hidden" id="productId" name="id">
            <div>
                <label class="label" for="name"><span class="label-text">Product Name</span></label>
                <input type="text" id="name" name="name" placeholder="Enter product name" class="input input-bordered w-full" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="sku"><span class="label-text">SKU</span></label>
                    <input type="text" id="sku" name="sku" placeholder="Stock Keeping Unit" class="input input-bordered w-full" required />
                </div>
                <div>
                    <label class="label" for="item_number"><span class="label-text">Item Number</span></label>
                    <input type="text" id="item_number" name="item_number" placeholder="Manufacturer/Supplier Item #" class="input input-bordered w-full" required />
                </div>
            </div>

            <div>
                <label class="label" for="barcode"><span class="label-text">Barcode (EAN/UPC)</span></label>
                <input type="text" id="barcode" name="barcode" placeholder="Enter product barcode" class="input input-bordered w-full" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="category"><span class="label-text">Category</span></label>
                    <input type="text" id="category" name="category" placeholder="e.g., Electronics, Books" class="input input-bordered w-full" />
                </div>
                <div>
                    <label class="label" for="supplier_name"><span class="label-text">Supplier Name</span></label>
                    <input type="text" id="supplier_name" name="supplier_name" placeholder="Supplier or Vendor Name" class="input input-bordered w-full" />
                </div>
            </div>

            <div>
                <label class="label" for="description"><span class="label-text">Description</span></label>
                <textarea id="description" name="description" placeholder="Detailed product description" class="textarea textarea-bordered w-full h-24"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="cost_price"><span class="label-text">Cost Price ($)</span></label>
                    <input type="number" id="cost_price" name="cost_price" placeholder="0.00" class="input input-bordered w-full" required min="0.01" step="0.01" />
                </div>
                <div>
                    <label class="label" for="sell_price"><span class="label-text">Sell Price ($)</span></label>
                    <input type="number" id="sell_price" name="sell_price" placeholder="0.00" class="input input-bordered w-full" required min="0.01" step="0.01" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="current_quantity"><span class="label-text">Current Quantity</span></label>
                    <input type="number" id="current_quantity" name="current_quantity" placeholder="0" class="input input-bordered w-full" required min="0" step="1" />
                </div>
                <div>
                    <label class="label" for="reorder_level"><span class="label-text">Reorder Level</span></label>
                    <input type="number" id="reorder_level" name="reorder_level" placeholder="0" class="input input-bordered w-full" min="0" step="1" />
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-accent w-full md:w-auto">Update Product</button>
            </div>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editProductForm = document.getElementById('editProductForm');
            const formMessageContainer = document.getElementById('formMessageContainer');
            const productIdInput = document.getElementById('productId');
            const submitButton = editProductForm.querySelector('button[type="submit"]');

            function displayEditFormMessage(message, type = 'info') {
                const alertClasses = {
                    info: 'alert-info', success: 'alert-success', warning: 'alert-warning', error: 'alert-error'
                };
                const alertClass = alertClasses[type] || 'alert-info';
                formMessageContainer.innerHTML = `
                    <div class="alert ${alertClass} shadow-lg">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                ${type === 'success' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />' : ''}
                                ${type === 'error' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />' : ''}
                                ${type === 'info' || type === 'warning' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' : ''}
                            </svg>
                            <span>${(Array.isArray(message) ? message.map(m => `<li>${m}</li>`).join('') : message)}</span>
                        </div>
                    </div>`;
                if (type !== 'error') setTimeout(() => { formMessageContainer.innerHTML = ''; }, 5000);
            }

            const urlParams = new URLSearchParams(window.location.search);
            const currentProductId = urlParams.get('id');

            if (!currentProductId || isNaN(parseInt(currentProductId)) || parseInt(currentProductId) <= 0) {
                displayEditFormMessage('Invalid or missing Product ID in the URL.', 'error');
                editProductForm.style.display = 'none'; // Hide form
                return;
            }

            productIdInput.value = currentProductId;
            displayEditFormMessage('Loading product data...', 'info');
            submitButton.disabled = true;

            fetch(`app/api/products/read_single.php?id=${currentProductId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => {
                            throw new Error(errData.message || `HTTP error! Status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(productData => {
                    formMessageContainer.innerHTML = ''; // Clear loading message
                    submitButton.disabled = false;
                    // Populate form fields
                    document.getElementById('name').value = productData.name || '';
                    document.getElementById('sku').value = productData.sku || '';
                    document.getElementById('item_number').value = productData.item_number || '';
                    document.getElementById('barcode').value = productData.barcode || '';
                    document.getElementById('category').value = productData.category || '';
                    document.getElementById('supplier_name').value = productData.supplier_name || '';
                    document.getElementById('description').value = productData.description || '';
                    document.getElementById('cost_price').value = productData.cost_price !== null ? productData.cost_price : '';
                    document.getElementById('sell_price').value = productData.sell_price !== null ? productData.sell_price : '';
                    document.getElementById('current_quantity').value = productData.current_quantity !== null ? productData.current_quantity : '';
                    document.getElementById('reorder_level').value = productData.reorder_level !== null ? productData.reorder_level : '';
                })
                .catch(error => {
                    console.error('Error fetching product data:', error);
                    displayEditFormMessage(`Error loading product data: ${error.message}`, 'error');
                    editProductForm.style.display = 'none'; // Hide form on error
                    submitButton.disabled = true;
                });

            editProductForm.addEventListener('submit', function(event) {
                event.preventDefault();
                formMessageContainer.innerHTML = ''; // Clear previous messages

                const formData = new FormData(editProductForm);
                const productData = {
                    id: parseInt(productIdInput.value) // Ensure ID is included and is an integer
                };

                for (let [key, value] of formData.entries()) {
                    if (key === 'id') continue; // Already handled

                    if (['cost_price', 'sell_price', 'reorder_level', 'current_quantity'].includes(key)) {
                        productData[key] = value === '' ? null : parseFloat(value);
                        if (key === 'reorder_level' && productData[key] === null && value !== '') {
                             productData[key] = parseFloat(value);
                        } else if (isNaN(productData[key]) && value !== '') {
                            productData[key] = value;
                        }
                    } else {
                        productData[key] = value.trim();
                    }
                }

                // Filter out fields that were not actually changed or are empty optional strings,
                // to send a cleaner payload, though the backend should handle this.
                // For simplicity here, we send all fields that were on the form.
                // The backend update logic should only update fields that are present in the request.

                const originalButtonText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Updating...';
                displayEditFormMessage('Submitting updated data...', 'info');

                fetch('app/api/products/update.php', {
                    method: 'POST', // Or 'PUT', ensure API supports it
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(productData)
                })
                .then(response => {
                    const status = response.status;
                    const ok = response.ok;
                    // Try to parse JSON regardless of 'ok' status, as error responses might also be JSON
                    return response.json().then(data => ({ status, ok, body: data }))
                        .catch(jsonError => {
                            // If JSON parsing fails, create a synthetic error object
                            console.warn('Failed to parse JSON response:', jsonError);
                            return { status, ok, body: { message: `Invalid response from server (Status: ${status})` }, errorParsing: true };
                        });
                })
                .then(result => {
                    if (result.ok && (result.status === 200 || result.status === 200)) { // update.php returns 200
                        displayEditFormMessage(result.body.message || 'Product updated successfully!', 'success');
                        // Optionally, re-fetch and re-populate to confirm changes or if API modifies data.
                        // For now, just show success. User can refresh or navigate away.
                    } else {
                        let errorMessage = 'An error occurred during update.';
                        if (result.body) {
                            if (result.body.errors && Array.isArray(result.body.errors)) {
                                errorMessage = result.body.errors; // Pass array to displayEditFormMessage
                            } else if (result.body.message) {
                                errorMessage = result.body.message;
                            } else if (result.errorParsing) {
                                errorMessage = result.body.message; // Use synthetic message
                            }
                        } else if (!result.ok) {
                             errorMessage = `Error: ${result.status} - Failed to update product.`;
                        }
                        displayEditFormMessage(errorMessage, 'error');
                    }
                })
                .catch(error => {
                    // This catches network errors or errors from the initial response.json() if not caught above
                    console.error('Update submission error:', error);
                    displayEditFormMessage('An unexpected network error occurred during update.', 'error');
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                });
            });
        });
    </script>

</body>
</html>
