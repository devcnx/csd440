<?php
declare(strict_types=1);

/**
 * Module 10.2 Programming Assignment — JSON Form
 * CSD440 Server-Side Scripting
 *
 * Presents a form that collects at least 8 fields of user data. The form
 * submits to BrittaneyJSONResponse.php, which validates, encodes the data
 * into JSON using json_encode(), and displays the result. If validation
 * fails, errors are stored in the session and the user is redirected back
 * to this form with error messages and repopulated fields.
 *
 * Form fields:
 *   - full_name:            required, max 100 chars
 *   - email:                required, valid email
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

// Retrieve flash data from session (set by BrittaneyJSONResponse on validation failure)
$errors  = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

// Generate CSRF token for form protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Page metadata
date_default_timezone_set('America/Chicago');
$studentName     = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 10.2 Programming Assignment — JSON Form';
$courseName      = 'CSD440 Server-Side Scripting';
$today           = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Form — <?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../../shared.css">
</head>

<body>
    <h1>JSON Data Form</h1>

    <div class="header-info">
        <p><strong><?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p><?php echo htmlspecialchars($assignmentTitle, ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'); ?></p>
        <p><?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

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

    <form method="POST" action="BrittaneyJSONResponse.php" class="registration-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <label for="full_name">Full Name <span class="required">*</span></label>
            <input type="text" id="full_name" name="full_name"
                   placeholder="e.g., Jane Doe" maxlength="100"
                   value="<?php echo old('full_name', $oldData); ?>" required>
<?php if (isset($errors['full_name'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email"
                   placeholder="e.g., jane@example.com"
                   value="<?php echo old('email', $oldData); ?>" required>
<?php if (isset($errors['email'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Phone <span class="required">*</span></label>
            <input type="tel" id="phone" name="phone"
                   placeholder="e.g., (555) 123-4567" maxlength="14"
                   value="<?php echo old('phone', $oldData); ?>" required>
<?php if (isset($errors['phone'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="age">Age <span class="required">*</span></label>
            <input type="number" id="age" name="age"
                   min="1" max="120" placeholder="e.g., 30"
                   value="<?php echo old('age', $oldData); ?>" required>
<?php if (isset($errors['age'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['age'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="city">City <span class="required">*</span></label>
            <input type="text" id="city" name="city"
                   placeholder="e.g., Omaha" maxlength="100"
                   value="<?php echo old('city', $oldData); ?>" required>
<?php if (isset($errors['city'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['city'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="state">State <span class="required">*</span></label>
            <select id="state" name="state" required>
                <option value="">— Select —</option>
<?php foreach (US_STATES as $st): ?>
                <option value="<?php echo $st; ?>"<?php echo selected('state', $st, $oldData); ?>><?php echo $st; ?></option>
<?php endforeach; ?>
            </select>
<?php if (isset($errors['state'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['state'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="programming_language">Programming Language <span class="required">*</span></label>
            <select id="programming_language" name="programming_language" required>
                <option value="">— Select —</option>
<?php foreach (PROGRAMMING_LANGUAGES as $lang): ?>
                <option value="<?php echo $lang; ?>"<?php echo selected('programming_language', $lang, $oldData); ?>><?php echo $lang; ?></option>
<?php endforeach; ?>
            </select>
<?php if (isset($errors['programming_language'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['programming_language'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="experience_years">Years of Experience <span class="required">*</span></label>
            <input type="number" id="experience_years" name="experience_years"
                   min="0" max="80" placeholder="e.g., 5"
                   value="<?php echo old('experience_years', $oldData); ?>" required>
<?php if (isset($errors['experience_years'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['experience_years'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-group">
            <label for="bio">Bio <span class="required">*</span></label>
            <textarea id="bio" name="bio" rows="4" maxlength="500"
                      placeholder="Tell us about yourself..." required><?php echo old('bio', $oldData); ?></textarea>
<?php if (isset($errors['bio'])): ?>
            <span class="error"><?php echo htmlspecialchars($errors['bio'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit">Encode as JSON</button>
            <a href="BrittaneyJSON.php" class="btn-secondary">Reset</a>
        </div>

        <p class="form-note"><span class="required">*</span> Required fields</p>
    </form>

</body>

</html>