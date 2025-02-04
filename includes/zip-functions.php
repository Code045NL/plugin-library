<?php
// filepath: /workspaces/plugin-library/includes/zip-functions.php

function create_plugin_zip($plugin_dir, $backup_dir, $plugin_slug) {
    $zip = new ZipArchive();
    $zip_file = $backup_dir . '/' . $plugin_slug . '.zip';

    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $root_path = realpath($plugin_dir);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root_path), RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_path = $plugin_slug . '/' . substr($file_path, strlen($root_path) + 1);
                $zip->addFile($file_path, $relative_path);
            }
        }

        $zip->close();
    } else {
        throw new Exception('Failed to create zip file.');
    }
}
?>