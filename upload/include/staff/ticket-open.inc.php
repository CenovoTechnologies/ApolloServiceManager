<?php
if (!defined('OSTSCPINC') || !$thisstaff
    || !$thisstaff->hasPerm(TicketModel::PERM_CREATE, false))
    die('Access Denied');
$info=array();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$sla   = SLA::class;   //Ticket SLA
$type  = 0; //Ticket Service Type
$serv  = 0; //Ticket Service
$cat   = 0; //Ticket Service Category
$sub   = 0; //Ticket Sub Category
$tmpl  = 0;
if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();
$forms = array();
if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    foreach ($topic->getForms() as $F) {
        if (!$F->hasAnyVisibleFields())
            continue;
        if ($_POST) {
            $F = $F->instanciate();
            $F->isValidForClient();
        }
        $forms[] = $F;
    }
}
if ($_POST)
    $info['duedate'] = Format::date(strtotime($info['duedate']), false, false, 'UTC');
?>
<div class="col-sm-12 col-md-12">
    <form action="tickets.php?a=open" method="post" id="save"  enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="create">
        <input type="hidden" name="a" value="open">
        <div class="sticky bar">
            <div class="content">
                <nav class="navbar navbar-dark nav-no-padding">
                    <button type="submit" class="btn btn-sm btn-outline-primary" value="<?php echo _P('action-button', 'Open');?>">
                        <i class="icon-folder-open"></i> Open
                    </button>
                    <input type="reset" class="btn btn-sm btn-outline-secondary"  name="reset"  value="<?php echo __('Reset');?>">
                    <input type="button" class="btn btn-sm btn-outline-secondary" name="cancel" value="<?php echo __('Cancel');?>"
                           onclick="javascript:
                   $('.richtext').each(function() {
                    var redactor = $(this).data('redactor');
                    if (redactor && redactor.opts.draftDelete)
                    redactor.deleteDraft();
                   });
                    window.location.href='tickets.php';  ">
                </nav>
            </div>
        </div>
        <div class="col-sm-7 col-md-8">
            <div class="spacer"></div>
            <div class="flush-left card">
                <div class="card-header">
                    <ul  class="nav nav-tabs card-header-tabs" id="ticket_tabs" role="tablist">
                        <li class="nav-item"><a id="record-incident-tab" class="nav-link active" data-toggle="tab" href="#record-incident">Record</a></li>
                        <li class="nav-item"><a id="classify-incident-tab" class="nav-link" data-toggle="tab" href="#classify-incident">Classify</a></li>
                        <li class="nav-item"><a id="resolve-incident-tab" class="nav-link" data-toggle="tab" href="#resolve-incident">Resolve</a></li>
                        <li class="nav-item"><a id="close-incident-tab" class="nav-link" data-toggle="tab" href="#close-incident">Close</a></li>
                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane card-block active" id="record-incident" role="tabpanel">

                            <div class="col-md-8">
                                <div class="row">
                                    <label style="margin-bottom:0.1em;">
                                        <?php
                                        if ($user) { ?>
                                            <div id="user-info" class="input-group">
                                                <input type="hidden" class="form-control" name="uid" id="uid" value="<?php echo $user->getId(); ?>" />
                                                <input class="form-control required" name="user-name" type="text" id="client-name" aria-describedby="user-lookup"
                                                       value="<?php /** @var User $user */
                                                       echo $user->getName(); ?>  (<?php echo $user->getEmail(); ?>)" placeholder="Customer">
                                                <span class="input-group-btn" id="user-lookup">
                                                    <a type="button" class="btn btn-secondary user-search" id="customer-lookup"
                                                       href="#" onclick="javascript:
                                                            $.userLookup('ajax.php/users/<?php echo $user->getId(); ?>/edit',
                                                            function(user) {
                                                            $('input#user_id').val(user.id);
                                                            $('#client-name').text(user.name);
                                                            $('#client-email').text('<'+user.email+'>');
                                                            });
                                                            return false;">
                                                       <span class="icon-search"></span>
                                                    </a>
                                                </span>
                                            </div>
                                            <?php
                                        } else { //Fallback: Just ask for email and name?>
                                            <div class="input-group">
                                                <input class="form-control required" name="user-name" type="text" id="client-name" aria-describedby="user-lookup"
                                                       value="<?php echo $info['username'] . $info['email'];?>" placeholder="Customer">
                                                <span class="input-group-btn" id="user-lookup">
                                                    <a type="button" class="btn btn-secondary user-search" id="customer-lookup"
                                                       href="?a=open&amp;uid={id}" data-dialog="ajax.php/users/lookup/form" >
                                                       <span class="icon-search"></span>
                                                    </a>
                                                </span>
                                            </div>
                                            <?php
                                        } ?>
                                    </label>
                                </div>
                                <div class="row">
                                    <label style="width:50%;">
                                        <select class="form-control required" name="source" type="text" id="source">
                                            <option value="" selected ><?php echo __('Select Source');?> </option>
                                            <?php
                                            $source = $info['source'] ?: 'Phone';
                                            foreach (Ticket::getSources() as $k => $v) {
                                                echo sprintf('<option value="%s" %s>%s</option>',
                                                    $k,
                                                    ($source == $k ) ? 'selected="selected"' : '',
                                                    $v);
                                            }
                                            ?>
                                        </select>
                                    </label>
                                </div>
                                <div id="dynamic-form">
                                    <?php
                                    foreach ($forms as $form) {
                                        print $form->getForm()->getMedia();
                                        include(STAFFINC_DIR .  'templates/dynamic-form.tmpl.php');
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4" style="padding-right:0;">
                                <div class="d-inline">
                                    <h6 class="sidebar-heading">Identification & Logging</h6>
                                </div>
                                <div class="row" style="padding:0 15px;">
                                    <button class="btn btn-sm btn-secondary" style="width:100%" type="button" id="select-template-btn" title="Select Incident Template">Select Incident Template</button>
                                </div>
                                <div class="row" style="padding:0 15px;">
                                    <button class="btn btn-sm btn-secondary" style="width:100%" type="button" id="first-resolution-btn" title="First Contact Resolution">First Contact Resolution</button>
                                </div>
                            </div>
                    </div>
                    <div class="tab-pane card-block" id="classify-incident" role="tabpanel">
                            <div class="col-md-8">
                                <div class="row">
                                    <label style="width:49%">Service Type:
                                        <select class="form-control required" name="servTypeId" type="text" id="servTypeId" style="width:100%">
                                            <option value="0" selected="selected">&mdash; Select Service Type &mdash;</option>
                                            <?php
                                            if($servTypes=ServiceType::getAllServiceTypes()) {
                                                foreach($servTypes as $id =>$name) {
                                                    echo sprintf('<option value="%d" %s>%s</option>',
                                                        $id, ($type==$id)?'selected="selected"':'',$name);
                                                }
                                            }
                                            ?>
                                        </select>
                                    </label>
                                    <label style="width:49%" id="impact-input">Impact:
                                        <?php
                                        foreach ($forms as $form) {
                                            include(STAFFINC_DIR .  'templates/dynamic-form-impact.tmpl.php');
                                        }
                                        ?>
                                    </label>
                                </div>
                                <div class="row">
                                    <label style="width:49%">Service:
                                        <select class="form-control required" disabled name="serviceId" type="text" id="serviceId" style="width:100%">
                                            <option value="0" selected="selected">&mdash; Select Service &mdash;</option>
                                            <?php
                                            if($services=Service::getAllServices()) {
                                                foreach($services as $id =>$name) {
                                                    echo sprintf('<option value="%d" %s>%s</option>',
                                                        $id, ($serv==$id)?'selected="selected"':'',$name);
                                                }
                                            }
                                            ?>
                                        </select>
                                    </label>
                                    <label style="width:49%" id="urgency-input">Urgency:
                                        <?php
                                        foreach ($forms as $form) {
                                            include(STAFFINC_DIR .  'templates/dynamic-form-urgency.tmpl.php');
                                        }
                                        ?>
                                    </label>
                                </div>
                                <div class="row">
                                    <label style="width:49%">Category:
                                        <select class="form-control required" disabled name="serviceCatId" type="text" id="serviceCatId" style="width:100%">
                                            <option value="0" selected="selected">&mdash; Select Category &mdash;</option>
                                            <?php
                                            if($serviceCats=ServiceCat::getAllServiceCategories()) {
                                                foreach($serviceCats as $id =>$name) {
                                                    echo sprintf('<option value="%d" %s>%s</option>',
                                                        $id, ($cat==$id)?'selected="selected"':'',$name);
                                                }
                                            }
                                            ?>
                                        </select>
                                    </label>
                                    <label style="width:49%" id="priority-input">Priority:
                                        <?php
                                        foreach ($forms as $form) {
                                            include(STAFFINC_DIR .  'templates/dynamic-form-priority.tmpl.php');
                                        }
                                        ?>
                                    </label>
                                </div>
                                <div class="row">
                                    <label style="width:49%">Sub Category:
                                        <select class="form-control required" disabled name="serviceSubCatId" type="text" id="serviceSubCatId" style="width:100%">
                                            <option value="0" selected="selected">&mdash; Select Sub Category &mdash;</option>
                                            <?php
                                            if($serviceSubCats=ServiceSubCat::getAllServiceCategories()) {
                                                foreach($serviceSubCats as $id =>$name) {
                                                    echo sprintf('<option value="%d" %s>%s</option>',
                                                        $id, ($sub==$id)?'selected="selected"':'',$name);
                                                }
                                            }
                                            ?>
                                        </select>
                                    </label>
                                    <label style="width:49%">
                                        <input class="form-control" disabled name="item-input" type="text" id="item-input" placeholder="Configuration Item">
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4" style="padding-right:0;">
                                <div class="d-inline">
                                    <h6 class="sidebar-heading">Categorize & Prioritize</h6>
                                </div>
                                <label style="width:100%">
                                    <select class="form-control-sm" name="slaId" type="text" id="slaId" style="width:100%">
                                        <option value="0" selected="selected">Select Service Level Agreement</option>
                                        <?php
                                        if($slas=SLA::getSLAs()) {
                                            foreach($slas as $id =>$name) {
                                                echo sprintf('<option value="%d" %s>%s</option>',
                                                    $id, ($sla==$id)?'selected="selected"':'',$name);
                                            }
                                        }
                                        ?>
                                    </select>
                                </label>
                            </div>
                    </div>
                    <div class="tab-pane card-block" id="resolve-incident" role="tabpanel">
                        <div class="col-md-8">

                        </div>
                        <div class="col-md-4" style="padding-right:0;">
                            <div class="d-inline">
                                <h6 class="sidebar-heading">Resolution & Recovery</h6>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane card-block" id="close-incident" role="tabpanel">
                        <div class="col-md-8">

                        </div>
                        <div class="col-md-4" style="padding-right:0;">
                            <div class="d-inline">
                                <h6 class="sidebar-heading">Incident Closure</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="spacer"></div>
            <div class="flush-left card">
                <div class="card-header">
                    <?php
                    $tcount = 0;
                    ?>
                    <ul  class="nav nav-tabs card-header-tabs" id="ticket_tabs" role="tablist">
                        <li class="nav-item"><a id="incident-info-tab" class="nav-link active" data-toggle="tab" href="#incident-info"><?php echo __('Incident Information');?></a> </li>
                        <li class="nav-item"><a id="ticket-thread-tab" class="nav-link" data-toggle="tab" href="#ticket-thread" role="tab"><?php
                                echo sprintf(__('Incident Thread (%d)'), $tcount); ?></a></li>
                        <li class="nav-item"><a id="ticket-tasks-tab" class="nav-link" data-toggle="tab" href="#ticket-tasks" role="tab"><?php
                                echo __('Tasks'); ?>
                            </a></li>
                        <li class="nav-item <?php
                            echo isset($errors['reply']) ? 'error' : ''; ?>">
                            <a class="nav-link" data-toggle="tab" href="#ticket-reply" id="post-reply-tab"><?php echo __('Add Comment');?></a>
                        </li>

                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane card-block active" id="incident-info" role="tabpanel">
                        <div>


                        </div>
                    </div>
                    <div class="tab-pane card-block" id="ticket-thread" role="tabpanel">
                        <div>
                            <div class="dropdown btn-group">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Filter Thread
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" id="all-entries">All Thread Entries</a>
                                    <a class="dropdown-item" id="msg-entries">Messages</a>
                                    <a class="dropdown-item" id="resp-entries">Responses</a>
                                    <a class="dropdown-item" id="note-entries">Notes</a>
                                    <a class="dropdown-item" id="action-entries">Actions</a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div id="ticket-tasks" class="tab-pane card-block" role="tabpanel">

                    </div>
                    <div id="ticket-reply" class="tab-pane card-block" role="tabpanel">
                        <div class="col-sm-12 col-md-12">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-5 col-md-4">
            <div class="spacer"></div>
            <div class="d-inline">
                <div class="pull-right flush-right">
                    <h5><?php echo "New"; ?></h5>
                </div>
                <div class="flush-left">
                    <h5>
                        Open New Incident
                    </h5>
                </div>
            </div>
            <div>
                <h6 class="sidebar-heading">Requester Information</h6>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Customer</dt>
                    <dd class="col-sm-8 sidebar-detail"><?php echo "";?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Department</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" id="user-org"><?php echo "";?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Phone</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" id="user-phone"><?php echo "";?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Email</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" id="user-email"><?php echo ""; ?></dd>
                </dl>
            </div>
            <div class="d-inline">
                <h6 class="sidebar-heading">Ticket Information</h6>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Status</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ''; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Due Date</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Source</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Impact</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Urgency</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Priority</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">SLA Plan</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Service Type</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo "" ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Service</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Category</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Sub-Category</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div class="spacer"></div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">
                        <?php echo 'Assigned To'; ?>
                    </dt>
                    <dd class="col-sm-8 sidebar-detail align-middle">
                        <?php echo '<span class="faded">&mdash; ' . __('Unassigned') . ' &mdash;</span>'; ?>
                    </dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Create Date</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
            <div>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Close Date</dt>
                    <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ""; ?></dd>
                </dl>
            </div>
        </div>
        <div class="clear"></div>
    </form>
</div>
<div class="modal fade" id="chooseIncidentTmpl">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Choose Incident Template
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </h5>
            </div>
            <div class="modal-body">
                <label>
                    <select name="topicId" id="topicId" class="form-control" onchange="
                        var data = $(':input[name]', '#dynamic-form').serialize();
                        $.ajax(
                        'ajax.php/form/help-topic/' + this.value,{
                         data: data,
                         dataType: 'json',
                         success: function(json) {
                            $('#dynamic-form').empty().append(json.html);
                            $(document.head).append(json.media);
                         }
                         });">
                        <option value="" selected > <?php echo __('Select Incident Template...');?> </option>
                        <?php
                        if($topics=Topic::getHelpTopics()) {
                            foreach($topics as $id =>$name) {
                                echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($tmpl==$id)?'selected="selected"':'',$name);
                            }
                        }
                        ?>
                    </select>
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#select-template-btn').click(function() {
        $('#chooseIncidentTmpl').modal('show');
    });
    $('#impact-input').find('select').change(function() {
        if ($('#urgency-input').find('select').prop('selectedIndex') > 0) {
            changePriority();
        }
    });
    $('#urgency-input').find('select').change(function() {
        if ($('#impact-input').find('select').prop('selectedIndex') > 0) {
            changePriority();
        }
    });
    function changePriority() {
        var u = $('#urgency-input').find('select').val();
        var i = $('#impact-input').find('select').val();
        $.ajax({
            url : 'ajax.php/tickets/1/calculatePriority/'+i+'/'+u,
            dataType : 'text',
            type : 'GET',
            success : function(data) {
                $('#priority-input').find('select').val(data);
            },
            error : function(xhr, textStatus, errorThrown) {
                alert(textStatus + " " + errorThrown);
            }
        });
    }
    $('#servTypeId').change(function() {
        var pId = $('#servTypeId').val();
        if (pId > 0) {
            $('#serviceId').find('option').remove();
            $.ajax({
                url : 'ajax.php/tickets/0/filterServices/'+pId,
                dataType : 'json',
                type : 'GET',
                success : function(data) {
                    $("#serviceId").append("<option value=''>&mdash; Select Service &mdash;</option>").prop("disabled", false);
                    $.each(data, function(key, value){
                        $("#serviceId").append("<option value='"+value.id+"'>" + value.name + "</option>");
                    });
                },
                error : function(xhr, textStatus, errorThrown) {
                    alert(textStatus + " " + errorThrown);
                },
                header :{"Content-Type": "application/json"}
            });
        }
    });
    $('#serviceId').change(function() {
        var pId = $('#serviceId').val();
        if (pId > 0) {
            $('#serviceCatId').find('option').remove();
            $.ajax({
                url : 'ajax.php/tickets/0/filterCategories/'+pId,
                dataType : 'json',
                type : 'GET',
                success : function(data) {
                    $("#serviceCatId").append("<option value=''>&mdash; Select Category &mdash;</option>").prop("disabled", false);
                    $.each(data, function(key, value){
                        $("#serviceCatId").append("<option value='"+value.id+"'>" + value.name + "</option>");
                    });
                },
                error : function(xhr, textStatus, errorThrown) {
                    alert(textStatus + " " + errorThrown);
                },
                header :{"Content-Type": "application/json"}
            });
        }
    });
    $('#serviceCatId').change(function() {
        var pId = $('#serviceCatId').val();
        if (pId > 0) {
            $('#serviceSubCatId').find('option').remove();
            $.ajax({
                url : 'ajax.php/tickets/0/filterSubCategories/'+pId,
                dataType : 'json',
                type : 'GET',
                success : function(data) {
                    $("#serviceSubCatId").append("<option value=''>&mdash; Select Sub Category &mdash;</option>").prop("disabled", false);
                    $.each(data, function(key, value){
                        $("#serviceSubCatId").append("<option value='"+value.id+"'>" + value.name + "</option>");
                    });
                },
                error : function(xhr, textStatus, errorThrown) {
                    alert(textStatus + " " + errorThrown);
                },
                header :{"Content-Type": "application/json"}
            });
        }
    });
    $(function() {
        $('input#user-email').typeahead({
            source: function (typeahead, query) {
                $.ajax({
                    url: "ajax.php/users?q="+query,
                    dataType: 'json',
                    success: function (data) {
                        typeahead.process(data);
                    }
                });
            },
            onselect: function (obj) {
                $('#uid').val(obj.id);
                $('#user-name').val(obj.name);
                $('#user-email').val(obj.email);
            },
            property: "/bin/true"
        });
        <?php
        // Popup user lookup on the initial page load (not post) if we don't have a
        // user selected
        if (!$_POST && !$user) {?>
        setTimeout(function() {
            $.userLookup('ajax.php/users/lookup/form', function (user) {
                window.location.href = window.location.href+'&uid='+user.id;
            });
        }, 100);
        <?php
        } ?>
    });
</script>
