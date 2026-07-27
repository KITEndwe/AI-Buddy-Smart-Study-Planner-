# AI Smart Study Planner (PHP / MySQL)

Final year project — Cavendish University Zambia.

## 1. Install in XAMPP

1. Copy this whole **`AI Smart Study Planner`** folder into `htdocs`
   (e.g. `C:\xampp\htdocs\AI Smart Study Planner`).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. Click **Import**, choose `sql/smart_buddy.sql`, and run it.
   This creates the `smart_buddy` database, all tables, and seed data.

## 2. Configure

Open `config/config.php` and check this line matches your folder name exactly:

```php
define('BASE_URL', '/AI Smart Study Planner');
```

Open `config/db.php` if your MySQL username/password differ from the XAMPP default
(`root` / empty password).

## 3. Run it

Visit `http://localhost/AI Smart Study Planner/` (your browser will usually
URL-encode the spaces automatically).

You'll land on the student login. Admins log in separately via the "Admin login"
tab on the same page, or directly at `/admin/login.php`.

**Demo logins (seeded by the SQL file):**
- Student: `chanda.mwansa@cavendish.ac.zm` / `Student@123`
- Admin: `admin@cavendish.ac.zm` / `Admin@123`

There is no public student registration — new students are created by an admin
(see below).

## 4. Turn on real AI (optional)

The AI study planner and AI study buddy chatbot run in **demo mode** by default (a
local PHP heuristic / rules-based responder — no external calls, nothing to configure).

To use live Gemini AI instead:
1. Get a key at https://aistudio.google.com/apikey
2. Open `config/config.php` and set:
   ```php
   define('GEMINI_API_KEY', 'your-key-here');
   ```
Both features automatically switch from demo mode to live generation once a key is set.

## Folder structure — two apps, one shared database

```
admin/              Everything admin-facing, self-contained
├── login.php / logout.php / dashboard.php
├── students/        add.php, edit.php, delete.php, index.php
│                    ← this is where a student gets a login and is assigned to a programme
├── programmes/      manage degree programmes
└── courses/         manage courses per programme

student/             Everything student-facing, self-contained
├── auth/             login.php (also the site's landing page), forgot/reset password
├── dashboard.php
├── courses/          enrol, track progress
├── assignments/      + exams/ + study sessions
├── groups/           study groups, shared notes, discussion
├── notes/             personal notes, file/YouTube attachments
├── goals/             weekly/monthly/semester goals
├── planner/           AI study planner (Gemini or heuristic fallback)
├── buddy/             AI study buddy chatbot (Gemini or rules-based fallback)
├── notifications/
└── profile/            includes password change with a strength meter

config/              Shared: DB connection + site config (Gemini key goes here)
includes/            Shared: header/sidebar/footer templates + helper functions
sql/                 smart_buddy.sql — full schema + seed data (the shared DB)
assets/              Shared CSS/JS used by both admin/ and student/
uploads/notes/       Uploaded note attachments
index.php            Router: sends you to the right login based on session
```

**How the two sides connect:** both `admin/` and `student/` read from the same
`smart_buddy` database via the shared `config/db.php` connection. An admin creates a
student under a programme in `admin/students/add.php` (with a temporary password,
strength-checked live) → that student then logs in at `student/auth/login.php` and
everything they see (courses, assignments, etc.) is scoped to the programme the
admin assigned them to.

## Notes for your report

- Passwords are hashed with PHP's `password_hash()` (bcrypt) — never stored in plain text.
- All database queries use PDO **prepared statements** to prevent SQL injection.
- Sessions (`$_SESSION`) control access — `require_login()` and `require_admin()` guard
  every protected page, and the two roles never share a login form.
- Student accounts are provisioned by an admin only — there is no open self-registration.



https://github.com/KITEndwe/AI-Buddy-Smart-Study-Planner-.git
