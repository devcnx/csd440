     <?php
     $status = "Active";

     // This fails: PHP searches for a variable called $statusly
     echo "The system is $statusly<br>";

     // Brackets isolate the variable, allowing the suffix to be treated as a string
     echo "The system is {$status}ly<br>";
     // Result: The system is Actively
     ?>
