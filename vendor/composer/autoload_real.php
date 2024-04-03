<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit270b0677e9aa93a025f239e7a4a6f73c
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit270b0677e9aa93a025f239e7a4a6f73c', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit270b0677e9aa93a025f239e7a4a6f73c', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit270b0677e9aa93a025f239e7a4a6f73c::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}