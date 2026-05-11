<?php
/**
 * Module 9.2 Programming Assignment — Query (Search) Page
 * CSD440 Server-Side Scripting
 *
 * Provides a search form that lets the user filter the bperrymorgan_ollama_models
 * table by model name (partial match), quantization scheme, installed status,
 * and parameter-count range. Uses prepared statements for all user-supplied
 * values to prevent SQL injection — consistent with the safe-DB-access pattern
 * established in Module 7 and carried through Module 8.
 *
 * The form submits via GET so search URLs are bookmarkable and shareable.
 * Results are rendered in a styled table with a count of matching rows.
 *
 * @author  Brittaney Perry-Morgan
 * @date    2026-05-11
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
 * Builds and executes a parameterized SELECT query from the submitted
 * search criteria.
 *
 * All conditions are AND-ed together. The model_name filter uses
 * LIKE with wildcards for partial matching; all other filters are
 * exact-match or range-based. Parameters are bound via mysqli
 * prepared statements to prevent SQL injection.
 *
 * @param  mysqli      $mysqli       Active MySQLi connection.
 * @param  string      $modelName    Partial model name to search (empty = all).
 * @param  string      $quantization Quantization filter (empty = all).
 * @param  string      $installed    Installed filter: '1', '0', or '' (all).
 * @param  string      $paramMin     Minimum parameter count (empty = no lower bound).
 * @param  string      $paramMax     Maximum parameter count (empty = no upper bound).
 * @param  string|null &$error       Populated with the failure reason on error.
 * @return array                     Result rows as associative arrays.
 */
function searchModels(
    mysqli $mysqli,
    string $modelName,
    string $quantization,
    string $installed,
    string $paramMin,
    string $paramMax,
    ?string &$error = null
): array {
    $conditions = [];
    $types = '';
    $params = [];

    // Model name — partial match (LIKE)
    if ($modelName !== '') {
        $conditions[] = 'model_name LIKE ?';
        $types .= 's';
        $params[] = '%' . $modelName . '%';
    }

    // Quantization — exact match
    if ($quantization !== '') {
        $conditions[] = 'quantization = ?';
        $types .= 's';
        $params[] = $quantization;
    }

    // Installed status — exact match
    if ($installed === '1' || $installed === '0') {
        $conditions[] = 'is_installed = ?';
        $types .= 'i';
        $params[] = (int)$installed;
    }

    // Parameter count range
    if ($paramMin !== '') {
        $conditions[] = 'parameter_count >= ?';
        $types .= 'd';
        $params[] = (float)$paramMin;
    }
    if ($paramMax !== '') {
        $conditions[] = 'parameter_count <= ?';
        $types .= 'd';
        $params[] = (float)$paramMax;
    }

    // Build the full SQL statement
    $sql = "SELECT model_id, model_name, parameter_count, quantization, "
         . "size_gb, release_date, is_installed, use_case "
         . "FROM " . DB_TABLE;

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY parameter_count ASC, model_name ASC';

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        return [];
    }

    // Bind parameters if any conditions were added
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $result->free();
    $stmt->close();

    return $rows;
}

// ── Process search ───────────────────────────────────────────
$modelName    = trim($_GET['model_name'] ?? '');
$quantization = trim($_GET['quantization'] ?? '');
$installed    = trim($_GET['installed'] ?? '');
$paramMin     = trim($_GET['param_min'] ?? '');
$paramMax     = trim($_GET['param_max'] ?? '');
$hasSearch    = ($modelName !== '' || $quantization !== '' || $installed !== ''
                 || $paramMin !== '' || $paramMax !== '');

$connectError = null;
$searchError  = null;
$results      = [];

$mysqli = connectToDatabase($connectError);
if ($mysqli !== null) {
    if ($hasSearch) {
        $results = searchModels(
            $mysqli,
            $modelName,
            $quantization,
            $installed,
            $paramMin,
            $paramMax,
            $searchError
        );
    }
    $mysqli->close();
}

// Page metadata
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 9.2 Programming Assignment — Query';
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
        return '<span class="empty">&mdash;</span>';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../../shared.css">
</head>

<body>
    <h1>Search Models</h1>

    <div class="header-info">
        <p><strong><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p><?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'); ?></p>
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

    <!-- ── Search Form ──────────────────────────────────────── -->
    <form method="GET" action="BrittaneyQuery.php" class="registration-form">
        <div class="form-group">
            <label for="model_name">Model Name</label>
            <input type="text" id="model_name" name="model_name"
                   placeholder="Partial match (e.g., llama)"
                   value="<?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-group">
            <label for="quantization">Quantization</label>
            <select id="quantization" name="quantization">
                <option value="">— Any —</option>
                <option value="Q4_K_M" <?php echo $quantization === 'Q4_K_M' ? 'selected' : ''; ?>>Q4_K_M</option>
                <option value="F16" <?php echo $quantization === 'F16' ? 'selected' : ''; ?>>F16</option>
            </select>
        </div>

        <div class="form-group">
            <label for="installed">Installed Locally</label>
            <select id="installed" name="installed">
                <option value="">— Any —</option>
                <option value="1" <?php echo $installed === '1' ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo $installed === '0' ? 'selected' : ''; ?>>No</option>
            </select>
        </div>

        <div class="form-group">
            <label for="param_min">Parameter Count (Min, billions)</label>
            <input type="number" id="param_min" name="param_min" step="0.01" min="0"
                   placeholder="e.g., 1"
                   value="<?php echo htmlspecialchars($paramMin, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-group">
            <label for="param_max">Parameter Count (Max, billions)</label>
            <input type="number" id="param_max" name="param_max" step="0.01" min="0"
                   placeholder="e.g., 100"
                   value="<?php echo htmlspecialchars($paramMax, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-actions">
            <button type="submit">Search</button>
            <a href="BrittaneyQuery.php" class="btn-secondary">Reset</a>
        </div>
    </form>

    <!-- ── Search Results ───────────────────────────────────── -->
<?php if ($hasSearch): ?>
    <h2>Search Results</h2>
    <div class="query-note">
        <p>Filters applied:
            <strong>Model name:</strong> <?php echo $modelName !== '' ? htmlspecialchars('"' . $modelName . '"', ENT_QUOTES, 'UTF-8') : '<em>any</em>'; ?>,
            <strong>Quantization:</strong> <?php echo $quantization !== '' ? htmlspecialchars($quantization, ENT_QUOTES, 'UTF-8') : '<em>any</em>'; ?>,
            <strong>Installed:</strong> <?php echo $installed === '1' ? 'Yes' : ($installed === '0' ? 'No' : '<em>any</em>'); ?>,
            <strong>Params:</strong> <?php echo $paramMin !== '' ? htmlspecialchars($paramMin, ENT_QUOTES, 'UTF-8') . 'B' : '—'; ?>
            – <?php echo $paramMax !== '' ? htmlspecialchars($paramMax, ENT_QUOTES, 'UTF-8') . 'B' : '—'; ?>
        </p>
    </div>

<?php if ($searchError !== null): ?>
    <div class="error-summary">
        <p class="error-message">Query failed: <?php echo htmlspecialchars($searchError, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
<?php elseif (empty($results)): ?>
    <p class="empty">No models matched your search criteria. Try broadening the filters.</p>
<?php else: ?>
    <table>
        <caption><?php echo count($results); ?> model<?php echo count($results) !== 1 ? 's' : ''; ?> found</caption>
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
<?php foreach ($results as $row): ?>
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
<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php else: ?>
    <div class="query-note">
        <p>Enter search criteria above and click <strong>Search</strong> to filter models.
           Leave all fields blank to list every row.</p>
    </div>
<?php endif; ?>

<?php endif; /* connection ok */ ?>

    <div class="form-actions">
        <a href="BrittaneyIndex.php">Index</a>
        <a href="BrittaneyForm.php" class="btn-secondary">Add Record</a>
    </div>
</body>

</html>
