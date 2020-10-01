<?php

global $cm_lang, $cm_texts;
// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.
$cm_link_org_identity_enroller_texts['en_US'] = array(
  // Titles, per-controller
  'ct.link_org_identity_enroller.1'             => 'Link Organizational Identity Enroller',
  'ct.link_org_identity_enroller.2'             => 'Link Enroller',
  'ct.link_org_identity_enroller.pl'            => 'Link Organizational Identity Enrollers',
  'ct.link_org_identity_enroller.info'          => 'This plugin will run each time an Enrollment Flow starts. The supported flows must be selected in this configuration page. Otherwise the plugin will be skipped. No COU enrollment flows are supported.',
  // Error messages
  'er.link_org_identity_enroller.search'            => 'Search request returned %1$s',
  'er.link_org_identity_enroller.noemail'           => 'Email not provided. Aborting.',
  'er.link_org_identity_enroller.no_aux_path'       => 'Found no Auxiliary Authentication path. Aborting.',
  'er.link_org_identity_enroller.no_remote_user'    => 'Remote User was empty.',
  'er.link_org_identity_enroller.expiration_passed' => 'The invitation time window to link has expired.',
  // Plugin text
  'pl.link_org_identity_enroller.co_name'                  => 'CO Name',
  'pl.link_org_identity_enroller.co_name.desc'             => 'This is the CO Name the enroller plugin belongs to',
  'pl.link_org_identity_enroller.flow'                     => 'Enrollment Flow',
  'pl.link_org_identity_enroller.flow.desc'                => 'Choose the Enrollment Flows to enable the plugin',
  'pl.link_org_identity_enroller.attribute'                => 'Attribute',
  'pl.link_org_identity_enroller.attribute.desc'           => 'This attribute will be checked for duplicates',
  'pl.link_org_identity_enroller.available_users'          => 'Available Users',
  'pl.link_org_identity_enroller.endpoint'                 => 'Logout Endpoint',
  'pl.link_org_identity_enroller.endpoint.desc'            => 'This is the shibd Logout Endpoint used from COmanage SP. You MUST provide one if you enable email confirmation mode. e.g. /shibboleth.sso/Logout',
  'pl.link_org_identity_enroller.aux_auth'                 => 'Auxiliary Authentication',
  'pl.link_org_identity_enroller.aux_auth.desc'            => 'This is the auxiliary authentication path. The path will be appended to the root path. e.g. for a path value "/aux_path" we get https://example.com/aux_path',
  'pl.link_org_identity_enroller.expiration'               => 'Invitation Validity (Minutes)',
  'pl.link_org_identity_enroller.expiration.desc'          => 'When confirming an email address (done via an "invitation"), the length of time (in minutes) the confirmation link is valid for (default is 1 day = 1440 minutes)',
  'pl.link_org_identity_enroller.vsub'                     => 'Subject For Email',
  'pl.link_org_identity_enroller.vsub.desc'                => 'Subject line for email message sent as part of linking redirect flow.',
  'pl.link_org_identity_enroller.subject.link'             => 'Invitation to Link to (@IDENTIFIER)',
  'pl.link_org_identity_enroller.invite.body.link'         => 'You have been invited to link to (@IDENTIFIER).',
  'pl.link_org_identity_enroller.vbody'                    => 'Email Body',
  'pl.link_org_identity_enroller.vbody.desc'               => 'Body for email message sent as part of linking redirect flow. Max 4000 characters.',
  'pl.link_org_identity_enroller.ce'                       => 'Email Confirmation Mode',
  'pl.link_org_identity_enroller.ce.desc'                  => 'See <a href="https://github.com/rciam/comanage-registry/blob/rciam-3.1.x/README.md">README</a> for mode definitions',
  'pl.link_org_identity_enroller.intro'                    => 'Introduction',
  'pl.link_org_identity_enroller.intro.desc'               => 'Optional text to display at the top of the CO Person selection page',
  'pl.link_org_identity_enroller.return'                   => 'Return parameter',
  'pl.link_org_identity_enroller.return.desc'              => 'This is the return query parameter with the Service URL. At the end of linking we will redirect at the url stored in this parameter.',
  'pl.link_org_identity_enroller.idpblacklist'             => 'Identity Provider Blacklist',
  'pl.link_org_identity_enroller.idpblacklist.desc'        => 'Provide the CSV list of Identity Providers that will be excluded from implicit linking',

  
  // Operation
  'op.link_org_identity_enroller.link'                    => 'Link',
  'op.link_org_identity_enroller.action'                  => 'Action',
  'op.link_org_identity_enroller.select-a'                => 'Select %1$s',
  'op.link_org_identity_enroller.abort'                   => 'Abort Linking',
  
  // Database
  'rs.link_org_identity_enroller.error'                   => 'Save failed',
  'rs.link_org_identity_eof.deleted'                 => 'Entry Deleted',
  
  
  'fd.link_org_identity_enroller.user'                    => 'User',
);