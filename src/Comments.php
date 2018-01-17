<?php
namespace ib3\Installer\Console;

class Comments
{
    public $begin = '<info>Negotiating bananas with the Monkey Lord...</info>';
    public $zip = '<info>Fetching the master zip from github...</info>';
    public $shuffle = '<info>Moving the zip contents to the correct folder...</info>';
    public $config = '<info>Updating the site config file...</info>';
    public $phpunit = '<info>Updating phpunit references...</info>';
    public $dotenv = '<info>Updating the .env references...</info>';
    public $vagrant = '<info>Installing vagrant...</info>';
    public $composer = '<info>Installing the composer dependencies...</info>';
    public $npm = '<info>Installing the npm dependencies...</info>';
    public $cleanup = '<info>Cleaning up the files...</info>';
    public $finished = '<info>Lovely Stuff! Now build something.</info>';
}
