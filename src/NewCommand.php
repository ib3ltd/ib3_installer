<?php
namespace ib3\Installer\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ib3\Installer\Console\Questions;
use ib3\Installer\Console\Comments;
use ib3\Installer\Console\Manipulate;

class NewCommand extends Command
{

  protected $options;

  /**
    * Configure the command options.
    *
    * @return void
    */
  protected function configure()
  {
  $this
    ->setName('new')
    ->setDescription('Create a new ib3 drupal website.')
    ->addArgument('name', InputArgument::REQUIRED);
  }
  /**
    * Execute the command.
    *
    * @param  InputInterface  $input
    * @param  OutputInterface  $output
    * @return void
    */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $comments = new Comments();
    $this->setOptions($input, $output);

    $output->writeln($comments->begin);
    $this->verifyWebsiteDoesntExist();

    $output->writeln($comments->zip);
    $this->download($zip_file = $this->makeFilename())
      ->extract($zip_file, $this->options['working_directory'])
      ->removeZip($zip_file);

    $output->writeln($comments->shuffle);
    $this->moveZipContents();

    $output->writeln($comments->config);
    $this->siteConfig();

    $output->writeln($comments->phpunit);
    $this->updatePhpUnit();

    $output->writeln($comments->dotenv);
    $this->updateDotEnv();

    $output->writeln($comments->vagrant);
    $this->updateVagrant();
    passthru('vagrant up');

    $output->writeln($comments->composer);
    passthru('composer install');

    $output->writeln($comments->npm);
    passthru('npm install');

    $output->writeln($comments->cleanup);
    $this->cleanup();

    $output->writeln($comments->finished);
  }
  /**
    * Set the install options
    *
    * @return void
    */
  protected function setOptions($input, $output)
  {
    $questions = new Questions();
    $helper = $this->getHelper('question');

    $this->options = [
      'name' => $input->getArgument('name'),
      'local' => $questions->local($input, $output, $helper),
      'environment' => $questions->environment($input, $output, $helper),
      'dbname' => $questions->dbname($input, $output, $helper),
      'dbuser' => $questions->dbuser($input, $output, $helper),
      'dbpassword' => $questions->dbpassword($input, $output, $helper),
      'protocol' => $questions->protocol($input, $output, $helper),
      'domain' => $questions->domain($input, $output, $helper),
      'working_directory' => implode(DIRECTORY_SEPARATOR, [getcwd(), $input->getArgument('name')]),
      'hash' => md5(uniqid(rand(), true)).'_'.md5(uniqid(rand(), true)),
    ];

    $this->options['working_directory'] = implode(DIRECTORY_SEPARATOR, [getcwd(), $this->options['name']]);
    $this->options['unit'] = $this->options['protocol'].'://'.$this->options['domain'];
    $this->options['dom'] = $this->options['domain'];
    $this->options['domain'] = '^'.str_replace('.','\.',$this->options['domain']).'$';

  }
  /**
    * Cleanup the files
    *
    * @return void
    */
  protected function cleanup()
  {
    $manipulate = new Manipulate();
    $manipulate->delTree(implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'html', 'sites']));
    symlink(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'html', 'sites'])
    );
  }
  /**
    * Config the .env settings
    *
    * @return void
    */
  protected function updateDotEnv()
  {
    $manipulate = new Manipulate();
    copy(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites', '.env.example']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites', '.env'])
    );
    $manipulate->updateFile(implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites', '.env']), ['#VERSION#','#HASH#'], [
      $this->options['environment'],
      $this->options['hash']
    ]);
  }
  /**
    * Config the site settings
    *
    * @return void
    */
  protected function updatePhpUnit()
  {
    $manipulate = new Manipulate();
    copy(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'phpunit.example.xml']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'phpunit.xml'])
    );
    $manipulate->updateFile(implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'phpunit.xml']), '#UNIT#', $this->options['unit']);
  }
  /**
    * Config vagrant
    *
    * @return void
    */
  protected function updateVagrant()
  {
    $manipulate = new Manipulate();
    $vagrant_file = implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'vagrant-config.xml']);
    $example_vagrant_osx_file = implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'vagrant-config-local.yaml.osx']);
    $vagrant_osx_file = implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'vagrant-config-local.yaml']);

    $manipulate->updateFile($vagrant_file, ['#HOST#','#DATABASE#','#USER#','#PASSWORD#'], [
      $this->options['dom'],
      $this->options['dbname'],
      $this->options['dbuser'],
      $this->options['dbpassword']
    ]);

    if ($this->options['local'] = 'osx') {
      copy($example_vagrant_osx_file, $vagrant_osx_file);
    }
  }
  /**
    * Update phpunit
    *
    * @return void
    */
  protected function siteConfig()
  {
    $manipulate = new Manipulate();

    $settings_directory = implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites', $this->options['environment']]);
    $example_settings_file = implode(DIRECTORY_SEPARATOR, [$settings_directory, 'settings.example.php']);
    $settings_file = implode(DIRECTORY_SEPARATOR, [$settings_directory, 'settings.php']);

    copy($example_settings_file, $settings_file);

    $manipulate->updateFile($settings_file, ['#HOST#','#DATABASE#','#USER#','#PASSWORD#'], [
      $this->options['domain'],
      $this->options['dbname'],
      $this->options['dbuser'],
      $this->options['dbpassword']
    ]);

    copy(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites', 'default', 'settings.example.php']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites', 'default', 'settings.php'])
    );
  }
  /**
    * Move the zip contents out of the sub folder
    *
    * @return void
    */
  protected function moveZipContents()
  {
    chdir($this->options['working_directory']);

    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'composer.json']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'composer.json'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'gulpfile.js']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'gulpfile.js'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'html']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'html'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'package.json']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'package.json'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'phpunit.example.xml']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'phpunit.example.xml'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'sites']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sites'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'sync']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'sync'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', 'tests']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'tests'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', '.editorconfig']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], '.editorconfig'])
    );
    rename(
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master', '.gitignore']),
      implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], '.gitignore'])
    );
    @rmdir(implode(DIRECTORY_SEPARATOR, [$this->options['working_directory'], 'drupal-master']));
  }
  /**
    * Verify that the website does not already exist.
    *
    * @param  string  $directory
    * @return void
    */
  protected function verifyWebsiteDoesntExist()
  {
    if (is_dir($this->options['working_directory'])) {
      throw new RuntimeException('Website already exists!');
    }
  }
  /**
    * Generate a random temporary filename.
    *
    * @return string
    */
  protected function makeFilename()
  {
    return getcwd().'/ib3_'.md5(time().uniqid()).'.zip';
  }
  /**
    * Download the temporary Zip to the given file.
    *
    * @param  string  $zipFile
    * @return $this
    */
  protected function download($zip_file)
  {
    $response = (new Client)->get('https://github.com/ib3ltd/drupal/archive/master.zip');
    file_put_contents($zip_file, $response->getBody());
    return $this;
  }
  /**
    * Extract the zip file into the given directory.
    *
    * @param  string  $zipFile
    * @param  string  $directory
    * @return $this
    */
  protected function extract($zip_file, $directory)
  {
    $archive = new ZipArchive;
    $archive->open($zip_file);
    $archive->extractTo($directory);
    $archive->close();
    return $this;
  }
  /**
    * Clean-up the Zip file.
    *
    * @param  string  $zipFile
    * @return $this
    */
  protected function removeZip($zip_file)
  {
    @chmod($zip_file, 0777);
    @unlink($zip_file);
    return $this;
  }
}
