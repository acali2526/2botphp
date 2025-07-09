<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products Page</title>
  <!-- Tailwind and DaisyUI from CDN -->
  <link href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex">

  <?php include '_sidebar.php'; ?>

  <main class="flex-grow p-6">
    <h1 class="text-3xl font-bold mb-4">Products List</h1>

    <div id="message-container" class="mb-4">
      <div class="alert alert-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Loading products...</span>
      </div>
    </div>

    <div id="product-table-container" class="overflow-x-auto">
      <!-- Table will be rendered here by JavaScript -->
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const messageContainer = document.getElementById('message-container');
      const productTableContainer = document.getElementById('product-table-container');

      function displayMessage(message, type = 'info') {
        productTableContainer.innerHTML = '';
        const alertClasses = {
          info: 'alert-info',
          success: 'alert-success',
          warning: 'alert-warning',
          error: 'alert-error'
        };
        const alertClass = alertClasses[type] || 'alert-info';
        messageContainer.innerHTML = `
          <div class="alert ${alertClass} shadow-lg">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                ${
                  type === 'error'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
              </svg>
              <span>${message}</span>
            </div>
          </div>`;
      }

      function renderProductsTable(products) {
        messageContainer.innerHTML = '';
        productTableContainer.innerHTML = '';

        if (!products || products.length === 0) {
          displayMessage('No products found. Add some new products!', 'info');
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
          </tr>`;
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        products.forEach(product => {
          const tr = document.createElement('tr');
          tr.className = 'hover';
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
          `;
          tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        productTableContainer.appendChild(table);
      }

      displayMessage('Loading products...', 'info');

      fetch('app/api/products/read.php')
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          if (data && Array.isArray(data)) {
            renderProductsTable(data);
          } else if (data && data.message) {
            displayMessage(data.message, 'info');
          } else {
            renderProductsTable([]);
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          displayMessage(`Error fetching products: ${error.message}`, 'error');
        });
    });
  </script>

</body>
</html>
