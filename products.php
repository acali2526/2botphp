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
      <input
        type="text"
        id="searchInput"
        placeholder="Search by Name, SKU, Barcode, Item #..."
        class="input input-bordered w-full max-w-md mr-2 shadow-sm"
      >
      <button id="clearSearchBtn" class="btn btn-ghost hidden">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Clear
      </button>
    </div>

    <div id="message-container" class="mb-4">
      <div class="alert alert-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             class="stroke-current shrink-0 w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Loading products...</span>
      </div>
    </div>

    <div id="product-table-container" class="overflow-x-auto">
      <!-- Product table will be rendered here by JavaScript -->
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const msgContainer = document.getElementById('message-container');
      const tableContainer = document.getElementById('product-table-container');
      const searchInput = document.getElementById('searchInput');
      const clearBtn    = document.getElementById('clearSearchBtn');

      let allProducts = [];

      function displayMessage(text, type = 'info') {
        tableContainer.innerHTML = '';
        const classes = {
          info:    'alert-info',
          success: 'alert-success',
          warning: 'alert-warning',
          error:   'alert-error'
        };
        msgContainer.innerHTML = `
          <div class="alert ${classes[type] || classes.info} shadow-lg">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   class="stroke-current shrink-0 w-6 h-6">
                ${type === 'error'
                  ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                  : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'}
              </svg>
              <span>${text}</span>
            </div>
          </div>`;
      }

      function renderTable(products) {
        msgContainer.innerHTML = '';
        tableContainer.innerHTML = '';

        if (!products.length) {
          const term = searchInput.value.trim();
          if (term) {
            displayMessage(`No products match your search for "${term}".`, 'warning');
          } else {
            displayMessage('No products found. Add some new products!', 'info');
          }
          return;
        }

        const table = document.createElement('table');
        table.className = 'table table-zebra w-full shadow-md';

        table.innerHTML = `
          <thead>
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
            </tr>
          </thead>
          <tbody>
            ${products.map(p => `
              <tr class="hover">
                <td>${p.name || ''}</td>
                <td>${p.sku || ''}</td>
                <td>${p.category || ''}</td>
                <td>${p.supplier_name || ''}</td>
                <td>${p.item_number || ''}</td>
                <td class="max-w-xs truncate" title="${p.description || ''}">${p.description || ''}</td>
                <td>${typeof p.cost_price === 'number' ? '$' + p.cost_price.toFixed(2) : ''}</td>
                <td>${typeof p.sell_price === 'number' ? '$' + p.sell_price.toFixed(2) : ''}</td>
                <td>${p.reorder_level ?? ''}</td>
                <td>${p.current_quantity ?? ''}</td>
                <td>${p.barcode || ''}</td>
                <td>
                  <a href="edit_product.php?id=${p.id}" class="btn btn-xs btn-outline btn-info">Edit</a>
                </td>
              </tr>
            `).join('')}
          </tbody>`;

        tableContainer.appendChild(table);
      }

      displayMessage('Loading products...', 'info');

      fetch('app/api/products/read.php')
        .then(res => {
          if (!res.ok) throw new Error(`Status ${res.status}`);
          return res.json();
        })
        .then(data => {
          allProducts = Array.isArray(data) ? data : [];
          renderTable(allProducts);
        })
        .catch(err => {
          console.error(err);
          displayMessage(`Error fetching products: ${err.message}`, 'error');
        });

      // Search/filter
      searchInput.addEventListener('input', () => {
        const term = searchInput.value.toLowerCase().trim();
        if (!term) {
          clearBtn.classList.add('hidden');
          return renderTable(allProducts);
        }
        clearBtn.classList.remove('hidden');
        renderTable(allProducts.filter(p =>
          (p.name || '').toLowerCase().includes(term) ||
          (p.sku || '').toLowerCase().includes(term) ||
          (p.barcode || '').toLowerCase().includes(term) ||
          (p.item_number || '').toLowerCase().includes(term)
        ));
      });

      clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        renderTable(allProducts);
        searchInput.focus();
      });
    });
  </script>

</body>
</html>
