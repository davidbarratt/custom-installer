<?php
/**
 * Custom Installer Test.
 *
 * @author David Barratt <david@davidwbarratt.com>
 * @copyright Copyright (c) 2014, David Barratt
 */
namespace DavidBarratt\CustomInstaller\Tests;

use DavidBarratt\CustomInstaller\CustomInstaller;
use Composer\Config;
use Composer\Composer;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use PHPUnit_Framework_TestCase;

class CustomInstallerTest extends PHPUnit_Framework_TestCase
{

    /**
     * testInstallPath
     *
     * @dataProvider dataForInstallPath
     */
    public function testInstallPath($name, $type, $path, $expected)
    {

        $composer = new Composer();
        $config = new Config();
        $composer->setConfig($config);

        $repository = $this->getMock('Composer\Repository\InstalledRepositoryInterface');
        $io = $this->getMock('Composer\IO\IOInterface');

        $installer = new CustomInstaller($io, $composer);
        $package = new Package($name, '1.0.0', '1.0.0');
        $package->setType($type);
        $consumerPackage = new RootPackage('foo/bar', '1.0.0', '1.0.0');
        $composer->setPackage($consumerPackage);
        $consumerPackage->setExtra(array(
          'custom-installer' => array(
            $type => $path,
          ),
        ));
        $result = $installer->getInstallPath($package);
        $this->assertEquals($expected, $result);
    }

    public function dataForInstallPath()
    {
        return array(
          array(
            'davidbarratt/davidwbarratt',
            'drupal-site',
            'sites/{$name}/',
            'sites/davidwbarratt/',
          ),
          array(
            'awesome/package',
            'custom-type',
            'custom/{$vendor}/{$name}/',
            'custom/awesome/package/',
          ),
          array(
            'drupal/core',
            'drupal-core',
            'web/',
            'web/',
          ),
        );
    }
}
