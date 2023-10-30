#!/bin/env php7.4
<?php
require_once(\realpath(__DIR__.'/../vendor').'/autoload.php');

$http = new \VtSoftware\Tools\PHPCurlClass\HTTP;
$http->url('https://hvg.hu');
$reply = $http->verbose()->header('X-asd-test', 'probe')->exec();
var_dump($http->headers(), $http->dump_log(), $reply);
