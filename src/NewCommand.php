<?php
namespace ib3\Installer\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends Command
{
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
        $this->verifyWebsiteDoesntExist(
            $directory = getcwd().'/'.$input->getArgument('name')
        );
        $output->writeln('<info>Keep shitting them out...</info>');
        $this->download($zipFile = $this->makeFilename())
             ->extract($zipFile, $directory)
             ->cleanUp($zipFile);

        $output->writeln('<comment>Website ready! Build something.</comment>');
    }
    /**
     * Get it installed.
     *
     * @return void
     */
    protected function install()
    {
      exec("mv  ".getcwd()."/drupal-master/* ".getcwd());
      rmdir(getcwd()."/drupal-master");
      exec(getcwd().'/. ib3installer');
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
