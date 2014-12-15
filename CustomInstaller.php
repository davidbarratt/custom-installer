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

      $prettyName = $package->getPrettyName();

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
            return true;
          }

        }

      }

      return false;
  }

  /**
   * {@inheritDoc}
   */
  public function install(InstalledRepositoryInterface $repo, PackageInterface $package) {

    $backups = $this->backupSubpaths($repo, $package);

    try {
      parent::install($repo, $package);
    }
    catch (\Exception $e) {
      // In the case of an exception we should restore backups first,
      // before the script fails.
      $this->restoreSubpaths($repo, $package, $backups);
      throw $e;
    }

    $this->restoreSubpaths($repo, $package, $backups);
  }

  /**
   * {@inheritDoc}
   */
  public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target) {
    $backups = $this->backupSubpaths($repo, $target);

    try {
      parent::update($repo, $initial, $target);
    }
    catch (\Exception $e) {
      // In the case of an exception we should restore backups first,
      // before the script fails.
      $this->restoreSubpaths($repo, $target, $backups);
      throw $e;
    }

    $this->restoreSubpaths($repo, $target, $backups);
  }

  /**
   * {@inheritDoc}
   */
  public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package) {

    $backups = $this->backupSubpaths($repo, $package);

    try {
      parent::uninstall($repo, $package);
    }
    catch (\Exception $e) {
      // In the case of an exception we should restore backups first,
      // before the script fails.
      $this->restoreSubpaths($repo, $package, $backups);
      throw $e;
    }

    $this->restoreSubpaths($repo, $package, $backups);
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

  /**
   * Backs up the configured subpaths.
   *
   * @param InstalledRepositoryInterface $repo
   * @param PackageInterface $package
   *
   * @return array
   *   Array of backed up path information:
   *   - key: original path
   *   - value: backup location
   */
  protected function backupSubpaths(InstalledRepositoryInterface $repo, PackageInterface $package) {
    $extra = $this->composer->getPackage()->getExtra();

    // if we got no subdir configuration, we do not have anythign to backup.
    if (empty($extra['custom-installer-preserve-subpaths'])) {
      return array();
    }

    $subpaths = $extra['custom-installer-preserve-subpaths'];
    $installPath = $this->getInstallPath($package);
    $installPathNormalized = $this->filesystem->normalizePath($installPath);

    // Check if any subpath maybe affected by installation of this package.
    $backup_paths = array();
    foreach ($subpaths as $path) {
      $normalizedPath = $this->filesystem->normalizePath($path);
      if (file_exists($path) && strpos($normalizedPath, $installPathNormalized) === 0) {
        $backup_paths[] = $normalizedPath;
      }
    }

    // If no paths need to be backed up, we simply proceed.
    if (empty($backup_paths)) {
      return array();
    }

    // Otherwise we back those up to a cache subdirectory.
    $cache_dir = $this->composer->getConfig()->get('cache-dir');
    $unique = $package->getUniqueName() . ' ' . time();
    $cache_root = $this->filesystem->normalizePath($cache_dir . '/custom-installer-preserve-subpaths/' . sha1($unique));
    $this->filesystem->ensureDirectoryExists($cache_root);

    $return = array();
    foreach ($backup_paths as $original) {
      $backup_location = $cache_root . '/' . sha1($original);
      $this->filesystem->rename($original, $backup_location);
      $return[$original] = $backup_location;
    }
    return $return;
  }

  /**
   * Restore previously backed up subpaths.
   *
   * @param InstalledRepositoryInterface $repo
   * @param PackageInterface $package
   * @param array $backups
   *   Array of backed up path information:
   *   - key: original path
   *   - value: backup location
   *
   * @see Installer::backupSubpaths()
   */
  protected function restoreSubpaths(InstalledRepositoryInterface $repo, PackageInterface $package, $backups) {
    if (empty($backups)) {
      return;
    }

    foreach ($backups as $original => $backup_location) {

      // Remove any code that was placed by the package at the place of
      // the original path.
      if (file_exists($original)) {
        if (is_dir($original)) {
          $this->filesystem->emptyDirectory($original, false);
          $this->filesystem->removeDirectory($original);
        }
        else {
          $this->filesystem->remove($original);
        }

        $this->io->write(sprintf('<comment>Content of package %s was overwritten with preserved path %s!</comment>', $package->getUniqueName(), $original), true);
      }

      $this->filesystem->ensureDirectoryExists(dirname($original));
      $this->filesystem->rename($backup_location, $original);

      if ($this->filesystem->isDirEmpty(dirname($backup_location))) {
        $this->filesystem->removeDirectory(dirname($backup_location));
      }
    }
  }

}
