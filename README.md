# 2botphp

# PHP & SQLite Inventory App Template

A lightweight and simple inventory management web application built with a modern, file-based stack. This project serves as a starter template for anyone looking to build a simple web app using PHP and SQLite without the need for a complex database server like MySQL. The frontend is styled with Tailwind CSS and the beautiful DaisyUI component library.


*(A sample screenshot of the application interface)*

## Features

-   **Full CRUD Functionality:** Create, Read, Update, and Delete inventory items.
-   **Single-Page Interface:** All operations are handled on a single page for simplicity.
-   **File-Based Database:** Uses SQLite, so no database server setup is required. The database is a single file in the project.
-   **Modern UI:** Clean and responsive user interface thanks to Tailwind CSS and DaisyUI.
-   **Secure by Design:** Uses a `public` directory as the web server's document root to prevent direct access to source code and sensitive files.
-   **Easy to Deploy:** Runs on any standard PHP server environment like XAMPP, MAMP, or WAMP.

## Tech Stack

-   **Backend:** PHP
-   **Database:** SQLite 3
-   **Frontend Styling:** Tailwind CSS 3
-   **UI Component Library:** DaisyUI
-   **Build Tool:** Node.js/npm (for compiling CSS)
-   **Local Server:** XAMPP / MAMP / WAMP

---

## Getting Started

Follow these instructions to get a copy of the project up and running on your local machine.

### Prerequisites

Make sure you have the following software installed:

-   **A local PHP server environment:**
    -   [XAMPP](https://www.apachefriends.org/index.html) (Windows, macOS, Linux)
    -   [MAMP](https://www.mamp.info/en/mamp/) (macOS, Windows)
-   **Node.js and npm:**
    -   [Download Node.js](https://nodejs.org/) (npm is included with Node.js)

### Installation & Setup

1.  **Get the Code**
    Clone the repository or download the ZIP file and place it in your server's web directory (e.g., `C:/xampp/htdocs/`).

    ```bash
    # Clone the repository (if you have Git)
    git clone https://github.com/your-username/inventory-app.git

    # Or simply place the project folder inside htdocs
    # The final path should be: /xampp/htdocs/inventory-app/
    ```

2.  **Install Frontend Dependencies**
    Open your terminal or command prompt, navigate to the project directory, and install the required npm packages.

    ```bash
    cd /path/to/htdocs/inventory-app
    npm install
    ```

3.  **Initialize the Database**
    Start the **Apache** service in your XAMPP/MAMP control panel. Then, open your web browser and navigate to the database initialization script. This will create the `inventory.db` file and the `items` table.

    **Go to:** `http://localhost/inventory-app/init_db.php`

    You should see a success message.
    > **Security Note:** It is highly recommended to delete the `init_db.php` file after the database has been successfully created.

4.  **Build the CSS**
    Run the build script from your terminal to compile the Tailwind CSS into a single stylesheet.

    ```bash
    npm run build
    ```

    For development, you can use the `watch` command to automatically re-compile the CSS whenever you make changes to your PHP or `input.css` files.
    ```bash
    npm run watch
    ```

5.  **Run the Application**
    You're all set! Open your browser and navigate to the application's public directory.

    **Main App URL:** `http://localhost/inventory-app/public/`

---

## How to Use

-   **Add an Item:** Fill out the "Add New Item" form and click the "Add Item" button.
-   **Edit an Item:** Click the "Edit" button next to any item in the table. The form will be pre-filled with its data. Make your changes and click "Update Item".
-   **Delete an Item:** Click the "Delete" button. A confirmation prompt will appear before the item is removed.

## Folder Structure

Here is an overview of the project's directory structure: