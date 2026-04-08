<?php
class LogHandler
{
    private $fileHandle;
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->fileHandle = fopen($filename, 'a');
        echo "Log Stream Opened for: $filename<br>";
    }

    public function write(string $message): void
    {
        $timestamp = date("Y-m-d H:i:s");
        fwrite($this->fileHandle, "[$timestamp] $message\n");
        echo "Wrote: $message<br>";
    }

    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
            echo "__destruct() fired: Log Stream Closed Safely.<br>";
        }
    }
}

echo "<div style='font-family: monospace; background: #f4f4f4; padding: 10px; border: 1px solid #ccc; line-height: 1.5;'>";
echo "<strong>=== Creating LogHandler instance ===</strong><br>";
$log = new LogHandler("system.log");

echo "<br><strong>=== Writing log entries ===</strong><br>";
$log->write("Application Started");
$log->write("User Session Created");
$log->write("Request Processed");

echo "<br><strong>=== Unsetting the Object ===</strong><br>";
unset($log);
echo "Object Destroyed — Destructor Just Ran Above!<br>";
echo "</div>";
