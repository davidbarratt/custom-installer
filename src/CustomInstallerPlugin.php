<?php
/**
 * Custom Installer Plugin.
 *
 * @author David Barratt <david@davidwbarratt.com>
 * @copyright Copyright (c) 2014, David Barratt
 */
namespace DavidBarratt\CustomInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class CustomInstallerPlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new CustomInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
