<?php
// Start session to store validation errors for persistence across redirects
session_start();

/**
 * Module 7.2 Programming Assignment — Form Response Handler
 * CSD440 Server-Side Scripting
 *
 * This script receives form data from BrittaneyForm.html, validates all
 * seven fields across multiple data types, and returns either a confirmation
 * page displaying the sanitized input or an error page listing validation
 * failures for correction.
 *
 * Form fields validated:
 *   - first_name (text, required)
 *   - last_name (text, required)
 *   - email (email, required, FILTER_VALIDATE_EMAIL)
 *   - age (integer, required, range 18-120)
 *   - phone (tel, required, 10-digit format)
 *   - website (url, optional, FILTER_VALIDATE_URL if provided)
 *   - bio (textarea, required, max 500 chars)
 *   - contact_preference (select, required, whitelist: email/phone/either)
 *
 * Data types demonstrated: text, email, integer, telephone, URL, textarea, select
 *
 * Requires PHP 8.0 or newer for typed properties, union types, and
 * modern string functions.
 *
 * @author  Brittaney Perry-Morgan
 * @date    2026-04-26
 * @php     >= 8.0
 */

// Set timezone for any date operations
date_default_timezone_set('America/Chicago');

/**
 * Validates and sanitizes text input.
 *
 * Trims whitespace and applies htmlspecialchars() for safe display.
 * Returns null if the input is empty after trimming.
 *
 * @param  string      $input The raw input value
 * @return string|null         Sanitized string, or null if empty
 */
function validateText(string $input): ?string
{
    $trimmed = trim($input);
    return $trimmed === '' ? null : htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
}

/**
 * Validates an email address.
 *
 * Uses PHP's FILTER_VALIDATE_EMAIL to check format. Returns the sanitized
 * email if valid, null otherwise.
 *
 * @param  string      $email The raw email input
 * @return string|null         Sanitized email, or null if invalid
 */
function validateEmail(string $email): ?string
{
    $trimmed = trim($email);
    if ($trimmed === '' || !filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
        return null;
    }
    return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
}

/**
 * Validates an integer within a specified range.
 *
 * Checks that the input is a valid integer and falls within the min/max
 * bounds. Returns the integer if valid, null otherwise.
 *
 * @param  string      $input The raw input value
 * @param  int         $min   Minimum acceptable value (inclusive)
 * @param  int         $max   Maximum acceptable value (inclusive)
 * @return int|null            Validated integer, or null if invalid
 */
function validateInteger(string $input, int $min, int $max): ?int
{
    $trimmed = trim($input);
    if ($trimmed === '') {
        return null;
    }
    $int = filter_var($trimmed, FILTER_VALIDATE_INT);
    if ($int === false || $int < $min || $int > $max) {
        return null;
    }
    return $int;
}

/**
 * Validates a phone number format.
 *
 * Accepts common US phone formats: (XXX) XXX-XXXX, XXX-XXX-XXXX, XXXXXXXXXX.
 * Returns a standardized format if valid, null otherwise.
 *
 * @param  string      $phone The raw phone input
 * @return string|null         Formatted phone number, or null if invalid
 */
function validatePhone(string $phone): ?string
{
    $trimmed = trim($phone);
    // Remove all non-digit characters
    $digits = preg_replace('/\D/', '', $trimmed);
    // Must have exactly 10 digits
    if (strlen($digits) !== 10) {
        return null;
    }
    // Format as (XXX) XXX-XXXX
    return sprintf('(%s) %s-%s',
        substr($digits, 0, 3),
        substr($digits, 3, 3),
        substr($digits, 6, 4)
    );
}

/**
 * Validates a URL.
 *
 * Uses FILTER_VALIDATE_URL to check format. Returns the sanitized URL
 * if valid, null if empty or invalid. This field is optional.
 *
 * @param  string      $url The raw URL input
 * @return string|null       Sanitized URL, or null if empty/invalid
 */
function validateUrl(string $url): ?string
{
    $trimmed = trim($url);
    if ($trimmed === '') {
        return null; // Optional field
    }
    if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
        return null;
    }
    return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
}

/**
 * Validates a textarea input with length limits.
 *
 * Trims whitespace and enforces a maximum character count. Returns the
 * sanitized text if valid, null if empty.
 *
 * @param  string      $input   The raw textarea input
 * @param  int         $maxLen  Maximum allowed characters
 * @return string|null           Sanitized text, or null if empty
 */
function validateTextarea(string $input, int $maxLen): ?string
{
    $trimmed = trim($input);
    if ($trimmed === '') {
        return null;
    }
    if (strlen($trimmed) > $maxLen) {
        return null;
    }
    return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
}

/**
 * Validates a select dropdown value against allowed options.
 *
 * Checks that the submitted value exists in the whitelist of acceptable
 * options. Returns the value if valid, null otherwise.
 *
 * @param  string      $input   The raw select value
 * @param  array       $options Whitelist of acceptable values
 * @return string|null           Validated option, or null if invalid
 */
function validateSelect(string $input, array $options): ?string
{
    $trimmed = trim($input);
    if ($trimmed === '' || !in_array($trimmed, $options, true)) {
        return null;
    }
    return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
}

/**
 * Collects validation errors for all form fields.
 *
 * Returns an associative array where keys are field names and values
 * are error messages. An empty array indicates all fields passed validation.
 *
 * @param  array $data The raw POST data
 * @return array       Array of error messages (empty if all valid)
 */
function validateFormData(array $data): array
{
    $errors = [];

    // First name — required text
    if (validateText($data['first_name'] ?? '') === null) {
        $errors['first_name'] = 'First name is required.';
    }

    // Last name — required text
    if (validateText($data['last_name'] ?? '') === null) {
        $errors['last_name'] = 'Last name is required.';
    }

    // Email — required, valid format
    if (validateEmail($data['email'] ?? '') === null) {
        $errors['email'] = 'A valid email address is required.';
    }

    // Age — required integer, 18-120
    if (validateInteger($data['age'] ?? '', 18, 120) === null) {
        $errors['age'] = 'Age is required and must be between 18 and 120.';
    }

    // Phone — required, 10 digits
    if (validatePhone($data['phone'] ?? '') === null) {
        $errors['phone'] = 'A valid 10-digit phone number is required.';
    }

    // Website — optional, but must be valid URL if provided
    $website = validateUrl($data['website'] ?? '');
    if ($data['website'] !== '' && $website === null) {
        $errors['website'] = 'Website must be a valid URL (e.g., https://example.com).';
    }

    // Bio — required textarea, max 500 chars
    if (validateTextarea($data['bio'] ?? '', 500) === null) {
        $errors['bio'] = 'Bio is required (max 500 characters).';
    }

    // Contact preference — required select
    if (validateSelect($data['contact_preference'] ?? '', ['email', 'phone', 'either']) === null) {
        $errors['contact_preference'] = 'Please select a contact preference.';
    }

    return $errors;
}

/**
 * Renders the confirmation page after successful validation.
 *
 * Displays all submitted data in a formatted table for user verification.
 *
 * @param  array $data The validated and sanitized form data
 * @return void
 */
function renderConfirmation(array $data): void
{
?>
    <div class="confirmation">
        <h2>Registration Successful</h2>
        <p class="success-message">Thank you! Your information has been received.</p>

        <table class="data-display">
            <caption>Your Submitted Information</caption>
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>First Name</td>
                    <td><?php echo $data['first_name']; ?></td>
                </tr>
                <tr>
                    <td>Last Name</td>
                    <td><?php echo $data['last_name']; ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo $data['email']; ?></td>
                </tr>
                <tr>
                    <td>Age</td>
                    <td><?php echo $data['age']; ?></td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td><?php echo $data['phone']; ?></td>
                </tr>
                <tr>
                    <td>Website</td>
                    <td><?php echo $data['website'] !== '' ? $data['website'] : '<em>Not provided</em>'; ?></td>
                </tr>
                <tr>
                    <td>Bio</td>
                    <td><?php echo nl2br($data['bio']); ?></td>
                </tr>
                <tr>
                    <td>Contact Preference</td>
                    <td><?php echo ucfirst($data['contact_preference']); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <a href="BrittaneyForm.php" class="btn-secondary">Submit Another Registration</a>
        </div>
    </div>
<?php
}

/**
 * Renders an error page when validation fails.
 *
 * Lists all validation errors and provides a link back to the form.
 * Also repopulates the form with previously entered values.
 *
 * @param  array $errors Array of error messages
 * @param  array $data   Previously submitted values (for repopulation)
 * @return void
 */
function renderErrorPage(array $errors, array $data): void
{
    $firstName = htmlspecialchars($data['first_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $lastName = htmlspecialchars($data['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($data['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $age = htmlspecialchars($data['age'] ?? '', ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($data['phone'] ?? '', ENT_QUOTES, 'UTF-8');
    $website = htmlspecialchars($data['website'] ?? '', ENT_QUOTES, 'UTF-8');
    $bio = htmlspecialchars($data['bio'] ?? '', ENT_QUOTES, 'UTF-8');
    $contactPref = htmlspecialchars($data['contact_preference'] ?? '', ENT_QUOTES, 'UTF-8');
?>
    <div class="error-summary">
        <h2>Correction Required</h2>
        <p class="error-message">Please fix the following issues and resubmit:</p>

        <ul class="error-list">
<?php foreach ($errors as $field => $message): ?>
            <li><?php echo $message; ?></li>
<?php endforeach; ?>
        </ul>

        <div class="form-actions">
            <a href="BrittaneyForm.php" class="btn-secondary">Return to Form</a>
        </div>
    </div>

    <h2>Previously Entered Data</h2>
    <div class="query-note">
        <p>Review your entries below and return to the form to make corrections.</p>
    </div>

    <table class="data-display">
        <caption>Data You Submitted</caption>
        <thead>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>First Name</td>
                <td><?php echo $firstName; ?></td>
            </tr>
            <tr>
                <td>Last Name</td>
                <td><?php echo $lastName; ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?php echo $email; ?></td>
            </tr>
            <tr>
                <td>Age</td>
                <td><?php echo $age; ?></td>
            </tr>
            <tr>
                <td>Phone</td>
                <td><?php echo $phone; ?></td>
            </tr>
            <tr>
                <td>Website</td>
                <td><?php echo $website !== '' ? $website : '<em>Not provided</em>'; ?></td>
            </tr>
            <tr>
                <td>Bio</td>
                <td><?php echo nl2br($bio); ?></td>
            </tr>
            <tr>
                <td>Contact Preference</td>
                <td><?php echo ucfirst($contactPref); ?></td>
            </tr>
        </tbody>
    </table>
<?php
}

// Page metadata
$studentName = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 7.2 Programming Assignment — Form Response';
$courseName = 'CSD440 Server-Side Scripting';
$date = date('F j, Y');

// Initialize variables
$errors = [];
$data = [];
$isValid = false;

// Verify form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to form if accessed directly
    header('Location: BrittaneyForm.php');
    exit;
}

// Collect and validate form data
$data = $_POST;
$errors = validateFormData($data);

// Determine if validation passed
$isValid = empty($errors);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isValid ? 'Registration Confirmed' : 'Correction Required'; ?> — <?php echo $assignmentTitle; ?></title>
    <link rel="stylesheet" href="/modules/shared.css">
</head>

<body>
    <h1><?php echo $isValid ? 'Registration Confirmed' : 'Form Submission Error'; ?></h1>
    
    <div class="header-info">
        <p><strong><?php echo $studentName; ?></strong></p>
        <p><?php echo $assignmentTitle; ?> — <?php echo $courseName; ?></p>
        <p><?php echo $date; ?></p>
    </div>

<?php
if ($isValid):
    // Prepare sanitized data for display
    $sanitizedData = [
        'first_name' => validateText($_POST['first_name']),
        'last_name' => validateText($_POST['last_name']),
        'email' => validateEmail($_POST['email']),
        'age' => validateInteger($_POST['age'], 18, 120),
        'phone' => validatePhone($_POST['phone']),
        'website' => validateUrl($_POST['website'] ?? ''),
        'bio' => validateTextarea($_POST['bio'], 500),
        'contact_preference' => validateSelect($_POST['contact_preference'], ['email', 'phone', 'either']),
    ];
    renderConfirmation($sanitizedData);
else:
    // Validation failed — store errors and data in session, then redirect
    // back to the form so the user sees their persisted input and errors
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $data;
    header('Location: BrittaneyForm.php');
    exit;
endif;
?>
</body>

</html>
