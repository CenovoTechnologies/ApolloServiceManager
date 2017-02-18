<?php
/*********************************************************************
servicecats.php

Service Categories.

Melissa Smith <melissa@cenovotechnologies.com
Copyright (c)  2017 cenovoTechnologies
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.servicecat.php');
include_once(INCLUDE_DIR.'class.service.php');

$serviceCat=null;
if($_REQUEST['id'] && !($serviceCat=ServiceCat::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('service_cat'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$serviceCat){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('service category'));
            }elseif($serviceCat->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this service category'));
            }elseif(!$errors['err']){
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this service category')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'create':
            $_serviceCat = ServiceCat::create();
            if ($_serviceCat->update($_POST, $errors)) {
                $serviceCat = $_serviceCat;
                $msg=sprintf(__('Successfully added %s.'), Format::htmlchars($_POST['service_cat']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this service category')),
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
                            __('one service category'));
            }
            if (!$errors) {
                $count=count($_POST['ids']);

                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $num = ServiceCat::objects()->filter(array(
                            'service_cat_id__in' => $_POST['ids'],
                        ))->update(array(
                            'isactive' => true,
                        ));

                        if ($num > 0) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected service category', 'selected service categories', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected service category', 'selected service categories', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected service category', 'selected service categories', $count));
                        }
                        break;
                    case 'disable':
                        $num = ServiceCat::objects()->filter(array(
                            'service_cat_id__in'=>$_POST['ids'],
                        ))->exclude(array(
                            'service_cat_id'=>$cfg->getDefaultServiceCatId(),
                        ))->update(array(
                            'isactive' => false,
                        ));
                        if ($num > 0) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully disabled %s'),
                                    _N('selected service category', 'selected service categories', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $num, $count,
                                    _N('selected service category', 'selected service categories', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected service category', 'selected service categories', $count));
                        }
                        break;
                    case 'delete':
                        $i = ServiceCat::objects()->filter(array(
                            'service_cat_id__in'=>$_POST['ids']
                        ))->delete();

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected service category', 'selected service categories', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected service category', 'selected service categories', $count));
                        elseif(!$errors['err'])
                            $errors['err']  = sprintf(__('Unable to delete %s.'),
                                _N('selected service category', 'selected service categories', $count));

                        break;
                    case 'sort':
                        try {
                            $cfg->setServiceCatSortMode($_POST['service_cat_sort_mode']);
                            if ($cfg->getServiceCatSortMode() == 'm') {
                                foreach ($_POST as $k=>$v) {
                                    if (strpos($k, 'sort-') === 0
                                        && is_numeric($v)
                                        && ($t = ServiceCat::lookup(substr($k, 5))))
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
    if ($id or $serviceCat) {
        if (!$id) $id=$serviceCat->getId();
    }
}

$page='servicecats.inc.php';
$tip_namespace = 'manage.servicecat';
if($serviceCat || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='servicecat.inc.php';
}

$nav->setTabActive('manage');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
