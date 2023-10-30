<?php
namespace VtSoftware\Tools\PHPCurlClass;

class Auth {
  private array $prefs = array();
  private ?\Closure $auth_header_build_callback = null;
  private ?String $auth_header_name = null;

  public function basic(): self {
    $this->prefs[\CURLOPT_HTTPAUTH] = \CURLAUTH_BASIC;

    return $this;
  }
  public function credentials(String $username, String $password): self {
    $this->prefs[\CURLOPT_USERPWD] = $username.':'.$password;

    return $this;
  }
  public function setName(String $header_name): self {
    $this->auth_header_name = $header_name;

    return $this;
  }
  public function setValueCallback(\Closure $callback): self {
    $this->auth_header_build_callback = $callback;

    return $this;
  }
  public function apply(object $to) {
    switch (true) {
      case ($to instanceof HTTP): {
        // TODO ...
        // $to->header($this->auth_header_name, $this->$auth_header_build_callback->__invoke($this->prefs));
      } break;
      case ($to instanceof FTP): {
        $to->setopts($this->prefs);
      } break;
    }
  }
}
