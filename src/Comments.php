<?php
namespace ib3\Installer\Console;


class Comments
{
  public $begin;
  public $zip;
  public $shuffle;
  public $config;
  public $phpunit;
  public $dotenv;
  public $composer;
  public $npm;
  public $cleanup;
  public $finished;

  function __construct()
  {
    $begin = '<comment>Negotiating bananas with the Monkey Lord...</comment>';
    $zip = '<comment>Fetching the master zip from github...</comment>';
    $shuffle = '<comment>Moving the zip contents to the correct folder...</comment>';
    $config = '<comment>Updating the site config file...</comment>';
    $phpunit = '<comment>Updating phpunit references...</comment>';
    $dotenv = '<comment>Updating the .env references...</comment>';
    $composer = '<comment>Installing the composer dependencies...</comment>';
    $npm = '<comment>Installing the npm dependencies...</comment>';
    $cleanup = '<comment>Cleaning up the files...</comment>';
    $finished = '<comment>Lovely Stuff! Now build something.</comment>';
  }
}
