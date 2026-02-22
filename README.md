# Portfolio

A minimal, modern portfolio website built with PHP and MySQL. Features a clean editorial design, dark/light theme support, and a complete admin panel for content management.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

## Features

### Frontend

- **Minimal Editorial Design** — Clean typography with Instrument Serif & Space Grotesk fonts
- **Dark/Light Theme** — Toggle with localStorage persistence, flash-free on load
- **Responsive Layout** — Mobile-first design with optimized touch targets (44px+)
- **Horizontal Project Slider** — Swipeable cards with navigation indicators
- **Smooth Animations** — Reveal effects, custom cursor (desktop), subtle transitions
- **Contact Form** — AJAX submission with validation
- **CV Download** — Printable resume page

### Admin Panel

- **Dashboard** — Quick stats and navigation
- **Settings Management** — Site title, hero content, about text, social links
- **Projects CRUD** — Add, edit, delete with image upload and drag-drop reordering
- **Skills Management** — Add/remove technology tags
- **Messages Inbox** — View contact form submissions with read/unread status
- **Image Upload** — Drag & drop, clipboard paste, file picker support

## Tech Stack

| Layer    | Technologies                                   |
| -------- | ---------------------------------------------- |
| Backend  | PHP 7.4+, PDO (MySQL)                          |
| Database | MySQL 5.7+ / MariaDB 10.2+                     |
| Frontend | Vanilla JS, CSS Custom Properties              |
| Icons    | Font Awesome 6.5                               |
| Fonts    | Google Fonts (Instrument Serif, Space Grotesk) |

## Installation

### Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite (XAMPP, LAMP, etc.)

### Quick Setup

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/portfolio.git
   cd portfolio
   ```

2. **Configure database connection**

   Edit `includes/config.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'portfolio_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. **Run setup**

   Navigate to `http://localhost/portfolio/setup.php` in your browser.
   This creates the database, tables, and default data.

4. **Login to admin**

   Go to `http://localhost/portfolio/admin/login.php`
   - Default password: `admin123`
   - **Change this immediately after first login!**

5. **Delete setup file**
   ```bash
   rm setup.php
   ```

### Manual Database Setup

Alternatively, import the schema directly:

```bash
mysql -u root -p < schema.sql
```

## Project Structure

```
portfolio/
├── admin/                 # Admin panel
│   ├── assets/           # Admin-specific CSS/JS
│   ├── index.php         # Dashboard
│   ├── login.php         # Authentication
│   ├── settings.php      # Site configuration
│   ├── projects.php      # Project management
│   ├── skills.php        # Skills/tech tags
│   ├── messages.php      # Contact submissions
│   └── upload.php        # Image upload handler
├── assets/
│   ├── css/
│   │   └── style.css     # Main stylesheet
│   └── js/
│       └── main.js       # Frontend interactions
├── includes/
│   └── config.php        # Database & helpers
├── uploads/              # User uploads
├── index.php             # Main portfolio page
├── cv.html               # Printable CV page
├── handle_message.php    # Contact form handler
├── setup.php             # Initial setup script
├── schema.sql            # Database schema
└── README.md
```

## Database Schema

### Tables

| Table      | Purpose                                               |
| ---------- | ----------------------------------------------------- |
| `settings` | Single-row site configuration (title, about, socials) |
| `skills`   | Technology/skill tags                                 |
| `projects` | Portfolio projects with images and links              |
| `messages` | Contact form submissions                              |

See [schema.sql](schema.sql) for complete table definitions.

## Configuration

### Environment Settings

In `includes/config.php`:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// URLs
define('SITE_URL', 'http://localhost/portfolio');
define('ADMIN_URL', SITE_URL . '/admin');
```

### Upload Limits

The `.htaccess` file in `/admin/` configures PHP upload limits:

```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
```

## Customization

### Theme Colors

Edit CSS custom properties in `assets/css/style.css`:

```css
:root {
  --bg: #0a0a0a;
  --bg-light: #141414;
  --text: #ffffff;
  --text-dim: #888888;
  --border: #222222;
  --accent: #ffffff;
}

[data-theme="light"] {
  --bg: #fafafa;
  --bg-light: #ffffff;
  --text: #0a0a0a;
  --text-dim: #666666;
  --border: #e5e5e5;
  --accent: #0a0a0a;
}
```

### Fonts

Fonts are loaded via Google Fonts in `style.css`:

```css
@import url("https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Space+Grotesk:wght@300..700&display=swap");
```

### Social Links

Add new social platforms by:

1. Adding a field in admin settings form (`admin/settings.php`)
2. Adding the column to settings table
3. Displaying in `index.php` contact/footer sections

## Security Notes

- **Change default password** immediately after setup
- **Delete `setup.php`** after installation
- **Set proper permissions** on `uploads/` directory (755)
- **Disable error display** in production:
  ```php
  error_reporting(0);
  ini_set('display_errors', 0);
  ```
- All user input is escaped with `htmlspecialchars()` via the `e()` helper
- Prepared statements used for all database queries

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile Safari / Chrome (iOS/Android)

## License

MIT License — feel free to use and modify.

## Author

Built with ❤️ by [Your Name](https://github.com/yourusername)
