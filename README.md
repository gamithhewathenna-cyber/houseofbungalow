# House of Bungalow — Phase 01 (Home Page + Admin)

A PHP + MySQL website built for cPanel hosting. It reproduces the supplied
Home Page design and includes a full admin panel for managing every piece of
Home Page content, plus newsletter subscriber capture.

---

## 1. What's included

```
hob/
├── index.php              → Home page (front end)
├── subscribe.php          → Newsletter form handler
├── database.sql           → Database schema + all seed content (IMPORT THIS)
├── .htaccess              → Apache config & protection
├── config/
│   └── config.php         → ★ Edit your database details here
├── includes/
│   ├── db.php             → PDO connection
│   ├── functions.php      → Content helpers
│   ├── header.php         → Site header + hero
│   └── footer.php         → Site footer + newsletter
├── admin/                 → Admin panel (login, editors, subscribers)
├── assets/
│   ├── css/style.css      → Front-end styles
│   └── img/               → Logos, hero, cards, storefront illustration
└── uploads/               → Images uploaded via the admin panel
```

---

## 2. Install on cPanel (step by step)

### Step 1 — Upload the files
1. Log in to **cPanel → File Manager**.
2. Go to the folder you want the site to live in:
   - Whole domain → `public_html`
   - Sub-folder → e.g. `public_html/hob`
3. Upload the contents of the `hob` folder (not the folder itself) and extract.

### Step 2 — Create the database
1. cPanel → **MySQL® Databases**.
2. **Create New Database**, e.g. `hob` → full name becomes `cpaneluser_hob`.
3. **Add New User**, e.g. `admin` with a strong password → `cpaneluser_admin`.
4. **Add User To Database** and grant **ALL PRIVILEGES**.
5. Note the three values: database name, user name, password.

### Step 3 — Import the schema
1. cPanel → **phpMyAdmin**.
2. Select your new database on the left.
3. **Import** tab → choose `database.sql` → **Go**.
   *(Character set is set to utf8mb4 automatically — accented text like
   “Café” imports correctly.)*
4. You should see the tables `admins`, `settings`, `blocks`, `subscribers`.

### Step 4 — Configure the connection
Open `config/config.php` and set:

```php
define('DB_HOST', 'localhost');            // usually 'localhost' on cPanel
define('DB_NAME', 'cpaneluser_hob');       // from Step 2
define('DB_USER', 'cpaneluser_admin');     // from Step 2
define('DB_PASS', 'your_password');        // from Step 2
```

If the site is in a **sub-folder**, also set:

```php
define('BASE_URL', '/hob');   // no trailing slash; leave '' for domain root
```

### Step 5 — Done
- Front end: `https://yourdomain.com/`  (or `/hob/`)
- Admin panel: `https://yourdomain.com/admin/`

---

## 3. Admin panel

**First login**

```
Username: admin
Password: admin123
```

Go to **Account** immediately and change both. (Password ≥ 6 characters.)

**What you can manage**
- **Header & Nav** — the two top links (VIP / RESERVE) and their URLs
- **Hero** — the full-width hero background image
- **Intro** — heading, subheading, address, all body paragraphs, both buttons,
  and the storefront illustration
- **Three Spaces** — the CAFÉ / RESTAURANT / BELOW cards (label, image, link, order)
- **The Door / Every Mood / What's On / Private Events** — headings, paragraphs, buttons
- **Footer & Nav** — footer text, social links, newsletter copy, and the footer
  navigation links (add / edit / delete / reorder)
- **Brand Logos** — footer partner marks (upload a logo image or show text; reorder)
- **Subscribers** — view everyone who joined the newsletter; export to CSV; delete

All changes appear on the live site immediately.

---

## 4. Requirements
- PHP 7.4+ (tested on PHP 8.3) with **PDO MySQL** enabled (standard on cPanel)
- MySQL / MariaDB
- Apache with `mod_rewrite` (standard on cPanel)

---

## 5. Security notes
- The `config/` and `includes/` folders are blocked from direct web access.
- The `uploads/` folder cannot execute PHP (protects against malicious uploads).
- Admin uses hashed passwords (bcrypt) and CSRF protection on all forms.
- After going live: change the admin password, and set `DEBUG` to `false`
  in `config.php` (it already ships as `false`).

---

## 6. Replacing images
You can replace any image from the admin panel (Hero, Intro illustration,
Space cards, Brand logos). Uploaded files are stored in `uploads/`. The
original design assets live in `assets/img/` if you ever need them.
```
hero.jpg · cafe.jpg · restaurant.jpg · below.jpg · building.png
logo-white.png (header) · logo-maroon.png (footer)
```
