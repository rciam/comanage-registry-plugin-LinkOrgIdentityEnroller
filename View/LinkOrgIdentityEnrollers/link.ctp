<?php
// Get a pointer to our model
$model = $this->name;
$req = Inflector::singularize($model);
$modelpl = Inflector::tableize($req);
$modelplugin = Inflector::singularize(Inflector::tableize($req));
$modelu = Inflector::underscore($req);

// Add breadcrumbs
print $this->element("coCrumb");
$crumbTxt = _txt('op.link_org_identity_enroller.select-a',array(_txt('fd.link_org_identity_enroller.user')));
$this->Html->addCrumb($crumbTxt);

// Add page title
$params = array();
$params['title'] = _txt('pl.link_org_identity_enroller.available_users');

// Add top links
$params['topLinks'] = array();
$redirect =   array(
        'plugin'      => null,
        'controller'  => 'co_petitions',
        'action'      => 'start',
        'coef'        => $coef,
        'done'        => $req,
    );
if (!empty($this->request->query)) {
    $redirect['?'] = $this->request->query;
}
$params['topLinks'][] = $this->Html->link(
  _txt('op.link_org_identity_enroller.abort'),
  $redirect,
  array('class' => 'cancelbutton')
);
print $this->element("pageTitleAndButtons", $params);

if(!empty($vv_introduction_text)) {
  print '<div class="co-info-topbox"><em class="material-icons">info</em>' . $vv_introduction_text . '</div>';
}

// Load my css
$this->Html->css('LinkOrgIdentityEnroller.linker', array('inline' => false));
?>

  <div id="link_org_identity_enrollers" class="co-grid co-grid-with-header">
    <div class="mdl-grid co-grid-header">
      <div class="mdl-cell mdl-cell--11-col"><?php print _txt('op.link_org_identity_enroller.select-a',array(_txt('fd.link_org_identity_enroller.user'))); ?></div>
      <div class="mdl-cell mdl-cell--1-col actions"><?php print _txt('op.link_org_identity_enroller.action'); ?></div>
    </div>
    
    <?php $i = 0; ?>
    <?php foreach ($vv_registrations_display as $registration): ?>
    <?php
    print $this->Form->create('LinkOrgIdentityEnroller', array(
      'url' => array(
        'controller' => 'link_org_identity_enroller_co_petitions',
        'action' => 'logout',
      ),
      'inputDefaults' => array('label' => false, 'div' => false)
      ));
    print $this->Form->hidden('co_id', array('default' => $co_id)) . "\n";
    print $this->Form->hidden('co_person_id', array('default' => $vv_registrations[$i][0]['pid'])) . "\n";
    print $this->Form->hidden('coef', array('default' => $coef)) . "\n";
    print $this->Form->hidden('cfg', array('default' => $cfg)) . "\n";
    if (!empty($query)) {
      print $this->Form->hidden('query', array('default' => $query)) . "\n";
    }

    
    ?>
    <div class="mdl-grid">
      <div class="mdl-cell mdl-cell--11-col mdl-cell--6-col-tablet mdl-cell--4-col-phone">
        <ul id="<?php print $this->action; ?>_link_org_identity_enroller" class="fields form-list">
          <?php foreach ($registration as $field => $value): ?>
          <li>
            <div class="field-name">
              <div class="field-title">
                <?php
                if($field != 'pid') {
                  print $field;
                }
                ?>
              </div>
            </div>
            <div class="field-info">
              <?php
              if($field != 'pid' &&  $field != 'IdP') {
                print is_array($value) ? implode(', ', $value) : $value;
              }
              if($field == 'IdP'){
                $default_item = reset($value);
                print ($this->Form->select('idpHint',
                  $value,
                  array(
                    'empty'   => false,
                    'default' => $default_item,
                  )));
              }
              ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="mdl-grid co-grid-header"/></div>
      </div>
      <div class="mdl-cell mdl-cell--1-col actions">
        <?php
         // Link button
        print $this->Form->submit("Link");
        print $this->Form->end();
        ?>
      </div>
    </div>
    <?php $i++; ?>
    <?php endforeach; ?>
    <div class="mdl-cell mdl-cell--1-col actions">
  <?php
  ?>
</div>
    <div class="clearfix"></div>
  </div>
<?php
?>
