<?php

namespace Samir\Provider;

use Silex\Application;

class ServiceConfigServiceProvider extends ConfigurationServiceProvider
{
  public function register(Application $app)
  {
    foreach ($this->readConfig() as $key => $value) {
      if ( ! empty($value)) {
        array_walk_recursive($value, function (&$value, $key) use ($app) {
          $matches = array();
          
          $value = preg_replace_callback('/\%(\w+)\%/', function ($matches) use ($app) {
            return $app[$matches[1]];
          }, $value);
        });
      } else {
        $value = array();
      }
      
      $app->register(new $key(), $value);
    }
  }

  public function boot(Application $app)
  {
    
  }
}