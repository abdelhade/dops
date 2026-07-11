<?php

$viewsPath = __DIR__ . '/resources/views';

$directoryIterator = new RecursiveDirectoryIterator($viewsPath);
$iterator = new RecursiveIteratorIterator($directoryIterator);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;

        // Determine resource name from path
        $pathParts = explode(DIRECTORY_SEPARATOR, $file->getPathname());
        $folder = $pathParts[count($pathParts) - 2];
        
        $resource = str_replace('_', '-', $folder);
        
        // If it's a generic partial, skip for now, we will handle them manually or specially
        if ($folder === 'partials' || $folder === 'layouts' || $folder === 'reports') {
            continue;
        }

        $content = str_replace('canCreateRecords()', "hasPermission('$resource', 'create')", $content);
        $content = str_replace('canEditRecords()', "hasPermission('$resource', 'update')", $content);
        $content = str_replace('canDeleteRecords()', "hasPermission('$resource', 'delete')", $content);

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated {$file->getPathname()}\n";
        }
    }
}
