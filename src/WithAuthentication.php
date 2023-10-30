<?php
namespace VtSoftware\Tools\PHPCurlClass;

trait WithAuthentication {
  private Auth $authentication;

  public function authenticate(\Closure $callback): self {
    $authInstance = new Auth;
    $callback($authInstance);
    $authInstance->apply($this);

    return $this;
  }
}
