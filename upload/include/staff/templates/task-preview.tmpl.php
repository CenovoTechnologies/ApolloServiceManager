<?php
$error=$msg=$warn=null;

/** @var Task $task */
if (!$task->checkStaffPerm($thisstaff))
     $warn.= __('You do not have access to this task');
elseif ($task->isOverdue())
    $warn.='&nbsp;<span class="Icon overdueTicket">'.__('Marked overdue!').'</span>';

echo sprintf(
        '<div style="width:600px; padding: 2px 2px 0 5px;" id="t%s">
         <h2>'.__('Task #%s').': %s</h2>',
         $task->getNumber(),
         $task->getNumber(),
         $task->getTitle());

if($error)
    echo sprintf('<div id="msg_error">%s</div>',$error);
elseif($msg)
    echo sprintf('<div id="msg_notice">%s</div>',$msg);
elseif($warn)
    echo sprintf('<div id="msg_warning">%s</div>',$warn);

echo '<ul class="nav nav-tabs" id="task-preview" role="tablist">';

echo '
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">
                <i class="icon-list-alt"></i>&nbsp;'.__('Task Summary').'
            </a>
        </li>';
if ($task->getThread()->getNumCollaborators()) {
    echo sprintf('
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" id="collab_tab" href="#collab" role="tab">
                <i class="icon-fixed-width icon-group faded"></i>&nbsp;'.__('Collaborators (%d)').'
            </a>
        </li>',
            $task->getThread()->getNumCollaborators());
}
echo '</ul>';
echo '<div class="tab-content" id="task-preview_container">';
echo '<div class="tab-pane active" id="summary" role="tabpanel">';
echo '<div class="col-sm-12">';
$status=sprintf('<span>%s</span>',ucfirst($task->getStatus()));
echo sprintf('
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">'.__('Status').'</dt>
            <dd class="col-sm-8 sidebar-detail">%s</dd>
        </dl>
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">'.__('Created').'</dt>
            <dd class="col-sm-8 sidebar-detail">%s</dd>
        </dl>',$status,
        $task->getCreateDate());

if ($task->isClosed()) {

    echo sprintf('
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">'.__('Completed').'</dt>
                <dd class="col-sm-8 sidebar-detail">%s</dd>
            </dl>',
            $task->getCloseDate());

} elseif ($task->isOpen() && $task->duedate) {
    echo sprintf('
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">'.__('Due Date').'</dt>
                <dd class="col-sm-8 sidebar-detail">%s</dd>
            </dl>',
            $task->duedate);
}
echo '</div>';


echo '<hr>
    <div class="col-sm-12">';
if ($task->isOpen()) {
    echo sprintf('
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">'.__('Assigned To').'</dt>
                <dd class="col-sm-8 sidebar-detail">%s</dd>
            </dl>', $task->getAssigned() ?: ' <span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>');
}
echo sprintf(
    '
        <dl class="row">
            <dt class="col-sm-4 sidebar-label align-middle">'.__('Department').'</dt>
            <dd class="col-sm-8 sidebar-detail">%s</dd>
        </dl>', $task->dept->getName());

echo '
    </div>';
echo '</div>';
?>
<?php
//TODO: add link to view if the user has permission
?>
<div class="tab-pane" id="collab" role="tabpanel">
    <div class="col-sm-12">
        <div class="spacer"></div>
        <?php
        /** @var Collaborator $collab */
        if (($collabs=$task->getThread()->getCollaborators())) {?>
        <?php
            foreach($collabs as $collab) {
                echo sprintf('<div class="row"><label>%s<i class="icon-%s"></i>
                        <a href="users.php?id=%d" class="no-pjax">%s</a><div class="font-italic">&lt;%s&gt;</div></label></div>',
                        ($collab->isActive()? '' : 'class="faded"'),
                        ($collab->isActive()? 'comments' :  'comment-alt'),
                        $collab->getUserId(),
                        $collab->getName(),
                        $collab->getEmail());
            }
        }  else {
            echo __("Task doesn't have any collaborators.");
        }?>
    </div>
    <br>
    <?php
    echo sprintf('<span><a class="collaborators"
                            href="#thread/%d/collaborators">%s</a></span>',
                            $task->getThreadId(),
                            $task->getThread()->getNumCollaborators()
                                ? __('Manage Collaborators') : __('Add Collaborator')
                                );
    ?>
</div>
</div>
</div>
