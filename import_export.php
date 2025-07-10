<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import/Export Products</title>
    <link href="public/css/output.css" rel="stylesheet">
</head>
<body class="flex">

    <?php include '_sidebar.php'; ?>

    <main class="flex-grow p-6">
        <h1 class="text-3xl font-bold mb-4">Import/Export Products</h1>

        <p class="mb-4">This page will allow importing and exporting of product data in formats like CSV or Excel.</p>

        <div class="alert alert-info mt-4 shadow-lg">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Functionality under development.</span>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            <div>
                <h2 class="text-xl font-semibold">Import Products</h2>
                <p class="text-sm text-gray-600">Upload a CSV or Excel file to bulk import products.</p>
                <button class="btn btn-disabled mt-2">Choose File to Import (Disabled)</button>
            </div>
            <div>
                <h2 class="text-xl font-semibold">Export Products</h2>
                <p class="text-sm text-gray-600">Download all product data as a CSV or Excel file.</p>
                <button class="btn btn-disabled mt-2">Export All Products (Disabled)</button>
            </div>
        </div>

    </main>

</body>
</html>
