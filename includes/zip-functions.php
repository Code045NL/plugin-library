<?php
// Function to create zip files for plugins
function create_plugin_zip($plugin_dir, $backup_dir, $plugin, $version) {
    $zip_file = $backup_dir . '/' . $plugin . '-' . $version . '.zip';
    
    if (file_exists($zip_file)) {
        return; // Skip if backup already exists
    }
    
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            error_log("Could not open zip file for writing: " . $zip_file);
            return;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            $file_path = $file->getRealPath();
            $relative_path = substr($file_path, strlen($plugin_dir) + 1);
            $zip->addFile($file_path, $relative_path);
        }
        
        $zip->close();
    } else {
        error_log("ZipArchive class not found. Make sure PHP Zip extension is installed.");
    }
}
?>