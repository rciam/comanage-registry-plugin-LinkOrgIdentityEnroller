<?php

class LinkIdentityRestClient extends LinkIdentityHttp {

  /**
   * @return string Request Location
   */
  protected function getReqLocation() {
    return 'pyff/api';
  }

  /**
   * @return string[] Array of Http Headers
   */
  protected function constructHeaders($content = false) {
    // Create HttpHeaders
    if($content) {
      $http_headers['Content-Type'] = 'application/json';
      $http_headers['Accept'] = 'application/json';
    }

    return $http_headers;
  }

  /**
   * @param string $action
   * @param string $http_protocol  POST, GET, PUT, PATCH, DELETE
   * @param array $get_fields   JSON formated data
   * @param array $post_fields  Query parameters
   * @param boolean $debug
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function Request($action, $http_protocol = 'GET', $post_fields = array(), $get_fields = array(), $debug = false) {
    try {
      $client = $this->httpClient();
      $options = [
        'debug' => $debug,
        'headers' => $this->constructHeaders(
          (!empty($post_fields) || !empty($get_fields))
        ),
      ];
      if(!empty($post_fields)) {
        $options['json'] = $post_fields;
      }
      if(!empty($get_fields)) {
        $options['query'] = $get_fields;
      }
      $response = $client->request($http_protocol, $this->getReqLocation() . '/' . $action, $options);
      return [
        'status_code' => $response->getStatusCode(),
        'msg' => $response->getReasonPhrase(),
        'data' => $response->getBody()->getContents(),
      ];
    } catch(\GuzzleHttp\Exception\RequestException $e) {
      if ($e->hasResponse()) {
        $response = $e->getResponse();
        return [
          'status_code' => $response->getStatusCode(),
          'msg' => $response->getReasonPhrase(),
          //          'data' => $response->getBody()->getContents(), // This is an html page
        ];
      }
    } catch(Exception $e) {
      return [
        'status_code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    }
  }
}
