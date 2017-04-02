<?php
/*
 * Ticket Preview popup template
 *
 */

/** @var Ticket $ticket */
$staff = $ticket->getStaff();
$lock = $ticket->getLock();
/** @var Staff $thisstaff */
$role = $thisstaff->getRole($ticket->getDeptId());
$error = $msg = $warn = null;
$thread = $ticket->getThread();

if ($lock && $lock->getStaffId() == $thisstaff->getId())
    $warn .= '&nbsp;<span class="Icon lockedTicket">'
        . sprintf(__('Ticket is locked by %s'), $lock->getStaffName()) . '</span>';
elseif ($ticket->isOverdue())
    $warn .= '&nbsp;<span class="Icon overdueTicket">' . __('Marked overdue!') . '</span>';

echo sprintf(
    '<div style="width:600px; padding: 2px 2px 0 5px;" id="t%s">
         <h2>' . __('Ticket #%s') . ': %s</h2>',
    $ticket->getNumber(),
    $ticket->getNumber(),
    $ticket->getSubject());

if ($error)
    echo sprintf('<div id="msg_error">%s</div>', $error);
elseif ($msg)
    echo sprintf('<div id="msg_notice">%s</div>', $msg);
elseif ($warn)
    echo sprintf('<div id="msg_warning">%s</div>', $warn);

echo '<ul class="nav nav-tabs" id="ticket-preview" role="tablist">';

echo '
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" role="tab" id="preview_tab" href="#preview"
            ><i class="icon-list-alt"></i>&nbsp;' . __('Ticket Summary') . '</a></li>';
echo sprintf('
        <li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" id="collab_tab" href="#collab"
            ><i class="icon-fixed-width icon-group
            faded"></i>&nbsp;' . __('Collaborators (%d)') . '</a></li>',
    $thread->getNumCollaborators());
echo '</ul>'; ?>
<div class="tab-content" id="ticket-preview_container">
    <div class="tab-pane active" id="preview" role="tabpanel">
        <div style="padding: 0 15px; width: 100%;">
            <div class="spacer"></div>
            <?php
            $ticket_state = sprintf('<span>%s</span>', ucfirst($ticket->getStatus()));
            if ($ticket->isOpen()) {
                if ($ticket->isOverdue())
                    $ticket_state .= ' &mdash; <span>' . __('Overdue') . '</span>';
                else
                    $ticket_state .= sprintf(' &mdash; <span>%s</span>', $ticket->getPriority());
            }
            ?>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Ticket State:</dt>
                <dd class="col-sm-8 sidebar-detail"><?php echo $ticket_state; ?></dd>
            </dl>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Created:</dt>
                <dd class="col-sm-8 sidebar-detail"><?php echo $ticket->getCreateDate(); ?></dd>
            </dl>
            <?php if ($ticket->isClosed()) { ?>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Closed:</dt>
                    <dd class="col-sm-8 sidebar-detail"><?php echo $ticket->getCloseDate(); ?> <span
                                class="faded">by <?php echo $staff ? $staff->getName() : 'staff'; ?></span></dd>
                </dl>
            <?php } elseif ($ticket->getEstDueDate()) { ?>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Due Date:</dt>
                    <dd class="col-sm-8 sidebar-detail"><?php echo $ticket->getEstDueDate(); ?></dd>
                </dl>
            <?php }
            if ($ticket->isOpen()) { ?>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle">Assigned To:</dt>
                    <dd class="col-sm-8 sidebar-detail"><?php echo $ticket->isAssigned() ? implode('/', $ticket->getAssignees()) : ' <span class="faded">&mdash; ' . __('Unassigned') . ' &mdash;</span>' ?></dd>
                </dl>
            <?php } ?>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">From:</dt>
                <dd class="col-sm-8 sidebar-detail"><a href="users.php?id=<?php echo $ticket->getUserId(); ?>"
                                                       class="no-pjax"><?php echo $ticket->getName(); ?></a> <span
                            class="faded"><?php echo $ticket->getEmail(); ?></span></dd>
            </dl>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Department:</dt>
                <dd class="col-sm-8 sidebar-detail"><?php echo $ticket->getDeptName(); ?></dd>
            </dl>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Service Template:</dt>
                <dd class="col-sm-8 sidebar-detail"><?php echo $ticket->getHelpTopic(); ?></dd>
            </dl>
        </div>
    </div>
    <div class="tab-pane" id="collab" role="tabpanel">
        <div style="padding: 0 15px; width: 100%;">
            <div class="spacer"></div>
            <?php
            if ($thread && ($collabs = $thread->getCollaborators())) { ?>
                <?php
                foreach ($collabs as $collab) {
                    echo sprintf('<tr><td %s>%s
                        <a href="users.php?id=%d" class="no-pjax">%s</a> &lt;%s&gt;</td></tr>',
                        ($collab->isActive() ? '' : 'class="faded"'),
                        (($U = $collab->getUser()) && ($A = $U->getAvatar()))
                            ? $A->getImageTag(20) : sprintf('<i class="icon-%s"></i>',
                            $collab->isActive() ? 'comments' : 'comment-alt'),
                        $collab->getUserId(),
                        $collab->getName(),
                        $collab->getEmail());
                }
            } else {
                echo __("Ticket doesn't have any collaborators.");
            } ?>
            <br>
        <?php
        echo sprintf('<span><a class="collaborators"
                            href="#tickets/%d/collaborators">%s</a></span>',
            $ticket->getId(),
            $thread && $thread->getNumCollaborators()
                ? __('Manage Collaborators') : __('Add Collaborator')
        );
        ?>
        </div>
    </div>
</div>
<?php
$options = array();
$options[] = array('action' => sprintf(__('Thread (%d)'), $ticket->getThreadCount()), 'url' => "tickets.php?id=$tid");
if ($ticket->getNumNotes())
    $options[] = array('action' => sprintf(__('Notes (%d)'), $ticket->getNumNotes()), 'url' => "tickets.php?id=$tid#notes");

if ($ticket->isOpen())
    $options[] = array('action' => __('Reply'), 'url' => "tickets.php?id=$tid#reply");

if ($role->hasPerm(TicketModel::PERM_ASSIGN))
    $options[] = array('action' => ($ticket->isAssigned() ? __('Reassign') : __('Assign')), 'url' => "tickets.php?id=$tid#assign");

if ($role->hasPerm(TicketModel::PERM_TRANSFER))
    $options[] = array('action' => __('Transfer'), 'url' => "tickets.php?id=$tid#transfer");

$options[] = array('action' => __('Post Note'), 'url' => "tickets.php?id=$tid#note");

if ($role->hasPerm(TicketModel::PERM_EDIT))
    $options[] = array('action' => __('Edit Ticket'), 'url' => "tickets.php?id=$tid&a=edit");

if ($options) {
    echo '<ul class="tip_menu">';
    foreach ($options as $option)
        echo sprintf('<li><a href="%s">%s</a></li>', $option['url'], $option['action']);
    echo '</ul>';
}

echo '</div>';
?>
