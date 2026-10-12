# MiniPress — a tiny CMS built from zero

A working content management system in ~900 lines of plain PHP: public blog, login,
dashboard, create/edit/delete posts, MySQL or SQLite. No framework, no Composer.

It exists to teach the exact stack in the Webrock Media / Code Craft job ad:
**object-oriented PHP, a central database, server-side logic, HTML5 / CSS3 / JavaScript**,
and the structure that WordPress itself is built on.

---

## 1. Run it (two minutes)

### Option A — instant, no XAMPP

```bash
cd minipress
php setup.php                       # creates tables + admin user + sample posts
php -S localhost:8000 -t public     # start PHP's built-in server
```

Open <http://localhost:8000> — sign in with **admin / admin123**.

> **Sinhala:** `php -S` කියන්නේ PHP එක්කම එන පොඩි web server එකක්. XAMPP නැතුව test කරන්න පුළුවන්.

### Option B — XAMPP + MySQL (the real-world setup)

1. Start **Apache** and **MySQL** in the XAMPP control panel.
2. Copy the `minipress` folder into `C:\xampp\htdocs\`.
3. Open `config.php` and change the driver:
   ```php
   'driver' => 'mysql',
   ```
4. In phpMyAdmin, run `schema.sql` (or just run `php setup.php` from the terminal).
5. Visit <http://localhost/minipress/public/>.

Requires PHP 8.1 or newer (XAMPP 8.2 is fine).

---

## 2. The fundamentals this job asks for

| Job ad phrase | Where it lives here | The idea |
|---|---|---|
| "object-oriented PHP programming" | `src/Database.php`, `src/Auth.php`, `src/Post.php` | Group related behaviour into a class instead of loose functions and globals |
| "maintain the central database" | `src/Database.php`, `schema.sql`, `setup.php` | One PDO connection, one schema, prepared statements everywhere |
| "server-side logic" | `public/admin/*.php` | Receive `$_POST`, validate, save, redirect |
| "standard HTML/CSS practices" | `public/partials/*`, `public/assets/style.css` | Semantic markup, one stylesheet, mobile-first, visible focus states |
| "front-end technologies incl. JavaScript" | `public/assets/app.js` | Progressive enhancement — the site works with JS turned off |
| "understanding of WordPress CMS" | all of it | WordPress is this, scaled up. Section 5 maps it across |

### The eight things to actually understand

1. **Request → response.** Every page is a PHP file that receives `$_GET` / `$_POST`,
   talks to the database, and prints HTML. Nothing else is happening.
   *Sinhala: browser එකෙන් request එකක් එනවා, PHP file එක run වෙලා HTML එකක් return කරනවා. ඒ විතරයි.*

2. **POST-redirect-GET.** After a successful form submit you `redirect()`, never print HTML.
   Otherwise refreshing the page saves the post twice. See `admin/edit.php`.

3. **Prepared statements.** SQL and user data travel on separate channels:
   ```php
   $stmt = $pdo->prepare('SELECT * FROM posts WHERE slug = ?');
   $stmt->execute([$slug]);           // never "WHERE slug = '$slug'"
   ```
   This is what stops SQL injection. *Sinhala: query එකට data කෙලින්ම concat කරන්න එපා.*

4. **Escape on output.** `e($title)` wraps `htmlspecialchars()`. Every single variable
   printed into HTML goes through it. That is what stops XSS.

5. **Passwords are hashed, never stored.** `password_hash()` to save,
   `password_verify()` to check. There is no way back to the original text.

6. **Sessions.** `$_SESSION['user_id']` is how the server remembers who you are between
   requests. `session_regenerate_id(true)` on login blocks session fixation.

7. **CSRF tokens.** A hidden random value in every form, compared with the session copy.
   Without it, another site can make your logged-in browser delete posts.

8. **Separation of concerns.** Pages never write SQL. They call `Post::published()`.
   Change the table once, every page follows. This is the "model" idea.

---

## 3. File map

```
config.php          driver + credentials (the only file you edit per environment)
setup.php           CLI: create tables, seed admin + sample posts
schema.sql          same schema as raw MySQL, for phpMyAdmin

src/
  bootstrap.php     loads everything, starts the session   (WordPress: wp-load.php)
  Database.php      one PDO connection, safe defaults       (WordPress: $wpdb)
  Auth.php          login / logout / requireLogin           (WordPress: wp_signon)
  Post.php          every query that touches `posts`        (WordPress: WP_Query)
  helpers.php       e(), csrf, slugify, excerpt, autop      (WordPress: esc_html, wpautop)

public/             <- this folder is the web root; src/ and config.php stay outside it
  index.php         post list                               (WordPress: index.php + The Loop)
  post.php          single post by slug                     (WordPress: single.php)
  partials/         header + footer                         (WordPress: get_header/get_footer)
  assets/           style.css, app.js
  admin/            login, dashboard, edit, delete          (WordPress: wp-admin)
```

Keeping `config.php` **outside** the web root matters: if PHP ever stops executing,
nobody can download your database password through the browser.

---

## 4. Data model

```
users                          posts
-----                          -----
id                             id
username        (unique)       author_id  -> users.id
password_hash                  title
display_name                   slug       (unique, used in the URL)
created_at                     body
                               status     draft | published
                               created_at, updated_at
```

`status` is the whole idea behind drafts: `index.php` filters on `status = 'published'`,
the dashboard does not filter. One column, two very different screens.

---

## 5. The bridge to WordPress

WordPress keeps posts, pages, menu items and media in **one** table, `wp_posts`, and tells
them apart with a `post_type` column. MiniPress is that table with the lid off.

| MiniPress | WordPress |
|---|---|
| `Post::published()` + `foreach` | The Loop: `while (have_posts()) { the_post(); }` |
| `src/Post.php` | `WP_Query` |
| `Database::pdo()` | the global `$wpdb` |
| `e($value)` | `esc_html()`, `esc_attr()`, `esc_url()` |
| `csrf_field()` | `wp_nonce_field()` |
| `autop()` | `wpautop()` |
| `public/partials/header.php` | `header.php` + `get_header()` |
| `public/admin/` | `wp-admin/` |
| nothing yet | hooks: `add_action()`, `add_filter()` |

**Your next step after this project:** install WordPress on XAMPP, make a theme folder with
`style.css`, `index.php`, `single.php`, `functions.php`, and rebuild this same front page as
a theme. You will recognise every piece. Then write one plugin that uses `add_action()` —
hooks are the only large idea in WordPress that MiniPress does not cover.

---

## 6. Exercises (in difficulty order)

1. Add a **search box** on the front page: `WHERE title LIKE ?` with `%term%` bound safely.
2. Add **pagination** — 5 posts per page with `LIMIT ? OFFSET ?`.
3. Fix `slugify()` so a Sinhala title does not collapse to `post`.
   (Try it now: write a post titled "සිංහල පෝස්ට්" and look at the link.)
4. Add a `categories` table and a `post_categories` join table — many-to-many.
5. Add **comments** with a name, body and an `approved` flag. Pending comments show
   only in the dashboard.
6. Add **image upload** to the editor: validate the real MIME type, rename the file,
   store it outside the web root or in `public/uploads/` with uploads not executable.
7. Turn the delete button into an **AJAX** call with `fetch()` that removes the table row
   without a page reload — keep the plain form working as a fallback.
8. Add a second user role (`editor`) that cannot delete other people's posts.

---

## 7. Interview talking points

If you build this and can explain these five answers, you are past most intern screens:

- *"How do you stop SQL injection?"* → prepared statements, and show `Post::findPublishedBySlug()`.
- *"Where do you store passwords?"* → hashed with `password_hash()`, show `Auth::attempt()`.
- *"What is a CSRF token?"* → `csrf_field()` / `csrf_verify()`, and why delete is a POST form.
- *"What is The Loop?"* → fetch posts, iterate, print — point at `public/index.php`.
- *"Why is `config.php` outside `public/`?"* → credentials must never be downloadable.

**Before you ship anything real:** change the admin password, set
`display_errors = Off` in production, and never commit `config.php` with live credentials.
