<?php
declare(strict_types=1);

/**
 * Module 10.2 — Shared Functions and Constants
 * CSD440 Server-Side Scripting
 *
 * Contains validation functions, data formatting helpers, and whitelist
 * constants used by both BrittaneyJSON.php and BrittaneyJSONResponse.php.
 * Included via require_once to avoid duplication.
 *
 * @author  Brittaney Perry-Morgan
 * @date    2026-05-16
 * @php     >= 8.0
 */

// ── Whitelist data for dropdowns ──────────────────────────────
const US_STATES = [
    'AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA',
    'HI','ID','IL','IN','IA','KS','KY','LA','ME','MD',
    'MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ',
    'NM','NY','NC','ND','OH','OK','OR','PA','RI','SC',
    'SD','TN','TX','UT','VT','VA','WA','WV','WI','WY',
];

const PROGRAMMING_LANGUAGES = [
    'C', 'C++', 'C#', 'Go', 'Java', 'JavaScript',
    'Kotlin', 'PHP', 'Python', 'Ruby', 'Rust', 'Swift', 'TypeScript',
];

/**
 * Validates and sanitizes text input.
 *
 * Trims whitespace and applies htmlspecialchars() for safe display.
 * Returns null if the input is empty after trimming.
 *
 * @param  string       $input The raw input value.
 * @return string|null         Sanitized string, or null if empty.
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
 * @param  string       $email The raw email input.
 * @return string|null         Sanitized email, or null if invalid.
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
 * @param  string    $input The raw input value.
 * @param  int       $min   Minimum acceptable value (inclusive).
 * @param  int       $max   Maximum acceptable value (inclusive).
 * @return int|null          Validated integer, or null if invalid.
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
 * Accepts common US phone formats. Returns a standardized (XXX) XXX-XXXX
 * format if exactly 10 digits are found, null otherwise.
 *
 * @param  string       $phone The raw phone input.
 * @return string|null         Formatted phone number, or null if invalid.
 */
function validatePhone(string $phone): ?string
{
    $trimmed = trim($phone);
    $digits = preg_replace('/\D/', '', $trimmed);
    if (strlen($digits) !== 10) {
        return null;
    }
    return sprintf('(%s) %s-%s',
        substr($digits, 0, 3),
        substr($digits, 3, 3),
        substr($digits, 6, 4)
    );
}

/**
 * Validates a select dropdown value against allowed options.
 *
 * Checks that the submitted value exists in the whitelist of acceptable
 * options. Returns the sanitized value if valid, null otherwise.
 *
 * @param  string       $input   The raw select value.
 * @param  array       $options Whitelist of acceptable values.
 * @return string|null           Validated option, or null if invalid.
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
 * Validates a textarea input with length limits.
 *
 * Trims whitespace and enforces a maximum character count. Returns the
 * sanitized text if valid, null if empty or too long.
 *
 * @param  string       $input  The raw textarea input.
 * @param  int          $maxLen Maximum allowed characters.
 * @return string|null          Sanitized text, or null if invalid.
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
 * Collects validation errors for all form fields.
 *
 * Returns an associative array where keys are field names and values
 * are error messages. An empty array indicates all fields passed validation.
 *
 * @param  array $data The raw POST data.
 * @return array       Array of error messages (empty if all valid).
 */
function validateFormData(array $data): array
{
    $errors = [];

    // Full name — required text, max 100 chars
    if (validateText($data['full_name'] ?? '') === null) {
        $errors['full_name'] = 'Full name is required.';
    } elseif (strlen(trim($data['full_name'])) > 100) {
        $errors['full_name'] = 'Full name must be 100 characters or fewer.';
    }

    // Email — required, valid format
    if (validateEmail($data['email'] ?? '') === null) {
        $errors['email'] = 'A valid email address is required.';
    }

    // Phone — required, 10 digits
    if (validatePhone($data['phone'] ?? '') === null) {
        $errors['phone'] = 'A valid 10-digit phone number is required.';
    }

    // Age — required integer, 1–120
    if (validateInteger($data['age'] ?? '', 1, 120) === null) {
        $errors['age'] = 'Age is required and must be between 1 and 120.';
    }

    // City — required text, max 100 chars
    if (validateText($data['city'] ?? '') === null) {
        $errors['city'] = 'City is required.';
    } elseif (strlen(trim($data['city'])) > 100) {
        $errors['city'] = 'City must be 100 characters or fewer.';
    }

    // State — required select from whitelist
    if (validateSelect($data['state'] ?? '', US_STATES) === null) {
        $errors['state'] = 'Please select a valid state.';
    }

    // Programming language — required select from whitelist
    if (validateSelect($data['programming_language'] ?? '', PROGRAMMING_LANGUAGES) === null) {
        $errors['programming_language'] = 'Please select a valid programming language.';
    }

    // Experience years — required integer, 0–80
    if (validateInteger($data['experience_years'] ?? '', 0, 80) === null) {
        $errors['experience_years'] = 'Years of experience is required (0–80).';
    }

    // Bio — required textarea, max 500 chars
    if (validateTextarea($data['bio'] ?? '', 500) === null) {
        $errors['bio'] = 'Bio is required (max 500 characters).';
    }

    return $errors;
}

/**
 * Builds a data array with proper types for JSON encoding.
 *
 * Numeric fields are cast to int so JSON output shows them as numbers,
 * not strings. Phone is formatted for display. All other fields are
 * trimmed raw strings (not HTML-escaped) to produce valid JSON.
 *
 * @param  array $postData The raw POST data.
 * @return array Data ready for json_encode().
 */
function buildJsonData(array $postData): array
{
    $phone = trim($postData['phone'] ?? '');
    $digits = preg_replace('/\D/', '', $phone);
    $formattedPhone = strlen($digits) === 10
        ? sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6, 4))
        : $phone;

    return [
        'full_name'            => trim($postData['full_name'] ?? ''),
        'email'                => trim($postData['email'] ?? ''),
        'phone'                => $formattedPhone,
        'age'                  => (int)trim($postData['age'] ?? '0'),
        'city'                 => trim($postData['city'] ?? ''),
        'state'                => trim($postData['state'] ?? ''),
        'programming_language' => trim($postData['programming_language'] ?? ''),
        'experience_years'     => (int)trim($postData['experience_years'] ?? '0'),
        'bio'                  => trim($postData['bio'] ?? ''),
    ];
}

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