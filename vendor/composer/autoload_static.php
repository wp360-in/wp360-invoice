<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit270b0677e9aa93a025f239e7a4a6f73c
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'CzProject\\GitPhp\\CommandProcessor' => __DIR__ . '/..' . '/czproject/git-php/src/CommandProcessor.php',
        'CzProject\\GitPhp\\Commit' => __DIR__ . '/..' . '/czproject/git-php/src/Commit.php',
        'CzProject\\GitPhp\\CommitId' => __DIR__ . '/..' . '/czproject/git-php/src/CommitId.php',
        'CzProject\\GitPhp\\Exception' => __DIR__ . '/..' . '/czproject/git-php/src/exceptions.php',
        'CzProject\\GitPhp\\Git' => __DIR__ . '/..' . '/czproject/git-php/src/Git.php',
        'CzProject\\GitPhp\\GitException' => __DIR__ . '/..' . '/czproject/git-php/src/exceptions.php',
        'CzProject\\GitPhp\\GitRepository' => __DIR__ . '/..' . '/czproject/git-php/src/GitRepository.php',
        'CzProject\\GitPhp\\Helpers' => __DIR__ . '/..' . '/czproject/git-php/src/Helpers.php',
        'CzProject\\GitPhp\\IRunner' => __DIR__ . '/..' . '/czproject/git-php/src/IRunner.php',
        'CzProject\\GitPhp\\InvalidArgumentException' => __DIR__ . '/..' . '/czproject/git-php/src/exceptions.php',
        'CzProject\\GitPhp\\InvalidStateException' => __DIR__ . '/..' . '/czproject/git-php/src/exceptions.php',
        'CzProject\\GitPhp\\RunnerResult' => __DIR__ . '/..' . '/czproject/git-php/src/RunnerResult.php',
        'CzProject\\GitPhp\\Runners\\CliRunner' => __DIR__ . '/..' . '/czproject/git-php/src/Runners/CliRunner.php',
        'CzProject\\GitPhp\\Runners\\MemoryRunner' => __DIR__ . '/..' . '/czproject/git-php/src/Runners/MemoryRunner.php',
        'CzProject\\GitPhp\\Runners\\OldGitRunner' => __DIR__ . '/..' . '/czproject/git-php/src/Runners/OldGitRunner.php',
        'CzProject\\GitPhp\\StaticClassException' => __DIR__ . '/..' . '/czproject/git-php/src/exceptions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit270b0677e9aa93a025f239e7a4a6f73c::$classMap;

        }, null, ClassLoader::class);
    }
}
