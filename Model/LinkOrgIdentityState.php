<?php

class LinkOrgIdentityState extends AppModel
{
  // Required by COmanage Plugins
  public $cmPluginType = "none";
  
  // Document foreign keys
  public $cmPluginHasMany = array();
  
  // Validation rules for table elements
  // We always need to provide validation values for foreign keys since they are used for the calculation of the implied CO Id
  public $validate = array(
    'link_org_identity_enroller_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A Link Enroller plugin config must be provided',
    ),
    'token' => array(
      'rule' => 'notBlank',
      'required' => false,
      'message' => 'Provide a token',
    ),
    'data' => array(
      'rule' => 'notBlank',
      'required' => true,
      'message' => 'Provide data',
    ),
    'type' => array(
      'rule' => array('inList', array(LinkOrgIdentityTypeEnum::Implicit,
                                      LinkOrgIdentityTypeEnum::Explicit)),
      'required' => true,
      'message' => 'Provide Linking type(Implicit|Explicit)',
    ),
  );

  /**
   * @param $token
   * @return array|null
   */
  public function getStateByToken($token) {
    $args = array();
    $args['conditions']['LinkOrgIdentityState.token'] = $token;
    $args['conditions'][] = "LinkOrgIdentityState.deleted IS NOT true";
    $args['contain'] = false;
    
    return $this->find('first',$args);
  }

  /**
   * @param $id
   */
  public function softDeleteEntry($id) {
    $this->id = $id;
    $this->saveField('deleted', true);
  }
  
}