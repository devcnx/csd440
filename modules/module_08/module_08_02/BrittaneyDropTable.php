<?php
/**
 * Module 8.2 Programming Assignment — Drop Table
 * CSD440 Server-Side Scripting
 *
 * Drops the bperrymorgan_ollama_models table from the course-supplied
 * baseball_01 database using MySQLi. Used between test runs to reset the
 * environment to a clean state. Uses DROP TABLE IF EXISTS so the script
 * is safe to run even when the table has already been removed.
 *
 * Reports whether the table existed before the DROP so the user can tell
 * the difference between a successful cleanup and an idempotent no-op.
 *
 * @author  Brittaney Perry-Morgan
 * @date    2026-05-03
 * @php     >= 8.0
 */

// ── Configuration ────────────────────────────────────────────
const DB_HOST  = 'localhost';
const DB_USER  = 'student1';
const DB_PASS  = 'pass';
const DB_NAME  = 'baseball_01';
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
 * Checks whether a given table currently exists in the active database.
 *
 * Used before the DROP so the success message can distinguish "actually
 * removed something" from "no-op, table was already gone."
 *
 * @param  mysqli $mysqli Active MySQLi connection.
 * @param  string $table  Unqualified table name to check.
 * @return bool           True if the table exists, false otherwise.
 */
function tableExists(mysqli $mysqli, string $table): bool
{
    $escaped = $mysqli->real_escape_string($table);
    $result = $mysqli->query("SHOW TABLES LIKE '" . $escaped . "'");
    if (!($result instanceof mysqli_result)) {
        return false;
    }
    $exists = $result->num_rows > 0;
    $result->free();
    return $exists;
}

/**
 * Issues the DROP TABLE statement against the active connection.
 *
 * Uses DROP TABLE IF EXISTS so the script is safely idempotent — running
 * it on an already-dropped table does not error out.
 *
 * @param  mysqli      $mysqli Active MySQLi connection.
 * @param  string|null &$error Populated with the failure reason on error.
 * @return bool                True on success, false on error.
 */
function dropOllamaModelsTable(mysqli $mysqli, ?string &$error = null): bool
{
    if (!$mysqli->query("DROP TABLE IF EXISTS " . DB_TABLE)) {
        $error = $mysqli->error;
        return false;
    }
    return true;
}

// ── Execute ──────────────────────────────────────────────────
$connectError  = null;
$dropError     = null;
$existedBefore = false;
$dropped       = false;

$mysqli = connectToDatabase($connectError);
if ($mysqli !== null) {
    $existedBefore = tableExists($mysqli, DB_TABLE);
    $dropped       = dropOllamaModelsTable($mysqli, $dropError);
    $mysqli->close();
}

// Page metadata
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 8.2 Programming Assignment — Drop Table';
$courseName      = 'CSD440 Server-Side Scripting';
$today           = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop Table — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/modules/shared.css">
</head>

<body>
    <h1>Drop Table</h1>

    <div class="header-info">
        <p><strong><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p><?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'); ?></p>
        <p><?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

<?php if ($connectError !== null): ?>
    <div class="error-summary">
        <h2>Connection Failed</h2>
        <p class="error-message">Could not connect to the <code><?php echo DB_NAME; ?></code> database.</p>
        <ul class="error-list">
            <li><?php echo htmlspecialchars($connectError, ENT_QUOTES, 'UTF-8'); ?></li>
        </ul>
        <p>Verify MySQL is running and the <code><?php echo DB_USER; ?></code> credentials are valid.</p>
    </div>

<?php elseif (!$dropped): ?>
    <div class="error-summary">
        <h2>Drop Failed</h2>
        <p class="error-message">The <code><?php echo DB_TABLE; ?></code> table could not be dropped.</p>
        <ul class="error-list">
            <li><?php echo htmlspecialchars($dropError ?? 'Unknown error.', ENT_QUOTES, 'UTF-8'); ?></li>
        </ul>
    </div>

<?php else: ?>
    <div class="confirmation">
        <h2><?php echo $existedBefore ? 'Table Dropped' : 'Nothing To Drop'; ?></h2>

<?php if ($existedBefore): ?>
        <p class="success-message">
            The <code><?php echo DB_TABLE; ?></code> table was found and removed
            from the <code><?php echo DB_NAME; ?></code> database.
        </p>
<?php else: ?>
        <p class="success-message">
            The <code><?php echo DB_TABLE; ?></code> table did not exist, so the
            DROP statement completed as a no-op. Database is in a clean state.
        </p>
<?php endif; ?>

        <div class="query-note">
            <strong>SQL executed:</strong>
            <code>DROP TABLE IF EXISTS <?php echo DB_TABLE; ?></code>
        </div>

        <p>Next step: rebuild the table by running
            <code><a href="BrittaneyCreateTable.php">BrittaneyCreateTable.php</a></code>.</p>
    </div>
<?php endif; ?>

    <div class="form-actions">
        <a href="BrittaneyCreateTable.php">Create Table</a>
        <a href="BrittaneyPopulateTable.php" class="btn-secondary">Populate Table</a>
        <a href="BrittaneyQueryTable.php" class="btn-secondary">Query Table</a>
    </div>
</body>

</html>
