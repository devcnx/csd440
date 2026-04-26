<?php
/**
 * XSS — Sanitized Output Fix
 * Module 7.1 Discussion Board — CSD440
 * 
 * htmlspecialchars() converts <, >, ", and ' to HTML entities.
 * The browser renders them as text instead of executing as HTML/JS.
 */

$safeQuery = htmlspecialchars($_GET["query"] ?? "", ENT_QUOTES, "UTF-8");
echo "<h1>Search results for: " . $safeQuery . "</h1>";

// The <script> tags become harmless text. The browser displays them,
// but doesn't execute them.
?>
