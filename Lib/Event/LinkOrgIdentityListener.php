<?php
App::uses('CakeEventListener', 'Event');
App::uses('CakeLog', 'Log');

class LinkOrgIdentityListener implements CakeEventListener
{
  // Define class name for cake
  public $name = "LinkOrgIdentityListener";
  
  public function implementedEvents() {
    return array(
      'Model.afterDelete' => 'cascadeDelete',
    );
  }
  
  public function cascadeDelete($events) {
    CakeLog::write('debug', __METHOD__ . "::@");
    // Get the id of the EOF i am deleting
    $id = $events->subject()->id;
    // Delete all entries in LinkOrgIdentityEoF
    $LinkOrgIdentityEof = ClassRegistry::init('LinkOrgIdentityEof');
    try {
      // deleteAll() will return true even if no records are deleted, as the conditions for the delete query were successful and no matching records remain.
      $LinkOrgIdentityEof->deleteAll(array('LinkOrgIdentityEof.co_enrollment_flow_id' => $id));
      CakeLog::write('debug', __METHOD__ . "::Command to delete EOF:{$id} succeeded.");
    } catch(Exception $e) {
      CakeLog::write('debug', __METHOD__ . "::Delete of EOF:{$id} failed =>" . $e->getMessage());
    }
    
  }
  
}