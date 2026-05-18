<?php
declare(strict_types=1);

/**
 * Module 10.2 Programming Assignment — JSON Response Handler
 * CSD440 Server-Side Scripting
 *
 * Receives form data from BrittaneyJSON.php, validates all fields server-side,
 * encodes the data into JSON format using json_encode(), and displays the
 * result in a well-formatted output. If validation fails, stores errors and
 * submitted data in the session and redirects back to the form.
 *
 * Form fields validated:
 *   - full_name:            required, max 100 chars
 *   - email:                required, valid email (FILTER_VALIDATE_EMAIL)
 *   - phone:                required, 10 digits, formatted (XXX) XXX-XXXX
 *   - age:                  required, integer 1–120
 *   - city:                 required, max 100 chars
 *   - state:                required, US state abbreviation (whitelist)
 *   - programming_language: required, whitelist of languages
 *   - experience_years:     required, integer 0–80
 *   - bio:                  required, max 500 chars
 *
 * @author  Brittaney Perry-Morgan
 * @date    2026-05-16
 * @php     >= 8.0
 */

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);
require_once __DIR__ . '/functions.php';

date_default_timezone_set('America/Chicago');

/**
 * Renders the JSON confirmation page after successful validation.
 *
 * Displays all submitted data in a formatted table and the raw JSON
 * output produced by json_encode() with JSON_PRETTY_PRINT.
 *
 * @param  array $sanitizedData HTML-escaped data for table display.
 * @param  array $jsonData      Raw data for JSON encoding.
 * @return void
 */
function renderConfirmation(array $sanitizedData, array $jsonData): void
{
    try {
        $jsonEncoded = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        $jsonEncoded = 'Error encoding data: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
?>
    <div class="confirmation">
        <h2>Data Encoded Successfully</h2>
        <p class="success-message">Your form data has been encoded into JSON format using <code>json_encode()</code>.</p>

        <table class="data-display">
            <caption>Submitted Data</caption>
            <tbody>
                <tr>
                    <td>Full Name</td>
                    <td><?php echo $sanitizedData['full_name']; ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><code><?php echo $sanitizedData['email']; ?></code></td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td><?php echo $sanitizedData['phone']; ?></td>
                </tr>
                <tr>
                    <td>Age</td>
                    <td><?php echo $sanitizedData['age']; ?></td>
                </tr>
                <tr>
                    <td>City</td>
                    <td><?php echo $sanitizedData['city']; ?></td>
                </tr>
                <tr>
                    <td>State</td>
                    <td><?php echo $sanitizedData['state']; ?></td>
                </tr>
                <tr>
                    <td>Programming Language</td>
                    <td><?php echo $sanitizedData['programming_language']; ?></td>
                </tr>
                <tr>
                    <td>Experience (Years)</td>
                    <td><?php echo $sanitizedData['experience_years']; ?></td>
                </tr>
                <tr>
                    <td>Bio</td>
                    <td><?php echo nl2br($sanitizedData['bio']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="confirmation json-output">
        <h2>JSON Output</h2>
        <p class="success-message">Encoded with <code>json_encode($data, JSON_PRETTY_PRINT)</code></p>
        <pre><code><?php echo htmlspecialchars($jsonEncoded, ENT_QUOTES, 'UTF-8'); ?></code></pre>
    </div>

    <div class="form-actions">
        <a href="BrittaneyJSON.php">Submit Another Entry</a>
    </div>
<?php
}

// Page metadata
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 10.2 Programming Assignment — JSON Response';
$courseName      = 'CSD440 Server-Side Scripting';
$today           = date('F j, Y');

// Initialize variables
$errors  = [];
$isValid = false;

// Verify form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to form if accessed directly
    header('Location: BrittaneyJSON.php');
    exit;
}

// Verify CSRF token to prevent cross-site request forgery
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $_SESSION['form_errors'] = ['csrf' => 'Invalid form submission. Please try again.'];
    $_SESSION['form_data'] = [];
    header('Location: BrittaneyJSON.php');
    exit;
}

// Collect and validate form data
$errors  = validateFormData($_POST);
$isValid = empty($errors);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isValid ? 'JSON Output' : 'Correction Required'; ?> — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../../shared.css">
</head>

<body>
    <h1><?php echo $isValid ? 'JSON Output' : 'Form Submission Error'; ?></h1>

    <div class="header-info">
        <p><strong><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p><?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'); ?></p>
        <p><?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

<?php
if ($isValid):
    // Prepare sanitized data for HTML display
    $sanitizedData = [
        'full_name'            => validateText($_POST['full_name']),
        'email'                => validateEmail($_POST['email']),
        'phone'                => validatePhone($_POST['phone']),
        'age'                  => validateInteger($_POST['age'], 1, 120),
        'city'                 => validateText($_POST['city']),
        'state'                => validateSelect($_POST['state'], US_STATES),
        'programming_language' => validateSelect($_POST['programming_language'], PROGRAMMING_LANGUAGES),
        'experience_years'     => validateInteger($_POST['experience_years'], 0, 80),
        'bio'                  => validateTextarea($_POST['bio'], 500),
    ];

    // Build raw data for JSON encoding (no HTML escaping, proper types)
    $jsonData = buildJsonData($_POST);

    renderConfirmation($sanitizedData, $jsonData);
else:
    // Validation failed — store errors and data in session, redirect to form
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: BrittaneyJSON.php');
    exit;
endif;
?>

</body>

</html>