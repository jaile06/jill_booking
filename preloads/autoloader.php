<?php

/**
 * @see http://www.php-fig.org/psr/psr-4/examples/
 */
spl_autoload_register(static function ($class) {
    $prefix = 'XoopsModules\\' . ucfirst(basename(dirname(__DIR__)));

    $baseDir =  dirname(__DIR__) . '/class/';

    $len = mb_strlen($prefix);

    if (0 !== strncmp($prefix, $class, $len)) {
        return;
    }

    $relativeClass = mb_substr($class, $len);

    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
