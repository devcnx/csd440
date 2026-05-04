<?php
/**
 * Module 8.2 Programming Assignment — Create Table
 * CSD440 Server-Side Scripting
 *
 * Creates the bperrymorgan_ollama_models table inside the course-supplied
 * baseball_01 database using MySQLi. The table catalogs locally available
 * Ollama language models — chosen as a topic because it ties into ongoing
 * AI-enablement work and naturally exercises multiple data types.
 *
 * Schema (8 columns, 5 distinct data types):
 *   - model_id        INT          AUTO_INCREMENT PRIMARY KEY
 *   - model_name      VARCHAR(100) UNIQUE
 *   - parameter_count DECIMAL(6,2) — model size in billions of parameters
 *   - quantization    VARCHAR(20)  — e.g., Q4_K_M, F16
 *   - size_gb         DECIMAL(6,2) — disk footprint in gigabytes
 *   - release_date    DATE
 *   - is_installed    TINYINT(1)   — boolean flag, 1 = pulled locally
 *   - use_case        VARCHAR(255)
 *
 * Intended run order: Create → Populate → Query. Drop is available for
 * cleanup between test runs.
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
 * Issues the CREATE TABLE statement against the active connection.
 *
 * Uses CREATE TABLE IF NOT EXISTS so the script is safely idempotent —
 * re-running it does not error out when the table already exists.
 *
 * @param  mysqli      $mysqli Active MySQLi connection.
 * @param  string|null &$error Populated with the failure reason on error.
 * @return bool                True if the table now exists, false on error.
 */
function createOllamaModelsTable(mysqli $mysqli, ?string &$error = null): bool
{
    $sql = "CREATE TABLE IF NOT EXISTS " . DB_TABLE . " (
        model_id        INT          NOT NULL AUTO_INCREMENT,
        model_name      VARCHAR(100) NOT NULL UNIQUE,
        parameter_count DECIMAL(6,2) NOT NULL,
        quantization    VARCHAR(20)  NOT NULL,
        size_gb         DECIMAL(6,2) NOT NULL,
        release_date    DATE         NOT NULL,
        is_installed    TINYINT(1)   NOT NULL DEFAULT 0,
        use_case        VARCHAR(255) NOT NULL,
        PRIMARY KEY (model_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$mysqli->query($sql)) {
        $error = $mysqli->error;
        return false;
    }
    return true;
}

/**
 * Reads the live table schema via DESCRIBE for display confirmation.
 *
 * Returns the column descriptors as an array of associative rows. An
 * empty array indicates the DESCRIBE query failed or the table is missing.
 *
 * @param  mysqli $mysqli Active MySQLi connection.
 * @return array          List of column descriptors (Field, Type, Null, …).
 */
function describeTable(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query("DESCRIBE " . DB_TABLE);
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }
    return $rows;
}

// ── Execute ──────────────────────────────────────────────────
$connectError = null;
$createError  = null;
$schemaRows   = [];
$tableCreated = false;

$mysqli = connectToDatabase($connectError);
if ($mysqli !== null) {
    $tableCreated = createOllamaModelsTable($mysqli, $createError);
    if ($tableCreated) {
        $schemaRows = describeTable($mysqli);
    }
    $mysqli->close();
}

// Page metadata
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 8.2 Programming Assignment — Create Table';
$courseName      = 'CSD440 Server-Side Scripting';
$today           = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Table — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/modules/shared.css">
</head>

<body>
    <h1>Create Table</h1>

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

<?php elseif (!$tableCreated): ?>
    <div class="error-summary">
        <h2>Create Failed</h2>
        <p class="error-message">The <code><?php echo DB_TABLE; ?></code> table could not be created.</p>
        <ul class="error-list">
            <li><?php echo htmlspecialchars($createError ?? 'Unknown error.', ENT_QUOTES, 'UTF-8'); ?></li>
        </ul>
    </div>

<?php else: ?>
    <div class="confirmation">
        <h2>Table Ready</h2>
        <p class="success-message">
            The <code><?php echo DB_TABLE; ?></code> table is in place
            (created if missing) inside the <code><?php echo DB_NAME; ?></code> database.
        </p>
        <div class="query-note">
            <strong>SQL executed:</strong>
            <code>CREATE TABLE IF NOT EXISTS <?php echo DB_TABLE; ?> ( … 8 columns … )</code>
        </div>
        <p>Next step: populate the table by running
            <code><a href="BrittaneyPopulateTable.php">BrittaneyPopulateTable.php</a></code>.</p>
    </div>

    <h2>Confirmed Schema</h2>
    <table>
        <caption>DESCRIBE <?php echo DB_TABLE; ?></caption>
        <thead>
            <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
                <th>Default</th>
                <th>Extra</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($schemaRows as $row): ?>
            <tr>
                <td><code><?php echo htmlspecialchars($row['Field'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                <td><?php echo htmlspecialchars($row['Type'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['Null'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['Key'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars((string)($row['Default'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['Extra'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

    <div class="form-actions">
        <a href="BrittaneyPopulateTable.php">Populate Table</a>
        <a href="BrittaneyQueryTable.php" class="btn-secondary">Query Table</a>
        <a href="BrittaneyDropTable.php" class="btn-secondary">Drop Table</a>
    </div>
</body>

</html>
