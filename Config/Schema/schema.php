<?php

class AppSchema extends CakeSchema {
  public $connection = 'default';

  public function before($event = array()) {
    return true;
  }

  public function after($event = array()) {
    return true;
  }

  public $link_org_identity_enrollers = array(
    'id' => array('type' => 'integer', 'autoIncrement' => true, 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
    'co_id' => array('type' => 'integer', 'null' => true, 'length' => 10),
    'status' => array('type' => 'string', 'null' => false, 'length' => 1),
    'cmp_attribute_name' => array('type' => 'string', 'null' => false, 'length' => 80),
    'email_redirect_mode' => array('type' => 'string', 'null' => false, 'length' => 1),
    'verification_subject' => array('type' => 'string', 'null' => false, 'length' => 256),
    'verification_body' => array('type' => 'string', 'null' => false, 'length' => 4000),
    'introduction_text' => array('type' => 'string', 'null' => false, 'length' => 4000),
    'idp_blacklist' => array('type' => 'string', 'null' => false, 'length' => 4000),
    'logout_endpoint' => array('type' => 'string', 'null' => false, 'length' => 80),
    'aux_auth' => array('type' => 'string', 'null' => false, 'length' => 80),
    'user_id_attribute' => array('type' => 'string', 'null' => false, 'length' => 64),
    'issuer_dn_attribute' => array('type' => 'string', 'null' => true, 'length' => 32),
    'subject_dn_attribute' => array('type' => 'string', 'null' => true, 'length' => 32),
    'return' => array('type' => 'string', 'null' => false, 'length' => 50),
    'exp_window' => array('type' => 'integer', 'null' => true, 'length' => 10),
    'created' => array('type' => 'datetime', 'null' => true),
    'modified' => array('type' => 'datetime', 'null' => true),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1),
    )
  );

  public $link_org_identity_states = array(
    'id' => array('type' => 'integer', 'autoIncrement' => true, 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
    'link_org_identity_enroller_id' => array('type' => 'integer', 'null' => true, 'length' => 10),
    'token' => array('type' => 'string', 'null' => false, 'length' => 80),
    'data' => array('type' => 'string', 'null' => false, 'length' => 2048),
    'type' => array('type' => 'string', 'null' => false, 'length' => 2),
    'created' => array('type' => 'datetime', 'null' => false),
    'modified' => array('type' => 'datetime', 'null' => true),
    'deleted' => array('type' => 'boolean', 'null' => false, 'default' => 'f'),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1),
      'link_org_identity_states_i1' => array('column' => 'token'),
    )
  );

  public $link_org_identity_eofs = array(
    'id' => array('type' => 'integer', 'autoIncrement' => true, 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
    'co_enrollment_flow_id' => array('type' => 'integer', 'null' => false, 'length' => 10),
    'link_org_identity_enroller_id' => array('type' => 'integer', 'null' => true, 'length' => 10),
    'mode' => array('type' => 'string', 'null' => true, 'length' => 1),
    'created' => array('type' => 'datetime', 'null' => false),
    'modified' => array('type' => 'datetime', 'null' => true),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1),
    )
  );
}


/**
 // Console/cake schema create --file schema.php --path /srv/comanage/registry-current/local/Plugin/LinkOrgIdentityEnroller/Config/Schema
 ALTER TABLE ONLY cm_link_org_identity_enrollers ADD CONSTRAINT cm_link_org_identity_enrollers_co_id_fkey FOREIGN KEY (co_id) REFERENCES cm_cos(id);
 ALTER TABLE ONLY cm_link_org_identity_states ADD CONSTRAINT cm_link_org_identity_states_link_org_identity_enroller_id_fkey FOREIGN KEY (link_org_identity_enroller_id) REFERENCES cm_link_org_identity_enrollers(id);
 ALTER TABLE ONLY cm_link_org_identity_eofs ADD CONSTRAINT cm_link_org_identity_eofs_link_org_identity_enroller_id_fkey FOREIGN KEY (link_org_identity_enroller_id) REFERENCES cm_link_org_identity_enrollers(id);
 ALTER TABLE ONLY cm_link_org_identity_eofs ADD CONSTRAINT cm_link_org_identity_eofs_co_enrollment_flow_id_fkey FOREIGN KEY (co_enrollment_flow_id) REFERENCES cm_co_enrollment_flows(id);
 */