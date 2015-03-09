<?php

namespace Samir\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ActionReader
{
  const ROUTE = "Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Route";
  
  const METHOD = "Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Method";
  
  private $bundle;
  
  public function __construct($bundle) 
  {
    $this->bundle = $bundle;
  }
  
  public function extract() 
  {
    $output = array();  
    $reader = new AnnotationReader();
    
    $reflector = new \ReflectionClass($this->bundle);    
    $shortName = $reflector->getShortName();
    $namespace = $reflector->getNamespaceName();
    
    $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
    
    if (NULL !== $annotation = $reader->getClassAnnotation($reflector, self::ROUTE)) {
      foreach ($methods as $method) {
        if (NULL !== $routeAnnotation = $reader->getMethodAnnotation($method, self::ROUTE)) {
          $methodAnnotation = $reader->getMethodAnnotation($method, self::METHOD);
          
          $methods = isset($methodAnnotation) ?  $methodAnnotation->getMethods() : array('MATCH');
          
          $output[] = array(
            'method'    => strtolower($methods[0]),
            'pattern'   => $annotation->getPattern() . $routeAnnotation->getPattern(),
            'action'    => $method->name,
            'name'      => $routeAnnotation->getName(),
            'defaults'  => $routeAnnotation->getDefaults()
          );
        }
      }
    }
    
    return $output;
  }
}