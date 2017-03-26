<?php
/*********************************************************************
    class.cron.php

    Nothing special...just a central location for all cron calls.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.


    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once INCLUDE_DIR.'class.signal.php';

class Cron {


    static function getCronTimeInterval() {
        $sql = 'SELECT cron_interval_time FROM '.CRON_SETTINGS_TABLE.' WHERE cron_settings_id = 1';
        return db_query($sql);
    }

    static function getLastCronRuntime() {
        $sql = 'SELECT last_cron_runtime FROM '.CRON_SETTINGS_TABLE.' WHERE cron_settings_id = 1';
        return db_query($sql);
    }

    static function updateLastCronRuntime($time) {
        $sql = 'UPDATE '.CRON_SETTINGS_TABLE.' SET last_cron_runtime = '.$time.' WHERE cron_settings_id = 1';
        db_query($sql);
    }

    static function MailFetcher() {
        require_once(INCLUDE_DIR.'class.mailfetch.php');
        MailFetcher::run(); //Fetch mail..frequency is limited by email account setting.
    }

    static function TicketMonitor() {
        require_once(INCLUDE_DIR.'class.ticket.php');
        Ticket::checkOverdue(); //Make stale tickets overdue
        Ticket::autoClose(); //close tickets that have reached their auto-close time
        // Cleanup any expired locks
        require_once(INCLUDE_DIR.'class.lock.php');
        Lock::cleanup();

    }

    static function PurgeLogs() {
        global $ost;
        // Once a day on a 5-minute cron
        if (rand(1,300) == 42)
            if($ost) $ost->purgeLogs();
    }

    function PurgeDrafts() {
        require_once(INCLUDE_DIR.'class.draft.php');
        Draft::cleanup();
    }

    static function CleanOrphanedFiles() {
        require_once(INCLUDE_DIR.'class.file.php');
        AttachmentFile::deleteOrphans();
    }

    function MaybeOptimizeTables() {
        // Once a week on a 5-minute cron
        $chance = rand(1,2000);
        switch ($chance) {
        case 42:
            @db_query('OPTIMIZE TABLE '.LOCK_TABLE);
            break;
        case 242:
            @db_query('OPTIMIZE TABLE '.SYSLOG_TABLE);
            break;
        case 442:
            @db_query('OPTIMIZE TABLE '.DRAFT_TABLE);
            break;

        // Start optimizing core ticket tables when we have an archiving
        // system available
        case 142:
            #@db_query('OPTIMIZE TABLE '.TICKET_TABLE);
            break;
        case 542:
            #@db_query('OPTIMIZE TABLE '.FORM_ENTRY_TABLE);
            break;
        case 642:
            #@db_query('OPTIMIZE TABLE '.FORM_ANSWER_TABLE);
            break;
        case 342:
            #@db_query('OPTIMIZE TABLE '.FILE_TABLE);
            # XXX: Please do not add an OPTIMIZE for the file_chunk table!
            break;

        // Start optimizing user tables when we have a user directory
        // sporting deletes
        case 742:
            #@db_query('OPTIMIZE TABLE '.USER_TABLE);
            break;
        case 842:
            #@db_query('OPTIMIZE TABLE '.USER_EMAIL_TABLE);
            break;
        }
    }

    static function run(){ //called by outside cron NOT autocron
        global $ost;
        if (!$ost || $ost->isUpgradePending())
            return;

        self::MailFetcher();
        self::TicketMonitor();
        self::PurgeLogs();
        // Run file purging about every 10 cron runs
        if (mt_rand(1, 9) == 4)
            self::CleanOrphanedFiles();
        self::PurgeDrafts();
        self::MaybeOptimizeTables();

        $data = array('autocron'=>false);
        Signal::send('cron', $data);
    }
}
?>
