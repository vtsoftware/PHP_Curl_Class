<?php
namespace VtSoftware\Tools\PHPCurlClass;

class FTP extends CURL {
  use WithAuthentication, RequestHistory;

  private String $host;
  private array $pwd = array();

  function __construct() {
    parent::__construct();

    register_shutdown_function(function($class) {
      $class->close();
    }, $this);

    $this->setopts(array(
      \CURLOPT_RETURNTRANSFER => true,
      \CURLOPT_FTP_USE_EPSV => false
    ));
  }

  public function command(String $command): String {
    $this->setopt(\CURLOPT_CUSTOMREQUEST, $command, false);
    $result = $this->run();
    $this->unsetopt(\CURLOPT_CUSTOMREQUEST);
    return $result;
  }

  public function close() {
    $this->event(__FUNCTION__);
    $this->command('QUIT');

    parent::close();
  }
  public function host(String $host): self {
    $this->host = $host;

    $this->setopt(\CURLOPT_URL, 'ftp://'.$this->host, false);

    return $this;
  }
  public function port(int $port): self {
    $this->setopt(\CURLOPT_PORT, $port, false);

    return $this;
  }
  public function connect(): bool {
    $this->event(__FUNCTION__);
    $this->run();
    return true;
  }
  public function pwd(): String {
    $this->event(__FUNCTION__);
    $this->pwd = $this->command('PWD');
    echo '[PWD: '.$this->pwd.']'."\n";

    return $this->pwd;
  }
  public function list() {
    $this->event(__FUNCTION__);

    $list = \explode("\n", \trim($this->command('MLSD')));
    foreach ($list as &$row) {
      \parse_str(\str_replace(array('; ', ';'), array('&name=', '&'), \trim($row)), $row);
    }

    return $list;
  }
  public function chdir(String $path): self {
    $this->event(__FUNCTION__, $path);
    $this->command('CWD '.$path);

    $this->pwd[] = $path;

    return $this;
  }
  public function cdup(): self {
    $this->event(__FUNCTION__);
    $this->command('CDUP');

    $this->pwd = \array_slice($this->pwd, 0, -1);

    return $this;
  }
  public function mkdir(String $name): self {
    $this->event(__FUNCTION__, $name);
    $this->command('MKD '.$name);

    return $this;
  }
  public function writeFile(String $name, String $content): self {
    /*    $fp=fopen($localfile,'r');
    curl_setopt($ch,CURLOPT_URL,"ftp://".$ftpUsername.':'.$ftpPassword.'@'.$ftpHost.'/htdocs/'.$localfile);
    curl_setopt($ch,CURLOPT_UPLOAD,1);
    curl_setopt($ch,CURLOPT_INFILE,$fp);
    curl_setopt($ch,CURLOPT_INFILESIZE,filesize($localfile));*/
    $this->event(__FUNCTION__, $name);

    $temporaryFile = \sys_get_temp_dir().\DIRECTORY_SEPARATOR.$name;
    \file_put_contents($temporaryFile, $content);

    $this->setopts(array(
      \CURLOPT_UPLOAD => true,
      \CURLOPT_INFILE => \fopen($temporaryFile, 'r'),
      \CURLOPT_INFILESIZE => \filesize($temporaryFile)
    ), false);
    $this->run();

    return $this;
  }
  public function readFile(String $name): String {
    return $this->command('RETR '.$name);
  }
  public function downloadFile(String $name, String $destination) {
    \file_put_contents($destination, $this->readFile($name));
  }
  public function dirsize(): int {
    $total = 0;

    $rows = $this->list();

    foreach ($rows as $line) {
      if (\array_key_exists('type', $line)) {
        if ($line['type'] === 'dir') {
          \usleep(500000);
          $this->chdir($line['name']);
          $total += $this->dirsize();
          $this->cdup();
        } else if ($line['type'] === 'file') {
          $total += $line['size'];
        }
      }
    }

    return $total;
  }
  public function rename(String $old, String $new): self {
    $this->command('RNFR '.$old);
    $this->command('RNTO '.$new);

    return $this;
  }
  public function delete_file(String $name): self {
    $this->command('DELE '.$name);

    return $this;
  }
  public function delete_dir(String $name): self {
    $this->command('RMD '.$name);

    return $this;
  }
}
