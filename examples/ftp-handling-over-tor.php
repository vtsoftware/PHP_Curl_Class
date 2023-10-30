#!/bin/env php7.4
<?php
require_once(\realpath(__DIR__.'/../vendor').'/autoload.php');
try {
  $ftp = new \VtSoftware\Tools\PHPCurlClass\FTP;
  $ftp->throughTOR('127.0.0.1');
  $ftp->verbose();
  $ftp->host('test.rebex.net')->port(21);
  $ftp->authenticate(function($auth) {
    $auth->credentials('demo', 'password');
  });
  $ftp->connect();

  //$ftp->chdir('/os-files');
  //$ftp->cdup();
  //$ftp->chdir('csabazar.hu');
  //var_dump($ftp->rename_file('_', '_a'));
  //echo $ftp->readFile('setup.sh');

  /*$t=date('H-i-s');
  $ftp->mkdir($t);
  $ftp->chdir('/'.$t);
  echo 'current dir: '.$ftp->pwd()."\n";

  $ftp->writeFile(mt_rand(10000, 99999).'_data.txt', 'asd
  meg némi asd is
  meg plusz egy pici bla
  na jó, még egy bla
  SŐT BLAH XD
  :D');*/

  //var_dump($ftp->list());

  echo $ftp->readFile('readme.txt');

  echo "\nlog:\n";
  var_dump($ftp->dump_log());
} catch (\Exception $e) {
  var_dump($e);
}