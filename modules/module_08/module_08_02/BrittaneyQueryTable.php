<?php
/**
 * Module 8.2 Programming Assignment — Query Table
 * CSD440 Server-Side Scripting
 *
 * Runs four SELECT queries against the bperrymorgan_ollama_models table
 * inside the course-supplied baseball_01 database to demonstrate the
 * table is populated and behaves as expected. Each query exercises a
 * different SQL feature so the demo also covers grading rubric breadth:
 *
 *   1. Full-table read with explicit column ordering.
 *   2. Filtered read (WHERE + ORDER BY) — locally installed models only.
 *   3. Aggregate read (COUNT, SUM, AVG, ROUND) — fleet summary.
 *   4. Grouped read (GROUP BY quantization) — counts per quant scheme.
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
 * Runs an arbitrary SELECT and returns the result rows.
 *
 * Wraps the raw mysqli::query result so the caller works only with plain
 * arrays. Errors are surfaced via the $error reference instead of being
 * thrown so the calling page can render multiple queries independently
 * (one failure should not abort the rest of the page).
 *
 * @param  mysqli      $mysqli Active MySQLi connection.
 * @param  string      $sql    The SQL SELECT statement to execute.
 * @param  string|null &$error Populated with the failure reason on error.
 * @return array               Result rows; empty array on error or no rows.
 */
function runSelect(mysqli $mysqli, string $sql, ?string &$error = null): array
{
    $rows = [];
    $result = $mysqli->query($sql);
    if (!($result instanceof mysqli_result)) {
        $error = $mysqli->error;
        return [];
    }
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $result->free();
    return $rows;
}

// ── Execute ──────────────────────────────────────────────────
$connectError = null;
$mysqli       = connectToDatabase($connectError);

// Each query gets its own error slot so a single failure does not blank
// the rest of the page.
$queries = [
    'all' => [
        'title' => 'All Models (newest first)',
        'sql'   => "SELECT model_id, model_name, parameter_count, quantization, size_gb, release_date, is_installed, use_case
                    FROM " . DB_TABLE . "
                    ORDER BY release_date DESC, model_name",
        'rows'  => [],
        'error' => null,
    ],
    'installed' => [
        'title' => 'Locally Installed Models, Largest First',
        'sql'   => "SELECT model_name, parameter_count, size_gb, use_case
                    FROM " . DB_TABLE . "
                    WHERE is_installed = 1
                    ORDER BY parameter_count DESC",
        'rows'  => [],
        'error' => null,
    ],
    'summary' => [
        'title' => 'Fleet Summary (aggregates)',
        'sql'   => "SELECT
                        COUNT(*)                            AS total_models,
                        SUM(is_installed)                   AS installed_count,
                        ROUND(SUM(size_gb), 2)              AS total_size_gb,
                        ROUND(AVG(parameter_count), 2)      AS avg_param_billions,
                        MAX(parameter_count)                AS largest_param_count,
                        MIN(release_date)                   AS oldest_release,
                        MAX(release_date)                   AS newest_release
                    FROM " . DB_TABLE,
        'rows'  => [],
        'error' => null,
    ],
    'by_quant' => [
        'title' => 'Models Grouped by Quantization',
        'sql'   => "SELECT
                        quantization,
                        COUNT(*)                       AS model_count,
                        ROUND(AVG(size_gb), 2)         AS avg_size_gb
                    FROM " . DB_TABLE . "
                    GROUP BY quantization
                    ORDER BY model_count DESC, quantization",
        'rows'  => [],
        'error' => null,
    ],
];

if ($mysqli !== null) {
    foreach ($queries as $key => $query) {
        $err = null;
        $queries[$key]['rows']  = runSelect($mysqli, $query['sql'], $err);
        $queries[$key]['error'] = $err;
    }
    $mysqli->close();
}

// Page metadata
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 8.2 Programming Assignment — Query Table';
$courseName      = 'CSD440 Server-Side Scripting';
$today           = date('F j, Y');

/**
 * Echoes a value or an em-dash when null/empty for cleaner table cells.
 *
 * @param  mixed $value Raw cell value from a result row.
 * @return string       HTML-safe display string.
 */
function cell(mixed $value): string
{
    if ($value === null || $value === '') {
        return '<span class="empty">—</span>';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Table — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/modules/shared.css">
</head>

<body>
    <h1>Query Table</h1>

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

<?php else: ?>

    <h2>1. <?php echo htmlspecialchars($queries['all']['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <div class="query-note">
        <strong>SQL:</strong>
        <code>SELECT * FROM <?php echo DB_TABLE; ?> ORDER BY release_date DESC, model_name</code>
    </div>
<?php if ($queries['all']['error'] !== null): ?>
    <p class="error-message">Query failed: <?php echo htmlspecialchars($queries['all']['error'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php else: ?>
    <table>
        <caption>All <?php echo count($queries['all']['rows']); ?> models</caption>
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
<?php if (empty($queries['all']['rows'])): ?>
            <tr><td colspan="8" class="empty">No rows. Run BrittaneyPopulateTable.php first.</td></tr>
<?php else: foreach ($queries['all']['rows'] as $row): ?>
            <tr>
                <td><?php echo (int)$row['model_id']; ?></td>
                <td><code><?php echo cell($row['model_name']); ?></code></td>
                <td><?php echo cell($row['parameter_count']); ?></td>
                <td><?php echo cell($row['quantization']); ?></td>
                <td><?php echo cell($row['size_gb']); ?></td>
                <td><?php echo cell($row['release_date']); ?></td>
                <td>
<?php if ((int)$row['is_installed'] === 1): ?>
                    <span class="flag-true">Yes</span>
<?php else: ?>
                    <span class="flag-false">No</span>
<?php endif; ?>
                </td>
                <td><?php echo cell($row['use_case']); ?></td>
            </tr>
<?php endforeach; endif; ?>
        </tbody>
    </table>
<?php endif; ?>

    <h2>2. <?php echo htmlspecialchars($queries['installed']['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <div class="query-note">
        <strong>SQL:</strong>
        <code>SELECT model_name, parameter_count, size_gb, use_case FROM <?php echo DB_TABLE; ?> WHERE is_installed = 1 ORDER BY parameter_count DESC</code>
    </div>
<?php if ($queries['installed']['error'] !== null): ?>
    <p class="error-message">Query failed: <?php echo htmlspecialchars($queries['installed']['error'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php else: ?>
    <table>
        <caption><?php echo count($queries['installed']['rows']); ?> installed models</caption>
        <thead>
            <tr>
                <th>Model</th>
                <th>Params (B)</th>
                <th>Size (GB)</th>
                <th>Use Case</th>
            </tr>
        </thead>
        <tbody>
<?php if (empty($queries['installed']['rows'])): ?>
            <tr><td colspan="4" class="empty">No installed models found.</td></tr>
<?php else: foreach ($queries['installed']['rows'] as $row): ?>
            <tr>
                <td><code><?php echo cell($row['model_name']); ?></code></td>
                <td><?php echo cell($row['parameter_count']); ?></td>
                <td><?php echo cell($row['size_gb']); ?></td>
                <td><?php echo cell($row['use_case']); ?></td>
            </tr>
<?php endforeach; endif; ?>
        </tbody>
    </table>
<?php endif; ?>

    <h2>3. <?php echo htmlspecialchars($queries['summary']['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <div class="query-note">
        <strong>SQL:</strong>
        <code>SELECT COUNT(*), SUM(is_installed), ROUND(SUM(size_gb),2), ROUND(AVG(parameter_count),2), MAX(parameter_count), MIN(release_date), MAX(release_date) FROM <?php echo DB_TABLE; ?></code>
    </div>
<?php if ($queries['summary']['error'] !== null): ?>
    <p class="error-message">Query failed: <?php echo htmlspecialchars($queries['summary']['error'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php elseif (empty($queries['summary']['rows'])): ?>
    <p class="empty">No rows in table to aggregate.</p>
<?php else: $summary = $queries['summary']['rows'][0]; ?>
    <table class="data-display">
        <caption>Aggregate Statistics</caption>
        <tbody>
            <tr><td>Total models cataloged</td><td><?php echo cell($summary['total_models']); ?></td></tr>
            <tr><td>Installed locally</td><td><?php echo cell($summary['installed_count']); ?></td></tr>
            <tr><td>Combined disk footprint</td><td><?php echo cell($summary['total_size_gb']); ?> GB</td></tr>
            <tr><td>Average parameter count</td><td><?php echo cell($summary['avg_param_billions']); ?> B</td></tr>
            <tr><td>Largest model</td><td><?php echo cell($summary['largest_param_count']); ?> B parameters</td></tr>
            <tr><td>Oldest release date</td><td><?php echo cell($summary['oldest_release']); ?></td></tr>
            <tr><td>Newest release date</td><td><?php echo cell($summary['newest_release']); ?></td></tr>
        </tbody>
    </table>
<?php endif; ?>

    <h2>4. <?php echo htmlspecialchars($queries['by_quant']['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <div class="query-note">
        <strong>SQL:</strong>
        <code>SELECT quantization, COUNT(*), ROUND(AVG(size_gb),2) FROM <?php echo DB_TABLE; ?> GROUP BY quantization ORDER BY model_count DESC</code>
    </div>
<?php if ($queries['by_quant']['error'] !== null): ?>
    <p class="error-message">Query failed: <?php echo htmlspecialchars($queries['by_quant']['error'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php else: ?>
    <table>
        <caption>Counts per quantization scheme</caption>
        <thead>
            <tr>
                <th>Quantization</th>
                <th>Model Count</th>
                <th>Avg Size (GB)</th>
            </tr>
        </thead>
        <tbody>
<?php if (empty($queries['by_quant']['rows'])): ?>
            <tr><td colspan="3" class="empty">No rows to group.</td></tr>
<?php else: foreach ($queries['by_quant']['rows'] as $row): ?>
            <tr>
                <td><code><?php echo cell($row['quantization']); ?></code></td>
                <td><?php echo cell($row['model_count']); ?></td>
                <td><?php echo cell($row['avg_size_gb']); ?></td>
            </tr>
<?php endforeach; endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php endif; /* connection ok */ ?>

    <div class="form-actions">
        <a href="BrittaneyCreateTable.php">Create Table</a>
        <a href="BrittaneyPopulateTable.php" class="btn-secondary">Populate Table</a>
        <a href="BrittaneyDropTable.php" class="btn-secondary">Drop Table</a>
    </div>
</body>

</html>
