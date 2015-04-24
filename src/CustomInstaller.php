<?php
/**
 * Custom Installer.
 *
 * @author David Barratt <david@davidwbarratt.com>
 * @copyright Copyright (c) 2014, David Barratt
 */
namespace DavidBarratt\CustomInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class CustomInstaller extends LibraryInstaller
{

    /**
     * @var \DavidBarratt\CustomInstaller\Configuration
     */
    protected $configuration;

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $configuration = $this->getPluginConfiguration();
        $pattern = $configuration->getPattern($package);
        if ($pattern) {
            return $this->buildPath($pattern,
              $this->getPackageReplacementTokens($package));
        } // In the case no pattern is given, we use the default behaviour.
        else {
            return parent::getInstallPath($package);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($packageType)
    {
        // The installer supports any package type, but we skip metapackages.
        return $packageType != 'metapackage';
    }

    /**
     * Retrieve replacement tokens for the given package.
     *
     * @param \Composer\Package\PackageInterface $package
     *
     * @return array
     */
    protected function getPackageReplacementTokens(PackageInterface $package)
    {
        $vars = array(
          '{$type}' => $package->getType(),
        );

        $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            $pieces = explode('/', $prettyName);;
            $vars['{$vendor}'] = $pieces[0];
            $vars['{$name}'] = $pieces[1];

        } else {
            $vars['{$vendor}'] = '';
            $vars['{$name}'] = $prettyName;
        }

        return $vars;
    }

    /**
     * Replace path pattern with replacement tokens.
     *
     * @param  string $pattern
     * @param  array $tokens
     * @return string
     */
    protected function buildPath($pattern, array $tokens = array())
    {
        return strtr($pattern, $tokens);
    }

    /**
     * Retrieve plugin configuration object.
     *
     * @return \DavidBarratt\CustomInstaller\Configuration
     */
    protected function getPluginConfiguration()
    {
        if (!isset($this->configuration)) {
            $extra = $this->composer->getPackage()->getExtra();
            $this->configuration = new Configuration($extra);
        }

        return $this->configuration;
    }

}
