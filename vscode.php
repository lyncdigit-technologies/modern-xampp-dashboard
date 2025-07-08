<?php
if (isset($_GET['path'])) {
    $path = escapeshellarg($_GET['path']);
    pclose(popen("code $path", "r")); // Requires VSCode added to system PATH
}
