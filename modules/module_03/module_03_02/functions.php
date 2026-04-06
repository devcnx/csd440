<?php
/**
 * Module 3.2 Programming Assignment
 * CSD440 Server-Side Scripting
 * 
 * External functions file containing the calculateSum function
 * that returns the sum of two numbers.
 * 
 * @author Brittaney Perry-Morgan
 * @date 2026-04-05
 */

/**
 * Calculates the sum of two numbers.
 * 
 * Takes two random numbers as parameters and returns their sum.
 * This function is called from BrittaneyTable3.php to generate
 * the values displayed in each cell of the table.
 * 
 * @param int $num1 The first number
 * @param int $num2 The second number
 * @return int The sum of num1 and num2
 */
function calculateSum($num1, $num2) {
    return $num1 + $num2;
}
?>
