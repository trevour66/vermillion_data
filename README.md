# Vermilion Data Application

This application is a web-based platform designed to manage data for Vermilion, built using Laravel, Tailwind CSS, and Vue.js. The platform was previously integrated with Infusionsoft but has been updated to fully rely on it own database for data management.

## Features

-   **Google Sheets Integration**: Google sheet is used as a backup database
-   **User-Friendly Interface**: The front-end is built with Tailwind CSS and Vue.js to offer a responsive and intuitive user experience.

## Installation

### Prerequisites

-   PHP 8.x or higher
-   Composer
-   Node.js and npm
-   Laravel 9.x or higher

### Setup

1. Clone the repository:

    ```bash
    git clone https://github.com/trevour66/vermillion_data.git
    cd vermillion_data
    ```

2. Install dependencies:

    ```bash
    composer install
    npm install
    ```

3. Set up the environment variables:

    ```bash
    cp .env.example .env
    ```

    Update the `.env` file with the necessary configuration, such as database connection and Google Sheets API keys.

4. Generate an application key:

    ```bash
    php artisan key:generate
    ```

5. Migrate the database (if applicable):

    ```bash
    php artisan migrate
    ```

6. Serve the application:

    ```bash
    php artisan serve
    ```

7. Build the front-end assets:
    ```bash
    npm run dev
    ```

## License

This project is open-source and available under the MIT License.
