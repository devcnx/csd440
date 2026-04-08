<?php
class User
{
    private string $username;

    public function __construct(string $name)
    {
        $this->username = $name;
    }

    public function getGreeting(): string
    {
        return "System Identified: {$this->username}";
    }

    public function getMemoryAddress(): string
    {
        return spl_object_id($this);
    }
}

echo "=== Creating 3 User instances ===<br>";

$users = [
    'userA' => new User("Brittaney"),
    'userB' => new User("Jordan"),
    'userC' => new User("Alex"),
];

foreach ($users as $label => $user) {
    echo "{$label} greeting: " . $user->getGreeting() . "<br>";
}

echo "<br>=== Memory addresses (proves they're separate objects) ===<br>";
foreach ($users as $label => $user) {
    echo "{$label} ID: " . $user->getMemoryAddress() . "<br>";
}

echo "<br>✓ Without \$this, each object would overwrite the other's data.<br>";
