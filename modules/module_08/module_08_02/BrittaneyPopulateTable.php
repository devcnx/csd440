<?php
/**
 * Module 8.2 Programming Assignment — Populate Table
 * CSD440 Server-Side Scripting
 *
 * Inserts seed data into the bperrymorgan_ollama_models table inside the
 * course-supplied baseball_01 database. Uses a parameterized prepared
 * statement (mysqli::prepare + bind_param) so the load step also serves
 * as a working demonstration of the safe-DB-access pattern from M7.1.
 *
 * The seed dataset describes eight Ollama models that span a realistic
 * range of parameter sizes, quantizations, release dates, and on-disk
 * footprints — chosen so the M9 query work has interesting data to filter
 * and aggregate over.
 *
 * Idempotency: the INSERT uses ON DUPLICATE KEY UPDATE keyed on
 * model_name so re-running this script refreshes existing rows rather
 * than throwing duplicate-key errors. The summary distinguishes inserts
 * from updates so the result is unambiguous.
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

// Seed rows, ordered by release date.
// Each row: [model_name, parameter_count_billions, quantization,
//            size_gb, release_date, is_installed, use_case]
const SEED_ROWS = [
    ['llava:13b',                13.00, 'Q4_K_M', 8.00, '2024-01-30', 0, 'Vision-language model for image understanding'],
    ['nomic-embed-text:latest',   0.14, 'F16',    0.27, '2024-02-01', 1, 'Text embeddings for RAG pipelines'],
    ['phi3:mini',                 3.80, 'Q4_K_M', 2.30, '2024-04-23', 1, 'Lightweight reasoning on limited hardware'],
    ['gemma2:9b',                 9.00, 'Q4_K_M', 5.44, '2024-06-27', 1, 'Open Google model, balanced performance'],
    ['mistral-nemo:12b',         12.00, 'Q4_K_M', 7.07, '2024-07-18', 1, 'Multilingual chat with long context window'],
    ['llama3.1:8b',               8.00, 'Q4_K_M', 4.92, '2024-07-23', 1, 'General-purpose chat and coding assistance'],
    ['llama3.1:70b',             70.00, 'Q4_K_M',39.50, '2024-07-23', 0, 'High-quality reasoning, slower local inference'],
    ['qwen2.5-coder:7b',          7.00, 'Q4_K_M', 4.36, '2024-09-19', 1, 'Code generation and refactoring'],
];

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
 * Inserts the seed dataset into the table using a prepared statement.
 *
 * Loops the SEED_ROWS constant, binding each row's values into the same
 * prepared INSERT to avoid SQL injection and re-parsing overhead. The
 * ON DUPLICATE KEY UPDATE clause keys on model_name (the UNIQUE column),
 * so re-running the script refreshes rows rather than failing.
 *
 * affected_rows semantics under ON DUPLICATE KEY UPDATE:
 *   1 = new row inserted
 *   2 = existing row updated
 *   0 = existing row matched but no values changed (counted as update)
 *
 * @param  mysqli      $mysqli Active MySQLi connection.
 * @param  string|null &$error Populated with the failure reason on error.
 * @return array               Counts: ['inserted' => int, 'updated' => int,
 *                                       'total' => int, 'failed' => int].
 */
function populateOllamaModelsTable(mysqli $mysqli, ?string &$error = null): array
{
    $sql = "INSERT INTO " . DB_TABLE . "
        (model_name, parameter_count, quantization, size_gb, release_date, is_installed, use_case)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            parameter_count = VALUES(parameter_count),
            quantization    = VALUES(quantization),
            size_gb         = VALUES(size_gb),
            release_date    = VALUES(release_date),
            is_installed    = VALUES(is_installed),
            use_case        = VALUES(use_case)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        return ['inserted' => 0, 'updated' => 0, 'total' => count(SEED_ROWS), 'failed' => count(SEED_ROWS)];
    }

    $inserted = 0;
    $updated  = 0;
    $failed   = 0;

    foreach (SEED_ROWS as $row) {
        // Type string: s = string, d = decimal/double, i = integer
        // Order matches the VALUES (?, ?, ?, ?, ?, ?, ?) above.
        $name        = $row[0];
        $params      = $row[1];
        $quant       = $row[2];
        $sizeGb      = $row[3];
        $releaseDate = $row[4];
        $installed   = $row[5];
        $useCase     = $row[6];

        $stmt->bind_param('sdsdsis', $name, $params, $quant, $sizeGb, $releaseDate, $installed, $useCase);

        if (!$stmt->execute()) {
            $failed++;
            continue;
        }

        // affected_rows: 1 = insert, 2 = update via ON DUPLICATE KEY,
        // 0 = matched but unchanged. Treat 0 and 2 both as "already there."
        if ($stmt->affected_rows === 1) {
            $inserted++;
        } else {
            $updated++;
        }
    }
    $stmt->close();

    return [
        'inserted' => $inserted,
        'updated'  => $updated,
        'total'    => count(SEED_ROWS),
        'failed'   => $failed,
    ];
}

/**
 * Reads every row currently in the table for post-load verification.
 *
 * @param  mysqli $mysqli Active MySQLi connection.
 * @return array          List of row associative arrays, ordered by model_id.
 */
function fetchAllRows(mysqli $mysqli): array
{
    $rows = [];
    $result = $mysqli->query("SELECT * FROM " . DB_TABLE . " ORDER BY model_id");
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }
    return $rows;
}

// ── Execute ──────────────────────────────────────────────────
$connectError  = null;
$populateError = null;
$counts        = ['inserted' => 0, 'updated' => 0, 'total' => 0, 'failed' => 0];
$rows          = [];
$populated     = false;

$mysqli = connectToDatabase($connectError);
if ($mysqli !== null) {
    $counts    = populateOllamaModelsTable($mysqli, $populateError);
    $populated = $populateError === null && $counts['failed'] === 0;
    if ($populateError === null) {
        $rows = fetchAllRows($mysqli);
    }
    $mysqli->close();
}

// Page metadata
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 8.2 Programming Assignment — Populate Table';
$courseName      = 'CSD440 Server-Side Scripting';
$today           = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Populate Table — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/modules/shared.css">
</head>

<body>
    <h1>Populate Table</h1>

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

<?php elseif ($populateError !== null): ?>
    <div class="error-summary">
        <h2>Populate Failed</h2>
        <p class="error-message">The prepared INSERT could not be initialized.</p>
        <ul class="error-list">
            <li><?php echo htmlspecialchars($populateError, ENT_QUOTES, 'UTF-8'); ?></li>
        </ul>
        <p>If the error mentions a missing table, run
            <code><a href="BrittaneyCreateTable.php">BrittaneyCreateTable.php</a></code> first.</p>
    </div>

<?php else: ?>
    <div class="confirmation">
        <h2>Seed Load Complete</h2>
        <p class="success-message">
            Processed <?php echo (int)$counts['total']; ?> rows against
            <code><?php echo DB_TABLE; ?></code>:
            <strong><?php echo (int)$counts['inserted']; ?></strong> inserted,
            <strong><?php echo (int)$counts['updated']; ?></strong> updated.
<?php if ($counts['failed'] > 0): ?>
            <span class="error"><?php echo (int)$counts['failed']; ?> failed.</span>
<?php endif; ?>
        </p>
        <div class="query-note">
            <strong>SQL pattern:</strong>
            <code>INSERT INTO <?php echo DB_TABLE; ?> ( … ) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE …</code>
            — bound seven times per row using <code>mysqli::prepare</code> + <code>bind_param</code>.
        </div>
        <p>Next step: verify with
            <code><a href="BrittaneyQueryTable.php">BrittaneyQueryTable.php</a></code>.</p>
    </div>

    <h2>Table Contents After Load</h2>
    <table>
        <caption>SELECT * FROM <?php echo DB_TABLE; ?> ORDER BY model_id</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Model</th>
                <th>Params (B)</th>
                <th>Quant</th>
                <th>Size (GB)</th>
                <th>Released</th>
                <th>Installed</th>
                <th>Use Case</th>
            </tr>
        </thead>
        <tbody>
<?php if (empty($rows)): ?>
            <tr><td colspan="8" class="empty">No rows present.</td></tr>
<?php else: foreach ($rows as $row): ?>
            <tr>
                <td><?php echo (int)$row['model_id']; ?></td>
                <td><code><?php echo htmlspecialchars($row['model_name'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                <td><?php echo htmlspecialchars($row['parameter_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['quantization'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['size_gb'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['release_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
<?php if ((int)$row['is_installed'] === 1): ?>
                    <span class="flag-true">Yes</span>
<?php else: ?>
                    <span class="flag-false">No</span>
<?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['use_case'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
<?php endforeach; endif; ?>
        </tbody>
    </table>
<?php endif; ?>

    <div class="form-actions">
        <a href="BrittaneyQueryTable.php">Query Table</a>
        <a href="BrittaneyCreateTable.php" class="btn-secondary">Create Table</a>
        <a href="BrittaneyDropTable.php" class="btn-secondary">Drop Table</a>
    </div>
</body>

</html>
