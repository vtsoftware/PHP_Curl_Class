<?php
namespace VtSoftware\Tools\PHPCurlClass;

class HTTP extends CURL {
  use WithAuthentication;

  private array $requestHeaders = array();
  private String $responseHeaders = '';
  private array $requestBuildCallback = array();
  private array $responseBuildCallback = array();
  private array $beforeCallbacks = array();
  private array $afterCallbacks = array();
  private Cookie $cookies;

  private String $protocol = 'http';
  private ?String $host = null;
  private ?int $port = null;
  private ?String $uri = null;
  private ?String $query_string = null;

  public function dumpRequest(): array {
    return array(
      'headers' => $this->requestHeaders,
      'body' => $this->getopt(\CURLOPT_POSTFIELDS)
    );
  }
  public function dumpResponse(): array {
    return array(
      'headers' => $this->responseHeaders,
      'body' => $this->last_response
    );
  }

  public static function parse_headers(String $headers): array {
    $headers = \explode("\n", $headers);
    $pairs = array();

    foreach ($headers as $header) {
      if (\strlen($header) >= 4 && \substr($header, 0, 4) != 'HTTP') {
        $parts = \explode(':', \trim($header), 2);
        $pairs[\strtolower(\trim($parts[0]))] = \trim($parts[1]);
      }
    }

    return $pairs;
  }

  function __construct(?String $url = null) {
    parent::__construct($url);

    parent::setopts(array(
      \CURLOPT_HEADERFUNCTION => function($instance, $header) {
        $this->responseHeaders .= $header;
        return strlen($header);
      },
      \CURLOPT_HEADER => false,
      \CURLOPT_RETURNTRANSFER => true
    ));
  }
  public function setRequestBuildCallback(String $key, ?\Closure $callback): self {
    $this->requestBuildCallback[$key] = $callback;

    return $this;
  }
  public function setResponseBuildCallback(String $key, ?\Closure $callback): self {
    $this->responseBuildCallback[$key] = $callback;

    return $this;
  }
  public function beforeCallback(String $key, ?\Closure $callback): self {
    $this->beforeCallbacks[$key] = $callback;

    return $this;
  }
  public function afterCallback(String $key, ?\Closure $callback): self {
    $this->afterCallbacks[$key] = $callback;

    return $this;
  }
  public function exec(bool $stay_awake = false) {
    if ($this->protocol === 'http' && $this->port === null) {
      $this->port = 80;
    }
    if ($this->protocol === 'https' && $this->port === null) {
      $this->port = 443;
    }
    if ($this->protocol === null && $this->port === 80) {
      $this->protocol = 'http';
    }
    if ($this->protocol === null && $this->port === 443) {
      $this->protocol = 'https';
    }
    if (!\is_null($this->host)) {
      $url =
        $this->protocol.'://'.
        $this->host.':'.
        $this->port.
        (($this->uri !== null || $this->query_string !== null) ? '/' : '').
        (($this->uri !== null) ? $this->uri : '').
        (($this->query_string !== null) ? '?'.$this->query_string : '');
        //echo $url;exit;
      $this->setopt(\CURLOPT_URL, $url);
    }

    if (!empty($this->requestBuildCallback)) {
      ksort($this->requestBuildCallback);
      foreach ($this->requestBuildCallback as $rqbKey => $rqb) {
        $rqb->__invoke($this);
      }
    }

    if (count($this->requestHeaders) > 0) {
      $headers = $this->requestHeaders;

      \array_walk($headers, function(&$value, $key) {
        $value = $key.': '.$value;
      });

      parent::setopt(\CURLOPT_HTTPHEADER, \array_values($headers));
    }

    if (!empty($this->beforeCallbacks)) {
      ksort($this->beforeCallbacks);
      foreach ($this->beforeCallbacks as $bfcKey => $bfc) {
        $bfc->__invoke($this);
      }
    }

    $response = parent::exec($stay_awake);

    if (!empty($this->afterCallbacks)) {
      ksort($this->afterCallbacks);
      foreach ($this->afterCallbacks as $afcKey => $afc) {
        $afc->__invoke($this);
      }
    }

    if (!empty($this->responseBuildCallback)) {
      ksort($this->responseBuildCallback);
      foreach ($this->responseBuildCallback as $rsbKey => $rsb) {
        $rsb->__invoke($this);
      }
    }

    $headers = $this->headers();

    if (\array_key_exists('content-type', $headers) && preg_match('`application/json`', $headers['content-type'])) {
      $response = \json_decode($response, true);
    }

    return $response;
  }
  public function protocol(String $protocol): self {
    $this->protocol = $protocol;

    return $this;
  }
  public function host(String $host): self {
    $this->host = $host;

    return $this;
  }
  public function port(int $port): self {
    $this->port = $port;

    return $this;
  }
  public function uri(String $uri): self {
    $this->uri = ltrim($uri, '/');

    return $this;
  }
  public function query_string(String $query_string): self {
    $this->query_string = $query_string;

    return $this;
  }
  public function url(String $url): self {
    $this->setopt(\CURLOPT_URL, $url);

    return $this;
  }
  public function user_agent(String $str): self {
    $this->setopt(\CURLOPT_USERAGENT, $str);

    return $this;
  }
  public function header(String $key, String $value): self {
    $this->requestHeaders[$key] = $value;

    return $this;
  }
  public function method(String $method): self {
    $this->setopt(\CURLOPT_CUSTOMREQUEST, $method);

    return $this;
  }
  public function headers() {
    $args = \func_get_args();

    if (isset($args[0]) && \is_array($args[0])) {
      foreach ($args[0] as $key => $value) {
        $this->header($key, $value);
      }

      return $this;
    } else if (\func_num_args() === 0 || \is_bool($args[0])) {
      $raw = $args[0] ?? false;

      if ($raw) {
        return $this->responseHeaders;
      } else {
        return static::parse_headers($this->responseHeaders);
      }
    }
  }
  public function body($data): self {
    if (\is_array($data)) {
      $data = \json_encode($data, \JSON_UNESCAPED_UNICODE);
    }

    $this->setopt(\CURLOPT_POSTFIELDS, $data);

    return $this;
  }
}
