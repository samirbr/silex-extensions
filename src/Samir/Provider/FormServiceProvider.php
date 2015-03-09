<?php

namespace Samir\Provider;

use Silex\Provider\FormServiceProvider as SilexFormServiceProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Silex\Application;

class FormServiceProvider extends SilexFormServiceProvider
{
  public function register(Application $app)
  {
    parent::register($app);
     
    $app['form.factory'] = $app->share($app->extend('form.factory', function (FormFactoryInterface $factory) use ($app) {
      
      $app['twig.form.templates'] = array_merge($app['twig.form.templates'], $app['form.templates']);
    
      foreach ($app['form.types'] as $form) {   
        $factory->addType(new $form);
        
        $reflected = new \ReflectionClass($form);
        $path = dirname($reflected->getFileName()) . '/../Resources/views/Form';
        $app['twig.loader']->addLoader(new \Twig_Loader_Filesystem($path));
      }

      return $factory;
    }));
    
    $app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($app) {
      $managerRegistry = new \Samir\Persistance\ManagerRegistry(null, array(), array('db.orm.em'), null, null, $app['db.orm.proxies_namespace']);
      $managerRegistry->setContainer($app);
      $extensions[] = new \Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($managerRegistry);
    
      return $extensions;
    }));
  }
}

