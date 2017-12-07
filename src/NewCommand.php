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
      $this->options['name'] = $input->getArgument('name');
      $this
        ->environment($input, $output)
        ->dbname($input, $output)
        ->dbuser($input, $output)
        ->dbpassword($input, $output)
        ->protocol($input, $output)
        ->domain($input, $output);

      var_dump($this->options);
      /*
      $this->verifyWebsiteDoesntExist(
        $directory = getcwd().'/'.$input->getArgument('name')
      );
      $output->writeln('<info>Keep shitting them out...</info>');
      $this->download($zipFile = $this->makeFilename())
        ->extract($zipFile, $directory)
        ->cleanUp($zipFile)
        ->install($input->getArgument('name'));
      $output->writeln('<comment>Website ready! Build something.</comment>');
      */
    }
    /**
     * What is the domain name.
     *
     * @return void
     */
    protected function domain($input, $output)
    {
      $helper = $this->getHelper('question');
      $question = new Question('What is the domain name (www.example.com): ');
      $question->setValidator(function ($answer) {
        if (!is_string($answer) || strlen($answer) == 0) {
          throw new \RuntimeException(
            'Enter a valid domain name'
          );
        }
        return $answer;
      });
      $this->options['domain'] = $helper->ask($input, $output, $question);
      return $this;
    }
    /**
     * What is the domain protocol.
     *
     * @return void
     */
    protected function protocol($input, $output)
    {
      $helper = $this->getHelper('question');
      $question = new ChoiceQuestion(
        'What is the domain protocol (defaults to http): ',
        ['http', 'https'],
        0
      );
      $question->setErrorMessage('Protocol %s is invalid.');
      $this->options['protocol'] = $helper->ask($input, $output, $question);
      return $this;
    }
    /**
     * What is the database password.
     *
     * @return void
     */
    protected function dbpassword($input, $output)
    {
      $helper = $this->getHelper('question');
      $question = new Question('What is the database password (defaults to [none]): ', '');
      $this->options['dbpassword'] = $helper->ask($input, $output, $question);
      return $this;
    }
    /**
     * Who is the database user.
     *
     * @return void
     */
    protected function dbuser($input, $output)
    {
      $helper = $this->getHelper('question');
      $question = new Question('Who is the database user (defaults to root): ', 'root');
      $question->setValidator(function ($answer) {
        if (!is_string($answer) || strlen($answer) == 0) {
          throw new \RuntimeException(
            'Enter a valid database user'
          );
        }
        return $answer;
      });
      $this->options['dbuser'] = $helper->ask($input, $output, $question);
      return $this;
    }
    /**
     * What is the database name.
     *
     * @return void
     */
    protected function dbname($input, $output)
    {
      $helper = $this->getHelper('question');
      $question = new Question('What is the database name: ');
      $question->setValidator(function ($answer) {
        if (!is_string($answer) || strlen($answer) == 0) {
          throw new \RuntimeException(
            'Enter a valid database name'
          );
        }
        return $answer;
      });
      $this->options['dbname'] = $helper->ask($input, $output, $question);
      return $this;
    }
    /**
     * Which environment is being set up.
     *
     * @return void
     */
    protected function environment($input, $output)
    {
      $helper = $this->getHelper('question');
      $question = new ChoiceQuestion(
        'Which environment do you wish to install (defaults to development)',
        ['development', 'staging', 'production'],
        0
      );
      $question->setErrorMessage('Environment %s is invalid');

      $this->options['environment'] = $helper->ask($input, $output, $question);
      return $this;
    }
    /**
     * Verify that the website does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyWebsiteDoesntExist($directory)
    {
      if (is_dir($directory)) {
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
    protected function download($zipFile)
    {
      $response = (new Client)->get('https://github.com/ib3ltd/drupal/archive/master.zip');
      file_put_contents($zipFile, $response->getBody());
      return $this;
    }
    /**
     * Extract the zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
      $archive = new ZipArchive;
      $archive->open($zipFile);
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
    protected function cleanUp($zipFile)
    {
      @chmod($zipFile, 0777);
      @unlink($zipFile);
      return $this;
    }
}
