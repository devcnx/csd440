<?php
/**
 * Module 1.3 Programming Assignment
 * CSD440 Server-Side Scripting
 * 
 * This program displays a greeting message and the sum of two numbers
 * using PHP. The program is written in HTML while the cell contents are
 * generated through PHP loops.
 * 
 * @author Brittaney Perry-Morgan
 * @date 2026-03-29
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>66    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brittaney's First PHP Program</title>
</head>
<body>
    <h1>Welcome to My First PHP Program</h1>

    <?php
    // PHP Snippet 1: Display a greeting message
    echo "<p>Hello, world! This is my first PHP program.</p>";

    // PHP Snippet 2: Calculate and display the sum of two numbers
    $num1 = 10;
    $num2 = 20;
    $sum = $num1 + $num2;
    echo "<p>The sum of $num1 and $num2 is: $sum</p>";
    ?>
</body>
</html>