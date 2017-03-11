<?php
/*********************************************************************
services.php

Service Catalogue.

Melissa Smith <melissa@cenovotechnologies.com
Copyright (c)  2017 cenovoTechnologies
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.service.php');
include_once(INCLUDE_DIR.'class.servicetype.php');
include_once(INCLUDE_DIR.'class.servicecat.php');
include_once(INCLUDE_DIR.'class.faq.php');
require_once(INCLUDE_DIR.'class.dynamic_forms.php');

$service=null;
if($_REQUEST['id'] && !($service=Service::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('service'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$service){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('service'));
            }elseif($service->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this service'));
            }elseif(!$errors['err']){
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this service')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'create':
            $_service = Service::create();
            if ($_service->update($_POST, $errors)) {
                $service = $_service;
                $msg=sprintf(__('Successfully added %s.'), $_POST['service']);
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this service')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            switch(strtolower($_POST['a'])) {
                case 'sort':
                    // Pass
                    break;
                default:
                    if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids']))
                        $errors['err'] = sprintf(__('You must select at least %s.'),
                            __('one service'));
            }
            if (!$errors) {
                $count=count($_POST['ids']);

                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $num = Service::objects()->filter(array(
                            'service_id__in' => $_POST['ids'],
                        ))->update(array(
                            'isactive' => true,
                        ));

                        if ($num > 0) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected service', 'selected services', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected service', 'selected services', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected service', 'selected services', $count));
                        }
                        break;
                    case 'disable':
                        $num = Service::objects()->filter(array(
                            'service_id__in'=>$_POST['ids'],
                        ))->exclude(array(
                            'service_id'=>$cfg->getDefaultServiceId(),
                        ))->update(array(
                            'isactive' => false,
                        ));
                        if ($num > 0) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully disabled %s'),
                                    _N('selected service', 'selected services', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $num, $count,
                                    _N('selected service', 'selected services', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected service', 'selected services', $count));
                        }
                        break;
                    case 'delete':
                        $i = Service::objects()->filter(array(
                            'service_id__in'=>$_POST['ids']
                        ))->delete();

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected service', 'selected services', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected service', 'selected services', $count));
                        elseif(!$errors['err'])
                            $errors['err']  = sprintf(__('Unable to delete %s.'),
                                _N('selected service', 'selected services', $count));

                        break;
                    case 'sort':
                        try {
                            $cfg->setServiceSortMode($_POST['service_sort_mode']);
                            if ($cfg->getServiceSortMode() == 'm') {
                                foreach ($_POST as $k=>$v) {
                                    if (strpos($k, 'sort-') === 0
                                        && is_numeric($v)
                                        && ($t = Service::lookup(substr($k, 5))))
                                        $t->setSortOrder($v);
                                }
                            }
                            $msg = __('Successfully set sorting configuration');
                        }
                        catch (Exception $ex) {
                            $errors['err'] = __('Unable to set sorting mode');
                        }
                        break;
                    default:
                        $errors['err']=__('Unknown action - get technical help.');
                }
            }
            break;
        default:
            $errors['err']=__('Unknown action');
            break;
    }
    if ($id or $service) {
        if (!$id) $id=$service->getId();
    }
}

$page='services.inc.php';
$tip_namespace = 'manage.service';
if($service || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='service.inc.php';
}

$nav->setTabActive('manage');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
