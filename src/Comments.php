<?php
namespace ib3\Installer\Console;


class Comments
{
  protected $begin;
  protected $zip;
  protected $shuffle;
  protected $config;
  protected $phpunit;
  protected $dotenv;
  protected $composer;
  protected $npm;
  protected $cleanup;
  protected $finished;

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
