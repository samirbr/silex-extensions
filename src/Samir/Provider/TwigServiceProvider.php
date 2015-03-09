<?php 

namespace Samir\Provider;

use Silex\Application;
use Silex\Provider\TwigServiceProvider as SilexTwigServiceProvider;

class TwigServiceProvider extends SilexTwigServiceProvider
{
  public function register(Application $app)
  {
    parent::register($app);
    
    $app['twig'] = $app->share($app->extend('twig', function ($twig) use ($app) {
      $twig->addExtension(new \Samir\Twig\Extensions\StrPadExtension($app));
      $twig->addExtension(new \Samir\Twig\Extensions\TextExtension($app));
      
      foreach ($app['twig.globals'] as $key => $value) {
        $twig->addGlobal($key, $value);
      }
      
      return $twig;
    }));
  }
}