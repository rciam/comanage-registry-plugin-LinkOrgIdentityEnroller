<?php
/*
 * Apache Configuration for dummy path
 <Location /registry-iss>
    AuthType shibboleth
    ShibRequestSetting requireSession 1
    ShibRequestSetting applicationId iss
    DirectoryIndex index.php
    AllowOverride All
    require valid-user

    <ifModule headers_module.c>
    </ifModule>

 </Location>

 *
 * We need a symbolic link from the plugin to the webroot of the apache. e.g.
   In the directory
   /var/www/comanage-dev.aai-dev.grnet.gr/html
   We have the following symbolic link
   registry-iss -> /srv/comanage/comanage-registry-rciam-3.1.1/local/Plugin/LinkOrgIdentityEnroller/webroot/auth/login

 * */

if(empty($_SERVER['REMOTE_USER'])) {
  // if there is no remote user redirect to COmanage with a noremote flag
  // Before adding more named parameters, remove any existed query parameters
  if(strpos($_REQUEST["return"], "?") !== false) {
    $request = explode("?", $_REQUEST["return"]);
    $_REQUEST["return"] = array_shift($request);
  }
  $url = urldecode($_REQUEST["return"]) . "/noremote:1";
  header('Location:' . $url);
  die();
  // The following code will never run. I am keeping for reference reasons.
  //  print "ERROR: REMOTE_USER is empty. Please check your configuration.";
  //  exit;
}

// List of SAML2 attributes.
// The following list of attributes is not going to be available as a whole for each request. Nevertheless i need a minimum set.
// TODO: Currently i will not make any checks for the minimum set of attributes required and i will let COmanage to decide what to do
$saml_attributes = array(
  "Shib-Handler",
  "Shib-Application-ID",
  "Shib-Session-ID",
  "Shib-Identity-Provider",
  "Shib-Authentication-Instant",
  "Shib-Authentication-Method",
  "Shib-AuthnContext-Class",
  "Shib-Session-Index",
  "AuthenticatingAuthority",
  "displayName",
  "distinguishedName",
  "eduPersonAssurance",
  "eduPersonEntitlement",
  "eduPersonScopedAffiliation",
  "eduPersonUniqueId",
  "givenName",
  "mail",
  "md_OrgDisName",
  "md_OrgUrl",
  "md_dispName",
  "md_orgName",
  "persistent-id",
  "sn",
  "subject-id",
);

// Get the data from the form
$shib_data = array_filter($_SERVER, function($key) use ($saml_attributes) {
  return in_array($key, $saml_attributes);
}, ARRAY_FILTER_USE_KEY);

// Get the token
$sliceBySlash = explode('/', urldecode($_REQUEST["return"]));
$tokenString = array_slice($sliceBySlash, -3,1,true);
$tokenString = reset($tokenString);
$tokenSplit = explode(':', $tokenString);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <style>
    @import url("https://fonts.googleapis.com/css?family=Lato:300,400");
    html * {
      font: 300 15px / 23px 'Lato';
    }
    html,
    body {
      height: 100%;
    }
    .container {
      display:flex;
      flex-direction: column;
      justify-content: center;
      height: inherit;
      text-align: center;
    }
    .redirect-box {
      margin: auto;
      height: auto;
      display: flex;
      flex-direction: column;
    }
    .comment {
      padding: 1rem;
    }
    .lds-spinner {
      display: inline-block;
      position: relative;
      margin: auto;
      width: 80px;
      height: 100px;
    }
    .lds-spinner div {
      transform-origin: 40px 50px; /*Second parameter is the inner radius*/
      animation: lds-spinner 1.2s linear infinite;
    }
    .lds-spinner div:after {
      content: " ";
      display: block;
      position: absolute;
      top: 3px;
      left: 37px;
      width: 7px;
      height: 30px;
      border-radius: 10%;
      background: #9FC6E2;
    }
    .lds-spinner div:nth-child(1) {
      transform: rotate(0deg);
      animation-delay: -1.1s;
    }
    .lds-spinner div:nth-child(2) {
      transform: rotate(30deg);
      animation-delay: -1s;
    }
    .lds-spinner div:nth-child(3) {
      transform: rotate(60deg);
      animation-delay: -0.9s;
    }
    .lds-spinner div:nth-child(4) {
      transform: rotate(90deg);
      animation-delay: -0.8s;
    }
    .lds-spinner div:nth-child(5) {
      transform: rotate(120deg);
      animation-delay: -0.7s;
    }
    .lds-spinner div:nth-child(6) {
      transform: rotate(150deg);
      animation-delay: -0.6s;
    }
    .lds-spinner div:nth-child(7) {
      transform: rotate(180deg);
      animation-delay: -0.5s;
    }
    .lds-spinner div:nth-child(8) {
      transform: rotate(210deg);
      animation-delay: -0.4s;
    }
    .lds-spinner div:nth-child(9) {
      transform: rotate(240deg);
      animation-delay: -0.3s;
    }
    .lds-spinner div:nth-child(10) {
      transform: rotate(270deg);
      animation-delay: -0.2s;
    }
    .lds-spinner div:nth-child(11) {
      transform: rotate(300deg);
      animation-delay: -0.1s;
    }
    .lds-spinner div:nth-child(12) {
      transform: rotate(330deg);
      animation-delay: 0s;
    }
    @keyframes lds-spinner {
      0% {
        opacity: 1;
      }
      100% {
        opacity: 0;
      }
    }

  </style>
</head>
<body onload="document.getElementById('formiss').submit();" style="margin:0;">
<div class="container">
  <div class="redirect-box">
    <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    <span class="comment">Your identities are being linked.<br>Please wait...</span>
  </div>
</div>
<?php
  print '<form id="formiss" name="formiss" action="' . urldecode($_REQUEST['return']) . '" method="post">';
  foreach($shib_data as $attr => $value) {
    print '<input type="hidden" name="' . htmlspecialchars($attr) . '" value="' . htmlspecialchars($value) . '" />'."\n";
  }
  print '<input type="hidden" name="_Token" id="csrf-token" value="' . $tokenSplit[1] . '" />';
  print '</form>';
?>
</body>
</html>
