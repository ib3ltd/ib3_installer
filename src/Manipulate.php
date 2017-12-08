<?php
namespace ib3\Installer\Console;


class Manipulate
{
  public function updateFile($file, $search, $replace)
  {
    $f = file_get_contents($file);
    $f = str_replace($search, $replace);
    file_put_contents($f);
  }

  public function delTree($dir)
  {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }
}
