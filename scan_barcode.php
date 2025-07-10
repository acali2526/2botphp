<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Barcode</title>
    <link href="public/css/output.css" rel="stylesheet">
    <style>
        /* Ensure video element is visible if it has 0 height initially */
        #barcodeScannerFeed {
            min-height: 240px; /* Or any other suitable height */
            background-color: #f0f0f0; /* Placeholder background */
        }
    </style>
</head>
<body class="flex">

    <?php include '_sidebar.php'; ?>

    <main class="flex-grow p-6">
        <h1 class="text-3xl font-bold mb-4">Scan Product Barcode</h1>

        <div class="max-w-md mx-auto">
            <video id="barcodeScannerFeed" class="w-full border rounded shadow-lg" playsinline></video>

            <div id="scannerControls" class="my-4 flex gap-2 justify-center">
                <button id="startScanBtn" class="btn btn-primary">Start Scan</button>
                <button id="stopScanBtn" class="btn btn-neutral">Stop Scan</button>
            </div>

            <div id="scannerStatusMessage" class="my-2 min-h-[3.5rem] p-2 text-center">
                <!-- Scanner status messages will appear here -->
                Click "Start Scan" to use your camera.
            </div>

            <div id="scannedProductDetails" class="my-4 p-4 border rounded shadow bg-base-100 min-h-[6rem]">
                Scan a barcode to see product details.
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/zxing-library.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const videoElement = document.getElementById('barcodeScannerFeed');
            const startScanBtn = document.getElementById('startScanBtn');
            const stopScanBtn = document.getElementById('stopScanBtn');
            const scannerStatusMessage = document.getElementById('scannerStatusMessage');
            const scannedProductDetails = document.getElementById('scannedProductDetails'); // For later

            let codeReader = null;
            let isScanning = false;

            function displayScannerMessage(message, type = 'info') {
                const alertClasses = {
                    info: 'alert-info', success: 'alert-success', warning: 'alert-warning', error: 'alert-error'
                };
                const alertClass = alertClasses[type] || 'alert-info';
                scannerStatusMessage.innerHTML = `
                    <div class="alert ${alertClass} shadow-sm text-sm py-2">
                        <div>
                            <span>${message}</span>
                        </div>
                    </div>`;
            }

            if (typeof ZXing === 'undefined') {
                displayScannerMessage('Error: ZXing library not loaded. Check internet connection or CDN link.', 'error');
                startScanBtn.disabled = true;
                stopScanBtn.disabled = true;
                return;
            }

            codeReader = new ZXing.BrowserMultiFormatReader();

            startScanBtn.addEventListener('click', () => {
                if (isScanning) return;
                isScanning = true;
                scannedProductDetails.innerHTML = 'Scan a barcode to see product details.'; // Reset details
                displayScannerMessage('Initializing scanner and requesting camera access...', 'info');
                startScanBtn.classList.add('loading', 'btn-disabled');
                stopScanBtn.classList.remove('btn-disabled');

                codeReader.decodeFromVideoDevice(undefined, videoElement, (result, error) => {
                    // This callback is called for every frame
                    if (result) {
                        // Barcode detected - to be handled in the next step
                        console.log("Barcode detected:", result);
                        const detectedBarcode = result.getText();
                        displayScannerMessage(`Detected: ${detectedBarcode}. Looking up...`, 'success');

                        // Stop further scanning immediately to prevent multiple rapid detections of the same barcode
                        // and to allow user to see the result before scanner might pick up something else.
                        if (codeReader && isScanning) {
                            codeReader.reset();
                            isScanning = false;
                            // Keep startScanBtn disabled until lookup is complete or user explicitly restarts
                            // stopScanBtn can also be marked as disabled as scanning has stopped.
                            stopScanBtn.classList.add('btn-disabled');
                        }
                        lookupProductByBarcode(detectedBarcode);
                    }
                    if (error && isScanning) { // Only process error if still actively scanning
                        if (!(error instanceof ZXing.NotFoundException)) {
                            console.error("Scan Error:", error);
                            displayScannerMessage(`Scan Error: ${error.message}`, 'error');
                            // Consider stopping scan on persistent errors other than NotFound
                        } else {
                            // NotFoundException is normal when no barcode is in view, clear status or show subtle "searching"
                           // displayScannerMessage('Searching for barcode...', 'info'); // Can be too noisy
                        }
                    }
                }).then(() => {
                    // This .then() is for the initial promise of decodeFromVideoDevice (camera started)
                    displayScannerMessage('Scanner active. Point camera at a barcode.', 'info');
                     startScanBtn.classList.remove('loading', 'btn-disabled'); // Re-enable if stopped, or keep disabled if continuously scanning
                }).catch(err => {
                    console.error("Camera access or initial decode error:", err);
                    displayScannerMessage(`Error starting scanner: ${err.message}. Ensure camera access is allowed.`, 'error');
                    isScanning = false;
                    startScanBtn.classList.remove('loading', 'btn-disabled');
                    stopScanBtn.classList.add('btn-disabled');
                });
            });

            stopScanBtn.addEventListener('click', () => {
                if (codeReader) {
                    codeReader.reset(); // Stops scanning and releases camera
                    isScanning = false;
                    displayScannerMessage('Scanner stopped.', 'info');
                    videoElement.srcObject = null; // Clear video feed display
                    startScanBtn.classList.remove('btn-disabled', 'loading');
                    stopScanBtn.classList.add('btn-disabled');
                    scannedProductDetails.innerHTML = 'Scan a barcode to see product details.';
                }
            });

            // Initial state for buttons
            stopScanBtn.classList.add('btn-disabled');

            function displayProductDetailsMessage(message, type = 'info') {
                const alertClasses = {
                    info: 'alert-info', success: 'alert-success', warning: 'alert-warning', error: 'alert-error'
                };
                const alertClass = alertClasses[type] || 'alert-info';
                // For product details, we might want a more structured display than just an alert.
                // This function will primarily handle messages within the scannedProductDetails area.
                // Actual product data rendering will be by renderProductDetails.
                scannedProductDetails.innerHTML = `
                    <div class="alert ${alertClass} shadow-sm">
                        <div>
                            <span>${message}</span>
                        </div>
                    </div>`;
            }

            // Placeholder for actual product data rendering
            function renderProductDetails(product) {
                scannedProductDetails.innerHTML = ''; // Clear previous messages/details

                const card = document.createElement('div');
                card.className = 'card w-full bg-base-200 shadow-xl'; // DaisyUI card

                const cardBody = document.createElement('div');
                cardBody.className = 'card-body';

                const title = document.createElement('h2');
                title.className = 'card-title text-xl';
                title.textContent = product.name || 'N/A';
                cardBody.appendChild(title);

                const detailsList = document.createElement('ul');
                detailsList.className = 'list-none space-y-1 text-sm mt-2';

                const fieldsToShow = [
                    { label: 'SKU', value: product.sku },
                    { label: 'Item #', value: product.item_number },
                    { label: 'Category', value: product.category },
                    { label: 'Supplier', value: product.supplier_name },
                    { label: 'Current Qty', value: product.current_quantity !== null ? product.current_quantity : 'N/A' },
                    { label: 'Sell Price', value: typeof product.sell_price === 'number' ? '$' + product.sell_price.toFixed(2) : 'N/A' },
                    { label: 'Cost Price', value: typeof product.cost_price === 'number' ? '$' + product.cost_price.toFixed(2) : 'N/A' },
                    { label: 'Reorder Lvl', value: product.reorder_level !== null ? product.reorder_level : 'N/A' },
                    { label: 'Barcode', value: product.barcode }
                ];

                fieldsToShow.forEach(field => {
                    if (field.value || field.value === 0) { // Show if value exists or is 0
                        const li = document.createElement('li');
                        li.innerHTML = `<span class="font-semibold">${field.label}:</span> ${field.value}`;
                        detailsList.appendChild(li);
                    }
                });

                if (product.description) {
                    const descLi = document.createElement('li');
                    descLi.innerHTML = `<span class="font-semibold">Description:</span> <p class="text-xs whitespace-pre-wrap">${product.description}</p>`;
                    detailsList.appendChild(descLi);
                }

                cardBody.appendChild(detailsList);
                card.appendChild(cardBody);
                scannedProductDetails.appendChild(card);
            }

            async function lookupProductByBarcode(barcodeValue) {
                displayProductDetailsMessage('Looking up product by barcode...', 'info');
                startScanBtn.classList.add('btn-disabled'); // Keep start disabled during lookup

                try {
                    const response = await fetch('app/api/products/read.php');
                    if (!response.ok) {
                        throw new Error(`API error! Status: ${response.status}`);
                    }
                    const allProducts = await response.json();

                    if (!Array.isArray(allProducts)) {
                        console.error("API did not return an array of products:", allProducts);
                        throw new Error("Invalid data format from API.");
                    }

                    const foundProduct = allProducts.find(product => product.barcode === barcodeValue);

                    if (foundProduct) {
                        renderProductDetails(foundProduct);
                    } else {
                        displayProductDetailsMessage(`No product found with barcode: ${barcodeValue}`, 'warning');
                    }
                } catch (error) {
                    console.error('Error during product lookup:', error);
                    displayProductDetailsMessage(`Error looking up product: ${error.message}`, 'error');
                } finally {
                    // Re-enable start button to allow user to scan again if they wish
                    startScanBtn.classList.remove('btn-disabled', 'loading');
                    // stopScanBtn is already disabled because scan was stopped on detection
                }
            }
        });
    </script>

</body>
</html>
