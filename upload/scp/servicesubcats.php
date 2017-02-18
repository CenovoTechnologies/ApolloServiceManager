<?php
/*********************************************************************
servicesubcats.php

Service Sub-Categories.

Melissa Smith <melissa@cenovotechnologies.com
Copyright (c)  2017 cenovoTechnologies
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.servicecat.php');
include_once(INCLUDE_DIR.'class.servicesubcat.php');

/** @var ServiceSubCat $serviceSubCat */
$serviceSubCat=null;
if($_REQUEST['id'] && !($serviceSubCat=ServiceSubCat::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('service_sub_cat'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$serviceSubCat){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('sub category'));
            }elseif($serviceSubCat->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this sub category'));
            }elseif(!$errors['err']){
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this sub category')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'create':
            $_serviceSubCat = ServiceSubCat::create();
            if ($_serviceSubCat->update($_POST, $errors)) {
                $serviceSubCat = $_serviceSubCat;
                $msg=sprintf(__('Successfully added %s.'), Format::htmlchars($_POST['service_sub_cat']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this sub category')),
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
                            __('one sub category'));
            }
            if (!$errors) {
                $count=count($_POST['ids']);

                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $num = ServiceSubCat::objects()->filter(array(
                            'service_sub_cat_id__in' => $_POST['ids'],
                        ))->update(array(
                            'isactive' => true,
                        ));

                        if ($num > 0) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected sub category', 'selected sub categories', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected sub category', 'selected sub categories', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected sub category', 'selected sub categories', $count));
                        }
                        break;
                    case 'disable':
                        $num = ServiceSubCat::objects()->filter(array(
                            'service_sub_cat_id__in'=>$_POST['ids'],
                        ))->exclude(array(
                            'service_sub_cat_id'=>$cfg->getDefaultServiceSubCatId(),
                        ))->update(array(
                            'isactive' => false,
                        ));
                        if ($num > 0) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully disabled %s'),
                                    _N('selected sub category', 'selected sub categories', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $num, $count,
                                    _N('selected sub category', 'selected sub categories', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected sub category', 'selected sub categories', $count));
                        }
                        break;
                    case 'delete':
                        $i = ServiceSubCat::objects()->filter(array(
                            'service_sub_cat_id__in'=>$_POST['ids']
                        ))->delete();

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected sub category', 'selected sub categories', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected sub category', 'selected sub categories', $count));
                        elseif(!$errors['err'])
                            $errors['err']  = sprintf(__('Unable to delete %s.'),
                                _N('selected sub category', 'selected sub categories', $count));

                        break;
                    case 'sort':
                        try {
                            $cfg->setServiceSubCatSortMode($_POST['service_sub_cat_sort_mode']);
                            if ($cfg->getServiceSubCatSortMode() == 'm') {
                                foreach ($_POST as $k=>$v) {
                                    if (strpos($k, 'sort-') === 0
                                        && is_numeric($v)
                                        && ($t = ServiceSubCat::lookup(substr($k, 5))))
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
    if ($id or $serviceSubCat) {
        if (!$id) $id=$serviceSubCat->getId();
    }
}

$page='servicesubcats.inc.php';
$tip_namespace = 'manage.servicesubcat';
if($serviceSubCat || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='servicesubcat.inc.php';
}

$nav->setTabActive('manage');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
