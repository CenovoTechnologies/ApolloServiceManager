<?php
/*********************************************************************
    cron.php

    Auto-cron handle.
    File requested as 1X1 image on the footer of every staff's page

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
define('AJAX_REQUEST', 1);
require('staff.inc.php');
require_once INCLUDE_DIR.'class.cron.php';
ignore_user_abort(1);//Leave me a lone bro!
@set_time_limit(0); //useless when safe_mode is on
$data=sprintf ("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%",
        71,73,70,56,57,97,1,0,1,0,128,255,0,192,192,192,0,0,0,33,249,4,1,0,0,0,0,44,0,0,0,0,1,0,1,0,0,2,2,68,1,0,59);

header('Content-type:  image/gif');
header('Cache-Control: no-cache, must-revalidate');
header('Content-Length: '.strlen($data));
header('Connection: Close');
print $data;
// Flush the request buffer
while(@ob_end_flush());
flush();
//Terminate the request
if (function_exists('fastcgi_finish_request'))
    fastcgi_finish_request();

ob_start(); //Keep the image output clean. Hide our dirt.
//The last cron time and cron interval time is stored in the database. This allows for better time intervals and what not
$lastCronCall = CRON::getLastCronRuntime();
$cronIntervalTime = CRON::getCronTimeInterval();
$sec = time() - $lastCronCall;
$caller = $thisstaff->getUserName();

// Agent can call cron once every 5 minutes.
if ($sec < $cronIntervalTime || !$ost || $ost->isUpgradePending())
    return ob_end_clean();

// Clear staff obj to avoid false credit internal notes & auto-assignment
$thisstaff = null;

// Release the session to prevent locking a future request while this is
// running
CRON::updateLastCronRuntime(time());
session_write_close();

// Age tickets: We're going to age tickets regardless of cron settings.
Cron::TicketMonitor();

// Run file purging about every 20 cron runs (1h40 on a five minute cron)
if (mt_rand(1, 20) == 4)
    Cron::CleanOrphanedFiles();

if($cfg && $cfg->isAutoCronEnabled()) { //ONLY fetch tickets if autocron is enabled!
    Cron::MailFetcher();  //Fetch mail.
    //$ost->logDebug(_S('Auto Cron'), sprintf(_S('Mail fetcher cron call [%s]'), $caller));
}

$data = array('autocron'=>true);
Signal::send('cron', $data);

ob_end_clean();
?>
