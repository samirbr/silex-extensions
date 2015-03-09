<?php

namespace Samir\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class KitPagesGridServiceProvider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['kitpages.gm'] = $app->share(function ($app) {
      return new \Kitpages\DataGridBundle\Service\GridManager($app['dispatcher']);
    });
  }

  public function boot(Application $app)
  {
  }
}