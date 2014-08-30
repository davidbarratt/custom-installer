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
   * {@inheritDoc}
   */
  public function getInstallPath(PackageInterface $package)
  {
      $type = $package->getType();
      $extra = $this->composer->getPackage()->getExtra();

      $vars = array(
        'type' => $type,
      );

      $prettyName = $this->package->getPrettyName();

      if (strpos($prettyName, '/') !== FALSE) {

        $pieces = explode('/', $prettyName);;

        $vars['vendor'] = $pieces[0];
        $vars['name'] = $pieces[1];

      } else {

        $vars['vendor'] = '';
        $vars['name'] = $prettyName;

      }

      return $this->templatePath($extra['custom-installer'][$type], $vars);
  }

  /**
   * {@inheritDoc}
   */
  public function supports($packageType)
  {
      if ($this->composer->getPackage()) {

        $extra = $this->composer->getPackage()->getExtra();

        if (!empty($extra['custom-installer'])) {

          if (!empty($extra['custom-installer'][$packageType])) {
            return TRUE;
          }

        }

      }

      return FALSE;
  }

  /**
   * Replace vars in a path
   *
   * @see Composer\Installers\BaseInstaller::templatePath()
   *
   * @param  string $path
   * @param  array  $vars
   * @return string
   */
  protected function templatePath($path, array $vars = array())
  {
      if (strpos($path, '{') !== false) {
          extract($vars);
          preg_match_all('@\{\$([A-Za-z0-9_]*)\}@i', $path, $matches);
          if (!empty($matches[1])) {
              foreach ($matches[1] as $var) {
                  $path = str_replace('{$' . $var . '}', $$var, $path);
              }
          }
      }

      return $path;
  }

}
