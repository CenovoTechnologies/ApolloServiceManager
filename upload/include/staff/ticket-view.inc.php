<?php
//Note that ticket obj is initiated in tickets.php.
/** @var Staff $thisstaff */
/** @var Ticket $ticket */
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffPerm($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$role  = $thisstaff->getRole($dept);
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();   //Ticket SLA
$type  = $ticket->getServiceTypeId(); //Ticket Service Type
$serv  = $ticket->getServiceId(); //Ticket Service
$cat   = $ticket->getCategoryId(); //Ticket Service Category
$sub   = $ticket->getSubCategoryId(); //Ticket Sub Category
$tmpl  = $ticket->getHelpTopic();
$resCode = $ticket->getResolutionCodeId(); //Resolution Code
$autoClose = $ticket->getAutoClosePlanId(); //Auto Close Plan
$lock  = $ticket->getLock();  //Ticket lock obj
if (!$lock && $cfg->getTicketLockMode() == Lock::MODE_ON_VIEW)
    $lock = $ticket->acquireLock($thisstaff->getId());
/** @var Lock $mylock */
$mylock = ($lock && $lock->getStaffId() == $thisstaff->getId()) ? $lock : null;
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(
            __('Current ticket status (%s) does not allow the end user to reply.'),
            $ticket->getStatus());
elseif ($ticket->isAssigned()
        && (($staff && $staff->getId()!=$thisstaff->getId())
            || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.= sprintf('&nbsp;&nbsp;<span class="Icon assignedTicket">%s</span>',
            sprintf(__('Ticket is assigned to %s'),
                implode('/', $ticket->getAssignees())
                ));

if (!$errors['err']) {

    if ($lock && $lock->getStaffId()!=$thisstaff->getId())
        $errors['err'] = sprintf(__('This ticket is currently locked by %s'),
                $lock->getStaffName());
    elseif (($emailBanned=Banlist::isBanned($ticket->getEmail())))
        $errors['err'] = __('Email is in banlist! Must be removed before any reply/response');
    elseif (!Validator::is_valid_email($ticket->getEmail()))
        $errors['err'] = __('EndUser email address is not valid! Consider updating it before responding');
}

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'.__('Marked overdue!').'</span>';

$info=array();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();

$forms = array();
if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    /** @var DynamicForm $F */
    /** @var Topic $topic */
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

?>
<div class="col-sm-12 col-md-12">
    <div class="sticky bar">
       <div class="content">
           <nav class="navbar navbar-dark nav-no-padding">
               <?php
               if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
                   <a href="#post-reply" class="btn btn-default post-response action-button" type="button"
                      data-placement="bottom" data-toggle="tooltip"
                      title="<?php echo __('Post Reply'); ?>"><i class="icon-mail-reply"></i></a>
                   <?php
               } ?>
               <a href="#post-note" id="post-note" class="btn btn-default post-response action-button" type="button"
                  data-placement="bottom" data-toggle="tooltip"
                  title="<?php echo __('Post Internal Note'); ?>"><i class="icon-file-text"></i></a>
               <?php // Status change options
               echo TicketStatus::status_options();
               // Assign
               /** @var Ticket $ticket */
               if (($ticket->isOpen() || $ticket->isStarted() || $ticket->isResolved()) && $role->hasPerm(TicketModel::PERM_ASSIGN)) {?>
                   <div class="btn-group">
                       <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown" title=" <?php echo $ticket->isAssigned() ? __('Assign') : __('Reassign'); ?>">
                           <i class="icon-user"></i>
                       </button>
                       <ul class="dropdown-menu" id="action-dropdown-assign">
                           <?php
                           // Agent can claim team assigned ticket
                           if (!$ticket->getStaff()
                           && (!$dept->assignMembersOnly()
                               || $dept->isMember($thisstaff))
                           ) { ?>
                           <li><a class="no-pjax ticket-action"
                                  data-redirect="tickets.php"
                                  href="#tickets/<?php echo $ticket->getId(); ?>/claim"><i
                                           class="icon-chevron-sign-down"></i> <?php echo __('Claim'); ?></a>
                               <?php
                               } ?>
                           <li><a class="no-pjax ticket-action"
                                  data-redirect="tickets.php"
                                  href="#tickets/<?php echo $ticket->getId(); ?>/assign/agents"><i
                                           class="icon-user"></i> <?php echo __('Agent'); ?></a>
                           <li><a class="no-pjax ticket-action"
                                  data-redirect="tickets.php"
                                  href="#tickets/<?php echo $ticket->getId(); ?>/assign/teams"><i
                                           class="icon-group"></i> <?php echo __('Team'); ?></a>
                       </ul>
                   </div>
                   <?php
               }
               // Transfer
               if ($role->hasPerm(TicketModel::PERM_TRANSFER)) {?>
                   <a type="button" class="btn btn-default ticket-action action-button" id="ticket-transfer" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Transfer');?>"
                      data-redirect="tickets.php" href="#tickets/<?php echo $ticket->getId(); ?>/transfer" role="button"><i class="icon-share"></i></a>
                   <?php
               } ?>
               <div class="btn-group">
                   <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown" title="<?php echo __('Print');?>" aria-haspopup="true" aria-expanded="false">
                       <i class="icon-print"></i>
                   </button>
                   <ul class="dropdown-menu">
                       <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=0"><i
                                       class="icon-file-alt"></i> <?php echo __('Ticket Thread'); ?></a>
                       <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=1"><i
                                       class="icon-file-text-alt"></i> <?php echo __('Thread + Internal Notes'); ?></a>
                   </ul>
               </div>
               <a type="button" class="btn btn-default action-button" title="<?php echo __('Promote to Problem'); ?>" href="#" role="button">
                   <i class="icon-signin"></i>
               </a>
               <?php
               /** @var Staff $thisstaff */
               /** @var Role $role */
               if ($thisstaff->hasPerm(Email::PERM_BANLIST)
                   || $role->hasPerm(TicketModel::PERM_EDIT)
                   || ($dept && $dept->isManager($thisstaff))) { ?>
                   <div class="btn-group">
                       <button type="button" class="btn btn-default dropdown-toggle action-button"
                               data-toggle="dropdown" title="<?php echo __('More'); ?>" aria-haspopup="true"
                               aria-expanded="false">
                           <i class="icon-cog"></i> <?php echo __('More'); ?>
                       </button>
                       <ul class="dropdown-menu">
                           <?php
                           if ($role->hasPerm(TicketModel::PERM_EDIT)) { ?>
                               <li><a class="change-user" href="#tickets/<?php
                                   echo $ticket->getId(); ?>/change-user"><i class="icon-user"></i> <?php
                                       echo __('Change Owner'); ?></a></li>
                               <?php
                           }

                           if (($ticket->isOpen() || $ticket->isStarted()) && ($dept && $dept->isManager($thisstaff))) {

                               if ($ticket->isAssigned()) { ?>
                                   <li><a class="confirm-action" id="ticket-release" href="#release"><i
                                                   class="icon-user"></i> <?php
                                           echo __('Release (unassign) Ticket'); ?></a></li>
                                   <?php
                               }

                               if (!$ticket->isOverdue()) { ?>
                                   <li><a class="confirm-action" id="ticket-overdue" href="#overdue"><i
                                                   class="icon-bell"></i> <?php
                                           echo __('Mark as Overdue'); ?></a></li>
                                   <?php
                               }

                               if ($ticket->isAnswered()) { ?>
                                   <li><a class="confirm-action" id="ticket-unanswered" href="#unanswered"><i
                                                   class="icon-circle-arrow-left"></i> <?php
                                           echo __('Mark as Unanswered'); ?></a></li>
                                   <?php
                               } else { ?>
                                   <li><a class="confirm-action" id="ticket-answered" href="#answered"><i
                                                   class="icon-circle-arrow-right"></i> <?php
                                           echo __('Mark as Answered'); ?></a></li>
                                   <?php
                               }
                           } ?>
                           <?php
                           if ($role->hasPerm(Ticket::PERM_EDIT)) { ?>
                               <li><a href="#ajax.php/tickets/<?php echo $ticket->getId();
                                   ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                                   ><i class="icon-paste"></i> <?php echo __('Manage Forms'); ?></a></li>
                               <?php
                           } ?>

                           <?php if ($thisstaff->hasPerm(Email::PERM_BANLIST)) {
                               if (!$emailBanned) { ?>
                                   <li><a class="confirm-action" id="ticket-banemail"
                                          href="#banemail"><i class="icon-ban-circle"></i> <?php echo sprintf(__('Ban Email <%s>'),
                                               $ticket->getEmail()); ?></a></li>
                                   <?php
                               } elseif ($unbannable) { ?>
                                   <li><a class="confirm-action" id="ticket-banemail"
                                          href="#unbanemail"><i class="icon-undo"></i> <?php echo sprintf(__('Unban Email <%s>'),
                                               $ticket->getEmail()); ?></a></li>
                                   <?php
                               }
                           }
                           if ($role->hasPerm(TicketModel::PERM_DELETE)) {
                               ?>
                               <li class="danger"><a class="ticket-action" href="#tickets/<?php
                                   echo $ticket->getId(); ?>/status/delete"
                                                     data-redirect="tickets.php"><i class="icon-trash"></i> <?php
                                       echo __('Delete Ticket'); ?></a></li>
                               <?php
                           }
                           ?>
                       </ul>
                   </div>
                   <?php
               }?>
           </nav>
        </div>
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
                <form action="tickets.php?id=<?php echo $ticket->getId(); ?>&a=record" method="post" id="saveRecord"  enctype="multipart/form-data">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="do" value="recordIncident">
                    <input type="hidden" name="a" value="record">
                    <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
                    <div class="col-md-8">
                        <div class="row">
                            <label style="margin-bottom:0.1em;">
                                <div class="input-group">
                                    <input type="hidden" class="form-control" name="uid" id="uid" value="<?php echo $user->getId(); ?>" />
                                    <input class="form-control required" name="user-name" type="text" id="client-name" aria-describedby="user-lookup"
                                           value="<?php /** @var User $user */
                                           echo $user->getName(); ?>  (<?php echo $user->getEmail(); ?>)" placeholder="Customer">
                                    <span class="input-group-btn" id="user-lookup">
                                        <a type="button" class="btn btn-secondary user-search" id="customer-lookup"
                                           href="#" onclick="javascript:
                                                $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/change-user',
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
                            </label>
                        </div>
                        <div class="row">
                            <label style="width:50%;">
                                <select class="form-control required" name="source" type="text" id="source">
                                    <option value="" selected >&mdash; <?php
                                        echo __('Select Source');?> &mdash;</option>
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
                        <div class="row" style="padding:0 15px;">
                            <input class="btn btn-sm btn-outline-primary" style="width:100%" type="submit" id="record-submit" name="submit" value="<?php echo __('Save');?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane card-block" id="classify-incident" role="tabpanel">
                <form action="tickets.php?id=<?php echo $ticket->getId(); ?>&a=classify" method="post" id="saveClassify"  enctype="multipart/form-data">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="do" value="update">
                    <input type="hidden" name="a" value="edit">
                    <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
                    <div class="col-md-8">
                        <div class="row">
                            <label style="width:49%">Service Type:
                                <select class="form-control" name="servTypeId" type="text" id="servTypeId" style="width:100%">
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
                                foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
                                    include(STAFFINC_DIR .  'templates/dynamic-form-impact.tmpl.php');
                                }
                                ?>
                            </label>
                        </div>
                        <div class="row">
                            <label style="width:49%">Service:
                                <select class="form-control" disabled name="serviceId" type="text" id="serviceId" style="width:100%">
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
                                foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
                                    include(STAFFINC_DIR .  'templates/dynamic-form-urgency.tmpl.php');
                                }
                                ?>
                            </label>
                        </div>
                        <div class="row">
                            <label style="width:49%">Category:
                                <select class="form-control" disabled name="serviceCatId" type="text" id="serviceCatId" style="width:100%">
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
                                foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
                                    include(STAFFINC_DIR .  'templates/dynamic-form-priority.tmpl.php');
                                }
                                ?>
                            </label>
                        </div>
                        <div class="row">
                            <label style="width:49%">Sub Category:
                                <select class="form-control" disabled name="serviceSubCatId" type="text" id="serviceSubCatId" style="width:100%">
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
                                <input class="form-control" name="item-input" type="text" id="item-input" placeholder="Configuration Item">
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
                        <div class="row" style="padding:0 15px;">
                            <input class="btn btn-sm btn-outline-primary" style="width:100%" type="submit" id="record-submit" name="submit" value="<?php echo __('Save');?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane card-block" id="resolve-incident" role="tabpanel">
                <form action="tickets.php?id=<?php echo $ticket->getId(); ?>#resolve" name="resolve" method="post" enctype="multipart/form-data">
                <?php csrf_token(); ?>
                    <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
                    <input type="hidden" name="a" value="resolve">
                    <input type="hidden" name="do" value="resolve">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="error"><?php echo $errors['resolve']; ?></div>
                        </div>
                        <div class="row">
                            <label style="width:49%">Resolution Code:
                                <select class="form-control" name="resolution_code_id" id="resolution_code_id">
                                    <option value="0" selected="selected">&mdash; Select Resolution Code &mdash;</option>
                                    <?php
                                    if($resCodes=ResolutionCode::getResolutionCodes()) {
                                        foreach($resCodes as $id =>$name) {
                                            echo sprintf('<option value="%d" %s>%s</option>',
                                                $id, ($resCode==$id)?'selected="selected"':'',$name['name']);
                                        }
                                    }
                                    ?>
                                </select>
                            </label>
                            <label style="width:49%">Auto Close Plan:
                                <select class="form-control" name="auto_close_plan_id" id="auto_close_plan_id">
                                    <option value="0" selected="selected">&mdash; Select Auto Close Plan &mdash;</option>
                                    <?php
                                    if($autoClosures=AutoClosure::getAutoClosures()) {
                                        foreach($autoClosures as $id =>$name) {
                                            if ($name['active']) { ?>
                                                <option value="<?php echo $id; ?>"
                                                    <?php echo ($autoClose == $id) ? 'selected' : ''; ?>>
                                                    <?php echo $name['name'] . " (" . $name['time'] . " hours)"; ?>
                                                </option>
                                                <?php
                                            }
                                        }
                                    }?>
                                </select>
                            </label>
                        </div>
                        <div class="row">
                            <label>Resolution Notes:
                                <?php
                                $signature = '';
                                switch ($thisstaff->getDefaultSignatureType()) {
                                    case 'dept':
                                        /** @var Dept $dept */
                                        if ($dept && $dept->canAppendSignature())
                                            $signature = $dept->getSignature();
                                        break;
                                    case 'mine':
                                        $signature = $thisstaff->getSignature();
                                        break;
                                } ?>
                                <input type="hidden" name="draft_id" value=""/>
                                <textarea name="resolveResponse" id="resolveResponse" cols="50"
                                          data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                                          data-signature="<?php
                                          echo Format::viewableImages($signature); ?>"
                                          placeholder="Detailed description of the steps used to resolve this incident"
                                          rows="12" wrap="soft"
                                          class="form-control <?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                          ?> draft draft-delete">
                                </textarea>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-right:0;">
                        <div class="d-inline">
                            <h6 class="sidebar-heading">Resolution & Recovery</h6>
                        </div>
                        <div class="row" style="padding:0 15px;">
                            <input class="btn btn-sm btn-outline-primary" style="width:100%" type="submit" id="resolveBtn" name="submit" value="<?php echo __('Resolve Incident');?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane card-block" id="close-incident" role="tabpanel">
                <form action="tickets.php?id=<?php echo $ticket->getId(); ?>#close" name="resolve" method="post" enctype="multipart/form-data">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
                    <input type="hidden" name="a" value="close">
                    <input type="hidden" name="do" value="close">
                    <div class="col-md-8">
                        <div class="row">
                            <label>Closing Notes:
                                <input type="hidden" name="draft_id" value=""/>
                                <textarea name="closeResponse" id="closeResponse" cols="50"
                                          placeholder="Closing Notes"
                                          rows="12" wrap="soft"
                                          class="form-control <?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                          ?> draft draft-delete">
                                </textarea>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-right:0;">
                        <div class="d-inline">
                            <h6 class="sidebar-heading">Incident Closure</h6>
                            <div class="row" style="padding:0 15px;">
                                <input class="btn btn-sm btn-outline-primary" style="width:100%" type="submit" id="closeBtn" name="submit" value="<?php echo __('Close Incident');?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="spacer"></div>
    <div class="flush-left card">
        <div class="card-header">
            <?php
            $tcount = $ticket->getThreadEntries($types)->count();
            ?>
            <ul  class="nav nav-tabs card-header-tabs" id="ticket_tabs" role="tablist">
                <li class="nav-item"><a id="incident-info-tab" class="nav-link active" data-toggle="tab" href="#incident-info"><?php echo __('Incident Information');?></a> </li>
                <li class="nav-item"><a id="ticket-thread-tab" class="nav-link" data-toggle="tab" href="#ticket-thread" role="tab"><?php
                        echo sprintf(__('Incident Thread (%d)'), $tcount); ?></a></li>
                <li class="nav-item"><a id="ticket-tasks-tab" class="nav-link" data-toggle="tab" href="#ticket-tasks" role="tab"><?php
                        echo __('Tasks');
                        if ($ticket->getNumTasks())
                            echo sprintf('&nbsp;(<span id="ticket-tasks-count">%d</span>)', $ticket->getNumTasks());
                        ?></a></li>
                <?php
                if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
                    <li class="nav-item <?php
                    echo isset($errors['reply']) ? 'error' : ''; ?>"><a class="nav-link" data-toggle="tab"
                                href="#ticket-reply" id="post-reply-tab"><?php echo __('Add Comment');?></a></li>
                    <?php
                } ?>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane card-block active" id="incident-info" role="tabpanel">
                <div>
                    <h4 class="card-title"><?php
                        $subject_field = TicketForm::getInstance()->getField('subject');
                        echo $subject_field->display($ticket->getSubject()); ?></h4>
                    <p class="card-text"><?php $message_field = TicketForm::getInstance()->getField('message');
                        echo $ticket->getFirstMessage(); ?></p>
                    <?php
                    foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
                        // Skip core fields shown earlier in the ticket view
                        // TODO: Rewrite getAnswers() so that one could write
                        //       ->getAnswers()->filter(not(array('field__name__in'=>
                        //           array('email', ...))));
                        $answers = $form->getAnswers()->exclude(Q::any(array(
                            'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
                            'field__name__in' => array('subject', 'priority', 'impact', 'urgency')
                        )));
                        $displayed = array();
                        /** @var DynamicFormEntryAnswer $a */
                        foreach($answers as $a) {
                            if (!($v = $a->display()))
                                continue;
                            $displayed[] = array($a->getLocal('label'), $v);
                        }
                        if (count($displayed) == 0)
                            continue;
                        ?>
                        <div>
                            <?php
                            foreach ($displayed as $stuff) {
                                list($label, $v) = $stuff;
                            ?>
                            <dl class="row">
                                <dt class="col-sm-3 sidebar-label align-middle"><?php echo $label; ?></dt>
                                <dd class="col-sm-9 sidebar-detail align-middle"><?php echo $v; ?></dd>
                            </dl>
                            <?php } ?>
                        </div>
                    <?php } ?>
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
                    <?php
                    // Render ticket thread
                    $ticket->getThread()->render(
                        array('M', 'R', 'N'),
                        array(
                            'html-id'   => 'ticketThread',
                            'mode'      => Thread::MODE_STAFF,
                            'sort'      => $thisstaff->thread_view_order
                        )
                    );
                    ?>
                </div>
            </div>
            <div id="ticket-tasks" class="tab-pane card-block" role="tabpanel">
                <?php
                include STAFFINC_DIR . 'templates/tasks.tmpl.php';
                ?>
            </div>
            <div id="ticket-reply" class="tab-pane card-block" role="tabpanel">
                <div class="col-sm-12 col-md-12">
                    <div class="btn-group">
                        <label>
                            <input type="radio" name="convooptions" id="opt1" checked>&nbsp;Reply to Conversation
                        </label>
                        <label>
                            <input type="radio" name="convooptions" id="opt2">&nbsp;Add an Internal Note
                        </label>
                    </div>
                    <div id="reply-div">
                        <?php
                        if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
                        <form id="reply" class="tab_content spellcheck exclusive"
                              data-lock-object-id="ticket/<?php echo $ticket->getId(); ?>"
                              data-lock-id="<?php echo $mylock ? $mylock->getId() : ''; ?>"
                              action="tickets.php?id=<?php
                              echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
                            <?php csrf_token(); ?>
                            <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
                            <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
                            <input type="hidden" name="a" value="reply">
                            <input type="hidden" name="lockCode" value="<?php echo $mylock ? $mylock->getCode() : ''; ?>">
                            <div class="row">
                                <div class="error"><?php echo $errors['reply'] ?></div>
                            </div>
                            <div class="row">
                                <label class="required">To:
                                    <?php
                                    # XXX: Add user-to-name and user-to-email HTML ID#s
                                    $to =sprintf('%s &lt;%s&gt;',
                                        $ticket->getName(),
                                        $ticket->getReplyToEmail());
                                    $emailReply = (!isset($info['emailreply']) || $info['emailreply']);
                                    ?>
                                    <select id="emailreply" name="emailreply" class="form-control">
                                        <option value="1" <?php echo $emailReply ?  'selected="selected"' : ''; ?>><?php echo $to; ?></option>
                                        <option value="0" <?php echo !$emailReply ? 'selected="selected"' : ''; ?>
                                        > <?php echo __('Do Not Email Reply'); ?> </option>
                                    </select>
                                </label>
                            </div>
                            <div class="row">
                                <label><?php echo __('Collaborators'); ?>:
                                    <input type='checkbox' value='1' name="emailcollab" class="form-control"
                                           id="t<?php echo $ticket->getThreadId(); ?>-emailcollab"
                                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
                                           style="display:<?php echo $ticket->getThread()->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
                                    >
                                    <?php
                                    $recipients = __('Add Recipients');
                                    if ($ticket->getThread()->getNumCollaborators())
                                        $recipients = sprintf(__('Recipients (%d of %d)'),
                                            $ticket->getThread()->getNumActiveCollaborators(),
                                            $ticket->getThread()->getNumCollaborators());

                                    echo sprintf('<span><a class="collaborators preview"
                                    href="#thread/%d/collaborators"><span id="t%d-recipients">%s</span></a></span>',
                                        $ticket->getThreadId(),
                                        $ticket->getThreadId(),
                                        $recipients);
                                    ?>
                                </label>
                            </div>
                            <div class="row">
                                <div class="error"><?php echo $errors['response']; ?></div>
                            </div>
                            <div class="row">
                                <label class="required">Response:
                                    <?php if ($cfg->isCannedResponseEnabled()) { ?>
                                        <select id="cannedResp" name="cannedResp" class="form-control">
                                            <option value="0" selected="selected"><?php echo __('Select a canned response');?></option>
                                            <option value='original'><?php echo __('Original Message'); ?></option>
                                            <option value='lastmessage'><?php echo __('Last Message'); ?></option>
                                            <?php
                                            if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {
                                                echo '<option value="0" disabled="disabled">
                                        ------------- '.__('Premade Replies').' ------------- </option>';
                                                foreach($cannedResponses as $id =>$title)
                                                    echo sprintf('<option value="%d">%s</option>',$id,$title);
                                            }
                                            ?>
                                        </select>
                                    <?php } # endif (canned-response-enabled)?>
                                </label>
                            </div>
                            <div class="row">
                                <label>
                                    <?php $signature = '';
                                    switch ($thisstaff->getDefaultSignatureType()) {
                                        case 'dept':
                                            /** @var Dept $dept */
                                            if ($dept && $dept->canAppendSignature())
                                                $signature = $dept->getSignature();
                                            break;
                                        case 'mine':
                                            $signature = $thisstaff->getSignature();
                                            break;
                                    } ?>
                                    <input type="hidden" name="draft_id" value="">
                                    <textarea name="response" id="response" cols="50"
                                              data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                                              data-signature="<?php
                                              echo Format::viewableImages($signature); ?>"
                                              placeholder="Start writing your response here. Use canned responses from the drop-down above"
                                              rows="9" wrap="soft"
                                              class="form-control <?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                              ?> draft draft-delete" <?php
                                    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.response', $ticket->getId(), $info['response']);
                                    echo $attrs; ?>></textarea>
                                </label>
                                <div id="reply_form_attachments" class="attachments" style="margin:10px 12px;">
                                    <?php
                                    print $response_form->getField('attachments')->render();
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <label><?php echo __('Signature');?>:
                                    <?php
                                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                                    ?>
                                    <label>
                                        <input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?>
                                    </label>
                                    <?php
                                    if($thisstaff->getSignature()) {?>
                                        <label>
                                            <input type="radio" name="signature" value="mine"
                                                <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?>
                                        </label>
                                        <?php
                                    } ?>
                                    <?php
                                    /** @var Dept $dept */
                                    if($dept && $dept->canAppendSignature()) { ?>
                                        <label>
                                            <input type="radio" name="signature" value="dept"
                                                <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                                            <?php echo sprintf(__('Department Signature (%s)'), $dept->getName()); ?>
                                        </label>
                                        <?php
                                    } ?>
                                </label>
                            </div>
                            <div class="row">
                                <label class="required"><?php echo __('Ticket Status');?>:
                                    <?php
                                    $outstanding = false;
                                    if ($role->hasPerm(TicketModel::PERM_CLOSE)
                                        && is_string($warning=$ticket->isCloseable())) {
                                        $outstanding =  true;
                                        echo sprintf('<div class="warning-banner">%s</div>', $warning);
                                    } ?>
                                    <select name="reply_status_id" class="form-control">
                                        <?php
                                        $statusId = $info['reply_status_id'] ?: $ticket->getStatusId();
                                        $states = array('open', 'progress', 'resolved');
                                        if ($role->hasPerm(TicketModel::PERM_CLOSE) && !$outstanding)
                                            $states = array_merge($states, array('closed'));

                                        foreach (TicketStatusList::getStatuses(
                                            array('states' => $states)) as $s) {
                                            if (!$s->isEnabled()) continue;
                                            $selected = ($statusId == $s->getId());
                                            echo sprintf('<option value="%d" %s>%s%s</option>',
                                                $s->getId(),
                                                $selected
                                                    ? 'selected="selected"' : '',
                                                __($s->getName()),
                                                $selected
                                                    ? (' ('.__('current').')') : ''
                                            );
                                        }
                                        ?>
                                    </select>
                                </label>
                            </div>
                            <p  style="text-align:right;">
                                <input class="btn btn-sm btn-outline-primary" type="submit" value="<?php echo __('Post Reply');?>">
                                <input class="btn btn-sm btn-outline-secondary" type="reset" value="<?php echo __('Reset');?>">
                            </p>
                        </form>
                    </div>
                    <?php
                    } ?>
                    <div id="note-div" style="display:none">
                        <form id="note" class="tab_content spellcheck exclusive"
                              data-lock-object-id="ticket/<?php echo $ticket->getId(); ?>"
                              data-lock-id="<?php echo $mylock ? $mylock->getId() : ''; ?>"
                              action="tickets.php?id=<?php echo $ticket->getId(); ?>#note"
                              name="note" method="post" enctype="multipart/form-data">
                            <?php csrf_token(); ?>
                            <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
                            <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime() * 60; ?>">
                            <input type="hidden" name="a" value="postnote">
                            <input type="hidden" name="lockCode" value="<?php echo $mylock ? $mylock->getCode() : ''; ?>">
                            <div class="row">
                                <label>
                                    <input type="text" class="form-control" name="title" id="title" size="60" value="<?php echo $info['title']; ?>"
                                        placeholder="<?php echo __('Note title - summary of the note (optional)'); ?>">
                                </label>
                                <br/>
                                <span class="error">&nbsp;<?php echo $errors['title']; ?></span>
                            </div>
                            <div class="row">
                                <div class="error"><?php echo $errors['note']; ?></div>
                            </div>
                            <div class="row">
                                <label>
                                    <textarea name="note" id="internal_note" cols="80"
                                              placeholder="<?php echo __('Write your notes here'); ?>"
                                              rows="9" wrap="soft"
                                              class="form-control <?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                              ?> draft draft-delete" <?php
                                    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.note', $ticket->getId(), $info['note']);
                                    echo $attrs; ?>></textarea>
                                </label>
                                <div class="attachments" style="margin:10px 12px;">
                                    <?php
                                    print $note_form->getField('attachments')->render();
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <label class="required"><?php echo __('Ticket Status');?>:
                                    <select name="note_status_id" class="form-control">
                                        <?php
                                        $statusId = $info['note_status_id'] ?: $ticket->getStatusId();
                                        $states = array('open', 'progress', 'resolved');
                                        if ($ticket->isCloseable() === true
                                            && $role->hasPerm(TicketModel::PERM_CLOSE))
                                            $states = array_merge($states, array('closed'));
                                        foreach (TicketStatusList::getStatuses(
                                            array('states' => $states)) as $s) {
                                            if (!$s->isEnabled()) continue;
                                            $selected = $statusId == $s->getId();
                                            echo sprintf('<option value="%d" %s>%s%s</option>',
                                                $s->getId(),
                                                $selected ? 'selected="selected"' : '',
                                                __($s->getName()),
                                                $selected ? (' ('.__('current').')') : ''
                                            );
                                        }
                                        ?>
                                    </select>
                                </label>
                            </div>
                            <br/><br/>
                            <p style="text-align:right;">
                                <input class="btn btn-sm btn-outline-primary" type="submit" value="<?php echo __('Post Note');?>">
                                <input class="btn btn-sm btn-secondary" type="reset" value="<?php echo __('Reset');?>">
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-5 col-md-4">
    <div class="spacer"></div>
    <div class="d-inline">
        <div class="pull-right flush-right">
            <h5><?php echo ($S = $ticket->getStatus()) ? $S->display() : ''; ?></h5>
        </div>
        <div class="flush-left">
            <h5><a href="tickets.php?id=<?php echo $ticket->getId(); ?>"
                   title="<?php echo __('Incident'); ?>">
                    <?php echo sprintf(__('Ticket #%s'), $ticket->getNumber()); ?></a>
            </h5>
        </div>
    </div>
    <div>
        <h6 class="sidebar-heading">Requester Information</h6>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Customer</dt>
            <dd class="col-sm-8 sidebar-detail">
                <a href="#tickets/<?php echo $ticket->getId(); ?>/user" onclick="
                        $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                        function (user) {
                        $('#user-'+user.id+'-name').text(user.name);
                        $('#user-'+user.id+'-email').text(user.email);
                        $('#user-'+user.id+'-phone').text(user.phone);
                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                        });
                        return false">
                    <i class="icon-user align-middle"></i>
                    <span class="align-middle" id="user-<?php echo $ticket->getOwnerId(); ?>-name">
                        <?php echo $ticket->getName();?></span>
                </a>
                <?php
                if ($user) { ?>
                    <a href="tickets.php?<?php echo Http::build_query(array(
                        'status'=>'open', 'a'=>'search', 'uid'=> $user->getId()
                    )); ?>" title="<?php echo __('Related Tickets'); ?>"
                       data-dropdown="#action-dropdown-stats">
                        (<b><?php echo $user->getNumTickets(); ?></b>)
                    </a>
                    <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                        <ul>
                            <?php
                            if(($open=$user->getNumOpenTickets()))
                                echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %s</a></li>',
                                    $user->getId(), sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open));

                            if(($started=$user->getNumStartedTickets()))
                                echo sprintf('<li><a href="tickets.php?a=search&status=progress&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %s</a></li>',
                                    $user->getId(), sprintf(_N('%d Ticket in Progress', '%d Tickets in Progress', $started), $started));

                            if(($resolved=$user->getNumResolvedTickets()))
                                echo sprintf('<li><a href="tickets.php?a=search&status=resolved&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %s</a></li>',
                                    $user->getId(), sprintf(_N('%d Resolved Ticket', '%d Resolved Tickets', $resolved), $resolved));

                            if(($closed=$user->getNumClosedTickets()))
                                echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i
                                                class="icon-folder-close-alt icon-fixed-width"></i> %s</a></li>',
                                    $user->getId(), sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed));
                            ?>
                            <li><a href="tickets.php?a=search&uid=<?php echo $ticket->getOwnerId(); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> <?php echo __('All Tickets'); ?></a></li>
                            <?php   if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
                                <li><a href="users.php?id=<?php echo
                                    $user->getId(); ?>"><i class="icon-user
                                    icon-fixed-width"></i> <?php echo __('Manage User'); ?></a></li>
                            <?php   } ?>
                        </ul>
                    </div>
                <?php                   } # end if ($user) ?>
            </dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Department</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" id="user-<?php echo $ticket->getOwnerId(); ?>-org"><?php echo $user->getOrganization();?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Phone</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" id="user-<?php echo $ticket->getOwnerId(); ?>-phone"><?php echo $user->getPhone();?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Email</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" id="user-<?php echo $ticket->getOwnerId(); ?>-email"><?php echo $ticket->getEmail(); ?></dd>
        </dl>
    </div>
    <div class="d-inline">
        <h6 class="sidebar-heading">Ticket Information</h6>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Status</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo ($S = $ticket->getStatus()) ? $S->display() : ''; ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Due Date</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getEstDueDate(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Source</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getSource(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Impact</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getImpact(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Urgency</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getUrgency(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Priority</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getPriority(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">SLA Plan</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $sla?$sla->getName():'<span class="faded">&mdash; '.__('None').' &mdash;</span>'; ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Service Type</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getServiceType(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Service</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getService(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Category</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getCategory(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Sub-Category</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getSubCategory(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Resolution Code</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getResolutionCode(); ?></dd>
        </dl>
        <div>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Auto Close Plan</dt>
                <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getAutoClosePlan(); ?></dd>
            </dl>
        </div>
    </div>
    <div class="spacer"></div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">
            <?php
            if($ticket->isOpen() || $ticket->isStarted() || $ticket->isResolved()) {
                echo 'Assigned To';
            } else {
                 echo 'Closed By';}?>
            </dt>
            <dd class="col-sm-8 sidebar-detail align-middle">
                <?php
                if($ticket->isOpen() || $ticket->isStarted() || $ticket->isResolved()) {
                    if ($ticket->isAssigned()) {
                        echo implode('/', $ticket->getAssignees());
                    } else {
                        echo '<span class="faded">&mdash; ' . __('Unassigned') . ' &mdash;</span>';
                    }
                } else {
                    if(($staff = $ticket->getStaff()))
                        echo $staff->getName();
                    else
                        echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
                }?>
            </dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Create Date</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getCreateDate(); ?></dd>
        </dl>
    </div>
    <div>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">Close Date</dt>
            <dd class="col-sm-8 sidebar-detail align-middle" ><?php echo $ticket->getCloseDate(); ?></dd>
        </dl>
    </div>
</div>
<div class="clear"></div>
<div style="display:none;" class="dialog" id="print-options">
    <h3><?php echo __('Ticket Print Options');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>"
        method="post" id="print-form" name="print-form" target="_blank">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label class="fixed-size" for="notes"><?php echo __('Print Notes');?>:</label>
            <label class="inline checkbox">
            <input type="checkbox" id="notes" name="notes" value="1"> <?php echo __('Print <b>Internal</b> Notes/Comments');?>
            </label>
        </fieldset>
        <fieldset>
            <label class="fixed-size" for="psize"><?php echo __('Paper Size');?>:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; <?php echo __('Select Print Paper Size');?> &mdash;</option>
                <?php
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach(Export::$paper_sizes as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', __($v));
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="reset" value="<?php echo __('Reset');?>">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('Print');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
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
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        <?php echo __('Are you sure you want to <b>claim</b> (self assign) this ticket?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>answered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>unanswered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <font color="red"><b>overdue</b></font>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>ban</b> %s?'), $ticket->getEmail());?> <br><br>
        <?php echo __('New tickets from the email address will be automatically rejected.');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>remove</b> %s from ban list?'), $ticket->getEmail()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>unassign</b> ticket from <b>%s</b>?'), $ticket->getAssigned()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <?php echo sprintf('%s <%s> will longer have access to the ticket',
            '<b>'.$ticket->getName().'</b>', $ticket->getEmail()); ?>
        </span>
        <?php echo sprintf(__('Are you sure you want to <b>change</b> ticket owner to %s?'),
            '<b><span id="newuser">this guy</span></b>'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(
            __('Are you sure you want to DELETE %s?'), __('this ticket'));?></strong></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered, including any associated attachments.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('OK');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<script type="text/javascript">
$(function() {
    $(window).load( function() {
        if ($('#serviceId').val() > 0) {
            $('#serviceId').prop("disabled", false);
        }
        if ($('#serviceCatId').val() > 0) {
            $('#serviceCatId').prop("disabled", false);
        }
        if ($('#serviceSubCatId').val() > 0) {
            $('#serviceSubCatId').prop("disabled", false);
        }
    });
    $(document).ready(function() {
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
                url : 'ajax.php/tickets/<?php echo $ticket->getId(); ?>/calculatePriority/'+i+'/'+u,
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
                    url : 'ajax.php/tickets/<?php echo $ticket->getId(); ?>/filterServices/'+pId,
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
                    url : 'ajax.php/tickets/<?php echo $ticket->getId(); ?>/filterCategories/'+pId,
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
                    url : 'ajax.php/tickets/<?php echo $ticket->getId(); ?>/filterSubCategories/'+pId,
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
    });
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        console.log(url);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').show().trigger('click');
            }
        });
    });

    // Post Reply or Note action buttons.
    $('a.post-response').click(function (e) {
        var $r = $('ul.tabs > li > a'+$(this).attr('href')+'-tab');
        if ($r.length) {
            // Make sure ticket thread tab is visiable.
            var $t = $('ul#ticket_tabs > li > a#ticket-thread-tab');
            if ($t.length && !$t.hasClass('active'))
                $t.trigger('click');
            // Make the target response tab active.
            if (!$r.hasClass('active'))
                $r.trigger('click');

            // Scroll to the response section.
            var $stop = $(document).height();
            var $s = $('div#response_options');
            if ($s.length)
                $stop = $s.offset().top-125;

            $('html, body').animate({scrollTop: $stop}, 'fast');
        }

        return false;
    });

    $('#select-template-btn').click(function() {
       $('#chooseIncidentTmpl').modal('show');
    });
    $('#opt1').click(function() {
        $('#reply-div').show();
        $('#note-div').hide();
    });
    $('#opt2').click(function() {
        $('#reply-div').hide();
        $('#note-div').show();
    });
    $('#all-entries').click(function() {
        $('.message').show();
        $('.note').show();
        $('.response').show();
        $('.action').show();
    });
    $('#msg-entries').click(function() {
        $('.message').show();
        $('.note').hide();
        $('.response').hide();
        $('.action').hide();
    });
    $('#resp-entries').click(function() {
        $('.message').hide();
        $('.note').hide();
        $('.response').show();
        $('.action').hide();
    });
    $('#note-entries').click(function() {
        $('.message').hide();
        $('.note').show();
        $('.response').hide();
        $('.action').hide();
    });
    $('#action-entries').click(function() {
        $('.action').show();
        $('.note').hide();
        $('.response').hide();
        $('.message').hide();
    });

});
</script>
