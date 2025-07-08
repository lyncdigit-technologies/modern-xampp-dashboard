<?php
$htdocsDir = realpath("C:/xampp/htdocs");
if (isset($_GET['folder'])) {
    $folder = basename($_GET['folder']);
    $path = $htdocsDir . DIRECTORY_SEPARATOR . $folder;

    if (is_dir($path)) {
        $zip = new ZipArchive();
        $zipName = $folder . '.zip';

        if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($path) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();

            header('Content-Type: application/zip');
            header("Content-Disposition: attachment; filename=\"$zipName\"");
            readfile($zipName);
            unlink($zipName);
            exit;
        }
    }
}
?>
