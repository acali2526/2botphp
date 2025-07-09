<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Product</title>
  <!-- Your compiled Tailwind CSS -->
  <link href="public/css/output.css" rel="stylesheet">
</head>
<body class="flex">

  <?php include '_sidebar.php'; ?>

  <main class="flex-grow p-6">
    <h1 class="text-3xl font-bold mb-4">Add New Product</h1>

    <div id="formMessageContainer" class="mb-4">
      <!-- Messages (info, success, error) will appear here -->
    </div>

    <form id="addProductForm" class="space-y-4 max-w-xl">
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
        <button type="submit" class="btn btn-primary w-full md:w-auto">Add Product</button>
      </div>
    </form>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const addProductForm = document.getElementById('addProductForm');
      const formMessageContainer = document.getElementById('formMessageContainer');

      function displayFormMessage(message, type = 'info') {
        const alertClasses = {
          info: 'alert-info',
          success: 'alert-success',
          warning: 'alert-warning',
          error: 'alert-error'
        };
        const alertClass = alertClasses[type] || 'alert-info';

        let content = '';
        if (type === 'error' && Array.isArray(message)) {
          content = '<ul>' + message.map(err => `<li>${err}</li>`).join('') + '</ul>';
        } else {
          content = message;
        }

        formMessageContainer.innerHTML = `
          <div class="alert ${alertClass} shadow-lg">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                ${type === 'success' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />' : ''}
                ${type === 'error'   ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />' : ''}
                ${(type === 'info' || type === 'warning') 
                  ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' 
                  : ''}
              </svg>
              <span>${content}</span>
            </div>
          </div>`;
        if (type !== 'error') {
          setTimeout(() => { formMessageContainer.innerHTML = ''; }, 5000);
        }
      }

      addProductForm.addEventListener('submit', function(e) {
        e.preventDefault();
        formMessageContainer.innerHTML = '';

        const formData = new FormData(addProductForm);
        const data = {};

        for (let [key, val] of formData.entries()) {
          if (['cost_price','sell_price','reorder_level','current_quantity'].includes(key)) {
            data[key] = val === '' ? null : parseFloat(val);
          } else {
            data[key] = val.trim();
          }
        }

        displayFormMessage('Submitting product data...', 'info');
        const btn = addProductForm.querySelector('button[type="submit"]');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Adding...';

        fetch('app/api/products/create.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify(data),
        })
        .then(res => res.json().then(body => ({ ok: res.ok, status: res.status, body })))
        .then(({ ok, status, body }) => {
          if (ok && status === 201) {
            displayFormMessage(`Product created successfully! ID: ${body.id}`, 'success');
            addProductForm.reset();
          } else {
            let err = body.errors || body.message || `Error ${status}`;
            displayFormMessage(err, 'error');
          }
        })
        .catch(() => {
          displayFormMessage('Network error. Please try again.', 'error');
        })
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = origText;
        });
      });
    });
  </script>

</body>
</html>
