#!/bin/env php7.4
<?php
require_once(\realpath(__DIR__.'/../vendor').'/autoload.php');

$ftp = new \VtSoftware\Tools\PHPCurlClass\FTP;
$ftp->verbose();
$ftp->host('10.10.10.10')->port(6800);
$ftp->authenticate(function($auth) {
  $auth->credentials('vtsoftware', 'asdasd');
});
$ftp->connect();

//$ftp->chdir('/os-files');
//$ftp->cdup();
//$ftp->chdir('domain.tld');
var_dump($ftp->rename_file('_', '_a'));
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

//echo "\nlog:\n";
//var_dump($ftp->dump_log());
