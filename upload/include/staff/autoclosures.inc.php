<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

$qs = array();
$sortOptions=array(
    'name' => 'name',
    'status' => 'isactive',
    'period' => 'time_period',
    'created' => 'created',
    'updated' => 'updated'
);

$orderWays = array('DESC'=>'DESC', 'ASC'=>'ASC');
$sort = ($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])]) ? strtolower($_REQUEST['sort']) : 'name';
if ($sort && $sortOptions[$sort]) {
    $order_column = $sortOptions[$sort];
}

$order_column = $order_column ? $order_column : 'name';

if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
    $order = $orderWays[strtoupper($_REQUEST['order'])];
} else {
    $order = 'ASC';
}

if ($order_column && strpos($order_column,',')) {
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = AutoClosure::objects()->count();
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);

$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('autoclosures.php', $qs);
$showing = $pageNav->showing().' '._N('Auto Close Plan', 'Auto Close Plans', $count);
$qstr .= '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');
?>
<div class="col-sm-12 col-md-12">
    <form action="autoclosures.php" method="POST" name="autoclosures">
        <div class="sticky bar opaque">
            <div class="content">
                <div class="pull-left">
                    <h2><?php echo __('Auto Close Plans');?></h2>
                </div>
                <div class="pull-right ">
                    <a href="#addAutoClosePlan" id="addBtn" class="green button action-button"><i class="icon-plus-sign"></i> <?php echo __('Add New Auto Close Plan');?></a>
                    <div class="btn-group">
                <span class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown">
                    <span ><i class="icon-cog"></i> <?php echo __('More');?></span>
                </span>
                        <ul id="actions" class="bleed-left dropdown-menu">
                            <li>
                                <a class="confirm" data-name="enable" href="autoclosures.php?a=enable">
                                    <i class="icon-ok-sign icon-fixed-width"></i>
                                    <?php echo __( 'Enable'); ?>
                                </a>
                            </li>
                            <li>
                                <a class="confirm" data-name="disable" href="autoclosures.php?a=disable">
                                    <i class="icon-ban-circle icon-fixed-width"></i>
                                    <?php echo __( 'Disable'); ?>
                                </a>
                            </li>
                            <li class="danger">
                                <a class="confirm" data-name="delete" href="autoclosures.php?a=delete">
                                    <i class="icon-trash icon-fixed-width"></i>
                                    <?php echo __( 'Delete'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="mass_process" >
        <input type="hidden" id="action" name="a" value="" >
        <table id="autoClosureTable" class="table table-condensed" border="0" cellspacing="1" cellpadding="0">
            <thead>
            <tr class="table-heading">
                <th width="4%">&nbsp;</th>
                <th width="38%"><a <?php echo $name_sort; ?> href="autoclosures.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name');?></a></th>
                <th width="8%"><a <?php echo $status_sort; ?> href="autoclosures.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status');?></a></th>
                <th width="15%"><a <?php echo $period_sort; ?> href="autoclosures.php?<?php echo $qstr; ?>&sort=period"><?php echo __('Time Period (hrs)');?></a></th>
                <th width="20%" nowrap><a <?php echo $created_sort; ?>href="autoclosures.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Date Added');?></a></th>
                <th width="20%" nowrap><a <?php echo $updated_sort; ?>href="autoclosures.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $total=0;
            $ids = ($errors && is_array($_POST['ids'])) ? $_POST['ids'] : null;
            if ($count) {
                $acs = AutoClosure::objects()
                    ->order_by(sprintf('%s%s',
                        strcasecmp($order, 'DESC') ? '' : '-',
                        $order_column))
                    ->limit($pageNav->getLimit())
                    ->offset($pageNav->getStart());

                $defaultId = $cfg->getDefaultAutoClosureId();
                /** @var AutoClosure $ac */
                foreach ($acs as $ac) {
                    $sel=false;
                    $id = $ac->getId();
                    if($ids && in_array($id, $ids))
                        $sel=true;

                    $default = '';
                    if ($id == $defaultId)
                        $default = '<small><em>(Default)</em></small>';
                    ?>
                    <tr id="<?php echo $id; ?>">
                        <td align="center">
                            <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $id; ?>"
                                <?php echo $sel ? 'checked="checked"' :'' ; ?>>
                        </td>
                        <td>&nbsp;<a href="#" class="no-pjax" value="<?php echo $id; ?>" id="<?php echo $id; ?>"><?php echo $ac->getName();
                                ?></a>&nbsp;<?php echo $default; ?></td>
                        <td><?php echo $ac->isActive() ? __('Active') : '<b>'.__('Disabled').'</b>'; ?></td>
                        <td style="text-align:right;padding-right:35px;"><?php echo $ac->getTimePeriod(); ?>&nbsp;</td>
                        <td>&nbsp;<?php echo $ac->getCreateDate(); ?></td>
                        <td>&nbsp;<?php echo $ac->getUpdateDate(); ?></td>
                    </tr>
                    <?php
                } //end of foreach.
            } ?>
            <tfoot>
            <tr>
                <td colspan="6">
                    <?php if ($count) { ?>
                        <?php echo __('Select');?>:&nbsp;
                        <button class="btn btn-sm btn-secondary"><a id="selectAll" href="#ckb"><?php echo __('All');?></a></button>
                        <button class="btn btn-sm btn-secondary"><a id="selectNone" href="#ckb"><?php echo __('None');?></a></button>
                        <button class="btn btn-sm btn-secondary"><a id="selectToggle" href="#ckb"><?php echo __('Toggle');?></a></button>
                    <?php }else{
                        echo __('No Auto Close plans found');
                    } ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <?php
        if ($count): //Show options..
            echo '<div>&nbsp;'.__('Page').':'.$pageNav->getPageLinks().'&nbsp;</div>';
            ?>

            <?php
        endif;
        ?>
    </form>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
            _N('selected Auto-Close plan', 'selected Auto-Close plans', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
            _N('selected Auto-Close plan', 'selected Auto-Close plans', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
                    _N('selected Auto-Close plan', 'selected Auto-Close plans', 2)); ?></strong></font>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('No, Cancel');?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('Yes, Do it!');?>" class="confirm">
        </span>
    </p>
    <div class="clear"></div>
</div>
<div class="modal fade" id="addAutoClosePlan">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" id="save">
                <input type="hidden" name="do" id="do" value="add">
                <input type="hidden" name="a" id="a" value="add">
                <input type="hidden" name="id" id="autoCloseId" value="">
                <?php csrf_token(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="title">Add New Auto Close Plan
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </h5>
                </div>
                <div class="modal-body">
                    <div style="padding: 0 15px; width: 100%;">
                        <div class="row">
                            <label class="required">Name:
                                <input type="text" class="form-control required" size="64" name="autoCloseName" id="autoCloseName" placeholder="Name"/>
                            </label>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <label class="form-check-label required" style="padding-left: 0;">Status:
                                    <input type="radio" name="isactive" id="isactive" value="1" checked> <?php echo __('Active'); ?>
                                    <input type="radio" name="isactive" id="notactive" value="0"> <?php echo __('Disabled'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <label class="required">Time Period:
                                <input type="number" class="form-control required" size="30" name="time_period" id="time_period" placeholder="Time Period (in hrs)"/>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary" id="submitButton">Add New Auto Close Plan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $(".modal").on("hidden.bs.modal", function() {
            $('#do').val('add');
            $('#a').val('add');
            $('#autoCloseId').val('');
            $('#isactive').prop('checked', true);
            $('#autoCloseName').val('');
            $('#time_period').val('');
            $('#title').text("Add New Auto Close Plan");
            $('#submitButton').text("Add New Auto Close Plan");
        });
    });
    $('#addBtn').click(function() {
        $('#addAutoClosePlan').modal('show');
    });

    $('#autoClosureTable').find('tr').click(function() {
        var id = $(this).attr('id');
        $.ajax({
            url : 'ajax.php/content/autoclosure/'+id,
            dataType : 'json',
            type : 'GET',
            success : function(data) {
                $('#do').val('update');
                $('#a').val('update');
                $('#autoCloseId').val(data.id);
                $('#title').text("Update Auto Close Plan");
                $('#submitButton').text("Update Auto Close Plan");
                $('#autoCloseName').val(data.name);
                $('#time_period').val(data.time_period);
                if (data.isactive == 1) {
                    $('#isactive').prop('checked', true);
                } else {
                    $('#notactive').prop('checked', true);
                }
                $('#addAutoClosePlan').modal('show');
            },
            error : function(xhr, textStatus, errorThrown) {
                alert(textStatus + " " + errorThrown);
            },
            header :{"Content-Type": "application/json"}
        });
    });
</script>
