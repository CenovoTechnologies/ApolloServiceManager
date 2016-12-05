<?php
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($user)) die('Invalid path');

$account = $user->getAccount();
$org = $user->getOrganization();


?>
<table class="table table-condensed" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%" class="has_bottom_border">
             <h2><a href="users.php?id=<?php echo $user->getId(); ?>"
             title="User"></i> <?php echo Format::htmlchars($user->getName()); ?></a></h2>
        </td>
        <td width="50%" class="right_align has_bottom_border">
            <?php    if ($thisstaff->hasPerm(User::PERM_DELETE)) { ?>
                <a id="user-delete" class="red button action-button pull-right user-action"
                   href="#users/<?php echo $user->getId(); ?>/delete"><i class="icon-trash"></i>
                    <?php echo __('Delete User'); ?></a>
            <?php } ?>
            <?php if ($thisstaff->hasPerm(User::PERM_MANAGE)) { ?>
                <?php
                if ($account) { ?>
                    <a id="user-manage" class="action-button pull-right user-action"
                       href="#users/<?php echo $user->getId(); ?>/manage"><i class="icon-edit"></i>
                        <?php echo __('Manage Account'); ?></a>
                    <?php
                } else { ?>
                    <a id="user-register" class="action-button pull-right user-action"
                       href="#users/<?php echo $user->getId(); ?>/register"><i class="icon-smile"></i>
                        <?php echo __('Register'); ?></a>
                    <?php
                } ?>
            <?php } ?>
<?php if (($account && $account->isConfirmed())
    || $thisstaff->hasPerm(User::PERM_EDIT)) { ?>
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown" title="<?php echo __('More'); ?>" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-cog"></i> <?php echo __('More');?>
                </button>
                <ul class="dropdown-menu">
                    <?php
                    if ($account) {
                        if (!$account->isConfirmed()) {
                            ?>
                            <li><a class="confirm-action" href="#confirmlink"><i
                                        class="icon-envelope"></i>
                                    <?php echo __('Send Activation Email'); ?></a></li>
                            <?php
                        } else { ?>
                            <li><a class="confirm-action" href="#pwreset"><i
                                        class="icon-envelope"></i>
                                    <?php echo __('Send Password Reset Email'); ?></a></li>
                            <?php
                        } ?>
                        <?php if ($thisstaff->hasPerm(User::PERM_MANAGE)) { ?>
                            <li><a class="user-action"
                                   href="#users/<?php echo $user->getId(); ?>/manage/access"><i
                                        class="icon-lock"></i>
                                    <?php echo __('Manage Account Access'); ?></a></li>
                            <?php
                        }
                    } ?>
                    <?php if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
                        <li><a href="#ajax.php/users/<?php echo $user->getId();
                            ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                            ><i class="icon-paste"></i>
                                <?php echo __('Manage Forms'); ?></a></li>
                    <?php } ?>

                </ul>
            </div>
<?php } ?>
        </td>
    </tr>
</table>
<!--<div class="avatar pull-left" style="margin: 10px; width: 80px;">-->
<!--    --><?php //echo $user->getAvatar(); ?>
<!--</div>-->
<table class="table table-condensed user_info" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td width="6%">
            <?php echo $user->getAvatar(); ?>
        </td>
        <td width="50%">
            <table width="100%">
                <tr>
                    <th style="width:30%; vertical-align: bottom"><?php echo __('Name'); ?>:</th>
                    <td style="vertical-align:bottom">
<?php
if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
                    <b><a href="#users/<?php echo $user->getId();
                    ?>/edit" class="user-action"><i
                        class="icon-edit"></i>
<?php }
                    echo Format::htmlchars($user->getName()->getOriginal());
if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
                        </a></b>
<?php } ?>
                    </td>
                </tr>
                <tr>
                    <th style="vertical-align:bottom"><?php echo __('Email'); ?>:</th>
                    <td style="vertical-align:bottom">
                        <span id="user-<?php echo $user->getId(); ?>-email"><?php echo $user->getEmail(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th style="vertical-align:bottom"><?php echo __('Organization'); ?>:</th>
                    <td style="vertical-align:bottom">
                        <span id="user-<?php echo $user->getId(); ?>-org">
                        <?php
                            if ($org)
                                echo sprintf('<a href="#users/%d/org" class="user-action">%s</a>',
                                        $user->getId(), $org->getName());
                            elseif ($thisstaff->hasPerm(User::PERM_EDIT)) {
                                echo sprintf(
                                    '<a href="#users/%d/org" class="user-action">%s</a>',
                                    $user->getId(),
                                    __('Add Organization'));
                            }
                        ?>
                        </span>
                    </td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align:bottom">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th style="width:30%; vertical-align: bottom"><?php echo __('Status'); ?>:</th>
                    <td style="vertical-align:bottom"> <span id="user-<?php echo $user->getId();
                    ?>-status"><?php echo $user->getAccountStatus(); ?></span></td>
                </tr>
                <tr>
                    <th style="vertical-align:bottom"><?php echo __('Created'); ?>:</th>
                    <td style="vertical-align:bottom"><?php echo Format::datetime($user->getCreateDate()); ?></td>
                </tr>
                <tr>
                    <th style="vertical-align:bottom"><?php echo __('Updated'); ?>:</th>
                    <td style="vertical-align:bottom"><?php echo Format::datetime($user->getUpdateDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<div class="clear"></div>
<ul class="nav nav-tabs" id="user-view-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tickets" role="tab"><i
    class="icon-list-alt"></i>&nbsp;<?php echo __('User Tickets'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#notes" role="tab"><i
    class="icon-pushpin"></i>&nbsp;<?php echo __('Notes'); ?></a></li>
</ul>
<div id="user-view-tabs_container" class="tab-content">
    <div id="tickets" class="tab-pane active" role="tabpanel">
    <?php
    include STAFFINC_DIR . 'templates/tickets.tmpl.php';
    ?>
    </div>

    <div class="tab-pane" id="notes" role="tabpanel">
    <?php
    $notes = QuickNote::forUser($user);
    $create_note_url = 'users/'.$user->getId().'/note';
    include STAFFINC_DIR . 'templates/notes.tmpl.php';
    ?>
    </div>
</div>
<div class="hidden dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm'); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>ban</b> %s?'), $user->getEmail()); ?>
        <br><br>
        <?php echo __('New tickets from the email address will be auto-rejected.'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="confirmlink-confirm">
        <?php echo sprintf(__(
        'Are you sure you want to send an <b>Account Activation Link</b> to <em> %s </em>?'),
        $user->getEmail()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="pwreset-confirm">
        <?php echo sprintf(__(
        'Are you sure you want to send a <b>Password Reset Link</b> to <em> %s </em>?'),
        $user->getEmail()); ?>
    </p>
    <div><?php echo __('Please confirm to continue.'); ?></div>
    <form action="users.php?id=<?php echo $user->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $user->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
<!--            <span class="buttons pull-left">-->
<!--                <input type="button" value="--><?php //echo __('Cancel'); ?><!--" class="close">-->
<!--            </span>-->
            <span class="btn-group-sm pull-right">
                <button class="btn btn-outline-primary" type="submit" value="<?php echo __('OK'); ?>">Okay</button>
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>

<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.user-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, [201, 204], function (xhr) {
            if (xhr.status == 204)
                window.location.href = 'users.php';
            else
                window.location.href = window.location.href;
            return false;
         }, {
            onshow: function() { $('#user-search').focus(); }
         });
        return false;
    });
});
</script>
