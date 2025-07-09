<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add New Product</title>
  <!-- Your compiled Tailwind CSS -->
  <link href="public/css/output.css" rel="stylesheet">
</head>
<body class="flex">

  <?php include '_sidebar.php'; ?>

  <main class="flex-grow p-6">
    <h1 class="text-3xl font-bold mb-4">Add New Product</h1>
    <p>This is where the form to add a new product will be.</p>
    <form class="mt-4 space-y-4">
      <div>
        <label class="label" for="product_name">
          <span class="label-text">Product Name</span>
        </label>
        <input 
          type="text" 
          id="product_name" 
          placeholder="Enter product name" 
          class="input input-bordered w-full max-w-xs" 
          required
        />
      </div>
      <div>
        <button type="submit" class="btn btn-accent">Save Product</button>
      </div>
    </form>
  </main>

</body>
</html>
