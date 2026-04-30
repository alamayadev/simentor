<?php
$dir = __DIR__ . '/../tests';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$modified = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        $new = preg_replace_callback('#^([ \t]*)/\*\*\s*@test\s*\*/\s*$#m', function($m){
            return $m[1] . '#[\\PHPUnit\\Framework\\Attributes\\Test]';
        }, $content, -1, $count);
        if ($count > 0 && $new !== $content) {
            file_put_contents($path, $new);
            $modified[] = $path;
        }
    }
}
if (count($modified) > 0) {
    echo "Modified files:\n";
    foreach ($modified as $m) echo "$m\n";
} else {
    echo "No files modified.\n";
}
