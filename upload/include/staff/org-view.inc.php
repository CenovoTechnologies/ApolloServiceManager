<?php
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($org)) die('Invalid path');

?>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%">
             <h2 style="vertical-align: bottom"><a href="orgs.php?id=<?php echo $org->getId(); ?>"
             title="Name"></i> <?php echo $org->getName(); ?></a></h2>
        </td>
        <td width="50%" class="right_align">
<?php if ($thisstaff->hasPerm(Organization::PERM_DELETE)) { ?>
            <a id="org-delete" class="red button action-button org-action"
            href="#orgs/<?php echo $org->getId(); ?>/delete"><i class="icon-trash"></i>
            <?php echo __('Delete Organization'); ?></a>
<?php } ?>
<?php if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
            <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown">
                <span ><i class="icon-cog"></i> <?php echo __('More'); ?></span>
            </button>
<?php } ?>
              <ul class="dropdown-menu">
<?php if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
                <li><a href="#ajax.php/orgs/<?php echo $org->getId();
                    ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                    ><i class="icon-paste"></i>
                    <?php echo __('Manage Forms'); ?></a></li>
<?php } ?>
              </ul>
            </div>
        </td>
    </tr>
</table>
<table class="table table-condensed" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="30%"><?php echo __('Name'); ?>:</th>
                    <td>
<?php if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
                    <b><a href="#orgs/<?php echo $org->getId();
                    ?>/edit" class="org-action"><i
                        class="icon-edit"></i>
<?php }
                    echo $org->getName();
    if ($thisstaff->hasPerm(Organization::PERM_EDIT)) { ?>
                    </a></b>
<?php } ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('Account Manager'); ?>:</th>
                    <td><?php echo $org->getAccountManager(); ?>&nbsp;</td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align:top">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="30%"><?php echo __('Created'); ?>:</th>
                    <td><?php echo $org->getCreateDate(); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Last Updated'); ?>:</th>
                    <td><?php echo $org->getUpdateDate(); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<div class="clear"></div>
<ul class="nav nav-tabs" id="orgtabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#users" role="tab"><i
    class="icon-user"></i>&nbsp;<?php echo __('Users'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#org-tickets" role="tab"><i
    class="icon-list-alt"></i>&nbsp;<?php echo __('Tickets'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#notes" role="tab"><i
    class="icon-pushpin"></i>&nbsp;<?php echo __('Notes'); ?></a></li>
</ul>
<div id="orgtabs_container" class="tab-content">
    <div class="tab-pane active" id="users" role="tabpanel">
    <?php
    include STAFFINC_DIR . 'templates/users.tmpl.php';
    ?>
    </div>
    <div class="tab-pane" id="org-tickets" role="tabpanel">
    <?php
    include STAFFINC_DIR . 'templates/tickets.tmpl.php';
    ?>
    </div>

    <div class="tab-pane" id="notes" role="tabpanel">
    <?php
    $notes = QuickNote::forOrganization($org);
    $create_note_url = 'orgs/'.$org->getId().'/note';
    include STAFFINC_DIR . 'templates/notes.tmpl.php';
    ?>
    </div>
</div>

<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.org-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, [201, 204], function (xhr) {
            if (xhr.status == 204)
                window.location.href = 'orgs.php';
            else
                window.location.href = window.location.href;
         }, {
            onshow: function() { $('#org-search').focus(); }
         });
        return false;
    });
});
</script>
