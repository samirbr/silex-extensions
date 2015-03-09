<?php

namespace Samir\Provider;

use Silex\Application;

class RoutingConfigServiceProvider extends ConfigurationServiceProvider
{
  public function register(Application $app)
  {
    foreach ($this->readConfig() as $controllerClass => $routes) {
      $controllerPrefix = $this->getControllerPrefix($controllerClass);
      
      $app[$controllerPrefix] = $app->share(function () use ($app, $controllerClass) {
        return new $controllerClass();
      });
      
      foreach ($routes as $routeNamePrefix => $settings) {
        $settings = (object)$settings;
        $method = strtolower($settings->method);
        
        $route = $app->$method($settings->pattern, $controllerPrefix . ':' . $settings->action)
          ->bind($routeNamePrefix);
          
        if (isset($settings->defaults)) {
          foreach ($settings->defaults as $key => $value) {
            $route->value($key, $value);
          }
        }
      }
    }
  }

  public function boot(Application $app)
  {    
  }
  
  private function getControllerPrefix($controllerClass)
  {
     $namespace = explode('\\', $controllerClass);
     return strtolower(strstr(end($namespace), "Controller", true)) .  '.controller';
  }
}