<?php
/**
 * Module 9.2 Programming Assignment — Add Record Form
 * CSD440 Server-Side Scripting
 *
 * Presents a form for inserting a new row into the bperrymorgan_ollama_models
 * table. Validates all fields server-side before executing a prepared INSERT
 * statement. On success, displays a confirmation page with the inserted data.
 * On validation failure, repopulates the form with the submitted values and
 * lists the errors that need correction.
 *
 * Validation rules:
 *   - model_name:    required, max 100 chars, must be unique in the table
 *   - parameter_count: required, numeric, >= 0.01
 *   - quantization:   required, max 20 chars
 *   - size_gb:         required, numeric, >= 0.01
 *   - release_date:    required, valid date (YYYY-MM-DD)
 *   - is_installed:    required, must be '0' or '1'
 *   - use_case:        required, max 255 chars
 *
 * Uses session-based PRG (Post/Redirect/Get) so refreshed submissions
 * do not create duplicate rows.
 *
 * @author  Brittaney Perry-Morgan
 * @date    2026-05-11
 * @php     >= 8.0
 */

session_start();

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
 * Checks whether a model name already exists in the table.
 *
 * Used during validation to enforce the UNIQUE constraint on
 * model_name before attempting the INSERT.
 *
 * @param  mysqli $mysqli    Active MySQLi connection.
 * @param  string $modelName The model name to check.
 * @return bool              True if the name already exists.
 */
function modelNameExists(mysqli $mysqli, string $modelName): bool
{
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM " . DB_TABLE . " WHERE model_name = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $modelName);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

/**
 * Inserts a new model record using a prepared statement.
 *
 * @param  mysqli      $mysqli     Active MySQLi connection.
 * @param  array       $data       Validated and sanitized data.
 * @param  string|null &$error     Populated on failure.
 * @return bool                    True on success, false on failure.
 */
function insertModel(mysqli $mysqli, array $data, ?string &$error = null): bool
{
    $sql = "INSERT INTO " . DB_TABLE . "
            (model_name, parameter_count, quantization, size_gb, release_date, is_installed, use_case)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        return false;
    }

    $stmt->bind_param(
        'sdsdsis',
        $data['model_name'],
        $data['parameter_count'],
        $data['quantization'],
        $data['size_gb'],
        $data['release_date'],
        $data['is_installed'],
        $data['use_case']
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

/**
 * Validates the submitted form data and returns an array of error messages.
 *
 * Each key matches a form field name so errors can be displayed inline.
 * Returns an empty array when all fields pass validation.
 *
 * @param  array $postData The raw POST data.
 * @param  bool  $checkUnique If true, checks model_name uniqueness against DB.
 * @return array Associative array of [field => error message].
 */
function validateFormData(array $postData, bool $checkUnique = false): array
{
    $errors = [];

    // Model name — required, max 100 chars, must be unique
    $modelName = trim($postData['model_name'] ?? '');
    if ($modelName === '') {
        $errors['model_name'] = 'Model name is required.';
    } elseif (strlen($modelName) > 100) {
        $errors['model_name'] = 'Model name must be 100 characters or fewer.';
    }

    // Parameter count — required, must be numeric and positive
    $paramCount = trim($postData['parameter_count'] ?? '');
    if ($paramCount === '') {
        $errors['parameter_count'] = 'Parameter count is required.';
    } elseif (!is_numeric($paramCount) || (float)$paramCount < 0.01) {
        $errors['parameter_count'] = 'Parameter count must be a positive number.';
    }

    // Quantization — required, max 20 chars
    $quantization = trim($postData['quantization'] ?? '');
    if ($quantization === '') {
        $errors['quantization'] = 'Quantization is required.';
    } elseif (strlen($quantization) > 20) {
        $errors['quantization'] = 'Quantization must be 20 characters or fewer.';
    }

    // Size in GB — required, must be numeric and positive
    $sizeGb = trim($postData['size_gb'] ?? '');
    if ($sizeGb === '') {
        $errors['size_gb'] = 'Size (GB) is required.';
    } elseif (!is_numeric($sizeGb) || (float)$sizeGb < 0.01) {
        $errors['size_gb'] = 'Size must be a positive number.';
    }

    // Release date — required, must be a valid date
    $releaseDate = trim($postData['release_date'] ?? '');
    if ($releaseDate === '') {
        $errors['release_date'] = 'Release date is required.';
    } else {
        $d = date_create($releaseDate);
        if (!$d || $d->format('Y-m-d') !== $releaseDate) {
            $errors['release_date'] = 'Release date must be a valid date (YYYY-MM-DD).';
        }
    }

    // Installed status — required, must be 0 or 1
    $installed = trim($postData['is_installed'] ?? '');
    if ($installed !== '0' && $installed !== '1') {
        $errors['is_installed'] = 'Please select whether the model is installed.';
    }

    // Use case — required, max 255 chars
    $useCase = trim($postData['use_case'] ?? '');
    if ($useCase === '') {
        $errors['use_case'] = 'Use case is required.';
    } elseif (strlen($useCase) > 255) {
        $errors['use_case'] = 'Use case must be 255 characters or fewer.';
    }

    // Unique check for model_name — only if no earlier errors on that field
    if ($checkUnique && !isset($errors['model_name']) && $modelName !== '') {
        $connErr = null;
        $mysqli = connectToDatabase($connErr);
        if ($mysqli !== null) {
            if (modelNameExists($mysqli, $modelName)) {
                $errors['model_name'] = 'A model with this name already exists.';
            }
            $mysqli->close();
        }
    }

    return $errors;
}

/**
 * Sanitizes validated POST data for safe display.
 *
 * @param  array $postData The raw POST data.
 * @return array Sanitized data safe for HTML output.
 */
function sanitizeFormData(array $postData): array
{
    return [
        'model_name'      => htmlspecialchars(trim($postData['model_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'parameter_count' => htmlspecialchars(trim($postData['parameter_count'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'quantization'    => htmlspecialchars(trim($postData['quantization'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'size_gb'         => htmlspecialchars(trim($postData['size_gb'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'release_date'     => htmlspecialchars(trim($postData['release_date'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'is_installed'     => trim($postData['is_installed'] ?? ''),
        'use_case'         => htmlspecialchars(trim($postData['use_case'] ?? ''), ENT_QUOTES, 'UTF-8'),
    ];
}

// ── Handle POST submission (PRG pattern) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateFormData($_POST, true);

    if (empty($errors)) {
        // All valid — insert the record
        $connErr = null;
        $mysqli = connectToDatabase($connErr);

        if ($mysqli === null) {
            // Store error in session and redirect back
            $_SESSION['form_errors'] = ['database' => 'Could not connect to the database: ' . $connErr];
            $_SESSION['form_data'] = $_POST;
            header('Location: BrittaneyForm.php');
            exit;
        }

        $insertError = null;
        $success = insertModel($mysqli, [
            'model_name'      => trim($_POST['model_name']),
            'parameter_count' => (float)trim($_POST['parameter_count']),
            'quantization'    => trim($_POST['quantization']),
            'size_gb'         => (float)trim($_POST['size_gb']),
            'release_date'    => trim($_POST['release_date']),
            'is_installed'    => (int)trim($_POST['is_installed']),
            'use_case'        => trim($_POST['use_case']),
        ], $insertError);

        $mysqli->close();

        if ($success) {
            $_SESSION['insert_success'] = true;
            $_SESSION['inserted_data'] = sanitizeFormData($_POST);
            header('Location: BrittaneyForm.php');
            exit;
        }

        // Insert failed — redirect back with error
        $_SESSION['form_errors'] = ['database' => $insertError ?? 'Failed to insert the record.'];
        $_SESSION['form_data'] = $_POST;
        header('Location: BrittaneyForm.php');
        exit;
    }

    // Validation failed — store errors and data, redirect back
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: BrittaneyForm.php');
    exit;
}

// ── GET request: display form ────────────────────────────────
$errors     = $_SESSION['form_errors'] ?? [];
$oldData    = $_SESSION['form_data'] ?? [];
$successMsg = $_SESSION['insert_success'] ?? false;
$inserted   = $_SESSION['inserted_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data'], $_SESSION['insert_success'], $_SESSION['inserted_data']);

/**
 * Returns old form data for a field, HTML-escaped.
 *
 * @param  string $field    The field name.
 * @param  array  $oldData  The stored old data.
 * @return string           Escaped value or empty string.
 */
function old(string $field, array $oldData): string
{
    return isset($oldData[$field]) ? htmlspecialchars(trim($oldData[$field]), ENT_QUOTES, 'UTF-8') : '';
}

/**
 * Returns 'selected' if a dropdown option matches the old value.
 *
 * @param  string $field    The field name.
 * @param  string $value    The option value to check.
 * @param  array  $oldData  The stored old data.
 * @return string           'selected' or empty string.
 */
function selected(string $field, string $value, array $oldData): string
{
    return (isset($oldData[$field]) && trim($oldData[$field]) === $value) ? ' selected' : '';
}

// Page metadata
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 9.2 Programming Assignment — Add Record';
$courseName      = 'CSD440 Server-Side Scripting';
$today           = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Record — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../../shared.css">
</head>

<body>
    <h1>Add a Model Record</h1>

    <div class="header-info">
        <p><strong><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p><?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'); ?></p>
        <p><?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

<?php if ($successMsg && !empty($inserted)): ?>
    <!-- ── Success Confirmation ──────────────────────────────── -->
    <div class="confirmation">
        <h2>Record Added Successfully</h2>
        <p class="success-message">The model has been inserted into the <code><?php echo DB_TABLE; ?></code> table.</p>

        <table class="data-display">
            <caption>Inserted Record</caption>
            <tbody>
                <tr>
                    <td>Model Name</td>
                    <td><code><?php echo $inserted['model_name']; ?></code></td>
                </tr>
                <tr>
                    <td>Parameter Count</td>
                    <td><?php echo $inserted['parameter_count']; ?> B</td>
                </tr>
                <tr>
                    <td>Quantization</td>
                    <td><?php echo $inserted['quantization']; ?></td>
                </tr>
                <tr>
                    <td>Size (GB)</td>
                    <td><?php echo $inserted['size_gb']; ?> GB</td>
                </tr>
                <tr>
                    <td>Release Date</td>
                    <td><?php echo $inserted['release_date']; ?></td>
                </tr>
                <tr>
                    <td>Installed Locally</td>
                    <td><?php echo $inserted['is_installed'] === '1' ? '<span class="flag-true">Yes</span>' : '<span class="flag-false">No</span>'; ?></td>
                </tr>
                <tr>
                    <td>Use Case</td>
                    <td><?php echo $inserted['use_case']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="form-actions">
        <a href="BrittaneyForm.php">Add Another Record</a>
        <a href="BrittaneyQuery.php" class="btn-secondary">Search Models</a>
        <a href="BrittaneyIndex.php" class="btn-secondary">Index</a>
    </div>

<?php else: ?>
    <!-- ── Error Messages ─────────────────────────────────────── -->
<?php if (!empty($errors)): ?>
    <div class="error-summary">
        <h2>Correction Required</h2>
        <p class="error-message">Please fix the following issues and resubmit:</p>
        <ul class="error-list">
<?php foreach ($errors as $message): ?>
            <li><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
<?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

    <!-- ── Add Record Form ───────────────────────────────────── -->
    <form method="POST" action="BrittaneyForm.php" class="registration-form">
        <div class="form-group">
            <label for="model_name">Model Name <span class="required">*</span></label>
            <input type="text" id="model_name" name="model_name"
                   placeholder="e.g., llama3.1:8b" maxlength="100"
                   value="<?php echo old('model_name', $oldData); ?>" required>
<?php if (isset($errors['model_name'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['model_name'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="parameter_count">Parameter Count (Billions) <span class="required">*</span></label>
            <input type="number" id="parameter_count" name="parameter_count"
                   step="0.01" min="0.01" placeholder="e.g., 8.00"
                   value="<?php echo old('parameter_count', $oldData); ?>" required>
<?php if (isset($errors['parameter_count'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['parameter_count'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="quantization">Quantization <span class="required">*</span></label>
            <input type="text" id="quantization" name="quantization"
                   placeholder="e.g., Q4_K_M" maxlength="20"
                   value="<?php echo old('quantization', $oldData); ?>" required>
<?php if (isset($errors['quantization'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['quantization'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="size_gb">Size (GB) <span class="required">*</span></label>
            <input type="number" id="size_gb" name="size_gb"
                   step="0.01" min="0.01" placeholder="e.g., 4.92"
                   value="<?php echo old('size_gb', $oldData); ?>" required>
<?php if (isset($errors['size_gb'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['size_gb'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="release_date">Release Date <span class="required">*</span></label>
            <input type="date" id="release_date" name="release_date"
                   value="<?php echo old('release_date', $oldData); ?>" required>
<?php if (isset($errors['release_date'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['release_date'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="is_installed">Installed Locally <span class="required">*</span></label>
            <select id="is_installed" name="is_installed" required>
                <option value="">— Select —</option>
                <option value="1"<?php echo selected('is_installed', '1', $oldData); ?>>Yes</option>
                <option value="0"<?php echo selected('is_installed', '0', $oldData); ?>>No</option>
            </select>
<?php if (isset($errors['is_installed'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['is_installed'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="use_case">Use Case <span class="required">*</span></label>
            <input type="text" id="use_case" name="use_case"
                   placeholder="e.g., General-purpose chat and coding assistance" maxlength="255"
                   value="<?php echo old('use_case', $oldData); ?>" required>
<?php if (isset($errors['use_case'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['use_case'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit">Add Record</button>
            <a href="BrittaneyForm.php" class="btn-secondary">Reset</a>
        </div>

        <p class="form-note"><span class="required">*</span> Required fields</p>
    </form>
<?php endif; ?>

    <div class="form-actions" style="margin-top: 30px;">
        <a href="BrittaneyIndex.php">Index</a>
        <a href="BrittaneyQuery.php" class="btn-secondary">Search Models</a>
    </div>
</body>

</html>
