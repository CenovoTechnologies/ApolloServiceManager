<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info = $qs = array();

if ($_REQUEST['a']=='add'){
    if (!$staff) {
        $staff = Staff::create(array(
            'isactive' => true,
        ));
        // Set some default permissions
        $staff->updatePerms(array(
            User::PERM_CREATE,
            User::PERM_EDIT,
            User::PERM_DELETE,
            User::PERM_MANAGE,
            User::PERM_DIRECTORY,
            Organization::PERM_CREATE,
            Organization::PERM_EDIT,
            Organization::PERM_DELETE,
            FAQ::PERM_MANAGE,
        ));
    }
    $title=__('Add New Agent');
    $action='create';
    $submit_text=__('Create');
}
else {
    //Editing Department.
    $title=__('Manage Agent');
    $action='update';
    $submit_text=__('Save Changes');
    $info['id'] = $staff->getId();
    $qs += array('id' => $staff->getId());
}
?>
<div class="col-sm-12 col-md-12">
<form action="staff.php?<?php echo Http::build_query($qs); ?>" method="post" id="save" autocomplete="off">
  <?php csrf_token(); ?>
  <input type="hidden" name="do" value="<?php echo $action; ?>">
  <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
  <input type="hidden" name="id" value="<?php echo $info['id']; ?>">

  <h2><?php echo $title; ?>
      <?php if (isset($staff->staff_id)) { ?><small>
      — <?php echo $staff->getName(); ?></small>
      <?php } ?>
</h2>

  <ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#account" role="tab"><i class="icon-user"></i> <?php echo __('Account'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#access" role="tab"><?php echo __('Access'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#permissions" role="tab"><?php echo __('Permissions'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#teams" role="tab"><?php echo __('Teams'); ?></a></li>
  </ul>
  <div class="tab-content">
  <div class="tab-pane active" role="tabpanel" id="account">
    <table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
      <tbody>
        <tr><td colspan="2"><div>
        <div class="avatar pull-left" style="margin: 10px;">
            <?php echo $staff->getAvatar(); ?>
        </div>
        <table class="table table-condensed" border="0" cellspacing="2" cellpadding="2" style="width:85%">
        <tr>
          <td class="required"><?php echo __('Name'); ?>:</td>
          <td>
            <input type="text" size="20" maxlength="64" name="firstname" class="form-control-sm auto first"
              autofocus value="<?php echo Format::htmlchars($staff->firstname); ?>"
              placeholder="<?php echo __("First Name"); ?>" />
            <input type="text" size="20" maxlength="64" name="lastname" class="form-control-sm auto last"
              value="<?php echo Format::htmlchars($staff->lastname); ?>"
              placeholder="<?php echo __("Last Name"); ?>" />
            <div class="error"><?php echo $errors['firstname']; ?></div>
            <div class="error"><?php echo $errors['lastname']; ?></div>
          </td>
        </tr>
        <tr>
          <td class="required"><?php echo __('Email Address'); ?>:</td>
          <td>
            <input type="email" size="40" maxlength="64" name="email" class="form-control-sm auto email"
              value="<?php echo Format::htmlchars($staff->email); ?>"
              placeholder="<?php echo __('e.g. me@mycompany.com'); ?>" />
            <div class="error"><?php echo $errors['email']; ?></div>
          </td>
        </tr>
        <tr>
          <td><?php echo __('Phone Number');?>:</td>
          <td>
            <input type="tel" size="18" name="phone" class="form-control-sm auto phone"
              value="<?php echo Format::htmlchars($staff->phone); ?>" />
            <?php echo __('Ext');?>
            <input type="text" class="form-control-sm" size="5" name="phone_ext"
              value="<?php echo Format::htmlchars($staff->phone_ext); ?>">
            <div class="error"><?php echo $errors['phone']; ?></div>
            <div class="error"><?php echo $errors['phone_ext']; ?></div>
          </td>
        </tr>
        <tr>
          <td><?php echo __('Mobile Number');?>:</td>
          <td>
            <input type="tel" size="18" name="mobile" class="form-control-sm auto phone"
              value="<?php echo Format::htmlchars($staff->mobile); ?>" />
            <div class="error"><?php echo $errors['mobile']; ?></div>
          </td>
        </tr>
        </table></div></td></tr>
      </tbody>
      <!-- ================================================ -->
      <tbody>
        <tr class="table-heading">
          <th colspan="2">
            <?php echo __('Authentication'); ?>
          </th>
        </tr>
        <tr>
          <td class="required"><?php echo __('Username'); ?>:
            <span class="error">*</span></td>
          <td>
            <input type="text" size="40"
              class="form-control-sm staff-username typeahead"
              name="username" value="<?php echo Format::htmlchars($staff->username); ?>" />
<?php if (!($bk = $staff->getAuthBackend()) || $bk->supportsPasswordChange()) { ?>
            <button type="button" class="action-button" onclick="javascript:
            $.dialog('ajax.php/staff/'+<?php echo $info['id'] ?: '0'; ?>+'/set-password', 201);">
              <i class="icon-refresh"></i> <?php echo __('Set Password'); ?>
            </button>
<?php } ?>
            <i class="offset help-tip icon-question-sign" href="#username"></i>
            <div class="error"><?php echo $errors['username']; ?></div>
          </td>
        </tr>
<?php
$bks = array();
foreach (StaffAuthenticationBackend::allRegistered() as $ab) {
  if (!$ab->supportsInteractiveAuthentication()) continue;
  $bks[] = $ab;
}
if (count($bks) > 1) {
?>
        <tr>
          <td><?php echo __('Authentication Backend'); ?>:</td>
          <td>
            <select name="backend" id="backend-selection" class="form-control-sm"
              onchange="javascript:
                if (this.value != '' && this.value != 'local')
                    $('#password-fields').hide();
                else if (!$('#welcome-email').is(':checked'))
                    $('#password-fields').show();
                ">
              <option value="">&mdash; <?php echo __('Use any available backend'); ?> &mdash;</option>
<?php foreach ($bks as $ab) { ?>
              <option value="<?php echo $ab::$id; ?>" <?php
                if ($staff->backend == $ab::$id)
                  echo 'selected="selected"'; ?>><?php
                echo $ab->getName(); ?></option>
<?php } ?>
            </select>
          </td>
        </tr>
<?php
} ?>
      </tbody>
      <!-- ================================================ -->
      <tbody>
        <tr class="table-heading">
          <th colspan="2">
            <?php echo __('Status and Settings'); ?>
          </th>
        </tr>
        <tr>
          <td colspan="2">
            <div class="error"><?php echo $errors['isadmin']; ?></div>
            <div class="error"><?php echo $errors['isactive']; ?></div>
            <label class="checkbox">
            <input type="checkbox" name="islocked" value="1"
              <?php echo (!$staff->isactive) ? 'checked="checked"' : ''; ?> />
              <?php echo __('Locked'); ?>
            </label>
            <label class="checkbox">
            <input type="checkbox" name="isadmin" value="1"
              <?php echo ($staff->isadmin) ? 'checked="checked"' : ''; ?> />
              <?php echo __('Administrator'); ?>
            </label>
            <label class="checkbox">
            <input type="checkbox" name="assigned_only"
              <?php echo ($staff->assigned_only) ? 'checked="checked"' : ''; ?> />
              <?php echo __('Limit ticket access to ONLY assigned tickets'); ?>
            </label>
            <label class="checkbox">
            <input type="checkbox" name="onvacation"
              <?php echo ($staff->onvacation) ? 'checked="checked"' : ''; ?> />
              <?php echo __('Vacation Mode'); ?>
            </label>
            <br/>
        </tr>
      </tbody>
    </table>

    <div style="padding:8px 3px; margin-top: 1.6em">
        <div class="big"><?php echo __('Internal Notes');?>: </div>
        <?php echo __("Be liberal, they're internal");?>
    </div>

    <textarea name="notes" class="form-control-sm richtext">
      <?php echo Format::viewableImages($staff->notes); ?>
    </textarea>
  </div>

  <!-- ============== DEPARTMENT ACCESS =================== -->

  <div class="tab-pane" id="access" role="tabpanel">
    <table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
      <tbody>
        <tr class="table-heading">
          <th colspan="3">
            <div><?php echo __('Primary Department'); ?> <span
            class="error">*</span></div>
          </th>
        </tr>
        <tr>
          <td style="vertical-align:top">
            <select name="dept_id" class="form-control-sm" id="dept_id" data-quick-add="department">
              <option value="0">&mdash; <?php echo __('Select Department');?> &mdash;</option>
              <?php
              foreach (Dept::getDepartments() as $id=>$name) {
                $sel=($staff->dept_id==$id)?'selected="selected"':'';
                echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,$name);
              }
              ?>
              <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
            </select>
            <i class="offset help-tip icon-question-sign" href="#primary_department"></i>
            <div class="error"><?php echo $errors['dept_id']; ?></div>
          </td>
          <td style="vertical-align:top">
            <select name="role_id" class="form-control-sm" data-quick-add="role">
              <option value="0">&mdash; <?php echo __('Select Role');?> &mdash;</option>
              <?php
              foreach (Role::getRoles() as $id=>$name) {
                $sel=($staff->role_id==$id)?'selected="selected"':'';
                echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,$name);
              }
              ?>
              <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
            </select>
            <i class="offset help-tip icon-question-sign" href="#primary_role"></i>
          </td>
          <td>
            <label class="inline checkbox">
            <input type="checkbox" name="assign_use_pri_role" <?php
                if ($staff->usePrimaryRoleOnAssignment())
                    echo 'checked="checked"';
                ?> />
                <?php echo __('Fall back to primary role on assignments'); ?>
                <i class="icon-question-sign help-tip"
                    href="#primary_role_on_assign"></i>
            </label>

            <div class="error"><?php echo $errors['role_id']; ?></div>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr id="extended_access_template" class="hidden">
          <td>
            <input type="hidden" data-name="dept_access[]" value="" />
          </td>
          <td>
            <select data-name="dept_access_role" class="form-control-sm" data-quick-add="role">
              <option value="0">&mdash; <?php echo __('Select Role');?> &mdash;</option>
              <?php
              foreach (Role::getRoles() as $id=>$name) {
                echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,$name);
              }
              ?>
              <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
            </select>
          </td>
          <td>
            <label class="inline checkbox">
              <input type="checkbox" data-name="dept_access_alerts" value="1" />
              <?php echo __('Alerts'); ?>
            </label>
            <a href="#" class="pull-right drop-access" title="<?php echo __('Delete');
              ?>"><i class="icon-trash"></i></a>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr class="table-heading">
          <th colspan="3">
            <?php echo __('Extended Access'); ?>
          </th>
        </tr>
<?php
$depts = Dept::getDepartments();
foreach ($staff->dept_access as $dept_access) {
  unset($depts[$dept_access->dept_id]);
}
?>
        <tr id="add_extended_access">
          <td colspan="2">
            <select id="add_access" class="form-control-sm" data-quick-add="department">
              <option value="0">&mdash; <?php echo __('Select Department');?> &mdash;</option>
              <?php
              foreach ($depts as $id=>$name) {
                echo sprintf('<option value="%d">%s</option>',$id,Format::htmlchars($name));
              }
              ?>
              <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
            </select>
            <button type="button" class="btn btn-sm btn-secondary">
              <?php echo __('Add'); ?>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- ================= PERMISSIONS ====================== -->

  <div id="permissions" class="tab-pane" role="tabpanel">
<?php
    $permissions = array();
    foreach (RolePermission::allPermissions() as $g => $perms) {
        foreach ($perms as $k=>$P) {
            if (!$P['primary'])
                continue;
            if (!isset($permissions[$g]))
                $permissions[$g] = array();
            $permissions[$g][$k] = $P;
        }
    }
?>
    <ul class="nav nav-tabs">
<?php
    $first = true;
    foreach ($permissions as $g => $perms) { ?>
      <li class="nav-item">
        <a <?php if ($first) { echo 'class="nav-link active"'; $first=false; }else{ echo 'class="nav-link"';} ?> data-toggle="tab" href="#<?php echo Format::slugify($g); ?>" role="tab"><?php echo Format::htmlchars(__($g));?></a>
      </li>
<?php } ?>
    </ul>
    <div class="tab-content">
<?php
    $first = true;
    foreach ($permissions as $g => $perms) { ?>
    <div class="tab-pane <?php if ($first) { echo 'active'; } else { $first = false; }
      ?>" id="<?php echo Format::slugify($g); ?>" role="tabpanel">
      <table class="table table-condensed">
<?php foreach ($perms as $k => $v) { ?>
        <tr>
          <td>
            <label>
            <?php
            echo sprintf('<input type="checkbox" name="perms[]" value="%s" %s />',
              $k, ($staff->hasPerm($k)) ? 'checked="checked"' : '');
            ?>
            &nbsp;
            <?php echo Format::htmlchars(__($v['title'])); ?>
            —
            <em><?php echo Format::htmlchars(__($v['desc'])); ?></em>
           </label>
          </td>
        </tr>
<?php   } ?>
      </table>
    </div>
<?php } ?>
    </div>
  </div>

  <!-- ============== TEAM MEMBERSHIP =================== -->

  <div class="tab-pane" id="teams" role="tabpanel">
    <table class="table table-condensed">
      <tbody>
        <tr class="table-heading">
          <th colspan="2">
            <?php echo __('Assigned Teams'); ?>
          </th>
        </tr>
<?php
$teams = Team::getTeams();
foreach ($staff->teams as $TM) {
  unset($teams[$TM->team_id]);
}
?>
        <tr id="join_team">
          <td colspan="2">
            <select id="add_team" class="form-control-sm" data-quick-add="team">
              <option value="0">&mdash; <?php echo __('Select Team');?> &mdash;</option>
              <?php
              foreach ($teams as $id=>$name) {
                echo sprintf('<option value="%d">%s</option>',$id,Format::htmlchars($name));
              }
              ?>
              <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-success">
              <?php echo __('Add'); ?>
            </button>
          </td>
        </tr>
      </tbody>
      <tbody>
        <tr id="team_member_template" class="hidden">
          <td>
            <input type="hidden" data-name="teams[]" value="" />
          </td>
          <td>
            <label>
              <input type="checkbox" data-name="team_alerts" value="1" />
              <?php echo __('Alerts'); ?>
            </label>
            <a href="#" class="pull-right drop-membership" title="<?php echo __('Delete');
              ?>"><i class="icon-trash"></i></a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  </div>
  <p style="text-align:left; padding-top: 5px;">
      <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>"><?php echo $submit_text; ?></button>
      <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
      <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick="window.history.go(-1);">
  </p>
</form>
</div>
<script type="text/javascript">
var addAccess = function(daid, name, role, alerts, error) {
  if (!daid) return;
  var copy = $('#extended_access_template').clone();

  copy.find('[data-name=dept_access\\[\\]]')
    .attr('name', 'dept_access[]')
    .val(daid);
  copy.find('[data-name^=dept_access_role]')
    .attr('name', 'dept_access_role['+daid+']')
    .val(role || 0);
  copy.find('[data-name^=dept_access_alerts]')
    .attr('name', 'dept_access_alerts['+daid+']')
    .prop('checked', alerts);
  copy.find('td:first').append(document.createTextNode(name));
  copy.attr('id', '').show().insertBefore($('#add_extended_access'));
  copy.removeClass('hidden');
  if (error)
      $('<div class="error">').text(error).appendTo(copy.find('td:last'));
  copy.find('a.drop-access').click(function() {
    $('#add_access').append(
      $('<option>')
        .attr('value', copy.find('input[name^=dept_access][type=hidden]').val())
        .text(copy.find('td:first').text())
    );
    copy.fadeOut(function() { $(this).remove(); });
    return false;
  });
};

$('#add_extended_access').find('button').on('click', function() {
  var selected = $('#add_access').find(':selected'),
      id = parseInt(selected.val());
  if (!id)
      return;
  addAccess(id, selected.text(), 0, true);
  selected.remove();
  return false;
});

var joinTeam = function(teamid, name, alerts, error) {
  if (!teamid) return;
  var copy = $('#team_member_template').clone();

  copy.find('[data-name=teams\\[\\]]')
    .attr('name', 'teams[]')
    .val(teamid);
  copy.find('[data-name^=team_alerts]')
    .attr('name', 'team_alerts['+teamid+']')
    .prop('checked', alerts);
  copy.find('td:first').append(document.createTextNode(name));
  copy.attr('id', '').show().insertBefore($('#join_team'));
  copy.removeClass('hidden');
  if (error)
      $('<div class="error">').text(error).appendTo(copy.find('td:last'));
  copy.find('a.drop-membership').click(function() {
    $('#add_team').append(
      $('<option>')
        .attr('value', copy.find('input[name^=teams][type=hidden]').val())
        .text(copy.find('td:first').text())
    );
    copy.fadeOut(function() { $(this).remove(); });
    return false;
  });
};

$('#join_team').find('button').on('click', function() {
  var selected = $('#add_team').find(':selected'),
      id = parseInt(selected.val());
  if (!id)
      return;
  joinTeam(id, selected.text(), true);
  selected.remove();
  return false;
});


<?php
foreach ($staff->dept_access as $dept_access) {
  echo sprintf('addAccess(%d, %s, %d, %d, %s);', $dept_access->dept_id,
    JsonDataEncoder::encode($dept_access->dept->getName()),
    $dept_access->role_id,
    $dept_access->isAlertsEnabled(),
    JsonDataEncoder::encode(@$errors['dept_access'][$dept_access->dept_id])
  );
}

foreach ($staff->teams as $member) {
  echo sprintf('joinTeam(%d, %s, %d, %s);', $member->team_id,
    JsonDataEncoder::encode($member->team->getName()),
    $member->isAlertsEnabled(),
    JsonDataEncoder::encode(@$errors['teams'][$member->team_id])
  );
}

?>
</script>
