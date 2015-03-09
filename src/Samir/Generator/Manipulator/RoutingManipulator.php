<?php

namespace Samir\Generator\Manipulator;

use Symfony\Component\Yaml\Yaml;

/**
 * Changes the PHP code of a YAML routing file.
 *
 * @author Samir El Aouar <samirbr@gmail.com> based on Sensio\Generator by Fabien Potencier <fabien@symfony.com>
 */
class RoutingManipulator
{
    private $file;

    /**
     * Constructor.
     *
     * @param string $file The YAML routing file path
     */
    public function __construct($file)
    {
        $this->file = $file;
    }
    
    public function addOptions($service, $options = array())
    {
      if (file_exists($this->file)) {
        $config = Yaml::parse($this->file);
        
        if (array_key_exists($service, $config)) {
          if ( ! array_key_exists($name, $config[$service])) {
            $config[$service] = $options;
          }
        }
      }

      if (false === file_put_contents($this->file, Yaml::dump($config))) {
          return false;
      }

      return true;
    }
    
    public function addResource($bundle, $name, $method = 'GET', $action, $pattern, $defaults = array())
    {
      if (file_exists($this->file)) {
        $config = Yaml::parse($this->file);
        
        if (array_key_exists($bundle, $config)) {
          $config[$bundle][$name] = array(
            'pattern' => $pattern,
            'method'  => $method,
            'action'  => $action,
           );
        } else {
          $config[$bundle] = array(
            $name => array(
              'pattern' => $pattern,
              'method'  => $method,
              'action'  => $action,
          ));
        }
        
        if ( ! empty($defaults)) {
          $config[$bundle][$name]['defaults'] = $defaults;
        }
      }
      
      if (false === file_put_contents($this->file, Yaml::dump($config))) {
          return false;
      }

      return true;
    }
}
