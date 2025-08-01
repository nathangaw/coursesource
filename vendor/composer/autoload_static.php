<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfe6c86ce192d4869b9098b11e1a7c89e
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Coursesource\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Coursesource\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/Coursesource',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfe6c86ce192d4869b9098b11e1a7c89e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfe6c86ce192d4869b9098b11e1a7c89e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfe6c86ce192d4869b9098b11e1a7c89e::$classMap;

        }, null, ClassLoader::class);
    }
}
