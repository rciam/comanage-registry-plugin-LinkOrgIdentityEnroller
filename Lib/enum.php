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

class LinkOrgIdentityStatusEnum
{
  const Active              = 'A';
  const Suspended           = 'S';
  const type = array(
    'A' => 'Active',
    'S' => 'Suspended',
  );
}

