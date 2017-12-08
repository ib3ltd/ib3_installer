<?php
namespace ib3\Installer\Console;

use RuntimeException;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;


class Questions
{
  /**
    * What is the domain name.
    *
    * @return void
    */
  public function domain($input, $output, $helper)
  {
    $question = new Question('What is the domain name (www.example.com): ');
    $question->setValidator(function ($answer) {
      if (!is_string($answer) || strlen($answer) == 0) {
        throw new \RuntimeException(
          'Enter a valid domain name'
        );
      }
      return $answer;
    });
    return $helper->ask($input, $output, $question);
  }
  /**
    * What is the domain protocol.
    *
    * @return void
    */
  public function protocol($input, $output, $helper)
  {
    $question = new ChoiceQuestion(
      'What is the domain protocol (defaults to http): ',
      ['http', 'https'],
      0
    );
    $question->setErrorMessage('Protocol %s is invalid.');
    return $helper->ask($input, $output, $question);
  }
  /**
    * What is the database password.
    *
    * @return void
    */
  public function dbpassword($input, $output, $helper)
  {
    $question = new Question('What is the database password (defaults to [none]): ', '');
    return $helper->ask($input, $output, $question);
  }
  /**
    * Who is the database user.
    *
    * @return void
    */
  public function dbuser($input, $output, $helper)
  {
    $question = new Question('Who is the database user (defaults to root): ', 'root');
    $question->setValidator(function ($answer) {
      if (!is_string($answer) || strlen($answer) == 0) {
        throw new \RuntimeException('Enter a valid database user');
    }
    return $answer;
    });
    return $helper->ask($input, $output, $question);
  }
  /**
    * What is the database name.
    *
    * @return void
    */
  public function dbname($input, $output, $helper)
  {
    $question = new Question('What is the database name: ');
    $question->setValidator(function ($answer) {
      if (!is_string($answer) || strlen($answer) == 0) {
        throw new \RuntimeException('Enter a valid database name');
      }
      return $answer;
    });
    return $helper->ask($input, $output, $question);
  }
  /**
    * Which environment is being set up.
    *
    * @return void
    */
  public function environment($input, $output, $helper)
  {
    $question = new ChoiceQuestion(
      'Which environment do you wish to install (defaults to development)',
      ['development', 'staging', 'production'],
      0
    );
    $question->setErrorMessage('Environment %s is invalid');
    return $helper->ask($input, $output, $question);
  }
}
