<?php
namespace VtSoftware\Tools\PHPCurlClass;

trait RequestHistory {
  public function event() {
    if (defined('CURLCLASS_LOG_PATH')) {
      if (!\is_dir(CURLCLASS_LOG_PATH)) {
        \mkdir(CURLCLASS_LOG_PATH, 0755, true);
      }

      $args = \func_get_args();
      $func = $args[0];
      unset($args[0]);

      \file_put_contents(CURLCLASS_LOG_PATH.'/'.\date('Y-m-d').'.txt', \date('H:i:s').\explode('.', (String)\microtime(true))[1].'; '.$func.': '.\implode(', ', $args)."\n", \FILE_APPEND);
    }
  }
}
