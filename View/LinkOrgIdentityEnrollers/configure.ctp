<?php
/**
 * COmanage Registry CO Service Token Setting Index View
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry
 * @since         COmanage Registry v2.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

$e = false;

if($this->action == "configure" && $permissions['configure'])
  $e = true;

// Add breadcrumbs
print $this->element("coCrumb");

$this->Html->addCrumb(_txt('ct.link_org_identity_enroller.1'));

// Add page title
$params = array();
$params['title'] = _txt('ct.link_org_identity_enroller.1');

// Add top links
$params['topLinks'] = array();

print $this->element("pageTitleAndButtons", $params);

print $this->Form->create('LinkOrgIdentityEnroller',
    array('url' => array('action' => 'configure', 'co' => $cur_co['Co']['id']),
      'inputDefaults' => array('label' => false, 'div' => false))) . "\n";
print $this->Form->hidden('LinkOrgIdentityEnroller.co_id', array('default' => $cur_co['Co']['id'])) . "\n";
// Store the token
$token_key = $this->request->params['_Token']['key'];
// Initiate the variable that we will use to enable or disable save addition of EOFs in the list
$vv_enable_eofs_save = !empty($vv_enable_eofs_save) ? $vv_enable_eofs_save : 'false';
// Disable if you do not have the permissions
if(!$e) {
  $vv_enable_eofs_save = false;
}

?>
<style>
  table.eofsTable {
    width: 100%;
    text-align: left;
  }
  table.eofsTable td, table.eofsTable th {
    border: none !important;
    padding-left: 0px;
    padding-bottom: 3px;
    background-color: inherit;
    width: 50%;
    color: black;
  }
  table.eofsTable tfoot td {
    font-size: 14px;
  }
  table.eofsTable tfoot .links {
    text-align: right;
  }
  table.eofsTable tfoot .links a{
    display: inline-block;
    background: #1C6EA4;
    color: #FFFFFF;
    padding: 2px 8px;
    border-radius: 5px;
  }
</style>
<script type="text/javascript">
  function fields_update_gadgets() {
    // Hide and show accordingly.
    var confirm = $("#LinkOrgIdentityEnrollerEmailRedirectMode option:selected").val();

    if(confirm != '<?php print LinkOrgIdentityRedirectModeEnum::Disabled; ?>') {
      $("#LinkOrgIdentityEnrollerExpWindow").closest("ul.field-children").show('fade');
      $("#LinkOrgIdentityEnrollerVerificationSubject").closest("li").show('fade');
      $("#LinkOrgIdentityEnrollerVerificationBody").closest("li").show('fade');
    } else {
      $("#LinkOrgIdentityEnrollerExpWindow").closest("ul.field-children").hide('fade');
    }
  }

  function js_local_onload() {
    fields_update_gadgets();
  }

  // Generate flash notifications for messages
  function generateLinkFlash(text, type, timeout) {
    var n = noty({
      text: text,
      type: type,
      dismissQueue: true,
      layout: 'topCenter',
      theme: 'comanage',
      timeout: timeout
    });
  }

  function updateDivDescription(element, msg) {
    divDescr = element.first().find("div:eq(2)");
    text = divDescr.html().split('-')[0].trim();
    text = text + "<span style='color:red'>' - " + msg + "</span>";
    divDescr.html(text);
  }

  function parseFullEOFList($json_obj_eof_list) {
    var $eof_full_list = {};
    $.each($json_obj_eof_list, function (key, value) {
      $eof_full_list[key] = value;
    });

    return $eof_full_list;
  }

  // Remove the row as soon as i press the delete button
  function removeEof(self) {
    var $tr = $(self).closest('tr');
    var $td = $tr.find("td:first");
    eof_id = $td.attr('eof_id');
    eof_name = $td.text();
    var $eof_data = {
      _Token: {}
    };
    $eof_data.id = $tr.attr('id');
    $eof_data._Token.key = '<?php echo $token_key;?>';
    var url_str = '<?php echo $this->Html->url(array(
      'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
      'controller' => 'link_org_identity_eofs',
      'action' => 'delete',
      'co' => $cur_co['Co']['id'])); ?>' + '/' + $tr.attr('id');
    $.ajax({
      type: "DELETE",
      url: url_str,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-CSRF-Token', '<?php echo $token_key;?>');
      },
      cache: false,
      data: $eof_data,
      success: function (response) {
        // Add EOF to the option list
        $('#enrollments_list').append($('<option>', {
          value: eof_id,
          text: eof_name
        }));
        // Finally remove the row
        $tr.remove();
        // Remove the Empty valued option if available
        $("#enrollments_list option[value='Empty']").remove();
        $('#eof_list_btn').removeAttr("disabled");
        generateLinkFlash("<?php print _txt('rs.link_org_identity_eof.deleted') ?>", "success", 2000);
      },
      error: function (response) {
        generateLinkFlash("Delete Failed", "error", 2000);
        console.log(response.responseText);
      }
    });
  }

  $(function () {
    // Explorer menu toggles
    $(".fieldGroupName").click(function(event) {
      event.preventDefault();
      $(this).next(".fields").slideToggle("fast");
      // toggle the +/- icon:
      if ($(this).find(".material-icons").text() == "indeterminate_check_box") {
        $(this).find(".material-icons").text("add_box");
      } else {
        $(this).find(".material-icons").text("indeterminate_check_box");
      }
    });

    $("#btn_save").click(function (e) {
      // 1. I should always have an attribute selected.
      // 2. Currently only mail is allowed
      $attrSelector = $('#attribute');
      $attrVal = $attrSelector.find('option:selected').val().trim();
      if ($attrVal === '' || $attrVal !== "mail") {
        updateDivDescription($attrSelector, "Only mail attribute accepted or Field is empty!");
        $("#coSpinner").remove();
        e.preventDefault();
      }

      // If the email mode is enabled then i need a Logout Endpoint
      if ($("#email_confirmation_mode").find('option:selected').val().trim() === 'E') {
        if ($("#logout_endpoint .field-info").find('input[type="text"]').val().trim() === "") {
          updateDivDescription($("#logout_endpoint"), "You must provide a Logout endpoint.");
          $("#coSpinner").remove();
          e.preventDefault();
        }
      } else if ($("#email_confirmation_mode").find('option:selected').val().trim() === 'X') {
        if ($("#auxiliary_authentication .field-info").find('input[type="text"]').val().trim() === "") {
          updateDivDescription($("#auxiliary_authentication"), "You must provide an Auxiliary Authentication Endpoint.");
          $("#coSpinner").remove();
          e.preventDefault();
        }
      }
    });

    // Enable or disable Addition of EOFs in the list
    var btn_status = <?php echo $vv_enable_eofs_save?>;
    if (btn_status) {
      $('#enrollments_list').removeAttr("disabled");
      $('#eof_list_btn').removeAttr("disabled");
    } else {
      $('#enrollments_list').attr("disabled", "disabled");
      $('#eof_list_btn').attr("disabled", "disabled");
    }

    // Load the EOFs option list
    var eof_list_remain = <?php echo json_encode($vv_enrollments_list); ?>;
    $.each(eof_list_remain, function (eof_id, eof_name) {
      $('#enrollments_list').append($('<option>', {
        value: eof_id,
        text: eof_name
      }));
    });
    // If the list has no data then show an Empty value
    if (eof_list_remain.length == 0) {
      $('#enrollments_list').append('<option value="Empty" disabled selected>Empty</option>');
      $('#eof_list_btn').attr("disabled", "disabled");
    }

    // Load the EOF saved list
    var eof_saved_list = <?php echo json_encode($link_org_identity_enrollers['LinkOrgIdentityEof']); ?>;
    var eof_full_list = parseFullEOFList(<?php echo json_encode($vv_full_enrollments_list); ?>);
    //debugger;
    $.each(eof_saved_list, function (key, value) {
      // TODO: something is happening with the span classes????
      hl_url = '<?php echo $this->Html->url(array(
        'plugin' => null,
        'controller' => 'co_enrollment_flows',
        'action' => 'edit')); ?>' + '/' + value.co_enrollment_flow_id;
      delete_button = '<button type="button" class="deletebutton ui-button ui-corner-all ui-widget" title="Delete" onclick="removeEof(this);">Delete</button>';
      row = "<tr id='" + value.id + "'>" +
        "<td eof_id='" + value.co_enrollment_flow_id + "'>" +
        "<a href='" + hl_url + "'>" + eof_full_list[value.co_enrollment_flow_id] + "</a></td>" +
        "<td>" + delete_button + "</td>" +
        "</tr>";
      $('#enrollment_flows_list_tb > tbody:last-child').append(row);
    });

    $('#eof_list_btn').click(function () {
      var $eof = $('#enrollments_list option:selected');
      eof_text = $eof.text();
      eof_id = $eof.val();
      // The data we will Post to COmanage. We include the token as well.
      var $eof_data = {
        _Token: {}
      };
      $eof_data.co_enrollment_flow_id = eof_id;
      $eof_data.deleted = 'false';
      $eof_data.link_org_identity_enroller_id = <?php echo !empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']) ?
        $link_org_identity_enrollers['LinkOrgIdentityEnroller']['id'] : -1;?>;
      $eof_data._Token.key = '<?php echo $token_key;?>';
      // Make the ajax call and add the data into your table
      $.ajax({
        type: "POST",
        url: '<?php echo $this->Html->url(array(
          'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
          'controller' => 'link_org_identity_eofs',
          'action' => 'add',
          'co' => $cur_co['Co']['id'])); ?>',
        beforeSend: function (xhr) {
          xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
          xhr.setRequestHeader('X-CSRF-Token', '<?php echo $token_key;?>');
        },
        cache: false,
        data: $eof_data,
        success: function (response) {
          hl_url = '<?php echo $this->Html->url(array(
            'plugin' => null,
            'controller' => 'co_enrollment_flows',
            'action' => 'edit')); ?>' + '/' + eof_id;
          delete_button = '<button type="button" class="deletebutton ui-button ui-corner-all ui-widget" title="Delete" onclick="removeEof(this);">' +
            '<span class="ui-button-icon ui-icon ui-icon-circle-close"></span>' +
            '<span class="ui-button-icon-space"> </span>Delete</button>';
          row = "<tr id='" + response.id + "'>" +
            "<td eof_id='" + eof_id + "'>" +
            "<a href='" + hl_url + "'>" + eof_text + "</a></td>" +
            "<td>" + delete_button + "</td>" +
            "</tr>";
          $('#enrollment_flows_list_tb > tbody:last-child').append(row);
          // Remove the EOF from the selection list
          $eof.remove();
          // If the list has no data then show an Empty value
          if ($('#enrollments_list option').length == 0) {
            $('#enrollments_list').append('<option value="Empty" disabled selected>Empty</option>');
            $('#eof_list_btn').attr("disabled", "disabled");
          }
          generateLinkFlash(response.eof_name + " picked.", "success", 2000);
        },
        error: function (response) {
          generateLinkFlash("EOF pick failed", "error", 2000);
        }
      });
    });
  });
</script>


<div class="co-info-topbox">
  <i class="material-icons">info</i>
  <?php print _txt('ct.link_org_identity_enroller.info'); ?>
</div>
<div id="<?php print $this->action; ?>_link_org_identity_enroller" class="explorerContainer">
  <div id="rciamLinking" class="personExplorer">
    <ul>
      <!-- Linking General Config -->
      <li id="fields-config" class="fieldGroup">
        <a href="#tabs-config" class="fieldGroupName">
          <em class="material-icons">indeterminate_check_box</em>
          <?php
          print _txt('op.link_org_identity_enroller.config');
          ?>
        </a>
        <div id="names-container" class="fields">
          <ul id="tabs-config" class="fields form-list">
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.co_name'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.co_name'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.co_name.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                print $vv_co_list[$cur_co['Co']['id']];
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('fd.link_org_identity_enroller.status'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print $this->Form->label('status', _txt('fd.link_org_identity_enroller.status')); ?>
                  <span class="required">*</span>
                </div>
              </div>
              <div class="field-info">
                <?php
                $attrs = array();
                $attrs['value'] = (!empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['status'])
                  ? $link_org_identity_enrollers['LinkOrgIdentityEnroller']['status']
                  : LinkOrgIdentityStatusEnum::Active);
                $attrs['empty'] = false;

                if($e) {
                  print $this->Form->select(
                    'status',
                    LinkOrgIdentityStatusEnum::type,
                    $attrs
                  );

                  if($this->Form->isFieldError('status')) {
                    print $this->Form->error('status');
                  }
                } else {
                  print LinkOrgIdentityStatusEnum::type[$link_org_identity_enrollers['LinkOrgIdentityEnroller']['status']];
                }
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.flow'))); ?>"
                style="display: flex !important;align-items: center;">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.flow'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.flow.desc'); ?></div>
              </div>
              <div class="field-info">
                <div class="field_wrapper">
                  <div>
                    <table id="enrollment_flows_list_tb" class="eofsTable">
                      <thead>
                      <th>
                        <strong><label>Name:</label></strong>
                        <select id="enrollments_list"/>
                      </th>
                      <th>
                        <input id="eof_list_btn" type="button" value="Add"
                               class="submit-button mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect">
                      </th>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
                </div>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.attribute'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.attribute'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.attribute.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = !empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['cmp_attribute_name']) ?
                  $link_org_identity_enrollers['LinkOrgIdentityEnroller']['cmp_attribute_name'] : "";
                print ($this->Form->select('LinkOrgIdentityEnroller.cmp_attribute_name',
                  $vv_cmp_attributes_list,
                  array(
                    'empty' => '(Choose One)',
                    'value' => $value
                  )));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.endpoint'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.endpoint'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.endpoint.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['logout_endpoint']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['logout_endpoint'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('LinkOrgIdentityEnroller.logout_endpoint', array('size' => 50, 'value' => $value));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.mdq.url'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.mdq.url'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.mdq.url.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['mdq_url']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['mdq_url'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('LinkOrgIdentityEnroller.mdq_url', array('size' => 50, 'value' => $value));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.aux_auth'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.aux_auth'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.aux_auth.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['aux_auth']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['aux_auth'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('LinkOrgIdentityEnroller.aux_auth', array('size' => 50, 'value' => $value));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.intro'))); ?>"
                class="field-stack">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.intro'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.intro.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $intro = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['introduction_text']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['introduction_text'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->textarea('LinkOrgIdentityEnroller.introduction_text', array('size' => 4000, 'value' => $intro));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.ce'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.ce'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.ce.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $attrs = array();
                $attrs['value'] = (!empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['email_redirect_mode'])
                  ? $link_org_identity_enrollers['LinkOrgIdentityEnroller']['email_redirect_mode']
                  : LinkOrgIdentityRedirectModeEnum::Disabled);
                $attrs['empty'] = false;
                $attrs['onchange'] = "fields_update_gadgets();";

                if($e) {
                  print $this->Form->select(
                    'email_redirect_mode',
                    LinkOrgIdentityRedirectModeEnum::type,
                    $attrs
                  );

                  if($this->Form->isFieldError('email_redirect_mode')) {
                    print $this->Form->error('email_redirect_mode');
                  }
                } else {
                  print LinkOrgIdentityRedirectModeEnum::type[$link_org_identity_enrollers['LinkOrgIdentityEnroller']['email_redirect_mode']];
                }

                ?>
              </div>
              <ul class="field-children">
                <li>
                  <div class="field-name">
                    <div class="field-title">
                      <?php print _txt('pl.link_org_identity_enroller.expiration'); ?>
                    </div>
                    <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.expiration.desc'); ?></div>
                  </div>
                  <div class="field-info">
                    <?php
                    $invitation_validity = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['exp_window']) ? DEF_INV_VALIDITY
                      : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['exp_window'], FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->input('LinkOrgIdentityEnroller.exp_window', array('default' => $invitation_validity));
                    ?>
                  </div>
                </li>
                <li>
                  <div class="field-name">
                    <div class="field-title">
                      <?php print _txt('pl.link_org_identity_enroller.vsub'); ?>
                    </div>
                    <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.vsub.desc'); ?></div>
                  </div>
                  <div class="field-info">
                    <?php
                    $subject = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['verification_subject']) ? _txt('pl.link_org_identity_enroller.subject.link')
                      : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['verification_subject'], FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->input('LinkOrgIdentityEnroller.verification_subject', array('default' => $subject));
                    ?>
                  </div>
                </li>
                <li class="field-stack">
                  <div class="field-name">
                    <div class="field-title">
                      <?php print _txt('pl.link_org_identity_enroller.vbody'); ?>
                    </div>
                    <div class="field-desc"><?php print _txt('fd.ef.vbody.desc'); ?></div>
                  </div>
                  <div class="field-info">
                    <?php
                    $body = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['verification_body']) ? _txt('pl.link_org_identity_enroller.invite.body.link')
                      : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['verification_body'], FILTER_SANITIZE_SPECIAL_CHARS);
                    print $this->Form->textarea('LinkOrgIdentityEnroller.verification_body', array('default' => $body));
                    ?>
                  </div>
                </li>
              </ul>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.return'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.return'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.return.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['return']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['return'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('LinkOrgIdentityEnroller.return', array('size' => 50, 'value' => $value));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.idpblacklist'))); ?>"
                class="field-stack">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.idpblacklist'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.idpblacklist.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $idp_blacklist = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['idp_blacklist']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['idp_blacklist'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->textarea('LinkOrgIdentityEnroller.idp_blacklist', array('size' => 4000, 'value' => $idp_blacklist));
                ?>
              </div>
            </li>
          </ul>
        </div>
      </li>
      <!-- Linking Attribute Mapper -->
      <li id="fields-mapper" class="fieldGroup">
        <a href="#tabs-attrmap" class="fieldGroupName">
          <em class="material-icons">indeterminate_check_box</em>
          <?php
          print _txt('op.link_org_identity_enroller.attrmap');
          ?>
        </a>
        <div id="names-container" class="fields">
          <ul id="tabs-attrmap" class="fields form-list">
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.useridattr'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.useridattr'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.useridattr.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['user_id_attribute']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['user_id_attribute'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('LinkOrgIdentityEnroller.user_id_attribute', array('size' => 50, 'value' => $value));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.subjectdn'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.subjectdn'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.subjectdn.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['subject_dn_attribute']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['subject_dn_attribute'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('LinkOrgIdentityEnroller.subject_dn_attribute', array('size' => 50, 'value' => $value));
                ?>
              </div>
            </li>
            <li id="<?php print Inflector::slug(strtolower(_txt('pl.link_org_identity_enroller.issuerdn'))); ?>">
              <div class="field-name">
                <div class="field-title">
                  <?php print _txt('pl.link_org_identity_enroller.issuerdn'); ?>
                </div>
                <div class="field-desc"><?php print _txt('pl.link_org_identity_enroller.issuerdn.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($link_org_identity_enrollers['LinkOrgIdentityEnroller']['issuer_dn_attribute']) ? ""
                  : filter_var($link_org_identity_enrollers['LinkOrgIdentityEnroller']['issuer_dn_attribute'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('LinkOrgIdentityEnroller.issuer_dn_attribute', array('size' => 50, 'value' => $value));
                ?>
              </div>
            </li>
          </ul>
        </div>
      </li>
      <li id="fields-btn" class="fieldGroup">  <!-- Save Button -->
        <div id="names-container" class="fields">
          <ul class="fields form-list">
            <?php if($e): ?>
              <li class="fields-submit">
                <div class="field-name">
                  <span class="required"><?php print _txt('fd.req'); ?></span>
                </div>
                <div class="field-info">
                  <?php
                  $options = array(
                    'style' => 'float:left;',
                    'id' => 'btn_save',
                  );
                  $submit_label = _txt('op.save');
                  print $this->Form->submit($submit_label, $options);
                  print $this->Form->end();
                  ?>
                </div>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </li> <!-- Save Button -->
    </ul>
  </div> <!-- PersonExplorer -->
</div>   <!-- Explorer Container -->