<?php
/**
 * Module 4.2 Programming Assignment
 * CSD440 Server-Side Scripting
 * 
 * A palindrome is a word, phrase, number, or sequence of characters that
 * reads identically forward and backward. The comparison is case-insensitive
 * and ignores spaces, so 'Racecar', 'racecar', and 'race car' are all valid
 * palindromes. Punctuation and special characters are stripped during testing.
 * 
 * Requirements for a valid palindrome:
 *   - The sequence reads the same character-by-character in both directions
 *   - Uppercase and lowercase letters are treated as equivalent
 *   - Spaces are ignored and do not affect the comparison
 *   - The comparison stops at the midpoint; characters beyond it mirror
 *     those before it
 * 
 * This program tests six strings (three palindromes, three non-palindromes)
 * and displays each string alongside its reversed form and the test result.
 * 
 * @author Brittaney Perry-Morgan
 * @date 2026-04-12
 */

/**
 * Determines whether a given string is a palindrome.
 * 
 * A palindrome reads the same forwards and backwards. This function
 * ignores case sensitivity and spaces by stripping them before the
 * comparison. A two-pointer technique compares characters from both
 * ends of the string, moving inward until they meet in the middle.
 * 
 * @param string $str The string to test
 * @return bool True if the string is a palindrome, false otherwise
 */
function isPalindrome(string $str): bool
{
    // Strip spaces, punctuation, and special characters, then lowercase the
    // result so the comparison is case- and punctuation-insensitive.
    $cleaned = strtolower(preg_replace('/[^a-z0-9]/i', '', $str));
    $length = strlen($cleaned);

    // Use two indices to compare characters from both ends
    for ($i = 0, $j = $length - 1; $i < $j; $i++, $j--) {
        if ($cleaned[$i] !== $cleaned[$j]) {
            return false;
        }
    }
    return true;
}

/**
 * Returns the reverse of a given string.
 * 
 * Iterates through the string from the last character to the first,
 * building a new string one character at a time using concatenation.
 * 
 * @param string $str The string to reverse
 * @return string The reversed string
 */
function reverseString(string $str): string
{
    $reversed = '';
    // Start from the last character and build the reversed string
    for ($i = strlen($str) - 1; $i >= 0; $i--) {
        $reversed .= $str[$i];
    }
    return $reversed;
}

// Define test strings — three palindromes, three non-palindromes
$testStrings = [
    'racecar',                         // palindrome
    'A man a plan a canal Panama',     // palindrome (ignores spaces/casing)
    'Was it a car or a cat I saw',     // palindrome
    'hello world',                     // not a palindrome
    'PHP is fun',                      // not a palindrome
    'OpenClaw AI'                      // not a palindrome
];

// Configuration
$studentName = 'Brittaney Perry-Morgan';
$assignmentTitle = 'Module 4.2 Programming Assignment';
$courseName = 'CSD440 Server-Side Scripting';
$date = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Palindrome Checker — <?php echo $assignmentTitle; ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 900px;
                margin: 40px auto;
                padding: 20px;
                background-color: #f5f5f5;
            }

            h1 {
                text-align: center;
                color: #2c3e50;
            }

            .header-info {
                text-align: center;
                margin-bottom: 30px;
                color: #555;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                background-color: #fff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            th {
                background-color: #2c3e50;
                color: #fff;
                padding: 12px;
                text-align: left;
            }

            td {
                padding: 12px;
                border-bottom: 1px solid #ddd;
            }

            tr:hover {
                background-color: #f9f9f9;
            }

            .result-true {
                color: #27ae60;
                font-weight: bold;
            }

            .result-false {
                color: #c0392b;
                font-weight: bold;
            }
        </style>
    </head>

    <body>
        <h1>Palindrome Checker</h1>
        <div class="header-info">
            <p><strong><?php echo $studentName; ?></strong></p>
            <p><?php echo $assignmentTitle; ?> — <?php echo $courseName; ?></p>
            <p><?php echo $date; ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Test String</th>
                    <th>Reversed String</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testStrings as $str): ?>
                    <?php
                    // Reverse the string using the reverseString function
                    $reversed = reverseString($str);
                    // Check if the string is a palindrome
                    $result = isPalindrome($str);
                    // Set display values based on the test result
                    $displayResult = $result ? 'PALINDROME' : 'NOT A PALINDROME';
                    $class = $result ? 'result-true' : 'result-false';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($str); ?></td>
                        <td><?php echo htmlspecialchars($reversed); ?></td>
                        <td class="<?php echo $class; ?>">
                            <?php echo $displayResult; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>

</html>