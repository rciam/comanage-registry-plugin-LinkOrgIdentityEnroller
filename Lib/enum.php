<?php

class LinkOrgIdentityRedirectModeEnum
{
  const Enabled  = 'E';
  const Disabled = 'X';
  const type      = array(
      'E' => 'Enabled',
      'X' => 'Disabled',
  );
}

class LinkOrgIdentityEofModeEnum
{
  const Signup  = 'S';
  const Link = 'L';
  const Undefined = 'U';
  const type      = array(
    'S' => 'Signup',
    'L' => 'Link',
    'U' => 'Undefined',
  );
}

class LinkOrgIdentityTypeEnum
{
  const Implicit           = 'IM';
  const Explicit           = 'EX';
  const type = array(
    'IM' => 'Implicit',
    'EX' => 'Explicit',
  );
}

class LinkOrgIdentityStatusEnum
{
  const Active              = 'A';
  const Suspended           = 'S';
  const type = array(
    'A' => 'Active',
    'S' => 'Suspended',
  );
}

class LinkOrgIdentityRestActionsEnum
{
  const SEARCH = 'search';
  const UPDATE = 'update';
  const type = array(
    'search' => 'Search',
    'update' => 'Update',
  );
}

