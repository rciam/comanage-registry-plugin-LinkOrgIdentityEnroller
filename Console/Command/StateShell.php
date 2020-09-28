<?php
/*
 * For execution run: Console/cake LinkOrgIdentityEnroller.state
 * Crontab entry(run everyday at midnight:
   m h  dom mon dow   command
   0 0 * * * /path/to/comanage/app && Console/cake state
 * */
class StateShell extends AppShell {
  public $uses = array('LinkOrgIdentityState');
  public function main() {
    // Soft delete the entries that are older than one day
    $this->delYesterdayEntries();
  }
  
  public function delYesterdayEntries() {
    $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
    if ( $this->LinkOrgIdentityState->updateAll( array('LinkOrgIdentityState.deleted' => true),
                                                 array(
                                                    'LinkOrgIdentityState.created <=' => $yesterday,
                                                    'LinkOrgIdentityState.deleted' => false
                                                 )
                                               )
    ) {
      $this->log(__METHOD__ . "::soft delete::affected rows => ". $this->LinkOrgIdentityState->getAffectedRows(), LOG_INFO);
    } else {
      $this->log(__METHOD__ . "::soft delete::affected rows => ". $this->LinkOrgIdentityState->getAffectedRows(), LOG_INFO);
    }
  }
}