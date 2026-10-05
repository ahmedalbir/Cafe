# D Cafe Premium PHP Web App

This is a dynamic cafe web app with a public site and an admin panel.

## What is included

- Premium public homepage with hero, about, featured products, menu, services, gallery, reservation, offers, testimonials, owner message, FAQ, contact, help center, blog, careers, and cart
- Admin panel with login, dashboard, and content management
- SQLite database that is created automatically on first run
- Session cart and order checkout
- Reservation, contact, help, newsletter, and career application forms
- Font Awesome icons and responsive UI

## Default admin login

- Email: `admin@dcafe.local`
- Password: `admin123`

## Database

This app uses one active database connection per installation. You use either SQLite for quick local testing or MySQL for a hosting-style setup, but not both at the same time.

The database file is created automatically at:

`storage/mycafe.sqlite`

The schema is stored in:

`database/schema.sql`

### MySQL mode

If you want MySQL instead of SQLite, set these environment variables before running the app:

- `DB_DRIVER=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_NAME=dcafe`
- `DB_USER=root`
- `DB_PASS=your_password`

The app uses the same canonical schema file for both SQLite and MySQL:

`database/schema.sql`

## How to run

### Option 1: XAMPP, WAMP, Laragon

1. Place the project inside your local web root.
2. Start PHP and the web server.
3. Open the project in your browser.

### Option 2: PHP built-in server

From the project root, run the app using any PHP server and open the local URL it gives you.

### Option 3: XAMPP

You can also run the project with XAMPP. The local PHP binary at `C:\xampp\php\php.exe` was used to verify syntax and database bootstrap in this workspace.

## Admin panel

Go to:

`/admin/login.php`

After login, you can manage:

- Products
- Categories
- Offers
- Reservations
- Orders
- Testimonials
- Gallery
- Blog posts
- Careers
- Job applications
- Messages
- Site settings

## Language mode

The public site supports Bangla/English switching through the top navbar.

## Replacing images

All placeholder images are in:

`assets/images/`

You can replace them with your own cafe photos. Keep the same filenames if you want to keep the current design without editing code.

## Notes

- The app uses free and open tools only.
- You can later switch SQLite to MySQL if you want a larger production deployment.
- Public and admin areas are separate.
