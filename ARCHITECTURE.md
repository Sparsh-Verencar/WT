# Book Spark - Application Architecture

## Overview
Book Spark is a web-based platform for buying and selling books. The application is built with a LAMP/XAMPP stack architecture using PHP for server-side processing, MySQL for data persistence, and HTML/CSS/JavaScript with jQuery AJAX for dynamic client-side interactions.

## Project Structure

```text
WT/
├── index.php                  # Landing/Home page
├── ARCHITECTURE.md            # This file
├── images/                    # Image assets (e.g., logo.png, book covers)
├── js/                        # JavaScript files for client-side logic
│   ├── index.js               # JS for landing page
│   ├── login.js               # JS for login page
│   ├── createAccount.js       # JS for account creation
│   ├── explorepage.js         # JS for explore page
│   ├── bookListing.js         # JS for book listing interactions
│   ├── myaccount.js           # JS for user account
│   ├── myorder.js             # JS for orders view
│   └── ajax/                  # AJAX scripts for async backend communication
│       ├── account-ajax.js    # AJAX for account actions
│       ├── bookListing-ajax.js# AJAX for book listing interactions
│       ├── createAccount-ajax.js # AJAX for registration
│       └── login-ajax.js      # AJAX for login
├── pages/                     # Application views (PHP)
│   ├── login.php              # User login page
│   ├── createAccount.php      # Account creation page
│   ├── explorepage.php        # Browse/explore books
│   ├── bookListing.php        # Detailed book listing view
│   ├── myaccountpage.php      # User account/profile page
│   ├── myorder.php            # User orders/purchase history
│   └── admin.php              # Admin dashboard (analytics & earnings)
├── php/                       # Backend PHP scripts
│   ├── db.php                 # Database connection and schema setup
│   ├── deleteAccount.php      # Account deletion endpoint
│   └── logout.php             # Logout session destruction
└── styles/                    # Stylesheets (modular CSS)
    ├── style.css              # Global styles (used on index.php)
    ├── login.css              # Login page styles
    ├── createAccount.css      # Account creation styles
    ├── explorepage.css        # Explore page styles
    ├── bookListing.css        # Book listing styles
    ├── myaccount.css          # Account page styles
    ├── myorder.css            # Orders page styles
    ├── modals.css             # Styles for UI modals
    └── admin.css              # Admin dashboard styles
```

## Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla & jQuery)
- **Backend**: PHP
- **Database**: MySQL (configured in `php/db.php`)
- **Communication**: Asynchronous AJAX requests (jQuery AJAX)

## Page Structure & Flow

### Entry Point
- **index.php** - Landing page with:
  - Navigation bar with session-aware auth buttons (Login/Create Account vs My Account/Logout)
  - Hero section with call-to-action (Explore button routes dynamically based on session)

### Authentication Pages
- **pages/login.php** - User login interface
- **pages/createAccount.php** - New user registration form
- Handled asynchronously via AJAX (`js/ajax/login-ajax.js`, `js/ajax/createAccount-ajax.js`) for seamless user experience.

### Application Pages (Authenticated)
- **pages/explorepage.php** - Main browse/search view for discovering books
- **pages/bookListing.php** - Detailed view of a specific book, allowing purchasing
- **pages/myaccountpage.php** - User profile, account settings, and account deletion
- **pages/myorder.php** - User's order history and purchase management

### Admin Dashboard (Restricted Access)
- **pages/admin.php** - Exclusive admin analytics dashboard (access restricted to admin user, default: user_id=1)
  - **Overview**: Key metrics including total platform earnings, books sold, active sellers, and total listings
  - **Books & Sales**: Detailed transaction history table showing all books with seller/buyer information
  - **Seller Performance**: Individual seller cards with revenue, conversion rates, and activity metrics
  - Styled with `styles/admin.css` to match the vibrant Book Spark theme

## Navigation Flow

```text
index.php (Landing)
    ├── Login → login.php (AJAX auth)
    ├── Create Account → createAccount.php (AJAX auth)
    └── Explore → explorepage.php (Requires session)
            ├── Select Book → bookListing.php (AJAX interactions)
            └── My Account (from nav) → myaccountpage.php
                ├── Orders → myorder.php
                └── Delete Account → php/deleteAccount.php
    └── Logout → php/logout.php
```

## Architecture Patterns

### Client-Server Communication
- The application uses a hybrid approach:
  - **Traditional Navigation**: Page transitions are standard server-rendered PHP files.
  - **Asynchronous Operations**: Data mutations (login, signup, book interactions, account settings) use AJAX calls to backend PHP endpoints. This prevents full page reloads and improves UX.

### Database Schema
- The database (`bookspark`) relies on three primary tables:
  1. `users`: Stores user credentials and profile information.
  2. `books`: Stores book details, linked to a seller (`seller_id`), and tracking status (`available`, `sold`).
  3. `orders`: Tracks transactions linking `buyer_id` (users) and `book_id` (books).
- Tables are initialized and migrated automatically through `php/db.php`.

### Modular Styling & Scripting
- **CSS**: Each view has a corresponding CSS file in `/styles`, allowing clear separation of concerns. `modals.css` handles reusable overlay components.
- **JavaScript**: Each view relies on its own JS logic in `/js`. Complex server interactions are abstracted into the `/js/ajax` directory to separate UI updates from data fetching.

## Development Considerations
- **Session Management**: Native PHP `$_SESSION` controls access to protected routes (like explore and account management).
- **Security**: Requires a local server like XAMPP/MAMP to execute PHP and serve MySQL.
- **Future Enhancements**: 
  - Implementation of prepared statements (PDO/MySQLi) if not already fully utilized.
  - Form validation improvements on both client and server sides.
  - Image upload handling for user profiles or custom book covers.
