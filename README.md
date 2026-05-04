# CSD440 Server-Side Scripting

Coursework repository for **CSD440 — Server-Side Scripting** at Bellevue University. Built in PHP, tested against both XAMPP (Apache + MySQL) and the PHP CLI development server.

**Author**: Brittaney Perry-Morgan
**Instructor**: Jack Lusby
**Term**: Spring 2026

---

## Tech Stack

- **Language**: PHP 8.x
- **Web server (option A)**: XAMPP — Apache + MySQL
- **Web server (option B)**: PHP built-in CLI server (`php -S`) — lighter weight for single-assignment testing
- **Editor**: Visual Studio Code / BBEdit

---

## Local Setup

### Option A — PHP CLI Server (fast, recommended for single-assignment testing)

```bash
cd modules/module_05/module_05_02
php -S localhost:8000
```

Open `http://localhost:8000/BrittaneyCustomers.php` in a browser. `Ctrl+C` to stop.

### Option B — XAMPP

1. Clone or symlink this repository into XAMPP's `htdocs` directory.
2. Start Apache (and MySQL when required by the assignment) via the XAMPP control panel.
3. Browse to `http://localhost/csd440/modules/module_NN/module_NN_NN/Brittaney*.php`.

---

## Module Index

| Module | Topic | Assignment |
|-------:|-------|------------|
| 1 | Apache / XAMPP Setup, First PHP Program | [module_01_03/BrittaneyFirstProgram.php](modules/module_01/module_01_03/BrittaneyFirstProgram.php) |
| 2 | Nested Loops, Random Number Table | [module_02_02/BrittaneyTable2.php](modules/module_02/module_02_02/BrittaneyTable2.php) |
| 3 | External Functions | [module_03_02/BrittaneyTable3.php](modules/module_03/module_03_02/BrittaneyTable3.php) |
| 4 | String Manipulation, Palindrome Checker | [module_04_02/BrittaneyPalindrome.php](modules/module_04/module_04_02/BrittaneyPalindrome.php) |
| 5 | Arrays, Customer Records | [module_05_02/BrittaneyCustomers.php](modules/module_05/module_05_02/BrittaneyCustomers.php) |
| 6 | Objects, MyInteger Class | [module_06_02/BrittaneyMyInteger.php](modules/module_06/module_06_02/BrittaneyMyInteger.php) |
| 7 | Forms, Validation & Sanitization | [module_07_02/BrittaneyForm.php](modules/module_07/module_07_02/BrittaneyForm.php) |
| 8 | PHP & MySQL (MySQLi CRUD scaffolding) | [module_08_02/BrittaneyCreateTable.php](modules/module_08/module_08_02/BrittaneyCreateTable.php) |
| 9 | CRUD Forms *(upcoming)* | — |
| 10 | JSON *(upcoming)* | — |
| 11 | PDFs *(upcoming)* | — |

---

## Repository Structure

```
csd440/
├── modules/
│   └── module_NN/
│       └── module_NN_NN/
│           ├── Brittaney*.php          # Assignment source
│           └── BPERRYMORGAN-MODULE...pdf  # Submitted deliverable
├── .gitignore
└── README.md
```

Each programming assignment lives in its own `module_NN_NN` subdirectory alongside its submission PDF.

---

## Documentation Standards

All PHP source files follow the course's documentation requirements:

- File-level docblock with assignment, course, description, `@author`, and `@date`
- Function-level docblocks with a description, `@param` entries, and `@return`
- PSR-12 formatting (4-space indent, standard brace placement)
- PHP 8 scalar type declarations and return types on function signatures
- `htmlspecialchars()` on every dynamic value rendered to HTML

---

## License / Academic Integrity

Coursework submitted for CSD440 at Bellevue University. Not licensed for reuse as submitted work by other students.
