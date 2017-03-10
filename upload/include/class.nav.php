<?php
/*********************************************************************
    class.nav.php

    Navigation helper classes. Pointless BUT helps keep navigation clean and free from errors.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once(INCLUDE_DIR.'class.app.php');

class StaffNav {

    var $activetab;
    var $activeMenu;
    var $panel;

    var $staff;

    function __construct($staff, $panel='staff'){
        $this->staff=$staff;
        $this->panel=strtolower($panel);
    }

    function __get($what) {
        // Lazily initialize the tabbing system
        switch($what) {
        case 'tabs':
            $this->tabs=$this->getTabs();
            break;
        case 'submenus':
            $this->submenus=$this->getSubMenus();
            break;
        default:
            throw new Exception($what . ': No such attribute');
        }
        return $this->{$what};
    }

    function getPanel(){
        return $this->panel;
    }

    function isAdminPanel(){
        return (!strcasecmp($this->getPanel(),'admin'));
    }

    function isStaffPanel() {
        return (!$this->isAdminPanel());
    }

    function getRegisteredApps() {
        return Application::getStaffApps();
    }

    function setTabActive($tab, $menu=''){

        if($this->tabs[$tab]){
            $this->tabs[$tab]['active']=true;
            if($this->activetab && $this->activetab!=$tab && $this->tabs[$this->activetab])
                 $this->tabs[$this->activetab]['active']=false;

            $this->activetab=$tab;
            if($menu) $this->setActiveSubMenu($menu, $tab);

            return true;
        }

        return false;
    }

    function setActiveTab($tab, $menu=''){
        return $this->setTabActive($tab, $menu);
    }

    function getActiveTab(){
        return $this->activetab;
    }

    function setActiveSubMenu($mid, $tab='') {
        if(is_numeric($mid))
            $this->activeMenu = $mid;
        elseif($mid && $tab && ($subNav=$this->getSubNav($tab))) {
            foreach($subNav as $k => $menu) {
                if(strcasecmp($mid, $menu['href'])) continue;

                $this->activeMenu = $k+1;
                break;
            }
        }
    }

    function getActiveMenu() {
        return $this->activeMenu;
    }

    function addSubMenu($item,$active=false){

        // Triger lazy loading if submenus haven't been initialized
        isset($this->submenus[$this->getPanel().'.'.$this->activetab]);
        $this->submenus[$this->getPanel().'.'.$this->activetab][]=$item;
        if($active)
            $this->activeMenu=sizeof($this->submenus[$this->getPanel().'.'.$this->activetab]);
    }


    function getTabs(){
        global $thisstaff;

        if(!$this->tabs) {
            $this->tabs = array();
            $this->tabs['dashboard'] = array(
                'desc'=>__('Hub'),'title'=>__('Agent Hub'), "class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#dashboard", "aria-expanded"=>"true", "aria-controls"=>"dashboard"
            );
            //$this->tabs['tasks'] = array('desc'=>__('Tasks'), 'title'=>__('Task Queue'), "class"=>"nav-header");
            $this->tabs['tickets'] = array('desc'=>__('Service Desk'),'title'=>__('Ticket Queue'), "class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#tickets", "aria-expanded"=>"false", "aria-controls"=>"tickets");
            $this->tabs['assets'] = array('desc'=>__('Asset Management'),'title'=>__('Assets'), "class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#assets", "aria-expanded"=>"false", "aria-controls"=>"assets");
            $this->tabs['kbase'] = array('desc'=>__('Knowledge Center'),'title'=>__('Knowledge Center'), "class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#kbase", "aria-expanded"=>"false", "aria-controls"=>"kbase");
            if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) {
                //$this->tabs['users'] = array('desc' => __('Users'), 'title' => __('User Directory'), "class"=>"nav-header");
            }
            if ($thisstaff->isAdmin()) {
                $this->tabs['admin'] = array('desc'=>__('Manage'),'title'=>__('Administration'), "class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#admin", "aria-expanded"=>"false", "aria-controls"=>"admin");
            }
            if (count($this->getRegisteredApps()))
                $this->tabs['apps']=array('desc'=>__('Applications'),'title'=>__('Applications'), "class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#apps", "aria-expanded"=>"false", "aria-controls"=>"apps");
        }

        return $this->tabs;
    }

    function getSubMenus(){ //Private.
        global $cfg;
        $staff = $this->staff;
        $submenus=array();
        foreach($this->getTabs() as $k=>$tab){
            $subnav=array();
            switch(strtolower($k)){
                case 'tasks':
                    //$subnav[]=array('desc'=>__('Tasks'), 'href'=>'tasks.php', 'iconclass'=>'Ticket', 'droponly'=>true);
                    break;
                case 'tickets':
                    $stats = $staff->getTicketsStats();
                    $open_name = _P('queue-name', 'Open');
                        
                        /*if($cfg->showAnsweredTickets()) {

                            if ($stats) {*/

                                $subnav[]=array('desc'=>$open_name.' ('.number_format($stats['open']).')',
                                                       'title'=>__('Open Tickets'),
                                                       'href'=>'tickets.php?status=open',
                                                       'droponly'=>true);


                                $subnav[]=array('desc'=>__('In Progress').' ('.number_format($stats['progress']).')',
                                                       'title'=>__('Tickets in Progress'),
                                                       'href'=>'tickets.php?status=progress',
                                                       'droponly'=>true);
                        /*    }
                        }*/
                        
                        $subnav[]=array('desc'=>__('Open Tasks'), 'href'=>'tasks.php',  'droponly'=>true);
                        
                        if($stats['overdue']) {
                            $subnav[]=array('desc'=>__('Overdue').' ('.number_format($stats['overdue']).')',
                                                   'title'=>__('Overdue Tickets'),
                                                   'href'=>'tickets.php?status=overdue',
                                                   'droponly'=>true);
                        
                            if($stats['overdue']>10)
                                $sysnotice=sprintf(__('%d overdue tickets!'),$stats['overdue']);
                        }

                        $subnav[]=array('desc' => __('Resolved').' ('.$staff->getNumResolvedTickets().')',
                                   'title'=>__('Resolved Tickets'),
                                   'href'=>'tickets.php?status=resolved',
                                   'droponly'=>true);

                        $subnav[]=array('desc' => __('Closed'),
                                   'title'=>__('Closed Tickets'),
                                   'href'=>'tickets.php?status=closed',
                                   'droponly'=>true);

                        if ($staff->hasPerm(TicketModel::PERM_CREATE, false)) {
                              $subnav[]=array('desc'=>__('New Ticket'),
                                               'title' => __('Open a New Ticket'),
                                               'href'=>'tickets.php?a=open',
                                               'id' => 'new-ticket',
                                               'droponly'=>true);
                            }
                    break;
                case 'dashboard':
                    $subnav[]=array('desc'=>__('Dashboard'),'href'=>'dashboard.php','iconclass'=>'no-pjax');
                    //$subnav[]=array('desc'=>__('Agent Directory'),'href'=>'directory.php','iconclass'=>'teams');
                    if(($assigned=$staff->getNumAssignedTickets())) {
                        
                            $subnav[]=array('desc'=>__('My Tickets').' ('.$assigned.')',
                                                   'title'=>__('Assigned Tickets'),
                                                   'href'=>'tickets.php?status=assigned',
                                                   'droponly'=>true);
                        }
                    $subnav[]=array('desc'=>__('My Tasks'), 'href'=>'tasks.php', 'droponly'=>true);
                    break;
                case 'users':
                    $subnav[] = array('desc' => __('User Directory'), 'href' => 'users.php');
                    $subnav[] = array('desc' => __('Organizations'), 'href' => 'orgs.php');
                    break;
                case 'assets':
                    $subnav[]=array('desc'=>__('Software'),'href'=>'#');
                    $subnav[] = array('desc' => __('Hardware'), 'href' => '#');
                    $subnav[] = array('desc' => __('Other Assets'), 'href' => '#');
                    break;
                case 'admin'://echo ROOT_PATH scp/admin.php
                    $subnav[] = array('desc' => __('Admin Control Panel'), 'href' =>'admin.php', 'iconclass' => 'no-pjax');
                    $subnav[] = array('desc' => __('User Directory'), 'href' => 'users.php');
                    $subnav[] = array('desc' => __('Organizations'), 'href' => 'orgs.php');
                    break;
                case 'kbase':
                    $subnav[]=array('desc'=>__('FAQs'),'href'=>'kb.php', 'urls'=>array('faq.php'));
                    if($staff) {
                        if ($staff->hasPerm(FAQ::PERM_MANAGE))
                            $subnav[]=array('desc'=>__('Categories'),'href'=>'categories.php');
                        if ($cfg->isCannedResponseEnabled() && $staff->hasPerm(Canned::PERM_MANAGE, false))
                            $subnav[]=array('desc'=>__('Canned Responses'),'href'=>'canned.php');
                    }
                   break;
                case 'apps':
                    foreach ($this->getRegisteredApps() as $app)
                        $subnav[] = $app;
                    break;
            }
            if($subnav)
                $submenus[$this->getPanel().'.'.strtolower($k)]=$subnav;
        }
        return $submenus;
    }

    function getSubMenu($tab=null){
        $tab=$tab?$tab:$this->activetab;
        return $this->submenus[$this->getPanel().'.'.$tab];
    }

    function getSubNav($tab=null){
        return $this->getSubMenu($tab);
    }

}

class AdminNav extends StaffNav{

    function __construct($staff){
        parent::__construct($staff, 'admin');
    }

    function getRegisteredApps() {
        return Application::getAdminApps();
    }

    function getTabs(){

        if(!$this->tabs){

            $tabs=array();
            $tabs['dashboard']=array('desc'=>__('Admin Dashboard'),'title'=>__('Admin Dashboard'),"class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#dashboard", "aria-expanded"=>"false", "aria-controls"=>"dashboard");
            $tabs['settings']=array('desc'=>__('System Settings'),'title'=>__('System Settings'),"class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#settings", "aria-expanded"=>"false", "aria-controls"=>"settings");
            $tabs['manage']=array('desc'=>__('Manage Options'),'title'=>__('Manage Options'),"class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#manage", "aria-expanded"=>"false", "aria-controls"=>"manage");
            $tabs['emails']=array('desc'=>__('Email Settings'),'title'=>__('Email Settings'),"class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#emails", "aria-expanded"=>"false", "aria-controls"=>"emails");
            $tabs['staff']=array('desc'=>__('Manage Agents'),'title'=>__('Manage Agents'),"class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#staff", "aria-expanded"=>"false", "aria-controls"=>"staff");
            if (count($this->getRegisteredApps()))
                $tabs['apps']=array('desc'=>__('Applications'),'title'=>__('Applications'),"class"=>"nav-header", "data-toggle"=>"collapse", "href"=>"#apps", "aria-expanded"=>"false", "aria-controls"=>"apps");
            $this->tabs=$tabs;
        }

        return $this->tabs;
    }

    function getSubMenus(){

        $submenus=array();
        foreach($this->getTabs() as $k=>$tab){
            $subnav=array();
            switch(strtolower($k)){
                case 'dashboard':
                    $subnav[]=array('desc'=>__('System Logs'),'href'=>'logs.php');
                    $subnav[]=array('desc'=>__('Information'),'href'=>'system.php');
                    break;
                case 'settings':
                    $subnav[]=array('desc'=>__('Company'),'href'=>'settings.php?t=pages');
                    $subnav[]=array('desc'=>__('System'),'href'=>'settings.php?t=system');
                    $subnav[]=array('desc'=>__('Tickets'),'href'=>'settings.php?t=tickets');
                    $subnav[]=array('desc'=>__('Tasks'),'href'=>'settings.php?t=tasks');
                    $subnav[]=array('desc'=>__('Agents'),'href'=>'settings.php?t=agents');
                    $subnav[]=array('desc'=>__('Users'),'href'=>'settings.php?t=users');
                    $subnav[]=array('desc'=>__('Knowledgebase'),'href'=>'settings.php?t=kb');
                    break;
                case 'manage':
                    $subnav[]=array('desc'=>__('Service Templates'),'href'=>'helptopics.php');
                    $subnav[]=array('desc'=>__('Service Types'),'href'=>'servicetypes.php');
                    $subnav[]=array('desc'=>__('Service Catalogue'),'href'=>'services.php');
                    $subnav[]=array('desc'=>__('Service Categories'),'href'=>'servicecats.php');
                    $subnav[]=array('desc'=>__('Service Sub Categories'),'href'=>'servicesubcats.php');
                    $subnav[]=array('desc'=>__('Auto Close Plans'),'href'=>'autoclosures.php');
                    $subnav[]=array('desc'=>__('Resolution Codes'),'href'=>'resolutioncodes.php');
                    $subnav[]=array('desc'=>__('Ticket Filters'),'href'=>'filters.php',
                                        'title'=>__('Ticket Filters'));
                    $subnav[]=array('desc'=>__('SLA Plans'),'href'=>'slas.php');
                    $subnav[]=array('desc'=>__('API Keys'),'href'=>'apikeys.php');
                    $subnav[]=array('desc'=>__('Pages'), 'href'=>'pages.php','title'=>'Pages');
                    $subnav[]=array('desc'=>__('Forms'),'href'=>'forms.php');
                    $subnav[]=array('desc'=>__('Lists'),'href'=>'lists.php');
                    $subnav[]=array('desc'=>__('Plugins'),'href'=>'plugins.php');
                    break;
                case 'emails':
                    $subnav[]=array('desc'=>__('Emails'),'href'=>'emails.php', 'title'=>__('Email Addresses'));
                    $subnav[]=array('desc'=>__('Settings'),'href'=>'emailsettings.php');
                    $subnav[]=array('desc'=>__('Banlist'),'href'=>'banlist.php',
                                        'title'=>__('Banned Emails'));
                    $subnav[]=array('desc'=>__('Template Sets'),'href'=>'templates.php','title'=>__('Email Template Sets'));
                    $subnav[]=array('desc'=>__('Diagnostic'),'href'=>'emailtest.php', 'title'=>__('Email Diagnostic'));
                    break;
                case 'staff':
                    $subnav[]=array('desc'=>__('Agents'),'href'=>'staff.php');
                    $subnav[]=array('desc'=>__('Teams'),'href'=>'teams.php');
                    $subnav[]=array('desc'=>__('Roles'),'href'=>'roles.php');
                    $subnav[]=array('desc'=>__('Departments'),'href'=>'departments.php');
                    break;
                case 'apps':
                    foreach ($this->getRegisteredApps() as $app)
                        $subnav[] = $app;
                    break;
            }
            if($subnav)
                $submenus[$this->getPanel().'.'.strtolower($k)]=$subnav;
        }

        return $submenus;
    }
}

class UserNav {

    var $navs=array();
    var $activenav;

    var $user;

    function __construct($user=null, $active=''){

        $this->user=$user;
        $this->navs=$this->getNavs();
        if($active)
            $this->setActiveNav($active);
    }

    function getRegisteredApps() {
        return Application::getClientApps();
    }

    function setActiveNav($nav){

        if($nav && $this->navs[$nav]){
            $this->navs[$nav]['active']=true;
            if($this->activenav && $this->activenav!=$nav && $this->navs[$this->activenav])
                 $this->navs[$this->activenav]['active']=false;

            $this->activenav=$nav;

            return true;
        }

        return false;
    }

    function getNavLinks(){
        global $cfg;

        //Paths are based on the root dir.
        if(!$this->navs){

            $navs = array();
            $user = $this->user;
            $navs['home']=array('desc'=>__('Apollo Service Manager Home'),'href'=>'index.php','title'=>'');
            if($cfg && $cfg->isKnowledgebaseEnabled())
                $navs['kb']=array('desc'=>__('Knowledgebase'),'href'=>'kb/index.php','title'=>'');

            // Show the "Open New Ticket" link unless BOTH client
            // registration is disabled and client login is required for new
            // tickets. In such a case, creating a ticket would not be
            // possible for web clients.
            if ($cfg->getClientRegistrationMode() != 'disabled'
                    || !$cfg->isClientLoginRequired())
                $navs['new']=array('desc'=>__('Open a New Ticket'),'href'=>'open.php','title'=>'');
            if($user && $user->isValid()) {
                if(!$user->isGuest()) {
                    $navs['tickets']=array('desc'=>sprintf(__('Check Requests (%d)'),$user->getNumTickets($user->canSeeOrgTickets())),
                                           'href'=>'tickets.php',
                                            'title'=>__('Show all Service Requests'));
                } else {
                    $navs['tickets']=array('desc'=>__('View Service Request Thread'),
                                           'href'=>sprintf('tickets.php?id=%d',$user->getTicketId()),
                                           'title'=>__('View request status'));
                }
            } else {
                $navs['status']=array('desc'=>__('Check Service Request'),'href'=>'view.php','title'=>'');
            }
            $this->navs=$navs;
        }

        return $this->navs;
    }

    function getNavs(){
        return $this->getNavLinks();
    }

}

?>
