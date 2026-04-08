     <?php
     $text = "PHP is great, but php is also easy to learn.";

     // Strict: Only replaces the uppercase version
     echo str_replace("PHP", "The Language", $text) . "<br>";
     // Result: The Language is great, but php is also easy to learn.

     // Flexible: Catches both versions
     echo str_ireplace("PHP", "The Language", $text) . "<br>";
     // Result: The Language is great, but The Language is also easy to learn.
     ?>