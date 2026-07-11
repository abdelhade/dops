<?php

$viewsPath = __DIR__ . '/resources/views';

$directoryIterator = new RecursiveDirectoryIterator($viewsPath);
$iterator = new RecursiveIteratorIterator($directoryIterator);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;

        $pathParts = explode(DIRECTORY_SEPARATOR, $file->getPathname());
        $folder = $pathParts[count($pathParts) - 2];
        
        $resource = str_replace('_', '-', $folder);
        
        if ($folder === 'partials' || $folder === 'layouts' || $folder === 'reports') {
            continue;
        }

        // Add 'resource' => 'xxx', to @include('partials.crud-actions', [
        if (strpos($content, "@include('partials.crud-actions', [") !== false) {
            if (strpos($content, "'resource' =>") === false) {
                $content = str_replace("@include('partials.crud-actions', [", "@include('partials.crud-actions', [\n                'resource' => '$resource',", $content);
            }
        }

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated {$file->getPathname()} with crud-actions resource\n";
        }
    }
}
