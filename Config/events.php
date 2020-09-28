<?php
// Load the Local listeners we created
require_once LOCAL . 'Plugin' . DS . 'LinkOrgIdentityEnroller' . DS . 'Lib' . DS . 'Event' . DS . 'LinkOrgIdentityListener.php';
// Load the frameworks Utility library
App::uses('ClassRegistry', 'Utility');

// Attach the LinkOrgIdenityListener to event Model.afterDelete for the specific Model
// CoEnrollmentFlow
$CoEnrollmentFlow = ClassRegistry::init('CoEnrollmentFlow');
$linkOrgIdentityL = new LinkOrgIdentityListener();
$CoEnrollmentFlow->getEventManager()->attach($linkOrgIdentityL);
