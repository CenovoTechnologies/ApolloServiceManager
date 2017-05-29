<?php
$report = new OverviewReport($_POST['start'], $_POST['period']);
$plots = $report->getPlotData();

?>
<script type="text/javascript" src="js/raphael-min.js?907ec36"></script>
<script type="text/javascript" src="js/g.raphael.js?907ec36"></script>
<script type="text/javascript" src="js/g.line-min.js?907ec36"></script>
<script type="text/javascript" src="js/g.dot-min.js?907ec36"></script>
<script type="text/javascript" src="js/dashboard.inc.js?907ec36"></script>

<link rel="stylesheet" type="text/css" href="css/dashboard.css?907ec36"/>
<div class="col-sm-12 col-md-12">
<form method="post" action="dashboard.php">

<!--<div style="margin-bottom:20px; padding-top:5px;">
    <div class="pull-left flush-left">
        <h2><?php /*echo __('Ticket Activity');
            */?>&nbsp;<i class="help-tip icon-question-sign" href="#ticket_activity"></i></h2>
    </div>
</div>
<div class="clear"></div>-->
<!-- Create a graph and fetch some data to create pretty dashboard -->
<!--<div style="position:relative">
    <div id="line-chart-here" style="height:300px"></div>
    <div style="position:absolute;right:0;top:0" id="line-chart-legend"></div>
</div>-->

<hr/>
    <div class="row">
        <div class="col-sm-4">
            <a href="tickets.php?status=assigned" style="width:100%;">
                <div class="card card-outline-info">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-inbox"></i> Incidents Assigned to Me</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="tickets.php?status=progress" style="width:100%;">
                <div class="card card-outline-info">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-tasks"></i> Incidents In Progress</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="tickets.php?status=unassigned" style="width:100%;">
                <div class="card card-outline-info">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-ticket"></i> Unclaimed Incidents</h4>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <a href="tasks.php?status=assigned" style="width:100%;">
                <div class="card card-outline-info">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-pencil-square-o"></i> Tasks Assigned to Me</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="tickets.php?status=overdue" style="width:100%;">
                <div class="card card-outline-info">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-exclamation-circle"></i> Past Due Incidents</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="#" style="width:100%;">
                <div class="card card-outline-info">
                    <div class="card-block">
                        <h4 class="card-title">
                            <i class="fa fa-clone"></i> Open Incidents By Category
                        </h4>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <a href="tickets.php?status=open" style="width:100%;">
                <div class="card card-outline-info" href="#">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-ticket"></i> New Incidents</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="task.php" style="width:100%;">
                <div class="card card-outline-info">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-pencil-square"></i> New Tasks</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a href="#" style="width:100%;">
                <div class="card card-outline-info" href="#">
                    <div class="card-block">
                        <h4 class="card-title"><i class="fa fa-tags"></i> Open Incidents By Type</h4>
                    </div>
                </div>
            </a>
        </div>
    </div>

<h2><?php echo __('Statistics'); ?>&nbsp;<i class="help-tip icon-question-sign" href="#statistics"></i></h2>
<p><?php echo __('Statistics of tickets organized by department, help topic, and agent.');?></p>

    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="stat_tabs" role="tablist">
            <?php
            $first = true;
            $groups = $report->enumTabularGroups();
            foreach ($groups as $g=>$desc) { ?>
                <li class="nav-item">
                    <a href="#<?php echo Format::slugify($g); ?>" class="nav-link <?php echo $first ? 'active' : ''; ?>" data-toggle="tab">
                        <?php echo Format::htmlchars($desc); ?>
                    </a>
                </li>
                <?php
                $first = false;
            } ?>
        </ul>
    </div>
    <div class="tab-content">
<?php
$first = true;
foreach ($groups as $g=>$desc) {
    $data = $report->getTabularData($g); ?>
    <div class="tab-pane card-block <?php echo ($first) ? 'active' : ''; ?>" id="<?php echo Format::slugify($g); ?>" role="tabpanel">
    <table class="dashboard-stats table">
        <thead>
        <tr class="table-heading">
<?php
    foreach ($data['columns'] as $j=>$c) { ?>
        <th <?php if ($j === 0) echo 'width="30%" class="flush-left"'; ?>><?php echo Format::htmlchars($c); ?></th>
<?php
    } ?>
    </tr>
        </thead>
    <tbody>
<?php
    foreach ($data['data'] as $i=>$row) {
        echo '<tr>';
        foreach ($row as $j=>$td) {
            if ($j === 0) { ?>
                <th class="flush-left"><?php echo Format::htmlchars($td); ?></th>
<?php       }
            else { ?>
                <td><?php echo Format::htmlchars($td);
                if ($td) { // TODO Add head map
                }
                echo '</td>';
            }
        }
        echo '</tr>';
    }
    $first = false; ?>
    </tbody>
    </table>
    <div style="margin-top: 5px"><button type="submit" class="link button" name="export"
        value="<?php echo Format::htmlchars($g); ?>">
        <i class="icon-download"></i>
        <?php echo __('Export'); ?></button></div>
    </div>
<?php
}
?>
    </div>
</form>
    </div>
<script>
    $.drawPlots(<?php echo JsonDataEncoder::encode($report->getPlotData()); ?>);
</script>
