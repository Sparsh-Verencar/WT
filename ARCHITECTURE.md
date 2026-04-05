# Book Spark - Application Architecture

## Overview
Book Spark is a web-based platform for buying and selling books. The application is built with HTML, CSS, and follows a multi-page architecture with a modular styling approach.

## Project Structure

```
WT/
├── index.html                 # Landing/Home page
├── ARCHITECTURE.md            # This file
├── images/                    # Image assets
│   └── logo.png              # Application logo
├── pages/                     # Application pages
│   ├── login.html            # User login page
│   ├── createAccount.html    # Account creation page
│   ├── explorepage.html      # Browse/explore books
│   ├── bookListing.html      # Detailed book listing view
│   ├── myaccountpage.html    # User account/profile page
│   └── myorder.html          # User orders/purchase history
└── styles/                    # Stylesheets (one per page + global)
    ├── style.css             # Global styles (used on index.html)
    ├── login.css             # Login page styles
    ├── createAccount.css     # Account creation styles
    ├── explorepage.css       # Explore page styles
    ├── bookListing.css       # Book listing styles
    ├── myaccount.css         # Account page styles
    └── myorder.css           # Orders page styles
```

## Technology Stack
- **HTML5**: Structure and markup
- **CSS3**: Styling and layout
- **Frontend Architecture**: Client-side navigation via page links

## Page Structure & Flow

### Entry Point
- **index.html** - Landing page with:
  - Navigation bar with logo and auth buttons
  - Hero section with call-to-action (Explore button)
  - Routes to login/signup and explore pages

### Authentication Pages
- **pages/login.html** - User login interface
- **pages/createAccount.html** - New user registration form

### Application Pages (Authenticated)
- **pages/explorepage.html** - Main browse/search view for discovering books
- **pages/bookListing.html** - Detailed view of a specific book with purchase option
- **pages/myaccountpage.html** - User profile and account settings
- **pages/myorder.html** - User's order history and purchase management

## Navigation Flow

```
index.html (Landing)
    ├── Login → login.html
    ├── Create Account → createAccount.html
    └── Explore → explorepage.html
            ├── Select Book → bookListing.html
            └── My Account (from nav) → myaccountpage.html
                └── Orders → myorder.html
```

## Styling Architecture

### Global Styles
- **styles/style.css** - Applied to index.html; contains base styling, navigation, and header section

### Page-Specific Styles
- Each page in `/pages` has a corresponding CSS file in `/styles`
- Naming convention: `[pageName].css` matches `[pageName].html`
- This modular approach allows:
  - Independent styling per page
  - Easy maintenance and updates
  - Clear separation of concerns

## Static Assets

### Images
- **images/** folder stores all image assets
- Currently includes: logo.png (used in navigation)

## Key Design Patterns

1. **Modular CSS**: Each page has dedicated stylesheets for easy maintenance
2. **Semantic Navigation**: All inter-page navigation uses standard `<a>` and `<button>` elements
3. **Responsive Layout**: CSS files handle responsive design per page
4. **Asset Organization**: Clear separation of structure (pages), styling (styles), and media (images)

## Development Considerations

- **Scalability**: Current structure works well for a small to medium-sized application
- **Future Enhancements**: Consider adding:
  - JavaScript for client-side interactions and form validation
  - Backend API integration for authentication and data persistence
  - Component-based architecture or template system (if application grows)
  - Package management (npm/yarn) for dependencies
  - Build tools (webpack, vite) for optimization

## Notes
- Authentication is handled via page navigation; backend implementation details not visible in frontend structure
- All links use relative paths for proper internal navigation
- The application is currently static; backend services would be needed for data persistence
