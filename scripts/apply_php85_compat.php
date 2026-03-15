<?php

$target = __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php';

if (!file_exists($target)) {
    exit(0);
}

$content = file_get_contents($target);

if ($content === false) {
    fwrite(STDERR, "Failed to read compatibility target file.\n");
    exit(1);
}

if (strpos($content, 'error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);') === false) {
    $content = str_replace(
        'error_reporting(-1);',
        "// Temporary compatibility for legacy Laravel on newer PHP (8.5+).\n        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);",
        $content
    );
}

if (strpos($content, 'if (in_array($level, [E_DEPRECATED, E_USER_DEPRECATED], true))') === false) {
    $content = str_replace(
        "    public function handleError(\$level, \$message, \$file = '', \$line = 0, \$context = [])\n    {\n",
        "    public function handleError(\$level, \$message, \$file = '', \$line = 0, \$context = [])\n    {\n        if (in_array(\$level, [E_DEPRECATED, E_USER_DEPRECATED], true)) {\n            return;\n        }\n\n",
        $content
    );
}

if (file_put_contents($target, $content) === false) {
    fwrite(STDERR, "Failed to write compatibility target file.\n");
    exit(1);
}

echo "Applied PHP 8.5 compatibility patch.\n";
