<?php

App::uses("StandardController", "Controller");
App::uses('Cache', 'Cache');

class LinkOrgIdentityEnrollersController extends StandardController
{
  // Class name, used by Cake
  public $name = "LinkOrgIdentityEnrollers";
  
  // This controller needs a CO to be set
  public $requires_co = true;
  
  public $uses = array(
    "LinkOrgIdentityEnroller.LinkOrgIdentityEnroller",
    "Co",
  );
  
  public function link(){
    $this->log(__METHOD__ . "::link", LOG_DEBUG);
    $registrations = $this->Session->read('Auth.User.Registrations');
    if(isset($registrations)) {
      $this->set('vv_registrations', $registrations);
      // Create a second array with the fields i want to display and everything hidden
      $registrations_display = array();
      foreach($registrations as $reg){
        // Iterate through fields and values for each reg
        $tmp_reg = array();
        $tmp_reg['Full Name'] = $this->LinkOrgIdentityEnroller->maskString($reg[0]['given'], 3, 1) . " " . $this->LinkOrgIdentityEnroller->maskString($reg[0]['family'], 2, 2);
        $tmp_reg['Email'] = $this->LinkOrgIdentityEnroller->maskString($reg[0]['pemail'], 2, 1);
        // XXX For now we will hide the CO name. Keep it for future use or reference
//        $tmp_reg['CO'] = $this->LinkOrgIdentityEnroller->maskString($reg[0]['co'], 2, 3);
        // Keep only one IdP entity ID if i have multiple value entries. Remove the IdP's with no entity ID available
        $single_idp = array();
        foreach ($reg[0]['idp'] as $key => $value) {
          if(!in_array($value, $single_idp) && !empty($value)) {
            $single_idp[$key] = $value;
          }
        }
        $tmp_reg['IdP'] = $single_idp;
        array_push($registrations_display, $tmp_reg);
      }
      $this->set('vv_registrations_display', $registrations_display);
      $this->set('co_id',$this->request->params['named']['co']);
      $this->set('coef',$this->request->params['named']['coef']);
      $this->set('cfg',$this->request->params['named']['cfg']);
      if(!empty($this->request->query)) {
        $this->set('query', serialize(json_encode($this->request->query)));
      }

      // Get the intro text from the configuration
      $this->LinkOrgIdentityEnroller->id = $this->request->params['named']['cfg'];
      $introduction_text = $this->LinkOrgIdentityEnroller->field('introduction_text');
      $this->Set('vv_introduction_text', $introduction_text);
    }
  }
  
  /**
   * Update a LinkOrgIdentityEnroller.
   *
   * @since  COmanage Registry v2.0.0
   */
  
  public function configure() {
    $configData = $this->LinkOrgIdentityEnroller->getConfiguration($this->cur_co['Co']['id']);
    $id = isset($configData['LinkOrgIdentityEnroller']) ? $configData['LinkOrgIdentityEnroller']['id'] : -1;
    
    if($this->request->is('post')) {
      // We're processing an update
      // if i had already set configuration before, now retrieve the entry and update
      if($id > 0){
        $this->LinkOrgIdentityEnroller->id = $id;
        $this->request->data['LinkOrgIdentityEnroller']['id'] = $id;
      }
      
      try {
        /*
         * The check of the fields' values happen in two phases.
         * 1. The framework is responsible to ensure the presentation of all the keys
         * everytime i make a post. We achieve this by setting the require field to true.
         * 2. On the other hand not all fields are required to have a value for all cases. So we apply logic and apply the notEmpty logic
         * in the frontend through Javascript.
         * */
        $save_options = array(
          'validate'  => true,
          'atomic' => true,
          'provisioning' => false,
        );
        
        if($this->LinkOrgIdentityEnroller->save($this->request->data, $save_options)){
          $this->Flash->set(_txt('rs.saved'), array('key' => 'success'));
        } else {
          $invalidFields = $this->LinkOrgIdentityEnroller->invalidFields();
          $this->log(__METHOD__ . "::exception error => ".print_r($invalidFields, true), LOG_DEBUG);
          $this->Flash->set(_txt('rs.link_org_identity_enroller.error'), array('key' => 'error'));
        }
      }
      catch(Exception $e) {
        $this->log(__METHOD__ . "::exception error => ".$e, LOG_DEBUG);
        $this->Flash->set($e->getMessage(), array('key' => 'error'));
      }
      // Redirect back to a GET
      $this->redirect(array('action' => 'configure', 'co' => $this->cur_co['Co']['id']));
    } else {
      $vv_enrollments_list = $this->LinkOrgIdentityEnroller->getEnrollmentFlows($this->request->params['named']['co']);
      $this->set('vv_full_enrollments_list', $vv_enrollments_list);
      if($id > 0){
        $this->set('vv_enable_eofs_save', true);
        // Get the EOFs that are already picked
        $used_eof_ids = Hash::extract($configData['LinkOrgIdentityEof'], '{n}.co_enrollment_flow_id');
        $vv_enrollments_list = array_filter(
          $vv_enrollments_list,
          function($key) use ($used_eof_ids) {
            return !in_array($key, $used_eof_ids);
          },
        ARRAY_FILTER_USE_KEY);
      }
      $this->set('vv_enrollments_list', $vv_enrollments_list);
      // Return the settings
      $this->set('link_org_identity_enrollers', $configData);
    }
  }
  
  public function beforeRender()
  {
    if($this->request->params['action'] == 'configure'){
      // Get the name of the current CO
      $args = array();
      $args['conditions']['Co.id'] = $this->request->params['named']['co'];
      $args['fields'] = array('Co.id', 'Co.name');
      $args['contain'] = false;
  
      $vv_co_list = $this->Co->find('list', $args);

      $this->set('vv_co_list', $vv_co_list);
    }
    list($cmp_list, $shibSession['cmp_attributes_list']) = $this->LinkOrgIdentityEnroller->getAttrValues();
    $this->set('vv_cmp_attributes_list', $cmp_list);
    parent::beforeRender();
  }
  
  function checkWriteFollowups($reqdata, $curdata = NULL, $origdata = NULL) {
    $this->Flash->set(_txt('rs.updated-a3', array(_txt('ct.link_org_identity_enrollers.2'))), array('key' => 'success'));
    return true;
  }
  
  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for auth decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v2.0.0
   * @return Array Permissions
   */
  
  function isAuthorized() {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $roles = $this->Role->calculateCMRoles();
  
    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();
  
    // Determine what operations this user can perform
    $p['configure'] = ($roles['cmadmin'] || $roles['coadmin']);
    $p['link'] = true;
    $p['logout'] = true;
    $this->set('permissions', $p);
    
    return($p[$this->action]);
  }
  
  
  /**
   * For Models that accept a CO ID, find the provided CO ID.
   * - precondition: A coid must be provided in $this->request (params or data)
   *
   * @since  COmanage Registry v2.0.0
   * @return Integer The CO ID if found, or -1 if not
   */
  
  public function parseCOID($data = null) {
    if($this->action == 'configure'
        || $this->action == 'link'
        || $this->action == 'logout') {
      if(isset($this->request->params['named']['co'])) {
        return $this->request->params['named']['co'];
      }
    }
    
    return parent::parseCOID();
  }
}