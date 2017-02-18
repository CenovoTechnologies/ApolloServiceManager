<?php
if (!defined('OSTADMININC') || !$thisstaff->isAdmin()) die('Access Denied');


$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = Service::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('services.php', $_qstr);
$showing = $pageNav->showing().' '._N('service', 'services', $count);

$order_by = 'sort';

?>
<div class="col-sm-12 col-md-12">
    <form action="services.php" method="POST" name="services">
        <div class="sticky bar opaque">
            <div class="content">
                <div class="pull-left">
                    <h2><?php echo __('Service Catalogue');?></h2>
                </div>
                <div class="pull-right">
                    <?php if ($cfg->getServiceSortMode() != 'a') { ?>
                        <button class="button no-confirm" type="submit" name="sort"><i class="icon-save"></i>
                            <?php echo __('Save'); ?></button>
                    <?php } ?>
                    <a href="services.php?a=add" class="green button action-button"><i class="icon-plus-sign"></i> <?php echo __('Add New Service');?></a>
                    <div class="btn-group">
                <span class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown">
            <span ><i class="icon-cog"></i> <?php echo __('More');?></span>
                </span>
                        <ul id="actions" class="bleed-left dropdown-menu">
                            <li>
                                <a class="confirm" data-name="enable" href="services.php?a=enable">
                                    <i class="icon-ok-sign icon-fixed-width"></i>
                                    <?php echo __( 'Enable'); ?>
                                </a>
                            </li>
                            <li>
                                <a class="confirm" data-name="disable" href="services.php?a=disable">
                                    <i class="icon-ban-circle icon-fixed-width"></i>
                                    <?php echo __( 'Disable'); ?>
                                </a>
                            </li>
                            <li class="danger">
                                <a class="confirm" data-name="delete" href="services.php?a=delete">
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
        <input type="hidden" id="action" name="a" value="sort" >
        <table class="table table-condensed" border="0" cellspacing="1" cellpadding="0">

            <thead>
            <tr class="table-heading"><td colspan="7">
                    <div style="padding:3px" class="pull-right"><?php echo __('Sorting Mode'); ?>:
                        <select name="service_sort_mode" class="form-control-sm" onchange="javascript:
            var $form = $(this).closest('form');
            $form.find('input[name=a]').val('sort');
            $form.submit();
        ">
                            <?php foreach (OsticketConfig::allTopicSortModes() as $i=>$m)
                                echo sprintf('<option value="%s"%s>%s</option>',
                                    $i, $i == $cfg->getServiceSortMode() ? ' selected="selected"' : '', $m); ?>
                        </select>
                    </div>
                </td></tr>
            <tr class="table-active">
                <th width="4%" style="height:20px;">&nbsp;</th>
                <th style="padding-left:4px;vertical-align:middle" width="36%"><?php echo __('Service'); ?></th>
                <th style="padding-left:4px;vertical-align:middle" width="8%"><?php echo __('Status'); ?></th>
                <th style="padding-left:4px;vertical-align:middle" width="8%"><?php echo __('Type'); ?></th>
                <th style="padding-left:4px;vertical-align:middle" width="10%"><?php echo __('Priority'); ?></th>
                <th style="padding-left:4px;vertical-align:middle" width="14%"><?php echo __('Department'); ?></th>
                <th style="padding-left:4px;vertical-align:middle" width="20%" nowrap><?php echo __('Last Updated'); ?></th>
            </tr>
            </thead>
            <tbody class="<?php if ($cfg->getServiceSortMode() == 'm') echo 'sortable-rows'; ?>"
                   data-sort="sort-">
            <?php
            $ids= ($errors && is_array($_POST['ids'])) ? $_POST['ids'] : null;
            if ($count) {
                $services = Service::objects()
                    ->order_by(sprintf('%s%s',
                        strcasecmp($order, 'DESC') ? '' : '-',
                        $order_by))
                    ->limit($pageNav->getLimit())
                    ->offset($pageNav->getStart());

                $S = $services;
                $names = $services = array();
                /** @var Service $service */
                foreach ($S as $service) {
                    $names[$service->getId()] = $service->getFullName();
                    $services[$service->getId()] = $service;
                }
                if ($cfg->getServiceSortMode() != 'm')
                    $names = Internationalization::sortKeyedList($names);

                $defaultDept = $cfg->getDefaultDept();
                $defaultPriority = $cfg->getDefaultPriority();
                $sort = 0;
                foreach($names as $service_id=>$name) {
                    $service = $services[$service_id];
                    $id = $service->getId();
                    $sort++; // Track initial order for transition
                    $sel=false;
                    if ($ids && in_array($id, $ids))
                        $sel=true;

                    if ($service->dept_id) {
                        $deptId = $service->dept_id;
                        $dept = (string) $service->dept;
                    } elseif ($defaultDept) {
                        $deptId = $defaultDept->getId();
                        $dept = (string) $defaultDept;
                    } else {
                        $deptId = 0;
                        $dept = '';
                    }
                    $priority = $service->priority ?: $defaultPriority;
                    ?>
                    <tr id="<?php echo $id; ?>">
                        <td align="center">
                            <input type="hidden" name="sort-<?php echo $id; ?>" value="<?php
                            echo $service->sort ?: $sort; ?>"/>
                            <input type="checkbox" class="ckb" name="ids[]"
                                   value="<?php echo $id; ?>" <?php
                            echo $sel ? 'checked="checked"' : ''; ?>>
                        </td>
                        <td>
                            <?php
                            if ($cfg->getServiceSortMode() == 'm') { ?>
                                <i class="icon-sort faded"></i>
                            <?php } ?>
                            <a href="services.php?id=<?php echo $id; ?>"><?php
                                echo Service::getServiceName($id); ?></a>
                        </td>
                        <td><?php echo $service->isactive ? __('Active') : __('Disabled'); ?></td>
                        <td><?php echo $service->ispublic ? __('Public') : __('Private'); ?></td>
                        <td><?php echo $priority; ?></td>
                        <td><a href="departments.php?id=<?php echo $deptId;
                            ?>"><?php echo $dept; ?></a></td>
                        <td>&nbsp;<?php echo Format::datetime($service->updated); ?></td>
                    </tr>
                    <?php
                } //end of foreach.
            }?>
            <tfoot>
            <tr>
                <td colspan="7">
                    <?php if ($count) { ?>
                        <?php echo __('Select');?>:&nbsp;
                        <button class="btn btn-sm btn-secondary"><a id="selectAll" href="#ckb"><?php echo __('All');?></a></button>
                        <button class="btn btn-sm btn-secondary"><a id="selectNone" href="#ckb"><?php echo __('None');?></a></button>
                        <button class="btn btn-sm btn-secondary"><a id="selectToggle" href="#ckb"><?php echo __('Toggle');?></a></button>
                    <?php }else{
                        echo __('No services found');
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
            _N('selected help topic', 'selected help topics', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
            _N('selected help topic', 'selected help topics', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
                    _N('selected help topic', 'selected help topics', 2));?></strong></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="btn-group-sm pull-right">
            <button type="button" value="<?php echo __('Submit');?>" class="btn btn-outline-primary">Submit</button>
        </span>
    </p>
    <div class="clear"></div>
</div>
