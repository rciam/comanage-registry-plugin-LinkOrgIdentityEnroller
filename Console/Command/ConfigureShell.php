<?php
/*
 * For execution run:cd /srv/comanage/comanage-registry-current Console/cake configure setup
 * */
class ConfigureShell extends AppShell {
  private $default_www_path = '/var/www/html';
  private $default_registry_path = '/srv/comanage/comanage-registry-current';
  private $reg_def_cron_fn = 'comanage-registry';

  var $uses = array(
    'LinkOrgIdentityEnroller.LinkOrgIdentityEnroller',
    'LinkOrgIdentityEnroller.LinkOrgIdentityEof',
    'LinkOrgIdentityEnroller.LinkOrgIdentityState');

  public function main() {
    $targetVersion = null;
    if(!empty($this->args[0])
       && $this->args[0] !== 'setup') {
      // Use requested target version
      $targetVersion = $this->args[0];
      $fn = '_ug' . $targetVersion;
      if(method_exists($this, $fn)) {
        $this->$fn();
      } elseif($this->args[0] === 'setupdb') {
        $this->setupdb();
      }  elseif($this->args[0] === 'setupcfg') {
        $this->setupcfg();
      }
      else {
        $this->out(_txt('er.ug.fail'));
        $this->out('This version does not exist.');
        exit;
      }
    }
    else {
      $this->out('Please provide target version');
    }
  }


  public function setupdb() {

    $db = ConnectionManager::getDataSource('default');
    $prefix = "";
    if(isset($db->config['prefix'])) {
      $prefix = $db->config['prefix'];
    }

    $query = array();
    //  cm_link_org_identity_enrollers
    $query[] = "ALTER TABLE ONLY " . $prefix . "link_org_identity_enrollers ADD CONSTRAINT ". $prefix . "link_org_identity_enrollers_co_id_fkey FOREIGN KEY (co_id) REFERENCES " . $prefix . "cos(id);";
    // cm_link_org_identity_eofs
    $query[] = "ALTER TABLE ONLY " . $prefix . "link_org_identity_eofs ADD CONSTRAINT ". $prefix . "link_org_identity_eofs_link_org_identity_enroller_id_fkey FOREIGN KEY (link_org_identity_enroller_id) REFERENCES " . $prefix . "link_org_identity_enrollers(id);";
    $query[] = "ALTER TABLE ONLY " . $prefix . "link_org_identity_eofs ADD CONSTRAINT ". $prefix . "link_org_identity_eofs_co_enrollment_flow_id_fkey FOREIGN KEY (co_enrollment_flow_id) REFERENCES " . $prefix . "co_enrollment_flows(id);";
    // cm_link_org_identity_states
    $query[] = "ALTER TABLE ONLY " . $prefix . "link_org_identity_states ADD CONSTRAINT ". $prefix . "link_org_identity_states_link_org_identity_enroller_id_fkey FOREIGN KEY (link_org_identity_enroller_id) REFERENCES " . $prefix . "link_org_identity_enrollers(id);";


    $db->begin();
    try {
      foreach ($query as $idx => $qr) {
        $result = $this->LinkOrgIdentityEnroller->query($qr);
        $this->out('<info>' . ($idx+1) . '. SQL command:</info> ' . $qr);
        $this->out('Query Result: ' . print_r($result, true));
      }
      $db->commit();
    }
    catch(Exception $e) {
      $db->rollback();
      $this->out('<error>' . $e->getMessage() . '</error>');
    }
  }

  public function setupcfg() {
    // Symbolic link
    $www_path = $this->in('www path:', null, $this->default_www_path);
    $registry_path = $this->in('registry path:', null, $this->default_registry_path);
    $plugin_path = $this->in('plugin path:', null, $this->default_registry_path . '/local/Plugin/LinkOrgIdentityEnroller');
    $iss_path = $plugin_path . '/webroot/auth/login';
    // Create the link
    $this->out('<info>Create registry-iss symbolic link if NOT present.</info> ');
    $output = shell_exec('ln -s ' . $iss_path . ' ' . $www_path . '/registry-iss');
    if(!is_null($output)) {
      $this->out($output);
    }
    // Cronjob
    $this->out('<info>Create crontask under /etc/cron.d/</info> ');
    $reg_cron_fn = $this->in('registry cron filename:', null, $this->reg_def_cron_fn);
    $cron_task = "0 * * * * su - www-data -s /bin/bash -c \"cd " . $registry_path . "/app && Console/cake LinkOrgIdentityEnroller.state\"";
    $crond_directory = "/etc/cron.d";
    $file_full_path = $crond_directory . '/' . $reg_cron_fn;
    $fn_reg_cron_handler = fopen($file_full_path, 'a+');
    fwrite($fn_reg_cron_handler, $cron_task . PHP_EOL);
    fclose($fn_reg_cron_handler);

  }
}
