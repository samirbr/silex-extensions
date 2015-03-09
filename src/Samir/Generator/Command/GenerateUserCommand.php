<?php

namespace Samir\Generator\Command;

use Silex\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Samir El Aouar <samirbr@gmail.com> based on Sensio\Generator by Fabien Potencier <fabien@symfony.com>
 */
class GenerateUserCommand extends ConsoleCommand
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
                new InputOption('username', '', InputOption::VALUE_REQUIRED, 'The username name'),
                new InputOption('password', '', InputOption::VALUE_REQUIRED, 'The user password'),
                new InputOption('role', '', InputOption::VALUE_OPTIONAL, 'The user role')
            ))
            ->setDescription('Generates a username')
            ->setHelp(<<<EOT
The <info>user:create</info> command creates user.
EOT
            )
            ->setName('user:create')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        
        $stmt = $this->app['db']->executeQuery("SHOW TABLES LIKE 'user'");
        
        if ( ! $table = $stmt->fetch()) { // create
          var_dump($table);
          $output->writeln(array(
            '',
            'Table `user` doesn\'t exists.',
            'Creating table <info>`user`</info>...',
            ''
          ));
            
          $stmt = $this->app['db']->executeQuery("CREATE TABLE `user` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(100) NOT NULL DEFAULT '',
            `password` VARCHAR(255) NOT NULL DEFAULT '',
            `roles` VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_username` (`username`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
          
          if ($stmt->fetch()) {
            $output->writeln(array(
              '',
              'Table `user` created: <info>OK</info>'
            ));
          } else {
            $output->writeln(array(
              '',
              'Error thrown while trying to create `user` table'
            ));
          }
        }
        
        $stmt = $this->app['db']->executeQuery("SELECT username FROM `user` WHERE username = ?", array($input->getOption('username')));
        
        if ( ! $stmt->fetch()) {
          $stmt = $this->app['db']->executeQuery("INSERT INTO `user` (`username`, `password`, `roles`) VALUES (?, ?, ?)", array(
            $input->getOption('username'),
            $this->app['security.encoder.digest']->encodePassword($input->getOption('password'), ''),
            $input->getOption('role')
          ));
          
          $output->writeln(array(
            '',
            sprintf("User %s successful created", $input->getOption('username'))
          ));
        } else {
          $output->writeln(array(
            '',
            sprintf("User '%s' already exists", $input->getOption('username'))
          ));
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the user creator');

        // namespace
        $output->writeln('This command helps you create a user.');

        $username = $dialog->ask($output, $dialog->getQuestion('The username', ''));
        $input->setOption('username', $username);
        
        $password = $dialog->ask($output, $dialog->getQuestion('The user password', ''));
        $input->setOption('password', $password);
        
        $role = $dialog->ask($output, $dialog->getQuestion('The user role', 'ROLE_ADMIN'), 'ROLE_ADMIN');
        $input->setOption('role', $role);
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
