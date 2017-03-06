<?php
/*********************************************************************
resolutioncodes.php

Resolution Codes

Melissa S Smith <melissa@cenovotechnologies.com>
Copyright (c)  2016-2017 Apollo Service Manager
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.
 *
 **********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.resolutioncode.php');

/** @var ResolutionCode $rc */
$rc=null;
if($_REQUEST['id'] && !($rc=ResolutionCode::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'),
        __('Resolution Code'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$rc){
                $errors['err']=sprintf(__('%s: Unknown or invalid'),
                    __('Resolution Code'));
            }elseif($rc->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this Resolution Code'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this Resolution Code')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'add':
            $_rc = ResolutionCode::create();
            if (($_rc->update($_POST, $errors))) {
                $msg=sprintf(__('Successfully added %s.'),
                    __('Resolution Code'));
                $_REQUEST['a']=null;
            } elseif (!$errors['err']) {
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this Resolution Code')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s.'),
                    __('one Resolution Code'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $num = ResolutionCode::objects()->filter(array(
                            'id__in' => $_POST['ids']
                        ));
                        if ($num) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected resolution code', 'selected resolution codes', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected resolution code', 'selected resolution codes', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected resolution code', 'selected resolution codes', $count));
                        }
                        break;
                    case 'disable':
                        $num = ResolutionCode::objects()->filter(array(
                            'id__in' => $_POST['ids']
                        ));

                        if ($num) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully disabled %s'),
                                    _N('selected resolution code', 'selected resolution codes', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $num, $count,
                                    _N('selected resolution code', 'selected resolution codes', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected resolution code', 'selected resolution codes', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach ($_POST['ids'] as $k => $v) {
                            if (($p=ResolutionCode::lookup($v))
                                && $p->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected resolution code', 'selected resolution codes', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected resolution code', 'selected resolution codes', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to delete %s.'),
                                _N('selected resolution', 'selected resolution codes', $count));
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
}

$page='resolutioncodes.inc.php';
/*if($sla || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='slaplan.inc.php';
    $ost->addExtraHeader('<meta name="tip-namespace" content="manage.sla" />',
        "$('#content').data('tipNamespace', 'manage.sla');");
}*/

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
