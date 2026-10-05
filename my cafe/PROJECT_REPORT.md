# Project Report

## D Cafe: A Dynamic Café Management and Online Ordering System

**Submitted by:** [Your Name]  
**Student ID:** [Your Student ID]  
**Department:** [Your Department]  
**Course:** [Course Title / Code]  
**Submitted to:** [Supervisor's Name]  
**University:** [University Name]  
**Submission Date:** 23 July 2026  

---

## Declaration

I declare that this project report, titled *D Cafe: A Dynamic Café Management and Online Ordering System*, is my original work. Any ideas or materials taken from other sources have been acknowledged appropriately.

## Acknowledgement

I sincerely thank my supervisor, department, and classmates for their guidance and encouragement during the development of this project. Their support helped transform the idea into a practical web-based café management solution.

## Abstract

D Cafe is a responsive web application designed to support the daily digital operations of a modern café. The system combines a customer-facing website with a protected administration panel. Customers can explore products, add items to a session-based cart, place orders, request reservations, contact the café, read blog posts, and submit career applications. Administrators can securely manage menu items, categories, offers, reservations, orders, messages, testimonials, gallery items, blog posts, careers, applications, and site settings.

The application is built with PHP, SQLite, HTML, CSS, JavaScript, and Font Awesome. It follows a lightweight architecture that is simple to run locally while remaining portable to MySQL for larger deployments. Core measures such as password hashing, prepared statements, CSRF protection, output escaping, session handling, and foreign-key constraints are included to improve security and data integrity. The result is an efficient, user-friendly, and scalable foundation for café digitalization.

**Keywords:** café management, online ordering, PHP, SQLite, reservation system, admin dashboard, responsive web application.

---

## 1. Introduction

Small cafés often manage menus, reservations, orders, customer messages, and promotional content through separate manual processes. This can lead to slow service, inconsistent information, and limited visibility of business activity. D Cafe was developed to centralize these activities in one web-based platform.

The project provides an attractive public interface for customers and a dedicated administrative workspace for staff. It supports a contemporary café experience while keeping the technical design accessible for local deployment and future expansion.

### 1.1 Problem Statement

Traditional café operations commonly rely on phone calls, handwritten reservation records, verbal order updates, and manually maintained menus. These methods are difficult to track, prone to errors, and inconvenient for customers. A single digital system is needed to improve customer access and operational control.

### 1.2 Objectives

The main objective is to design and develop a responsive café management system that improves customer service and administrative efficiency.

Specific objectives are:

- Provide an informative and visually engaging café website.
- Enable customers to browse menu products and place online orders.
- Provide a reservation request facility.
- Store customer communications and career applications centrally.
- Give administrators secure tools to manage operational and website content.
- Support both SQLite for local use and MySQL for larger deployments.
- Support Bangla and English interface switching for accessibility.

### 1.3 Scope

The current system covers online menu browsing, cart-based ordering, reservations, contact/help forms, newsletter signup, blog content, careers, gallery content, and an administrator dashboard. Payment gateway integration, live delivery tracking, and multi-role staff permissions are outside the current implementation and are recommended for future work.

---

## 2. Requirement Analysis

### 2.1 Functional Requirements

| User | Main functions |
|---|---|
| Customer | Browse products, filter menu items, add items to cart, submit orders, request a reservation, send a message, view offers, browse gallery/blog, apply for jobs, switch language |
| Administrator | Log in, view dashboard statistics, manage products/categories/offers, process reservations/orders/messages, manage testimonials/gallery/blog/careers/applications/settings |
| System | Validate requests, persist records, calculate order totals, maintain sessions, protect administration routes, and present responsive pages |

### 2.2 Non-functional Requirements

The system is designed to be responsive, easy to use, maintainable, and portable. It uses a normalized relational database structure, a clean separation between public and administration areas, and reusable helper functions. The visual design is optimized for desktop and mobile screens.

---

## 3. Technology Stack and Architecture

### 3.1 Technology Stack

| Layer | Technology | Purpose |
|---|---|---|
| Front end | HTML5, CSS3, JavaScript | Layout, responsive styling, user interaction |
| Back end | PHP | Routing logic, forms, sessions, validation, database access |
| Database | SQLite / MySQL | Persistent storage; SQLite is default, MySQL is supported through environment variables |
| Icons | Font Awesome | User-interface icons |
| Development environment | XAMPP/WAMP/Laragon or PHP built-in server | Local execution |

### 3.2 Architectural Overview

The application uses a simple layered design:

```text
Customer Browser / Admin Browser
            |
            v
PHP Pages (index.php and admin pages)
            |
            v
Shared Application Layer
(bootstrap, site helpers, admin configuration, i18n)
            |
            v
PDO Data Access Layer
            |
            v
SQLite Database / Optional MySQL Database
```

The public homepage is provided through `index.php`. Shared initialization, configuration, security helpers, database setup, and reusable utility functions are located in `includes/`. The administration area is separated under `admin/`. Static styling, scripts, and visual assets are stored under `assets/`.

---

## 4. System Design

### 4.1 Major Modules

| Module | Description |
|---|---|
| Public website | Hero, about, services, featured products, full menu, offers, gallery, testimonials, FAQ, contact, help, blog, careers, and cart |
| Product and category management | Create, update, activate/deactivate, and organize menu items |
| Cart and orders | Session cart, quantity handling, checkout data collection, order and order-item storage |
| Reservation management | Customer reservation requests with date, time, guests, special requests, and status tracking |
| Communication management | Contact/help messages, newsletter handling, and career applications |
| Content management | Offers, testimonials, gallery items, blog posts, career posts, and site settings |
| Administration | Login-protected dashboard, statistics, recent activity, CRUD screens, and status updates |
| Language support | Bangla/English selection through the public navigation |

### 4.2 Database Design

The database includes the following principal tables:

| Table | Purpose |
|---|---|
| `users` | Administrator identity and hashed password |
| `settings` | Configurable café name, contact data, colours, hours, and related settings |
| `categories`, `products` | Menu catalogue and stock/featured status |
| `offers` | Promotional offers and discounts |
| `reservations` | Customer table bookings and processing status |
| `orders`, `order_items` | Customer orders and their line items |
| `contacts` | Contact and help-centre messages |
| `testimonials`, `gallery_items`, `blog_posts` | Public-facing content |
| `careers`, `applications` | Job listings and applicant details |

Important relationships include one category to many products, one order to many order items, and one career listing to many applications. Foreign keys preserve referential integrity; for example, deleting an order also deletes its related order items.

### 4.3 Input–Process–Output Model

| Input | Process | Output |
|---|---|---|
| Product/order data | Validate, store order and order items, update admin view | Order confirmation and manageable order record |
| Reservation details | Validate and save with pending status | Reservation request visible to administrator |
| Admin content updates | CSRF verification, validation, database operation | Updated public website content |
| Contact/application forms | Store request and assign status | Centralized communication or recruitment record |

---

## 5. Implementation Highlights

The implementation includes the following notable features:

- **Automatic database bootstrap:** if no database schema exists, the application initializes the database and seeds essential sample content.
- **Portable database configuration:** SQLite is used by default; MySQL can be enabled through `DB_DRIVER`, host, port, database, user, and password environment variables.
- **Session-based cart:** customers can build an order without first creating an account.
- **Admin dashboard:** displays counts for products, reservations, messages, orders, and offers, plus recent activity.
- **Reusable data layer:** PDO prepared statements are exposed through shared helper functions, reducing repeated database code.
- **Media upload support:** administrators can upload visual assets for manageable content sections.
- **Responsive visual interface:** CSS and JavaScript provide a polished café interface across device sizes.

### 5.1 Security Considerations

The project implements several practical security controls:

- Passwords are stored using PHP's `password_hash()` function.
- Database queries use PDO prepared statements to reduce SQL injection risk.
- CSRF tokens protect state-changing form submissions.
- Output is escaped with `htmlspecialchars()` through the shared `h()` helper.
- Admin routes use a session-based `require_admin()` access check.
- SQLite foreign keys are enabled to enforce data relationships.

For production use, further improvements should include rate limiting, stricter upload MIME validation, HTTPS, environment-managed credentials, audit logs, and role-based authorization.

---

## 6. Testing and Validation

The project can be tested using the following acceptance checklist:

| Test case | Expected result |
|---|---|
| Open public homepage | Responsive homepage loads with café sections and menu data |
| Switch language | Public interface changes between Bangla and English mode |
| Add product to cart | Cart retains selected products during the active session |
| Submit checkout | Order and its line items are stored in the database |
| Submit reservation | Reservation is stored with a pending status |
| Submit contact form | Message appears in the administration panel |
| Admin login | Valid administrator credentials open the dashboard; invalid credentials are rejected |
| Manage content | Create/edit/delete actions update the relevant public/admin records |
| Database portability | Application uses SQLite by default and can connect to MySQL when configured |

PHP syntax and database bootstrap should be verified before demonstration. User interface testing should also be performed in current desktop and mobile browsers.

---

## 7. Results and Discussion

D Cafe successfully demonstrates how a café can consolidate its customer experience and operational tasks into a single web system. The public side supports discovery, engagement, ordering, and reservations, while the administrative side gives staff one place to maintain menu information, promotions, customer requests, and content.

The choice of PHP with SQLite makes the project efficient for academic demonstration and local deployment because it requires minimal infrastructure. At the same time, the optional MySQL configuration improves the project's relevance for a larger hosting environment. The bilingual option is particularly valuable for a Bangladeshi context, where customers may prefer either Bangla or English.

---

## 8. Limitations and Future Enhancements

Current limitations and recommended enhancements include:

- Integrate secure online payments such as SSLCommerz, bKash, Nagad, or card payment.
- Add customer accounts, order history, password reset, and email/SMS confirmations.
- Implement real-time order and reservation notifications.
- Add stock alerts, low-inventory rules, sales reports, and downloadable analytics.
- Introduce staff roles such as manager, cashier, barista, and content editor.
- Add delivery-zone calculation and live order tracking.
- Strengthen uploads with MIME/type checks, file-size limits, and randomized filenames.
- Add automated tests and deployment configuration.

---

## 9. Conclusion

D Cafe is a complete web-based café management and online ordering project that addresses key operational needs through a modern, responsive interface. It allows customers to explore and engage with the café online while providing administrators with centralized management tools. Its use of PHP, SQLite/MySQL, secure coding practices, and modular design makes it an appropriate and practical university software project. With payment integration, notifications, analytics, and more advanced access control, the system can be extended into a production-ready café platform.

---

## References

1. PHP Documentation. *PHP Manual*. https://www.php.net/docs.php
2. SQLite. *SQLite Documentation*. https://www.sqlite.org/docs.html
3. MySQL. *MySQL Reference Manual*. https://dev.mysql.com/doc/
4. Font Awesome. *Icons Documentation*. https://fontawesome.com/docs
5. MDN Web Docs. *Web Development Documentation*. https://developer.mozilla.org/

---

## Appendix A: Project Structure

```text
my cafe/
├── index.php                 # Public website and form actions
├── admin/                    # Login, dashboard, content management, invoice
├── includes/                 # Bootstrap, site helpers, i18n, admin configuration
├── database/schema.sql       # Canonical relational database schema
├── storage/mycafe.sqlite     # Default SQLite database
└── assets/                   # CSS, JavaScript, images, and uploads
```

## Appendix B: Demonstration Credentials

For local academic demonstration only:

- **Email:** `admin@dcafe.local`
- **Password:** `admin123`

Change the default password before any real deployment.
