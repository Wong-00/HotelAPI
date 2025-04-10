<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit860e97bf9a6dc73811520727f1ec37d1
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/api',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit860e97bf9a6dc73811520727f1ec37d1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit860e97bf9a6dc73811520727f1ec37d1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit860e97bf9a6dc73811520727f1ec37d1::$classMap;

        }, null, ClassLoader::class);
    }
}
