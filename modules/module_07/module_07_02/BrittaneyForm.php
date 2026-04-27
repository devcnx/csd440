<!DOCTYPE html>
<html lang="en">

<?php
// Start session to check for validation errors from the response handler
session_start();

// Retrieve any stored form data and errors, then clear them from the session
// so they don't persist on subsequent visits
$errors = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

/**
 * Helper to safely repopulate input fields with old data.
 *
 * @param  string $field The name attribute of the form field
 * @return string         The sanitized value, or empty string
 */
function old(string $field): string
{
    global $oldData;
    return isset($oldData[$field]) ? htmlspecialchars($oldData[$field], ENT_QUOTES, 'UTF-8') : '';
}

/**
 * Checks if a select option should be pre-selected.
 *
 * @param  string $field   The name attribute of the select element
 * @param  string $value   The option value to check
 * @return string           'selected' if it matches, otherwise empty string
 */
function selected(string $field, string $value): string
{
    global $oldData;
    return (isset($oldData[$field]) && $oldData[$field] === $value) ? 'selected' : '';
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module 7.2 Programming Assignment — Brittaney Form</title>
    <link rel="stylesheet" href="/modules/shared.css">
</head>

<body>
    <h1>User Registration Form</h1>

    <div class="header-info">
        <p><strong>Brittaney Perry-Morgan</strong></p>
        <p>Module 7.2 Programming Assignment — CSD440 Server-Side Scripting</p>
        <p id="current-date"></p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="error-summary">
        <h2>Correction Required</h2>
        <p class="error-message">Please fix the following issues and resubmit:</p>
        <ul class="error-list">
            <?php foreach ($errors as $message): ?>
                <li><?php echo $message; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="query-note">
        <p>This form collects seven fields across multiple data types: text, email, integer, telephone, URL,
            textarea, and select dropdown. All required fields must be completed before submission.</p>
    </div>

    <form method="POST" action="BrittaneyResponse.php" class="registration-form">

        <div class="form-group">
            <label for="first_name">First Name <span class="required">*</span></label>
            <input type="text" id="first_name" name="first_name"
                   placeholder="Enter your first name" value="<?php echo old('first_name'); ?>" required>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name <span class="required">*</span></label>
            <input type="text" id="last_name" name="last_name"
                   placeholder="Enter your last name" value="<?php echo old('last_name'); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email"
                   placeholder="you@example.com" value="<?php echo old('email'); ?>" required>
        </div>

        <div class="form-group">
            <label for="age">Age <span class="required">*</span></label>
            <input type="number" id="age" name="age" min="18" max="120"
                   placeholder="18" value="<?php echo old('age'); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number <span class="required">*</span></label>
            <input type="tel" id="phone" name="phone"
                   placeholder="(555) 123-4567" pattern="[0-9\-\(\)\s]{10,15}"
                   value="<?php echo old('phone'); ?>" required>
        </div>

        <div class="form-group">
            <label for="website">Website (Optional)</label>
            <input type="url" id="website" name="website"
                   placeholder="https://yourwebsite.com" value="<?php echo old('website'); ?>">
        </div>

        <div class="form-group">
            <label for="bio">Bio <span class="required">*</span></label>
            <textarea id="bio" name="bio" rows="4"
                      placeholder="Tell us about yourself (max 500 characters)"
                      maxlength="500" required><?php echo old('bio'); ?></textarea>
        </div>

        <div class="form-group">
            <label for="contact_preference">Preferred Contact Method <span class="required">*</span></label>
            <select id="contact_preference" name="contact_preference" required>
                <option value="">— Select an option —</option>
                <option value="email" <?php echo selected('contact_preference', 'email'); ?>>Email</option>
                <option value="phone" <?php echo selected('contact_preference', 'phone'); ?>>Phone</option>
                <option value="either" <?php echo selected('contact_preference', 'either'); ?>>Either</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit" value="register">Submit Registration</button>
        </div>

        <p class="form-note"><span class="required">*</span> Required fields</p>
    </form>

    <script>
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', {
            timeZone: 'America/Chicago',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    </script>
</body>

</html>
