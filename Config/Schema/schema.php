<?php
class AppSchema extends CakeSchema
{
  
  public function before($event = array())
  {
    return true;
  }
  
  public function after($event = array())
  {
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
CREATE TABLE cm_link_org_identity_eofs(
  id serial PRIMARY KEY,
  co_enrollment_flow_id  INTEGER NOT NULL REFERENCES cm_co_enrollment_flows(id) ON DELETE CASCADE,
  link_org_identity_enroller_id  INTEGER NOT NULL REFERENCES cm_link_org_identity_enrollers(id),
  created TIMESTAMP NOT NULL,
  modified TIMESTAMP NOT NULL,
  deleted BOOLEAN
);

// Console/cake schema create --file schema.php --path /srv/comanage/registry-current/local/Plugin/LinkOrgIdentityEnroller/Config/Schema

ALTER TABLE ONLY public.cm_link_org_identity_enrollers ADD CONSTRAINT cm_link_org_identity_enrollers_co_id_fkey FOREIGN KEY (co_id) REFERENCES public.cm_cos(id);

ALTER TABLE ONLY public.cm_link_org_identity_states ADD CONSTRAINT cm_link_org_identity_states_link_org_identity_enroller_id_fkey FOREIGN KEY (link_org_identity_enroller_id) REFERENCES public.cm_link_org_identity_enrollers(id);

ALTER TABLE ONLY public.cm_link_org_identity_eofs ADD CONSTRAINT cm_link_org_identity_eofs_link_org_identity_enroller_id_fkey FOREIGN KEY (link_org_identity_enroller_id) REFERENCES public.cm_link_org_identity_enrollers(id);
ALTER TABLE ONLY public.cm_link_org_identity_eofs ADD CONSTRAINT cm_link_org_identity_eofs_co_enrollment_flow_id_fkey FOREIGN KEY (co_enrollment_flow_id) REFERENCES public.cm_co_enrollment_flows(id);

GRANT SELECT ON TABLE public.cm_link_org_identity_states TO cmregistryuser_proxy;
GRANT SELECT ON TABLE public.cm_link_org_identity_enrollers TO cmregistryuser_proxy;
GRANT SELECT ON TABLE public.cm_link_org_identity_eofs TO cmregistryuser_proxy;
 */