<?php

/**
 * Enable REST. These *MUST* come before the default CakePHP routes.
 */

Router::mapResources(array(
  'link_org_identity_eofs'
));
Router::parseExtensions();

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
require CAKE . 'Config' . DS . 'routes.php';
