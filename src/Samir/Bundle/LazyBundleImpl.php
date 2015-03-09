<?php

namespace Samir\Bundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class LazyBundleImpl implements BundleInterface
{
	protected $entity;
	protected $bundle;
	protected $reflected;
	protected $name;
	
	public function __construct($bundle, $entity)
	{
		$this->bundle = $bundle;
		$this->entity = $entity;		
	}

	public function boot() 
	{
		
	}
	
	public function shutdown() 
	{
	
	}
	
	public function build(ContainerBuilder $container) 
	{
	
	}
	
	public function getContainerExtension() 
	{
	
	}
	
	public function setContainer(ContainerInterface $container = null)
	{
		
	}
	
	public function getParent() 
	{
		return null;
	}
	
	public function getName() 
	{
		if (null !== $this->name) {
				return $this->name;
		}

		if (null === $this->reflected) {
			$this->reflected = new \ReflectionClass($this->bundle . '\\' . $this->entity);
		}
		
		$name = $this->reflected->getName();
		$pos = strrpos($name, '\\');

		return $this->name = false === $pos ? $name :  substr($name, $pos + 1);
	}
	
	public function getNamespace() 
	{
			if (null === $this->reflected) {
					$this->reflected = new \ReflectionClass($this->bundle . '\\' . $this->entity);
			}

			return str_replace('/', '\\', dirname(str_replace('\\', '/', $this->reflected->getNamespaceName())));
	}
	
	public function getPath() 
	{
		if (null === $this->reflected) {
				$this->reflected = new \ReflectionClass($this->bundle . '\\' . $this->entity);
		}

		return dirname(dirname($this->reflected->getFileName()));
	}
}