<?php

namespace Samir\Generator\Command;

use Silex\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Sensio\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Samir\Generator\Generator\SilexProjectGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

/**
 * Generates projects.
 *
 * @author Samir El Aouar based on Sensio\Generator by Fabien Potencier <fabien@symfony.com>
 */
class GenerateSilexProjectCommand extends ConsoleCommand
{
    private $generator;
    private $skeletonPath;
    private $app;
    
    public function __construct(Application $app, $skeletonPath = null) 
		{
			$this->app = $app;
			$this->skeletonPath = empty($skeletonPath) ? __DIR__.'/../Resources/skeleton/' : $skeletonPath;
		
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
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the project to create'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the project'),
                new InputOption('project-name', '', InputOption::VALUE_REQUIRED, 'The optional project name'),
                new InputOption('structure', '', InputOption::VALUE_NONE, 'Whether to generate the whole directory structure'),
            ))
            ->setDescription('Generates a project')
            ->setHelp(<<<EOT
The <info>generate:project</info> command helps you generates new projects.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--namespace</comment> is the only one needed if you follow the
conventions):

<info>php app/console generate:project --namespace=Acme/Blog</info>

Note that you can use <comment>/</comment> instead of <comment>\\ </comment>for the namespace delimiter to avoid any
problem.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options:

<info>php app/console generate:project --namespace=Acme/Blog --dir=src [--project-name=...] --no-interaction</info>
EOT
            )
            ->setName('generate:project')
        ;
    }

    /**
     * @see Command
     *
     * @throws \RuntimeException         When project can't be executed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = 'annotation';
        
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $namespace = $input->getOption('namespace');
        if (!$bundle = $input->getOption('project-name')) {
            $bundle = strtr($namespace, array('\\' => ''));
        }
        
        $root_dir = getcwd().'/'.$input->getOption('dir');
        $dir = Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace);
        $structure = $input->getOption('structure');

        $dialog->writeSection($output, 'Project generation');

        if (!$this->getApp('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generator = $this->getGenerator();
        $generator->generate($namespace, $bundle, $dir, $format, $structure);

        $output->writeln('Generating the project code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $namespace, $bundle, $dir));

        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $format = 'annotation';
        
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Silex project generator');

        // namespace
        $output->writeln(array(
            'Each project is hosted under a namespace (like <comment>Acme/Blog</comment>).',
            'The namespace should begin with a "vendor" name like your company name, your',
            'project name, or your client name, followed by one or more optional category',
            'sub-namespaces, and it should end with the bundle name itself.',
            '',
            'Use <comment>/</comment> instead of <comment>\\ </comment> for the namespace delimiter to avoid any problem.',
            '',
        ));

        $namespace = $dialog->ask($output, $dialog->getQuestion('Project namespace', $input->getOption('namespace')), $input->getOption('namespace'));
        $input->setOption('namespace', $namespace);

        // bundle name
        $bundle = $input->getOption('project-name') ?: strtr($namespace, array('\\' => '', '\\' => ''));
        $output->writeln(array(
            '',
            'In your code, a project is often referenced by its name. It can be the',
            'concatenation of all namespace parts but it\'s really up to you to come',
            'up with a unique name (a good practice is to start with the vendor name).',
            'Based on the namespace, we suggest <comment>'.$bundle.'</comment>.',
            '',
        ));
        $bundle = $dialog->ask($output, $dialog->getQuestion('Project name', $bundle), $bundle);
        $input->setOption('project-name', $bundle);

        // target dir
        $dir = $input->getOption('dir') ?: realpath($this->getApp('root_dir').'src');
        $output->writeln(array(
            '',
            'The project can be generated anywhere. The suggested default directory uses',
            'the standard conventions.',
            '',
        ));
        $dir = $dialog->askAndValidate($output, $dialog->getQuestion('Target directory', $dir), function ($dir) use ($bundle, $namespace) { return Validators::validateTargetDir($dir, $bundle, $namespace); }, false, $dir);
        $input->setOption('dir', $dir);

        // optional files to generate
        $output->writeln(array(
            '',
            'To help you get started faster, the command can generate some',
            'code snippets for you.',
            '',
        ));
        
        $structure = $input->getOption('structure');
        if (!$structure && $dialog->askConfirmation($output, $dialog->getQuestion('Do you want to generate the whole directory structure', 'no', '?'), false)) {
            $structure = true;
        }
        $input->setOption('structure', $structure);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s</info>\" project\nin \"<info>%s</info>\" using the \"<info>%s</info>\" format.", $namespace, $dir, $format),
            '',
        ));
    }

    protected function checkAutoloader(OutputInterface $output, $namespace, $bundle, $dir)
    {
        $output->write('Checking that the project is autoloaded: ');
        if (!class_exists($namespace.'\\'.$bundle)) {
            return array(
                '- Edit the <comment>composer.json</comment> file and register the project',
                '  namespace in the "autoload" section:',
                '',
            );
        }
    }

    protected function updateKernel($dialog, InputInterface $input, OutputInterface $output, KernelInterface $kernel, $namespace, $bundle)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of your Kernel', 'yes', '?'), true);
        }

        $output->write('Enabling the bundle inside the Kernel: ');
        $manip = new KernelManipulator($kernel);
        try {
            $ret = $auto ? $manip->addBundle($namespace.'\\'.$bundle) : false;

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf('- Edit <comment>%s</comment>', $reflected->getFilename()),
                    '  and add the following project in the <comment>AppKernel::registerBundles()</comment> method:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $namespace.'\\'.$bundle),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>AppKernel::registerBundles()</comment>.', $namespace.'\\'.$bundle),
                '',
            );
        }
    }

    protected function updateRouting($dialog, InputInterface $input, OutputInterface $output, $bundle, $format)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of the Routing', 'yes', '?'), true);
        }

        $output->write('Importing the bundle routing resource: ');
        $routing = new RoutingManipulator($this->getApp('root_dir').'/config/routing.yml');
        try {
            $ret = $auto ? $routing->addResource($bundle, $format) : false;
            if (!$ret) {
                if ('annotation' === $format) {
                    $help = sprintf("        <comment>resource: \"@%s/Controller/\"</comment>\n        <comment>type:     annotation</comment>\n", $bundle);
                } else {
                    $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing.%s\"</comment>\n", $bundle, $format);
                }
                $help .= "        <comment>prefix:   /</comment>\n";

                return array(
                    '- Import the bundle\'s routing resource in the app main routing file:',
                    '',
                    sprintf('    <comment>%s:</comment>', $bundle),
                    $help,
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Project <comment>%s</comment> is already imported.', $bundle),
                '',
            );
        }
    }

    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new SilexProjectGenerator($this->app, $this->skeletonPath . 'project');
            
        }

        return $this->generator;
    }

    public function setGenerator(BundleGenerator $generator)
    {
        $this->generator = $generator;
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
