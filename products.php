<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Page</title>
    <link href="public/css/output.css" rel="stylesheet">
</head>
<body class="flex">

    <?php include '_sidebar.php'; ?>

    <main class="flex-grow p-6">
        <h1 class="text-3xl font-bold mb-4">Products List</h1>

        <div class="mb-4 flex items-center">
            <input type="text" id="searchInput" placeholder="Search by Name, SKU, Barcode, Item #..." class="input input-bordered w-full max-w-md mr-2 shadow-sm">
            <button id="clearSearchBtn" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Clear
            </button>
        </div>

        <div id="message-container" class="mb-4">
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Loading products...</span>
            </div>
        </div>

        <div id="product-table-container" class="overflow-x-auto">
            <!-- Product table will be rendered here by JavaScript -->
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.getElementById('message-container');
            const productTableContainer = document.getElementById('product-table-container');
            const searchInput = document.getElementById('searchInput'); // Added for later use
            const clearSearchBtn = document.getElementById('clearSearchBtn'); // Added for later use

            let allFetchedProducts = []; // To store the original list of all products

            function displayMessage(message, type = 'info') {
                productTableContainer.innerHTML = ''; // Clear table area
                const alertClasses = {
                    info: 'alert-info',
                    success: 'alert-success',
                    warning: 'alert-warning',
                    error: 'alert-error'
                };
                const alertClass = alertClasses[type] || 'alert-info';
                // Using a generic icon for simplicity, can be changed per type
                messageContainer.innerHTML = `
                    <div class="alert ${alertClass} shadow-lg">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                ${type === 'error' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />' : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'}
                            </svg>
                            <span>${message}</span>
                        </div>
                    </div>`;
            }

            function renderProductsTable(productsToRender) {
                messageContainer.innerHTML = ''; // Clear any messages
                productTableContainer.innerHTML = ''; // Clear previous table

                if (!productsToRender || productsToRender.length === 0) {
                    if (searchInput.value.trim() !== '') {
                        // If search input has text, it means the filter yielded no results
                        displayMessage(`No products match your search for "${searchInput.value}".`, 'warning');
                    } else {
                        // If search input is empty, it means the original list is empty
                        displayMessage('No products found. Add some new products!', 'info');
                    }
                    return;
                }

                const table = document.createElement('table');
                table.className = 'table table-zebra w-full shadow-md';

                const thead = document.createElement('thead');
                thead.innerHTML = `
                    <tr class="bg-gray-200 text-gray-700">
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Item #</th>
                        <th class="max-w-xs truncate">Description</th>
                        <th>Cost Price</th>
                        <th>Sell Price</th>
                        <th>Reorder Lvl</th>
                        <th>Stock Qty</th>
                        <th>Barcode</th>
                        <th>Actions</th>
                    </tr>`;
                table.appendChild(thead);

                const tbody = document.createElement('tbody');
                productsToRender.forEach(product => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover'; // DaisyUI class for hover effect
                    tr.innerHTML = `
                        <td>${product.name || ''}</td>
                        <td>${product.sku || ''}</td>
                        <td>${product.category || ''}</td>
                        <td>${product.supplier_name || ''}</td>
                        <td>${product.item_number || ''}</td>
                        <td class="max-w-xs truncate" title="${product.description || ''}">${product.description || ''}</td>
                        <td>${typeof product.cost_price === 'number' ? '$' + product.cost_price.toFixed(2) : ''}</td>
                        <td>${typeof product.sell_price === 'number' ? '$' + product.sell_price.toFixed(2) : ''}</td>
                        <td>${product.reorder_level !== null ? product.reorder_level : ''}</td>
                        <td>${product.current_quantity !== null ? product.current_quantity : ''}</td>
                        <td>${product.barcode || ''}</td>
                        <td>
                            <a href="edit_product.php?id=${product.id}" class="btn btn-xs btn-outline btn-info">Edit</a>
                            <!-- Delete button will be added in Task 12 -->
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);
                productTableContainer.appendChild(table);
            }

            displayMessage('Loading products...', 'info'); // Initial message

            fetch('app/api/products/read.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && Array.isArray(data)) {
                        allFetchedProducts = data; // Store the original full list
                        renderProductsTable(allFetchedProducts);
                    } else if (data && data.message) {
                        allFetchedProducts = []; // Ensure it's an empty array for consistency
                        displayMessage(data.message, 'info');
                    } else {
                        allFetchedProducts = [];
                        renderProductsTable(allFetchedProducts); // Treat as empty list
                    }
                })
                .catch(error => {
                    allFetchedProducts = []; // Clear on error too
                    console.error('Fetch error:', error);
                    displayMessage(`Error fetching products: ${error.message}`, 'error');
                });

            // Search/Filter Logic
            searchInput.addEventListener('input', function() {
                const searchTerm = searchInput.value.toLowerCase().trim();

                if (searchTerm === '') {
                    renderProductsTable(allFetchedProducts);
                    clearSearchBtn.classList.add('hidden'); // Or disable
                    return;
                }

                clearSearchBtn.classList.remove('hidden'); // Or enable

                const filteredProducts = allFetchedProducts.filter(product => {
                    return (product.name && product.name.toLowerCase().includes(searchTerm)) ||
                           (product.sku && product.sku.toLowerCase().includes(searchTerm)) ||
                           (product.barcode && product.barcode.toLowerCase().includes(searchTerm)) ||
                           (product.item_number && product.item_number.toLowerCase().includes(searchTerm));
                });
                renderProductsTable(filteredProducts);
            });

            // Initially hide clear button (or set initial state based on searchInput.value)
            if (searchInput.value === '') {
                clearSearchBtn.classList.add('hidden');
            }

            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                renderProductsTable(allFetchedProducts);
                clearSearchBtn.classList.add('hidden'); // Hide after clearing
                searchInput.focus(); // Optional: refocus on search input
            });
        });
    </script>

</body>
</html>
