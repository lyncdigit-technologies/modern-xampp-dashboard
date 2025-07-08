<?php
if (isset($_GET['path'])) {
    $path = str_replace('/', '\\', $_GET['path']); // Convert to Windows backslashes
    $command = "start \"\" \"" . $path . "\"";
    pclose(popen("cmd /c $command", "r"));
}
