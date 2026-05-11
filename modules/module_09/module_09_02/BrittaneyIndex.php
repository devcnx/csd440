<?php
/**
 * Module 9.2 Programming Assignment — Index Page
 * CSD440 Server-Side Scripting
 *
 * Landing page for the Module 9 CRUD application. Displays a site map
 * with links to every PHP file in this assignment — the three new pages
 * (Index, Query, Add Record) plus the four carried over from Module 8
 * (Create Table, Populate Table, Query Table, Drop Table).
 *
 * Also attempts a lightweight database check so the user can see at a
 * glance whether the bperrymorgan_ollama_models table exists and how
 * many rows it holds before navigating deeper.
 *
 * @author  Brittaney Perry-Morgan
 * @date    2026-05-11
 * @php     >= 8.0
 */

// ── Configuration ────────────────────────────────────────────
const DB_HOST = 'localhost';
const DB_USER = 'student1';
const DB_PASS = 'pass';
const DB_NAME = 'baseball_01';
const DB_TABLE = 'bperrymorgan_ollama_models';

/**
 * Establishes a MySQLi connection to the baseball_01 database.
 *
 * Disables MySQLi's default exception reporting so connection failure is
 * surfaced through the returned $error reference instead of an uncaught
 * exception, letting the caller render a clean HTML error page.
 *
 * @param  string|null &$error Populated with the failure reason on error.
 * @return mysqli|null         Live connection on success, null on failure.
 */
function connectToDatabase(?string &$error = null): ?mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        $error = $mysqli->connect_error;
        return null;
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

/**
 * Checks whether the Ollama models table currently exists.
 *
 * @param  mysqli $mysqli Active MySQLi connection.
 * @return bool          True if the table is present, false otherwise.
 */
function tableExists(mysqli $mysqli): bool
{
    $escaped = $mysqli->real_escape_string(DB_TABLE);
    $result = $mysqli->query("SHOW TABLES LIKE '" . $escaped . "'");
    if (!($result instanceof mysqli_result)) {
        return false;
    }
    $exists = $result->num_rows > 0;
    $result->free();
    return $exists;
}

/**
 * Returns the current row count of the Ollama models table.
 *
 * Returns 0 if the table does not exist or the query fails.
 *
 * @param  mysqli $mysqli Active MySQLi connection.
 * @return int            Number of rows in the table.
 */
function rowCount(mysqli $mysqli): int
{
    $result = $mysqli->query("SELECT COUNT(*) AS cnt FROM " . DB_TABLE);
    if (!($result instanceof mysqli_result)) {
        return 0;
    }
    $row = $result->fetch_assoc();
    $count = (int) ($row['cnt'] ?? 0);
    $result->free();
    return $count;
}

// ── Execute ──────────────────────────────────────────────────
$connectError = null;
$tablePresent = false;
$rowCountVal = 0;

$mysqli = connectToDatabase($connectError);
if ($mysqli !== null) {
    $tablePresent = tableExists($mysqli);
    if ($tablePresent) {
        $rowCountVal = rowCount($mysqli);
    }
    $mysqli->close();
}

// Page metadata
$studentName = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 9.2 Programming Assignment — Index';
$courseName = 'CSD440 Server-Side Scripting';
$today = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Index — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="stylesheet" href="../../shared.css">
    </head>

    <body>
        <h1>Ollama Models Manager</h1>

        <div class="header-info">
            <p><strong><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
            <p><?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?> —
                <?php echo htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'); ?></p>
            <p><?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <?php if ($connectError !== null): ?>
            <div class="error-summary">
                <h2>Database Unavailable</h2>
                <p class="error-message">Could not connect to the <code><?php echo DB_NAME; ?></code> database.</p>
                <ul class="error-list">
                    <li><?php echo htmlspecialchars($connectError, ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
                <p>Verify MySQL is running and the <code><?php echo DB_USER; ?></code> credentials are valid.</p>
            </div>
        <?php else: ?>
            <div class="confirmation">
                <h2>Database Status</h2>
                <?php if ($tablePresent): ?>
                    <p class="success-message">
                        Table <code><?php echo DB_TABLE; ?></code> is present
                        (<?php echo (int) $rowCountVal; ?> row<?php echo $rowCountVal !== 1 ? 's' : ''; ?>).
                    </p>
                <?php else: ?>
                    <p class="error-message">
                        Table <code><?php echo DB_TABLE; ?></code> does not exist yet.
                        Run <strong>Create Table</strong> and <strong>Populate Table</strong> first.
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h2>Module 9 — New Pages</h2>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="BrittaneyIndex.php">Index</a></td>
                    <td>This page — site map and database status check</td>
                </tr>
                <tr>
                    <td><a href="BrittaneyQuery.php">Query</a></td>
                    <td>Search models by name, quantization, installed status, or parameter range</td>
                </tr>
                <tr>
                    <td><a href="BrittaneyForm.php">Add Record</a></td>
                    <td>Insert a new Ollama model into the database via a validated form</td>
                </tr>
            </tbody>
        </table>

        <h2>Module 8 — Table Management</h2>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="BrittaneyCreateTable.php">Create Table</a></td>
                    <td>Creates the <code><?php echo DB_TABLE; ?></code> table (idempotent)</td>
                </tr>
                <tr>
                    <td><a href="BrittaneyPopulateTable.php">Populate Table</a></td>
                    <td>Loads the 8-row seed dataset (INSERT … ON DUPLICATE KEY UPDATE)</td>
                </tr>
                <tr>
                    <td><a href="BrittaneyQueryTable.php">Query Table</a></td>
                    <td>Four predefined SELECT queries against the table</td>
                </tr>
                <tr>
                    <td><a href="BrittaneyDropTable.php">Drop Table</a></td>
                    <td>Removes the table for a clean reset (DROP TABLE IF EXISTS)</td>
                </tr>
            </tbody>
        </table>
    </body>

</html>