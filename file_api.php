<?php
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$project = $_GET['project'] ?? ($_POST['project'] ?? '');
$base = realpath("C:/xampp/htdocs/$project");

if (!$project || !is_dir($base)) {
    http_response_code(400);
    die("Invalid project.");
}

// === LIST FILES ===
if ($action === 'list') {
    $files = [];
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($rii as $file) {
        if ($file->isFile()) {
            $rel = str_replace($base . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $files[] = $rel;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($files);
    exit;
}

// === READ FILE ===
if ($action === 'read') {
    $file = $_GET['file'];
    $path = realpath($base . DIRECTORY_SEPARATOR . $file);
    if (strpos($path, $base) === 0 && is_file($path)) {
        echo file_get_contents($path);
    } else {
        http_response_code(403);
        echo "Access denied.";
    }
    exit;
}

// === POST actions ===
$data = json_decode(file_get_contents("php://input"), true);

// === SAVE FILE ===
if ($data['action'] === 'save') {
    $path = $base . DIRECTORY_SEPARATOR . $data['file'];
    if (strpos(realpath(dirname($path)), $base) === 0) {
        file_put_contents($path, $data['content']);
        echo "✅ File saved.";
    } else echo "❌ Invalid path.";
    exit;
}

// === DELETE FILE ===
if ($action === 'delete') {
    $file = $_GET['file'];
    $path = realpath($base . DIRECTORY_SEPARATOR . $file);
    if (strpos($path, $base) === 0) {
        is_dir($path) ? rmdir($path) : unlink($path);
        echo "🗑️ Deleted.";
    } else echo "❌ Invalid path.";
    exit;
}

// === RENAME ===
if ($data['action'] === 'rename') {
    $old = $base . DIRECTORY_SEPARATOR . $data['old'];
    $new = $base . DIRECTORY_SEPARATOR . $data['new'];
    if (file_exists($old)) {
        rename($old, $new);
        echo "✅ Renamed.";
    } else echo "❌ Not found.";
    exit;
}

// === CREATE FILE ===
if ($data['action'] === 'create_file') {
    $path = $base . DIRECTORY_SEPARATOR . $data['file'];
    file_put_contents($path, "<?php\n");
    echo "✅ File created.";
    exit;
}

// === CREATE FOLDER ===
if ($data['action'] === 'create_folder') {
    $path = $base . DIRECTORY_SEPARATOR . $data['folder'];
    mkdir($path, 0777, true);
    echo "✅ Folder created.";
    exit;
}
