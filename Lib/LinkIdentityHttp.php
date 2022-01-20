<?php
//require_once '../Vendor/autoload.php';

abstract class LinkIdentityHttp {
  protected $protocol = null;
  protected $host = null;
  protected $port = null;
  protected $location = null;
  protected $url = null;
  protected $robot_cert = null;
  protected $robot_key = null;
  protected $_http_client = null;
  protected $_fcert_robot = null;
  protected $_fkey_robot = null;


  /**
   * VomsRestClient constructor.
   * @param $host
   * @param $port
   * @param $location
   * @param $robot_cert
   * @param $robot_key
   * @param $url
   */
  public function __construct($protocol, $host, $port, $location, $robot_cert, $robot_key, $url = null) {
    $this->protocol = $protocol;
    $this->host = $host;
    $this->port = $port;
    $this->location = $location;
    $this->robot_cert = $robot_cert;
    $this->robot_key = $robot_key;
    $this->url = $url;

    $this->_fkey_robot = $this->sslKey();
    $this->_fcert_robot = $this->sslCert();
  }

  /**
   *  Close the ssl_key and ssl_cert temporary files if still open
   */
  public function __destruct() {
    if(!is_null($this->_fcert_robot)) {
      fclose($this->_fcert_robot);
    }
    if(!is_null($this->_fkey_robot)) {
      fclose($this->_fkey_robot);
    }
  }

  /**
   * create the location of the api
   */
  abstract protected function getReqLocation();

  /**
   * create the Request
   */
  abstract public function Request($action, $post_fields, $debug);

  /**
   * Construct the Headers for the request
   * @param boolean $content whether you have a content or not
   * @return string[] Array of Http Headers
   */
  abstract protected function constructHeaders($content);

  /**
   * @return string|null
   * @todo generalize the construction of the endpoint
   */
  protected function baseUri() {
    // Client provided the url
    if(!is_null($this->url)) {
      return $this->url . ':' . $this->port;
    }
    // Client provided the pieces we need to construct the url
    if(is_null($this->protocol) || is_null($this->host) || is_null($this->port) || is_null($this->location)) {
      return null;
    }
    return $this->protocol . '://' . $this->host . ':' . $this->port;
  }

  /**
   * @return Object GuzzleHttp\Client | null
   */
  protected function httpClient() {
    if(is_null($this->baseUri())) {
      return null;
    }
    if(is_null($this->_http_client)) {
      $this->_http_client = new GuzzleHttp\Client($this->getDefaults());
    }
    return $this->_http_client;
  }

  /**
   * @return file handler robot_cert
   */
  protected function sslCert() {
    if(is_null($this->robot_cert)) {
      return null;
    }
    $handle_fcert = tmpfile();
    fwrite($handle_fcert, $this->robot_cert);
    // XXX Uncomment for debug
    //    $user_fcert = stream_get_meta_data($handle_fcert)['uri'];
    //    var_dump(file_get_contents($user_fcert));
    return $handle_fcert;
  }

  /**
   * @return file handler robot_key
   */
  protected function sslKey() {
    if(is_null($this->robot_key)) {
      return null;
    }
    $handle_fkey = tmpfile();
    fwrite($handle_fkey, $this->robot_key);
    $user_fkey = stream_get_meta_data($handle_fkey)['uri'];
    // XXX uncomment for debug
    //    var_dump(file_get_contents($user_fkey));
    chmod($user_fkey, 0644);
    return $handle_fkey;
  }

  /**
   * @return array
   * @todo Extend config page to handle these options. Create two sections. Server config, Http Config
   * @todo Make verify ssl a configuration
   */
  public function getDefaults() {
    $defaults = array(
      'base_uri' => $this->baseUri(),
      'timeout' => 5,
      'connect_timeout' => 2,
      'verify' => false
    );
    if(isset($this->_fcert_robot)) {
      $defaults['cert'] = stream_get_meta_data($this->_fcert_robot)['uri'];
    }
    if(isset($this->_fkey_robot)) {
      $defaults['ssl_key'] = stream_get_meta_data($this->_fkey_robot)['uri'];
    }

    return $defaults;
  }
}
