<?php

class LinkIdentityClient
{
  private $protocol = null;
  private $host = null;
  private $port = null;
  private $location = null;
  private $url = null;
  private $robot_cert = null;
  private $robot_key = null;
  private $rest_client = null;
  private $_config = array();

  public function __construct($protocol, $host, $port, $location, $robot_cert, $robot_key, $url = null) {
    $this->protocol = $protocol;
    $this->host = $host;
    $this->port = $port ?? 80;  // Use port 80 by default
    $this->location = $location;
    $this->robot_cert = $robot_cert;
    $this->robot_key = $robot_key;
    $this->url = $url;
    $this->_config = [
      $this->protocol,
      $this->host,
      $this->port,
      $this->location,
      $this->robot_cert,
      $this->robot_key,
      $this->url
    ];
  }

  /**
   * Get an instance of the HttpClient
   * @return object GuzzleHttp\Client VomsRestClient
   */
  private function restClient() {
    if($this->rest_client === null) {
      $this->rest_client = new LinkIdentityRestClient(...$this->_config);
    }
    return $this->rest_client;
  }

  /**
   * @param string $value   String to search the Metadata for
   * @return array|void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function mdqSearch($value) {
    $get_fields = [
      'query' => $value
    ];
    return $this->restClient()->Request(
      LinkOrgIdentityRestActionsEnum::SEARCH,
      'GET',
      [],
      $get_fields);
  }

}