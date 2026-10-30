<?php declare(strict_types=1);

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
