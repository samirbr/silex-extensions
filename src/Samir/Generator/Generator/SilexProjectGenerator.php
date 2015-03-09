<?php

namespace Samir\Generator\Generator;

use Silex\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Generates a project.
 *
 * @author Samir El Aouar based on Sensio\Generator by Fabien Potencier <fabien@symfony.com>
 */
class SilexProjectGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;
    private $app;

    public function __construct(Application $app, $skeletonDir)
    {
        $this->app = $app;
        $this->filesystem = $this->getApp('filesystem');
        $this->skeletonDir = $skeletonDir;
    }
    
    public function getApp($key = "")
		{
			if (empty($key)) {
				return $this->app;
			} else {
				return $this->app[$key];
			}
		}

    public function generate($namespace, $bundle, $dir, $format, $structure)
    {
        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $basename = substr($bundle, 0, -6);
        $parameters = array(
            'namespace_dir'  => $namespace,
            'namespace'   => str_replace('/', '\\', $namespace),
            'bundle'      => $bundle,
            'format'      => $format,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
            'project_name'    => str_replace('/', ' ', $namespace),
            'vendor_name'     => strstr($bundle, '/', true),
            'class_name'    => 'Default',
            'routes'          => array(
              array(
                'name_prefix' => 'home',
                'action'      => 'indexAction',
                'pattern'     => '/',
                'method'      => 'GET',
              )
            )
        );
        
        $this->filesystem->mkdir('web');
        
        $this->renderFile($this->skeletonDir, '.htaccess', '.htaccess', $parameters);
        $this->renderFile($this->skeletonDir, 'index.php', 'web/index.php', $parameters);
        $this->renderFile($this->skeletonDir, 'config/routing.yml', $dir.'/Resources/config/routing.yml', $parameters);
        $this->renderFile($this->skeletonDir, 'controller.php', $dir.'/Controller/DefaultController.php', $parameters);
        $this->renderFile($this->skeletonDir, 'default.html.twig', $dir.'/Resources/views/default.html.twig', $parameters);

        if ($structure) {
            $this->filesystem->mkdir($dir.'/Resources/doc');
            $this->filesystem->touch($dir.'/Resources/doc/index.rst');
            $this->filesystem->mkdir($dir.'/Resources/translations');
            $this->filesystem->copy($this->skeletonDir.'/messages.xlf', $dir.'/Resources/translations/messages.xlf');
            $this->filesystem->mirror($this->skeletonDir.'/assets', 'web/assets', null, true);
        }
    }
}
