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
     * Tests installation path for given package/spec combination.
     *
     * @param string $name      Full package name (including vendor)
     * @param string $type      Composer package type
     * @param array  $spec      Custom-Installer configuration
     * @param string $expected  Expected path
     *
     * @dataProvider dataForInstallPath
     */
    public function testInstallPath($name, $type, $spec, $expected)
    {

        $composer = new Composer();
        $config = new Config(false, realpath('.'));
        $composer->setConfig($config);

        $repository = $this->getMock('Composer\Repository\InstalledRepositoryInterface');
        $io = $this->getMock('Composer\IO\IOInterface');

        $installer = new CustomInstaller($io, $composer);
        $package = new Package($name, '1.0.0', '1.0.0');
        $package->setType($type);
        $consumerPackage = new RootPackage('foo/bar', '1.0.0', '1.0.0');
        $composer->setPackage($consumerPackage);
        $consumerPackage->setExtra(array(
          'custom-installer' => $spec,
        ));
        $result = $installer->getInstallPath($package);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testing multiple install paths.
     *
     * @return array
     */
    public function dataForInstallPath()
    {
        return array(
          array(
            'davidbarratt/davidwbarratt',
            'drupal-site',
            array('drupal-site' => 'sites/{$name}/'),
            'sites/davidwbarratt/',
          ),
          array(
            'awesome/package',
            'custom-type',
            array('custom-type' => 'custom/{$vendor}/{$name}/'),
            'custom/awesome/package/',
          ),
          array(
            'drupal/core',
            'drupal-core',
            array('drupal-core' => 'web/'),
            'web/',
          ),
          // Without spec.
          array(
            'package/without-spec',
            'library',
            array(),
            realpath('.') . '/vendor/package/without-spec',
          ),
          // New format
          array(
            'config/with-new-format',
            'custom-type',
            array(
              'custom/{$type}/{$vendor}/{$name}/' => array('type:custom-type'),
            ),
            'custom/custom-type/config/with-new-format/',
          ),
          // Per package
          array(
            'config/per-package',
            'custom-type',
            array(
              'custom/{$vendor}/{$name}/' => array('config/per-package'),
            ),
            'custom/config/per-package/',
          ),
          // Package over type
          array(
            'config/package-over-type',
            'custom-type',
            array(
              'type-folder/{$vendor}/{$name}/' => array('type:custom-type'),
              'package-folder/{$vendor}/{$name}/' => array('config/package-over-type'),
            ),
            'package-folder/config/package-over-type/',
          ),
        );
    }
}
