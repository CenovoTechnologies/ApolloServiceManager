<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
$pages = Page::getPages();
?>
<div class="col-sm-12 col-md-12">
<h2><?php echo __('Company Profile'); ?></h2>
<form action="settings.php?t=pages" method="post" id="save"
    enctype="multipart/form-data">
<?php csrf_token(); ?>



<input type="hidden" name="t" value="pages" >

<ul class="nav nav-tabs">
    <li class="nav-item"><a <a class="nav-link active" data-toggle="tab" href="#basic-information" role="tab"><i class="icon-asterisk"></i>
        <?php echo __('Basic Information'); ?></a></li>
    <li class="nav-item"><a <a class="nav-link" data-toggle="tab" href="#site-pages" role="tab"><i class="icon-file"></i>
        <?php echo __('Site Pages'); ?></a></li>
    <li class="nav-item"><a <a class="nav-link" data-toggle="tab" href="#logos" class="tab"><i class="icon-picture"></i>
        <?php echo __('Logos'); ?></a></li>
    <li class="nav-item"><a <a class="nav-link" data-toggle="tab" href="#backdrops" class="tab"><i class="icon-picture"></i>
        <?php echo __('Login Backdrop'); ?></a></li>
</ul>
<div class="tab-content">
<div class="tab-pane active" id="basic-information" role="tabpanel">
<table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
    <tbody>
    <?php
        $form = $ost->company->getForm();
        $form->addMissingFields();
        $form->render();
    ?>
    </tbody>
</table>
</div>
<div class="tab-pane" id="site-pages" role="tabpanel">
<table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo sprintf(__(
                'To edit or add new pages go to %s Manage &gt; Site Pages %s'),
                '<a href="pages.php">','</a>'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width:20%" class="required"><?php echo __('Landing Page'); ?>:</td>
            <td>
                <span>
                <select class="form-control-sm" name="landing_page_id">
                    <option value="">&mdash; <?php echo __('Select Landing Page'); ?> &mdash;</option>
                    <?php
                    foreach($pages as $page) {
                        if(strcasecmp($page->getType(), 'landing')) continue;
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $page->getId(),
                                ($config['landing_page_id']==$page->getId())?'selected="selected"':'',
                                $page->getName());
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['landing_page_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#landing_page"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td class="required"><?php echo __('Offline Page'); ?>:</td>
            <td>
                <span>
                <select class="form-control-sm" name="offline_page_id">
                    <option value="">&mdash; <?php echo __('Select Offline Page');
                        ?> &mdash;</option>
                    <?php
                    foreach($pages as $page) {
                        if(strcasecmp($page->getType(), 'offline')) continue;
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $page->getId(),
                                ($config['offline_page_id']==$page->getId())?'selected="selected"':'',
                                $page->getName());
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['offline_page_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#offline_page"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td class="required"><?php
                echo __('Default Thank-You Page'); ?>:</td>
            <td>
                <span>
                <select class="form-control-sm" name="thank-you_page_id">
                    <option value="">&mdash; <?php
                        echo __('Select Thank-You Page'); ?> &mdash;</option>
                    <?php
                    foreach($pages as $page) {
                        if(strcasecmp($page->getType(), 'thank-you')) continue;
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $page->getId(),
                                ($config['thank-you_page_id']==$page->getId())?'selected="selected"':'',
                                $page->getName());
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['thank-you_page_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_thank_you_page"></i>
                </span>
            </td>
        </tr>
    </tbody>
</table>
</div>
<div class="tab-pane" id="logos" role="tabpanel">
<table class="table table-condensed table-bordered" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo __('System Default Logo'); ?><i class="help-tip icon-question-sign" href="#logos"></i>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <table style="width:100%">
                    <thead>
                        <tr class="table-heading" >
                            <th>Client</th>
                            <th>Staff</th>
                            <th>Logo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="radio" class="form-check-input" name="selected-logo" value="0"
                                       style="margin-left: 1em"
                                       <?php if (!$ost->getConfig()->getClientLogoId())
                                        echo 'checked="checked"'; ?>/>
                            </td>
                            <td>
                                <input type="radio" class="form-check-input" name="selected-logo-scp" value="0"
                                       style="margin-left: 1em"
                                       <?php if (!$ost->getConfig()->getStaffLogoId())
                                            echo 'checked="checked"'; ?>/>
                            </td>
                            <td>
                                <img src="<?php echo ROOT_PATH; ?>assets/default/images/logo.png"
                                     alt="Default Logo" class="img-fluid"
                                     style="box-shadow: 0 0 0.5em rgba(0,0,0,0.5);
                                            margin: 0.5em; height: 5em;
                                            vertical-align: middle"/>
                                <img src="<?php echo ROOT_PATH; ?>scp/images/asm-logo.png"
                                     alt="Default Logo" class="img-fluid"
                                     style="box-shadow: 0 0 0.5em rgba(0,0,0,0.5);
                                            margin: 0.5em; height: 5em;
                                            vertical-align: middle"/>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="3">
                                <?php echo __('Use a custom logo'); ?>&nbsp;<i class="help-tip icon-question-sign" href="#upload_a_new_logo"></i>
                            </th>
                        </tr>
                        <?php
                        $current = $ost->getConfig()->getClientLogoId();
                        $currentScp = $ost->getConfig()->getStaffLogoId();
                        foreach (AttachmentFile::allLogos() as $logo) { ?>
                        <tr>
                            <td>
                                <input type="radio" class="form-check-input" name="selected-logo"
                                       style="margin-left: 1em" value="<?php
                            echo $logo->getId(); ?>" <?php
                            if ($logo->getId() == $current)
                                echo 'checked="checked"'; ?>/>
                            </td>
                            <td>
                                <input type="radio" class="form-check-input" name="selected-logo-scp"
                                       style="margin-left: 1em" value="<?php
                            echo $logo->getId(); ?>" <?php
                            if ($logo->getId() == $currentScp)
                                echo 'checked="checked"'; ?>/>
                            </td>
                            <td>
                                <img src="<?php echo $logo->getDownloadUrl(); ?>"
                                     alt="Custom Logo" class="img-fluid"
                                     style="box-shadow: 0 0 0.5em rgba(0,0,0,0.5);
                                            margin: 0.5em; height: 5em;
                                            vertical-align: middle;"/>
                                <?php if ($logo->getId() != $current && $logo->getId() != $currentScp) { ?>
                                <label class="checkbox inline">
                                    <input type="checkbox" name="delete-logo[]" value="<?php
                                    echo $logo->getId(); ?>"/> <?php echo __('Delete'); ?>
                                </label>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php echo __('Upload a new logo'); ?>:
                <input type="file" class="form-control-file" name="logo[]" size="30" value="" />
                <font class="error"><br/><?php echo $errors['logo']; ?></font>
            </td>
        </tr>
    </tbody>
</table>
</div>

<div class="tab-pane" id="backdrops" role="tabpanel">
<table class="table table-condensed table-bordered" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo __('System Default Backdrop'); ?><i
                class="help-tip icon-question-sign" href="#backdrops"></i>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <table style="width:100%">
                    <thead>
                        <tr class="table-heading">
                            <th>Staff</th>
                            <th>Backdrop</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="radio" class="form-check-input" name="selected-backdrop" value="0"
                                       style="margin-left: 1em"
                                       <?php if (!$ost->getConfig()->getStaffLogoId())
                                            echo 'checked="checked"'; ?>/>
                            </td>
                            <td>
                                <img src="<?php echo ROOT_PATH; ?>scp/images/login-headquarters.jpg"
                                     alt="Default Backdrop" class="img-fluid"
                                     style="box-shadow: 0 0 0.5em rgba(0,0,0,0.5);
                                            margin: 0.5em; height: 6em;
                                            vertical-align: middle"/>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                <?php echo __('Use a custom backdrop');
                                ?>&nbsp;<i class="help-tip icon-question-sign" href="#upload_a_new_backdrop"></i>
                            </th>
                        </tr>
                        <?php
                        $current = $ost->getConfig()->getStaffLoginBackdropId();
                        foreach (AttachmentFile::allBackdrops() as $logo) { ?>
                        <tr>
                            <td>
                                <input type="radio" class="form-check-input" name="selected-backdrop"
                                       style="margin-left: 1em" value="<?php
                            echo $logo->getId(); ?>" <?php
                            if ($logo->getId() == $current)
                                echo 'checked="checked"'; ?>/>
                            </td>
                            <td>
                                <img src="<?php echo $logo->getDownloadUrl(); ?>"
                                     alt="Custom Backdrop" class="img-fluid"
                                     style="box-shadow: 0 0 0.5em rgba(0,0,0,0.5);
                                            margin: 0.5em; height: 6em;
                                            vertical-align: middle;"/>
                                <?php if ($logo->getId() != $current) { ?>
                                <label class="checkbox inline">
                                    <input type="checkbox" name="delete-backdrop[]" value="<?php
                                    echo $logo->getId(); ?>"/> <?php echo __('Delete'); ?>
                                </label>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table
                    <?php echo __('Upload a new backdrop'); ?>:
                <input type="file" class="form-control-file" name="backdrop[]" size="30" value="" />
                <font class="error"><br/><?php echo $errors['backdrop']; ?></font>
            </td>
        </tr>
    </tbody>
</table>
</div>
</div>
<p style="text-align:left;">
    <button class="btn btn-sm btn-outline-primary" type="submit" name="submit-button" value="<?php
    echo __('Save Changes'); ?>">Save Changes</button>
    <button class="btn btn-sm btn-secondary" type="reset" name="reset" value="<?php
    echo __('Reset Changes'); ?>">Reset Changes</button>
</p>
</form>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm'); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(
        __('Are you sure you want to DELETE %s?'),
        _N('selected image', 'selected images', 2)); ?></strong></font>
        <br/><br/><?php echo __('Deleted data CANNOT be recovered.'); ?>
    </p>
    <div><?php echo __('Please confirm to continue.'); ?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('No, Cancel'); ?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('Yes, Do it!'); ?>" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>

<script type="text/javascript">
$(function() {
    $('#save input:submit.button').bind('click', function(e) {
        var formObj = $('#save');
        if ($('input:checkbox:checked', formObj).length) {
            e.preventDefault();
            $('.dialog#confirm-action').undelegate('.confirm');
            $('.dialog#confirm-action').delegate('input.confirm', 'click', function(e) {
                e.preventDefault();
                $('.dialog#confirm-action').hide();
                $('#overlay').hide();
                formObj.submit();
                return false;
            });
            $('#overlay').show();
            $('.dialog#confirm-action .confirm-action').hide();
            $('.dialog#confirm-action p#delete-confirm')
            .show()
            .parent('div').show().trigger('click');
            return false;
        }
        else return true;
    });
});
</script>
