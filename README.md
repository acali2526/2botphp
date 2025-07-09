# Small Business Inventory Management App

## Description

A simple web application designed to help small businesses manage their physical product inventory. This application aims to replace manual tracking processes, reduce stock discrepancies, and provide a clear overview of inventory levels.

## Features

*   **Comprehensive Product Management:**
    *   CRUD operations (Create, Read, Update, Delete) for products.
    *   Track SKU, barcode, name, category, supplier, item number, description, cost/sell prices, reorder levels, and current quantity.
*   **Real-time Stock Levels:** (Future implementation will make this more dynamic) Current stock quantities are stored and can be updated.
*   **Basic Page Navigation:**
    *   Home page (`index.php`)
    *   Products page (`products.php`) to view and manage products.
    *   Add New Product page (`add_product.php`) for easy data entry.
    *   Reusable side navigation bar for consistent user experience.
*   **Database:** Uses SQLite for lightweight and file-based data storage.
*   **`.xlsx` Import/Export:** (Planned) Functionality to import existing product lists and export inventory data.
*   **Barcode Scanning:** (Planned) Integration for barcode scanning to quickly find or add products.

## Technology Stack

*   **Backend:** PHP
*   **Database:** SQLite
*   **Frontend:**
    *   Tailwind CSS (Utility-first CSS framework)
    *   DaisyUI (Component library for Tailwind CSS)

## Setup/Installation

### Prerequisites

1.  **PHP:** Ensure PHP is installed on your system (version 7.4 or higher recommended).
2.  **`pdo_sqlite` Extension:** The PHP Data Objects (PDO) SQLite extension must be enabled.
    *   You can typically enable this in your `php.ini` file by uncommenting or adding `extension=pdo_sqlite`.
    *   Verify by running `php -m | grep sqlite` in your terminal; you should see `pdo_sqlite`.

### Database Setup

1.  Navigate to the project's root directory in your terminal.
2.  Run the database setup script:
    ```bash
    php setup_database.php
    ```
    This will create a `database.sqlite` file in the root directory and set up the necessary `products` table. You should see success messages.
3.  (Optional) You can verify the table structure by running:
    ```bash
    php verify_database.php
    ```

### Running the Application

You need a web server to serve the PHP files.

1.  **Using PHP's Built-in Web Server (for development/testing):**
    *   Open your terminal in the project's root directory.
    *   Run the command:
        ```bash
        php -S localhost:8000
        ```
        (You can replace `8000` with another port if it's in use).
    *   Open your web browser and go to `http://localhost:8000`.

2.  **Using a Local Web Server Stack (e.g., XAMPP, MAMP, WAMP):**
    *   Place the entire project folder into your web server's document root (e.g., `htdocs` for XAMPP, `www` for WAMP).
    *   Start your Apache server (or equivalent) through the XAMPP/MAMP/WAMP control panel.
    *   Open your web browser and navigate to the project directory (e.g., `http://localhost/your_project_folder_name/`).


## Usage

Once the application is running:

*   The **Home** page (`index.php`) provides a general welcome.
*   Navigate to the **Products** page (`products.php`) using the sidebar to view a list of all products (once data is added).
*   Click on **Add New Product** in the sidebar to go to the `add_product.php` page, where you can input details for new inventory items.

Further development will add more interactive features to these pages.
---

*This README was generated and last updated by Jules, your AI Software Engineer.*