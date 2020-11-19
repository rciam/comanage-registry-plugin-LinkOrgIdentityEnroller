<?php

App::uses('CoPetitionsController', 'Controller');
App::uses('Cache', 'Cache');
App::uses('CakeEmail', 'Network/Email');

class LinkOrgIdentityEnrollerCoPetitionsController extends CoPetitionsController
{
  // Class name, used by Cake
  public $name = "LinkOrgIdentityEnrollerCoPetitions";
  public $in_reauth = false;
  public $components = array(
    'Security' => array(
      'csrfUseOnce' => true,
    )
  );
  private $redirect_location = "/";

  public $uses = array(
    "CoEnrollmentFlow",
    "OrgIdentity",
    "CoGroupMember",
    "CoGroup",
    "AuthenticationEvent",
    "CoInvite",
    "CoPetition", // this is mandatory for the enroller plugin
    "LinkOrgIdentityEnroller.LinkOrgIdentityEnroller",
    "LinkOrgIdentityEnroller.LinkOrgIdentityState");
  
  /**
   * Enrollment Flow selectOrgIdentity (authenticate mode)
   *
   * @param Integer $id CO Petition ID
   * @param Array $oiscfg Array of configuration data for this plugin
   * @param Array $onFinish URL, in Cake format
   * @param Integer $actorCoPersonId CO Person ID of actor
   * @since  COmanage Registry v3.1.1
   */
  protected function execute_plugin_start($id, $onFinish)
  {
    $this->log(__METHOD__ . "::@", LOG_DEBUG);
    $eaf = !empty($this->request->params['named']['eaf']) ?
      $this->request->params['named']['eaf']
      : 0;
    $fullBsUrl = Configure::read('App.fullBaseUrl');
    
    if($eaf){
      // get the data from the database
      $state_db_entry = $this->LinkOrgIdentityState->getStateByToken($this->request->params['named']['token']);
      // Soft delete the state entry
      $this->LinkOrgIdentityState->softDeleteEntry($state_db_entry['LinkOrgIdentityState']['id']);
      // Check if it has expired
      $dataTable = json_decode(unserialize($state_db_entry['LinkOrgIdentityState']['data']), true);
      // Get the configuration
      $loiecfg = $this->LinkOrgIdentityEnroller->getConfiguration($dataTable['registered_user']['co_id']);
      // Get the attribute holding the remote user
      $user_id_attribute = $loiecfg['LinkOrgIdentityEnroller']['user_id_attribute'];
      
      // Calculate if the linking petition is still valid
      $exp_window = (int)$loiecfg['LinkOrgIdentityEnroller']['exp_window'];
      $startDate = strtotime($state_db_entry['LinkOrgIdentityState']['modified']);
      $curDateTime = date('Y-m-d H:i:s');
      $endDate = strtotime($curDateTime);
      $diff_minutes = (int)(abs($endDate - $startDate)/60);
      if($diff_minutes > $exp_window  || (!empty($this->request->params['named']['noremote']) && $this->request->params['named']['noremote'])){
        $msg = ((!empty($this->request->params['named']['noremote']) && $this->request->params['named']['noremote'])) ?
          "Remote User was empty." : "The invitation time window to link has expired.";
          //_txt('er.link_org_identity_enroller.no_remote_user') : _txt('er.link_org_identity_enroller.expiration_passed');
        $this->Flash->set($msg, array('key' => 'error'));
        $this->redirect($this->redirect_location);
      }
      // Return if there is no remote
      
      // For the case we are linking from inside a user's profile, fetch and store the new data
      if(empty($dataTable['cmp_attributes_list'])) {
        $attrValuesArray = !empty($this->request->data) ? $this->request->data : null;
        list($cmp_list, $dataTable['cmp_attributes_list']) = $this->LinkOrgIdentityEnroller->getAttrValues($attrValuesArray);
        // Check if this user_id_attribute identifier already exists
        if($this->LinkOrgIdentityEnroller->findDuplicateOrgId($dataTable['cmp_attributes_list'][$user_id_attribute], $dataTable['registered_user']['co_id'])) {
          $this->Flash->set(_txt('er.ia.exists',
            array(filter_var($dataTable['cmp_attributes_list'][$user_id_attribute],FILTER_SANITIZE_SPECIAL_CHARS))),
            array('key' => 'error'));
          $this->redirect_location = $fullBsUrl . '/registry/co_people/canvas/'. $dataTable['registered_user']['co_person_id'];
          $this->redirect($this->redirect_location);
        }
      }

      $this->log(__METHOD__ . "::dataTable['cmp_attributes_list'] => " .print_r($dataTable['cmp_attributes_list'],true), LOG_DEBUG);
      $verified = (preg_match("/mail/i", $loiecfg['LinkOrgIdentityEnroller']['cmp_attribute_name'])!= false ) ? true : false;
      // Try to save the schema to the database with only one transaction
      if ($this->LinkOrgIdentityEnroller->createOrgIdentity($dataTable['registered_user'],
                                                            $dataTable['cmp_attributes_list'],
                                                            $verified,
                                                            $loiecfg['LinkOrgIdentityEnroller'])) {
        // Inform the user
        $this->Flash->set(_txt('rs.saved'), array('key' => 'success'));
        // The default redirect uri will the profile/canvas of the user
        $this->redirect_location = $fullBsUrl . '/registry/co_people/canvas/'. $dataTable['registered_user']['co_person_id'];
        // Check if there is a final service/target_new to redirect to
        $return = $loiecfg['LinkOrgIdentityEnroller']['return'];
        // TODO: Add to history
        if(!empty($return) && !empty($dataTable['query'][$return])){
          $this->redirect_location = $dataTable['query'][$return];
        } else {
          // Now that i added the idp successfully and since there is no target_new to redirect
          // Recalculate the session
          $this->loginSessionRebuild();
        }
      } else {
        // An error just happened during DB transaction. Inform the user and redirect to registry home page
        $this->Flash->set(_txt('er.db.save'), array('key' => 'error'));
        $this->log(__METHOD__ . "::validation errors => " .print_r($this->OrgIdentity->validationErrors,true), LOG_DEBUG);
        $this->redirect_location = "/";
      }
      // Now redirect to the appropriate location
      $this->redirect($this->redirect_location);
    }
    
    /*
     *  THIS IS THE ENTRY OF THE FIRST PASS
     * */
    // Get the EnrollmentFlows list and the CoEnrollmentAttributes
    $args = array();
    $args['conditions']['CoEnrollmentFlow.id'] = $this->request->params['named']['coef'];
    $args['conditions'][] = 'not CoEnrollmentFlow.deleted';
    $args['contain'] = array('CoEnrollmentAttribute');
    $args['fields'] = array(
      'CoEnrollmentFlow.co_id',
      'CoEnrollmentFlow.name',
      'CoEnrollmentFlow.authz_level',
      'CoEnrollmentFlow.match_policy');
    $eof_ea = $this->CoEnrollmentFlow->find('first',$args);
    unset($args);
    
    // Get the configuration
    $loiecfg = $this->LinkOrgIdentityEnroller->getConfiguration($eof_ea['CoEnrollmentFlow']['co_id']);

    /*
     * Redirect onFinish, in the case that the plugin is:
     * 1. disabled
     * 2. No EOFs have been picked
     * 3. The EOF that invoked the plugin is not part of the list picked EOFs
     */
    if ( empty($loiecfg['LinkOrgIdentityEof_list'])
         || !array_key_exists($this->request->params['named']['coef'], $loiecfg['LinkOrgIdentityEof_list'])
         || $loiecfg['LinkOrgIdentityEnroller']['status'] !== LinkOrgIdentityStatusEnum::Active) {
      $this->log(__METHOD__ . "::Plugin is not supported for this enrollment ", LOG_DEBUG);
      $this->redirect($onFinish);
    }
    // XXX Default to Explicit Linking
    $linking_type = LinkOrgIdentityTypeEnum::Explicit;
    $this->Session->write('Plugin.LinkOrgIdentityEnroller.type', LinkOrgIdentityTypeEnum::Explicit);

    // XXX Explicit Linking
    if($eof_ea['CoEnrollmentFlow']['authz_level'] !== EnrollmentAuthzEnum::AuthUser &&
      $eof_ea['CoEnrollmentFlow']['authz_level'] !== EnrollmentAuthzEnum::None &&
      $eof_ea['CoEnrollmentFlow']['match_policy'] === EnrollmentMatchPolicyEnum::Self){
      $target = array();
      $target['action'] = 'logout';
      $target['controller'] = Inflector::tableize($this->name);
      $target['plugin'] = Inflector::singularize(Inflector::tableize($this->plugin));
      $target['co'] = (int)$eof_ea['CoEnrollmentFlow']['co_id'];
      $target['coef'] = (int)$onFinish['coef'];
      $target['cfg'] = (int)$loiecfg['LinkOrgIdentityEnroller']['id'];
      if (!empty($this->request->query)) {
        $target['?'] = $this->request->query;
      }
      $this->redirect($target);
    }
    // XXX Continue with the Enrollment Flow
    if($eof_ea['CoEnrollmentFlow']['authz_level'] === EnrollmentAuthzEnum::None ) {
      $this->redirect($onFinish);
    }

    // XXX Implicit Linking
    if ( $eof_ea['CoEnrollmentFlow']['authz_level'] === EnrollmentAuthzEnum::AuthUser ) {
      $linking_type = LinkOrgIdentityTypeEnum::Implicit;
      $this->Session->write('Plugin.LinkOrgIdentityEnroller.type', LinkOrgIdentityTypeEnum::Implicit);
      // Let's check if the Identifier is already in the registry for the current CO
      $duplicate = $this->LinkOrgIdentityEnroller->findDuplicateOrgId($this->Session->read('Auth.User.username'), $eof_ea['CoEnrollmentFlow']['co_id']);
      if ($duplicate) {
        // The default redirect uri will the profile/canvas of the user
        $this->log(__METHOD__ . "::Found duplicate.");
        $this->redirect_location = $fullBsUrl . '/registry';
        if(!empty($this->Session->read('Auth.User.co_person_id'))) {
          $this->redirect_location = $fullBsUrl . '/registry/co_people/canvas/' . $this->Session->read(
              'Auth.User.co_person_id'
            );
        }
        $this->Flash->set(_txt('er.ia.exists',
          array(filter_var($this->Session->read('Auth.User.username'),FILTER_SANITIZE_SPECIAL_CHARS))),
          array('key' => 'error'));
        // Check if there is a final service/target_new to redirect to
        $return = $loiecfg['LinkOrgIdentityEnroller']['return'];
        if (!empty($return) && !empty($this->request->params['named'][$return])) {
          $this->redirect_location = $this->request->params['named'][$return];
          $this->Session->destroy('Message.flash');
        }
        $this->redirect($this->redirect_location);
      }
    }
    // Get the env attribute that we will use for comparison
    $attribute_type = $loiecfg['LinkOrgIdentityEnroller']['cmp_attribute_name'];
    $attribute_value = !empty(getenv($attribute_type)) ?
      getenv($attribute_type)
      : "";
    
    // XXX Continue with the Enrollment Flow if the attribute is not available in the environment
    if (empty($attribute_value)) {
      $this->log(__METHOD__ . "::attribute value is empty. The enrollment flow will proceed in default mode.");
      $this->redirect($onFinish);
    }
    // If we want to add and idp in the registered user, means that the auth array in the Session will have CO entries and won't be empty
    // Now that i have the type and value i should check the registry
    list($registrations, $orgIdentities_list) = $this->LinkOrgIdentityEnroller->getCoPersonMatches(
      $attribute_type,
      $attribute_value,
      $eof_ea['CoEnrollmentFlow']['co_id'],
      $loiecfg['LinkOrgIdentityEnroller']['idp_blacklist']);
    
    // XXX If there are no registered users and this is an Implicit Linking continue with the enrollment
    if(empty($registrations)){
      $this->redirect($onFinish);
    }
    
    $target = array();
    $this->Session->write('Auth.User.Registrations', $registrations);
    $this->Session->write('Auth.User.OrgIdentities', $orgIdentities_list);
    $target['action'] = 'link';
    $target['controller'] = Inflector::tableize($this->plugin);
    $target['plugin'] = Inflector::singularize(Inflector::tableize($this->plugin));
    $target['co'] = (int)$eof_ea['CoEnrollmentFlow']['co_id'];
    $target['coef'] = (int)$onFinish['coef'];
    $target['cfg'] = (int)$loiecfg['LinkOrgIdentityEnroller']['id'];
    if (!empty($this->request->query)) {
      $target['?'] = $this->request->query;
    }
  
    $this->redirect($target);
  }
  
  /**
   * Process petitioner attributes
   *
   * @since  COmanage Registry v3.1.1
   */
  protected function execute_plugin_petitionerAttributes($id, $onFinish) {
    // The step is done
    $this->redirect($onFinish);
  }
  
  /**
   * Callback before other controller methods are invoked or views are rendered.
   * - postcondition: If invalid enrollment flow provided, session flash message set
   *
   * @since  COmanage Registry v3.1.1
   */
  
  function beforeFilter() {
    // We need some special authorization logic here, depending on the type of flow.
    // This is loosely based on parent::beforeFilter().
    $noAuth = false;
    
    // For self signup, we simply require a token (and for the token to match).
    if( $this->action == "processConfirmation"
      || $this->action == "collectIdentifier"
      || $this->action == "duplicateCheck"
      || $this->action == "checkEligibility"
      || $this->action == "sendApprovalNotification"
      || $this->action == "finalize"
      || $this->action == "provision"
      || $this->action == "redirectOnConfirm") {
      $token = $this->CoPetition->field('enrollee_token', array('CoPetition.id' => $this->parseCoPetitionId()));
    } else if ( $this->action == "start" &&
                isset($this->request->params['named']['eaf']) &&
                $this->request->params['named']['eaf'] == 1) {
      if (!empty($this->LinkOrgIdentityState->getStateByToken($this->request->params['named']['token']))) {
        //TODO: improve the security here
        $token = $this->request->params['named']['token'];
        $this->Security->validatePost = false;
        $this->Security->enabled = false;
        $this->Security->csrfCheck = false;
      }
    }
    else {
      $token = $this->CoPetition->field('petitioner_token', array('CoPetition.id' => $this->parseCoPetitionId()));
    }
    $passedToken = $this->parseToken();
    
    if($token && $token != '' && $passedToken) {
      if($token == $passedToken) {
        // If we were passed a reauth flag, we require authentication even though
        // the token matched. This enables account linking.
        if(!isset($this->request->params['named']['reauth'])
          || $this->request->params['named']['reauth'] != 1) {
          $noAuth = true;
        } else {
          // Store a hint for isAuthorized that we matched the token and are reauthenticating,
          // so we can authorize the transaction.
          $this->in_reauth = true;
        }
        
        // Dump the token into a viewvar in case needed
        $this->set('vv_petition_token', $token);
      } else {
        $this->Flash->set(_txt('er.token'), array('key' => 'error'));
        $this->redirect($this->redirect_location);
      }
    }
    
    if($noAuth) {
      $this->Auth->allow($this->action);
    }
  }
  
  
  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v3.1.1
   * @return Array Permissions
   */
  
  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();
    
    $petitionId = $this->parseCoPetitionId();
    $curToken = null;
  
    // For self signup, we simply require a token (and for the token to match)
    if($petitionId) {
      $curToken = $this->CoPetition->field('petitioner_token', array('CoPetition.id' => $this->parseCoPetitionId()));
    }
    
    
    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();
    
    // Signup based collection, we need the user in the petition.
    // Note we can't invalidate this token because for the duration of the enrollment
    // $REMOTE_USER may or may not match a valid login identifier (though probably it should).
    $p['start'] = ($curToken == $this->parseToken());
    // Here we need a valid user
    $p['petitionerAttributes'] = empty($this->Session->check('Auth.User')) ?
                                 false :
                                 $this->Session->check('Auth.User');
  
    // Probably an account linking being initiated, so we need a valid user
    $p['selectOrgIdentityAuthenticate'] = $roles['copersonid'] || $this->in_reauth;
    
    $this->set('permissions', $p);
    return $p[$this->action];
  }
  
  
  public function logout() {
    // Add to the request and the database the values we need
    $shibSession = array();
    $shibSession['registered_user']['co_person_id'] = !empty($this->request->data['LinkOrgIdentityEnroller']['co_person_id']) ?
      $this->request->data['LinkOrgIdentityEnroller']['co_person_id']
      : $this->Session->read('Auth.User.co_person_id');
    $shibSession['registered_user']['cfg'] = !empty($this->request->data['LinkOrgIdentityEnroller']['cfg']) ?
      $this->request->data['LinkOrgIdentityEnroller']['cfg']
      : $this->request->params['named']['cfg'];
    $shibSession['registered_user']['co_id'] = !empty($this->request->data['LinkOrgIdentityEnroller']['co_id']) ?
      $this->request->data['LinkOrgIdentityEnroller']['co_id']
      : $this->request->params['named']['co'];
    // Save all the query params and retrieve them as soon as you return from authentication
    // Query params contain the target new url
    if (!empty($this->request->data['LinkOrgIdentityEnroller']['query'])) {
      $shibSession['query'] = json_decode(unserialize($this->request->data['LinkOrgIdentityEnroller']['query']));
    }
    // Get the identifier of type epuid of the registered user
    try {
      // todo: Move this into the Model
      // todo: Identifier should be configurable. We should not force type to ePUID
      $this->Identifier = ClassRegistry::init('Identifier');
      $identifier_registered_user = $this->Identifier->find('first', array(
        'contain' => false,
        'fields' => array('identifier'),
        'conditions' => array(
          'co_person_id' => $shibSession['registered_user']['co_person_id'],
          'type' => IdentifierEnum::ePUID,
        )
      ));
      $shibSession['registered_user']['identifier'] = !empty($identifier_registered_user['Identifier']['identifier']) ?
        $identifier_registered_user['Identifier']['identifier']
        : "";
    } catch (Exception $e){
      $this->log(__METHOD__ . "::an error occurred while retrieving the identifier => ". $e, LOG_DEBUG);
    }
    // If the user is not registered, this means that we are during a SignUp EOF), get the environmental variables and match them with the cmp attributes
    // TODO:Check if i use this multiple times. If so use CACHE to store the result and reuse
    // TODO: like in here: local/Plugin/LinkOrgIdentityEnroller/Controller/LinkOrgIdentityEnrollersController.php:132
    if(empty($this->Session->read('Auth.User.cos'))) {
      list($cmp_list, $shibSession['cmp_attributes_list']) = $this->LinkOrgIdentityEnroller->getAttrValues();
    }
    // Serialize the environmental variable and save them to the database
    $attrs = serialize(json_encode($shibSession));
    // Create a token for validation
    $token = Security::generateAuthKey();
    // The Idphint variable is the entity id of OrgIdentity. We will urlencode it and we will
    // add them to the redirect uri
    $orgIdentitiesList = $this->Session->read('Auth.User.OrgIdentities');
    $coef = !empty($this->request->data['LinkOrgIdentityEnroller']['coef']) ?
      $this->request->data['LinkOrgIdentityEnroller']['coef']
      : $this->request->params['named']['coef'];
    // Save the data into my table
    $data = array();
    $data['link_org_identity_enroller_id'] = $shibSession['registered_user']['cfg'];
    $data['token'] = $token;
    $data['data'] = $attrs;
    $data['type'] =$this->Session->read('Plugin.LinkOrgIdentityEnroller.type');
    // Save everything to database and logout or redirect
    if($this->LinkOrgIdentityState->save($data)){
      $this->log(__METHOD__ . "::successfuly saved the data", LOG_DEBUG);
      unset($data);
    } else {
      $invalidFields = $this->LinkOrgIdentityState->invalidFields();
      $this->log(__METHOD__ . '::exception error => ' . print_r($invalidFields, true), LOG_DEBUG);
      $this->Flash->set("Database(Link State) save failed.", array('key' => 'error'));
    }
  
    // Redirect to the linking enrollment flow
    $fullBsUrl = Configure::read('App.fullBaseUrl');
    $return = "/registry/link_org_identity_enroller/link_org_identity_enroller_co_petitions/start" .
      "/token:{$token}" .
      "/eaf:1" .
      "/coef:{$coef}";
    $urlenc_return = urldecode($return);
    if (!empty($this->request->data['LinkOrgIdentityEnroller']['idpHint'])
        && !empty($orgIdentitiesList)
        && !empty($orgIdentitiesList[$this->request->data['LinkOrgIdentityEnroller']['idpHint']])) {
      $idphint = urlencode($orgIdentitiesList[$this->request->data['LinkOrgIdentityEnroller']['idpHint']]);
      $urlenc_return .= "?idphint={$idphint}";
    } else {
      $this->log(__METHOD__ . "::No idpHint parameter or OrgIdentity list is empty.", LOG_DEBUG);
    }
  
    $this->LinkOrgIdentityEnroller->id = $shibSession['registered_user']['cfg'];
    $emailMode = $this->LinkOrgIdentityEnroller->field('email_redirect_mode');
    // If the email verification is disabled then redirect for login without logging out
    if($emailMode === LinkOrgIdentityRedirectModeEnum::Disabled) {
      $aux_auth_path = $this->LinkOrgIdentityEnroller->field('aux_auth');
      if(empty($aux_auth_path)) {
        // I do not know where to go. So throw a message and redirect
        $this->Flash->set(_txt('er.link_org_identity_enroller.no_aux_path'), array('key' => 'error'));
        $this->log(__METHOD__ . "::Found no Auxiliary Authentication path.", LOG_DEBUG);
        // if there is no aux path i should continue with the default flow. Else i will have a dead lock
        $continue = array(
          'plugin'      => null,
          'controller'  => 'co_petitions',
          'action'      => 'start',
          'coef'        => $coef,
          'done'        => $this->plugin,
        );
        $this->redirect($continue);
      }
      $registryIssUrl = $fullBsUrl . $aux_auth_path;
      $this->redirect($registryIssUrl. "?return=" . $fullBsUrl . $urlenc_return);
    }
    
    /*
     * LOGOUT AND SEND EMAIL CONFIRMATION
     * */
    // Manually logout the user
    $this->Session->delete('Auth.User');
    $this->Session->delete('User');
    $this->Session->destroy();
  
    // Get the configuration and retrieve the logout endpoint as well as the expiration time window
    $shibLogout = $this->LinkOrgIdentityEnroller->field('logout_endpoint');
    // Prepare and send the email if the mode is set to Automatic
    // Add the body and subject and substitute the placeholders
    $subs = array(
      'IDENTIFIER' => $shibSession['registered_user']['identifier'],
    );
    $subject = $this->LinkOrgIdentityEnroller->field('verification_subject');
    $subject = processTemplate($subject, $subs);
    $body = $this->LinkOrgIdentityEnroller->field('verification_body');
    $body = processTemplate($body, $subs);
    $msgBd = $body . "\n" . "Follow the link: " . $fullBsUrl . $return;
    // Send an email with the redirect link needed after logout
    // Now that i found all the data that i need start the process of sending and saving
    // Send the email
    $status = $this->LinkOrgIdentityEnroller->sendEmail($msgBd, getenv('mail'), $subject, null);
    if($status){
      $this->log(__METHOD__ . "::successfully sent email", LOG_DEBUG);
    } else{
      $this->log(__METHOD__ . "::Sending email failed. No Exception returned", LOG_DEBUG);
    }
    // We are doing this since for some occasions the return is not working. These are the SLO configured IdPs
    // This is for logging out and redirecting to the enrollment
    // TODO: Make immediate redirect a configuration
    //$this->redirect($fullBsUrl . $shibLogout. "?return=" . urlencode($return));
    $this->redirect($fullBsUrl . $shibLogout);
  }
  
  
  /**
   * This function is the sort version of the login() action in the UsersController.
   * Our goal is to recalculate the Session variables to match the new status after the addition of the new IdP in the CO
   * We want to move the function in the Model but the Session is not available there.
   * TODO: What is the best practice for this. What should i do with the Session??
   */
  private function loginSessionRebuild() {
    $u = $this->Session->read('Auth.User.username');
    
    if(!empty($u)) {
      // This is an Org Identity. Figure out which Org Identities this username
      // (identifier) is associated with. First, pull the identifiers.
      
      // We use $oargs here instead of $args because we may reuse this below
      $oargs = array();
      $oargs['joins'][0]['table'] = 'identifiers';
      $oargs['joins'][0]['alias'] = 'Identifier';
      $oargs['joins'][0]['type'] = 'INNER';
      $oargs['joins'][0]['conditions'][0] = 'OrgIdentity.id=Identifier.org_identity_id';
      $oargs['conditions']['Identifier.identifier'] = $u;
      $oargs['conditions']['Identifier.login'] = true;
      // Join on identifiers that aren't deleted (including if they have no status)
      $oargs['conditions']['OR'][] = 'Identifier.status IS NULL';
      $oargs['conditions']['OR'][]['Identifier.status <>'] = SuspendableStatusEnum::Suspended;
      // As of v2.0.0, OrgIdentities have validity dates, so only accept valid dates (if specified)
      // Through the magic of containable behaviors, we can get all the associated
      $oargs['conditions']['AND'][] = array(
        'OR' => array(
          'OrgIdentity.valid_from IS NULL',
          'OrgIdentity.valid_from < ' => date('Y-m-d H:i:s', time())
        )
      );
      $oargs['conditions']['AND'][] = array(
        'OR' => array(
          'OrgIdentity.valid_through IS NULL',
          'OrgIdentity.valid_through > ' => date('Y-m-d H:i:s', time())
        )
      );
      // data we need in one clever find
      $oargs['contain'][] = 'PrimaryName';
      $oargs['contain'][] = 'Identifier';
      $oargs['contain']['CoOrgIdentityLink']['CoPerson'][0] = 'Co';
      $oargs['contain']['CoOrgIdentityLink']['CoPerson'][1] = 'CoPersonRole';
      $oargs['contain']['CoOrgIdentityLink']['CoPerson']['CoGroupMember'] = 'CoGroup';
      
      $orgIdentities = $this->OrgIdentity->find('all', $oargs);
      
      // Grab the org IDs and CO information
      $orgs = array();
      $cos = array();
      
      foreach($orgIdentities as $o) {
        $orgs[] = array(
          'org_id' => $o['OrgIdentity']['id'],
          'co_id' => $o['OrgIdentity']['co_id']
        );
        
        foreach($o['CoOrgIdentityLink'] as $l)
        {
          // If org identities are pooled, OrgIdentity:co_id will be null, so look at
          // the identity links to get the COs (via CO Person).
          
          $cos[ $l['CoPerson']['Co']['name'] ] = array(
            'co_id' => $l['CoPerson']['Co']['id'],
            'co_name' => $l['CoPerson']['Co']['name'],
            'co_person_id' => $l['co_person_id'],
            'co_person' => $l['CoPerson']
          );
          
          // And assemble the Group Memberships
          
          $params = array(
            'conditions' => array(
              'CoGroupMember.co_person_id' => $l['co_person_id']
            ),
            'contain' => false
          );
          $memberships = $this->CoGroupMember->find('all', $params);
          
          foreach($memberships as $m){
            $params = array(
              'conditions' => array(
                'CoGroup.id' => $m['CoGroupMember']['co_group_id']
              ),
              'contain' => false
            );
            $result = $this->CoGroup->find('first', $params);
            
            if(!empty($result)) {
              $group = $result['CoGroup'];
              
              $cos[ $l['CoPerson']['Co']['name'] ]['groups'][ $group['name'] ] = array(
                'co_group_id' => $m['CoGroupMember']['co_group_id'],
                'name' => $group['name'],
                'member' => $m['CoGroupMember']['member'],
                'owner' => $m['CoGroupMember']['owner']
              );
            }
          }
        }
      }
      
      $this->Session->write('Auth.User.org_identities', $orgs);
      $this->Session->write('Auth.User.cos', $cos);
      
      // Use the primary organizational name as the session name.
      
      if(isset($orgIdentities[0]['PrimaryName'])) {
        $this->Session->write('Auth.User.name', $orgIdentities[0]['PrimaryName']);
      }
      
      // Determine last login for the identifier. Do this before we record
      // the current login. We don't currently check identifiers associated with
      // other Org Identities because doing so would be a bit challenging...
      // we're logging in at a platform level, which COs do we query? For now,
      // someone who wants more login details can view them via their canvas.
      
      $lastlogins = array();
      
      if(!empty($orgIdentities[0]['Identifier'])) {
        foreach($orgIdentities[0]['Identifier'] as $id) {
          if(!empty($id['identifier']) && isset($id['login']) && $id['login']) {
            $lastlogins[ $id['identifier'] ] = $this->AuthenticationEvent->lastlogin($id['identifier']);
          }
        }
      }
    } else {
      throw new RuntimeException(_txt('er.auth.empty'));
    }
  }
}
