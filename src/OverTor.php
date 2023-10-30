<?php
namespace VtSoftware\Tools\PHPCurlClass;

trait OverTOR {
  private int $tor_dataPort;
  private int $tor_controlPort;
  private String $tor_host;

  function throughTOR(String $host, int $port = 9050, int $ctrlPort = 9051): self {
    $this->tor_host = $host;
    $this->tor_dataPort = $port;
    $this->tor_controlPort = $ctrlPort;

    $this->proxy($this->tor_host, $this->tor_dataPort);

    return $this;
  }
  public function newTORCircle(): bool {
    $socket = \socket_create(\AF_INET, \SOCK_STREAM, \SOL_TCP);
    if (\socket_connect($socket, $this->tor_host, $this->tor_controlPort)) {
      \socket_send($socket, 'AUTHENTICATE'."\r\n", 100, \MSG_EOF);

      $response = '';
      \socket_recv($socket, $response, 20, \MSG_PEEK);

      if (\substr($response, 0, 3) == '250') {
        \socket_send($socket, 'SIGNAL NEWNYM'."\r\n", 100, \MSG_EOF);
        \socket_close($socket);

        $this->close();
        // TODO reinit?
        return false;
      }
    }

    return true;
  }
}
