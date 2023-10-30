<?php
namespace VtSoftware\Tools\PHPCurlClass;

class CURL {
  use OverTor;

  public $instance = null;
  private array $opts = array();
  private $log_storage;
  public ?String $last_response = null;
  private array $last_error = array(0, '');

  function __construct(?String $url = null) {
    if (!\extension_loaded('curl')) {
      throw new Exception('php-curl extension needed for class.');
    }

    $this->instance = \curl_init($url);
  }
  function getopt(int $option) {
    return @$this->opts[$option];
  }
  function setopt(int $option, $value, bool $state = true): self {
    if ($this->instance === null) {
      throw new Exception('');
    }
    if (\curl_setopt($this->instance, $option, $value) === false) {
      throw new Exception('');
    }

    if ($state) {
      $this->opts[$option] = $value;
    }

    return $this;
  }
  function setopts(array $options, bool $state = true): self {
    if ($this->instance === null) {
      throw new Exception('');
    }
    if (\curl_setopt_array($this->instance, $options) === false) {
      throw new Exception('');
    }

    if ($state) {
      $this->opts = \array_merge($this->opts, $options);
    }

    return $this;
  }
  function unsetopt(int $option): self {
    $this->setopt($option, null);
    unset($this->opts[$option]);
    return $this;
  }
  function getinfo(?int $option = null): mixed {
    if ($this->instance === null) {
      throw new Exception('');
    }

    return \curl_getinfo($this->instance, $option);
  }
  public function proxy(String $host, int $port, int $type = \CURLPROXY_SOCKS5): self {
    $this->setopts(array(
      \CURLOPT_PROXY => $host,
      \CURLOPT_PROXYPORT => $port,
      \CURLOPT_PROXYTYPE => $type
    ));

    return $this;
  }
  function run() {
    echo __FUNCTION__.' @ '.__LINE__."\n";
    return $this->exec(true);
    echo __FUNCTION__.' @ '.__LINE__."\n";
  }
  function exec(bool $stay_awake = false) {
    if (
      !defined('CURLCLASS_IGNORE_VERBOSE_HDR_OUT') &&
      \array_key_exists(\CURLOPT_VERBOSE, $this->opts) &&
      $this->opts[\CURLOPT_VERBOSE] === true &&
      \array_key_exists(\CURLINFO_HEADER_OUT, $this->opts) &&
      $this->opts[\CURLINFO_HEADER_OUT] === true
    ) {
      throw new Exception(
        'With CURLOPT_VERBOSE and CURLINFO_HEADER_OUT flags maybe empty the verbose log.'."\n".
        'Watch this: https://bugs.php.net/search.php?search_for=CURLINFO_HEADER_OUT&cmd=display&status=All&bug_type=All&project=All'."\n".
        'To supress this exception, define "CURLCLASS_IGNORE_VERBOSE_HDR_OUT" flag!'
      );
    }

    $result = \curl_exec($this->instance);
    $no = \curl_errno($this->instance);

    if ($no > 0) {
      $or = \curl_error($this->instance);

      if (!defined('CURLCLASS_NO_EXEC_EXCEPTION')) {
        var_dump($result);
        throw new Exception($or, $no);
      } else {
        $this->last_error = array($no, $or);

        $this->close();

        return false;
      }
    }

    $this->last_response = $result;

    if (\array_key_exists(\CURLOPT_VERBOSE, $this->opts) && $this->opts[\CURLOPT_VERBOSE] === true) {
      \fclose($this->log_storage);
    }

    if ($result === false) {
      if (\array_key_exists(\CURLOPT_VERBOSE, $this->opts) && $this->opts[\CURLOPT_VERBOSE] === true) {
        echo $this->dump_log();
      }

      throw new Exception(\curl_error($this->instance), \curl_errno($this->instance));
    }

    if (!$stay_awake) {
      $this->close();
    }

    return $result;
  }
  function close() {
    if ($this->instance !== null) {
      \curl_close($this->instance);
      $this->instance = null;
    }
  }
  function verbose(bool $is = true): self {
    $this->log_storage = fopen('php://temp', 'w+');
    $this->setopts(array(
      \CURLOPT_VERBOSE => $is,
      \CURLOPT_STDERR => $this->log_storage
    ));

    return $this;
  }
  function timeout(int $interval, bool $is_ms = false): self {
    if ($is_ms) {
      $this->setopt(\CURLOPT_TIMEOUT_MS, $interval);
    } else {
      $this->setopt(\CURLOPT_TIMEOUT, $interval);
    }

    return $this;
  }
  public function dump_log() {
    if (!\array_key_exists(\CURLOPT_VERBOSE, $this->opts) || $this->opts[\CURLOPT_VERBOSE] !== true) {
      \rewind($this->log_storage);
      return \stream_get_contents($this->log_storage);
    }

    return false;
  }
  public function getLastResponse(): String {
    return $this->last_response;
  }
}
