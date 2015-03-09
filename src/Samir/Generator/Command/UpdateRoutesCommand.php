<?php

namespace Samir\Generator\Command;

use Silex\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Samir\Generator\Manipulator\RoutingManipulator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Samir\Bundle\LazyBundleImpl;
use Samir\Reader\ActionReader;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Samir El Aouar <samirbr@gmail.com> based on Sensio\Generator by Fabien Potencier <fabien@symfony.com>
 */
class UpdateRoutesCommand extends ConsoleCommand
{
    private $app;
		
		public function __construct(Application $app, $skeletonPath = null) 
		{
			$this->app = $app;
		
			parent::__construct();
		}
		
		public function getApp($key = "")
		{
			if (empty($key)) {
				return $this->app;
			} else {
				return $this->app[$key];
			}
		}
		
		/**
     * @see Command
     */
    protected function configure()
    {
        $this
          ->setDefinition(array(
            new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'
          )))
          ->setDescription('Updates routes')
          ->setHelp(<<<EOT
The <info>routing:update</info> command update the routes.
EOT
          )
          ->setName('routing:update')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $dialog = $this->getDialogHelper();
      
      if ($input->isInteractive()) {
          if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm update', 'yes', '?'), true)) {
              $output->writeln('<error>Command aborted</error>');
              return 1;
          }
      }
      
      $entity = Validators::validateEntityName($input->getOption('entity'));
      list($bundle, $entity) = $this->parseShortcutNotation($entity);
      $bundle = $this->getBundle($bundle, $entity);
      
      $output->write('Importing the CRUD routes: ');
      $routing = new RoutingManipulator($bundle->getPath().'/Resources/config/routing.yml');        
      $parts = explode('\\', $entity);
      $entityClass = array_pop($parts);
      $controllerClass = $bundle->getNamespace() . '\\Controller\\' . $entityClass . 'Controller';
      $actions = $this->getActions($controllerClass);
      
      try {
        foreach ($actions as $action) {
          $ret = $auto ? $routing->addResource($controllerClass, $action['name'], $action['method'], $action['action'], $action['pattern'], $action['defaults']) : false;
        }
      } catch (\RuntimeException $exc) {
          $ret = false;
      }
    }
    
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        
        $entity = $dialog->askAndValidate($output, $dialog->getQuestion('The Entity shortcut name', $input->getOption('entity')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, $input->getOption('entity'));
        $input->setOption('entity', $entity);
    }
    
    protected function getActions($bundle)
    {
      $reader = new ActionReader($bundle);
      return $reader->extract();
    }
		
		protected function getBundle($bundle, $entity)
		{
			return new LazyBundleImpl($bundle, $entity);
		}
    
    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }
}
