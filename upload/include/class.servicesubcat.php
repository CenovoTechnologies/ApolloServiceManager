<?php
/*********************************************************************
class.servicecat.php

Service Sub-Category helper

Melissa Smith <melissa@cenovotechnologies.com
Copyright (c)  2017 cenovoTechnologies
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/

require_once INCLUDE_DIR . 'class.service.php';
require_once INCLUDE_DIR . 'class.servicecat.php';
require_once INCLUDE_DIR . 'class.sequence.php';

class ServiceSubCat extends VerySimpleModel
    implements TemplateVariable {

    static $meta = array(
        'table' => SERVICE_SUB_CAT_TABLE,
        'pk' => array('service_sub_cat_id'),
        'ordering' => array('service_sub_cat'),
        'joins' => array(
            'service' => array(
                'list' => false,
                'constraint' => array(
                    'service_cat_pid' => 'ServiceCat.service_cat_id',
                ),
            ),
            'page' => array(
                'null' => true,
                'constraint' => array(
                    'page_id' => 'Page.id',
                ),
            ),
        ),
    );

    var $_forms;

    const DISPLAY_DISABLED = 2;

    const FORM_USE_PARENT = 4294967295;

    const FLAG_CUSTOM_NUMBERS = 0x0001;

    const SORT_ALPHA = 'a';
    const SORT_MANUAL = 'm';

    function asVar() {
        return $this->getName();
    }

    static function getVarScope() {
        return array(
            'fullname' => __('Service Sub-Category full path'),
            'name' => __('Service Sub-Category'),
            'parent' => array(
                'class' => 'ServiceCat', 'desc' => __('Parent'),
            ),
        );
    }

    function getId() {
        return $this->service_sub_cat_id;
    }

    function getPid() {
        return $this->service_sub_cat_pid;
    }

    function getParent() {
        return $this->parent;
    }

    function getName() {
        return $this->service_sub_cat;
    }

    function getLocalName() {
        return $this->getLocal('name');
    }

    function getFullName() {
        return self::getServiceSubCatName($this->getId()) ?: $this->service_sub_cat;
    }

    static function getServiceSubCatName($id) {
        $names = static::getServiceSubCategories(false, true);
        return $names[$id];
    }

    static function getServiceSubCatParent($pid) {
        return static::getParentById($pid);
    }

    function getPageId() {
        return $this->page_id;
    }

    function getPage() {
        return $this->page;
    }

    function isEnabled() {
        return $this->isActive();
    }

    /**
     * Determine if the service is currently enabled. The ancestry of
     * this service will be considered to see if any of the parents are
     * disabled. If any are disabled, then this service will be considered
     * disabled.
     *
     * Parameters:
     * $chain - array<id:bool> recusion chain used to detect loops. The
     *      chain should be maintained and passed to a parent's ::isActive()
     *      method. When consulting a parent, if the local service ID is a key
     *      in the chain, then this service has already been considered, and
     *      there is a loop in the ancestry
     */
    function isActive(array $chain=array()) {
        if (!$this->isactive)
            return false;

        if (!isset($chain[$this->getId()]) && ($p = $this->getParent())) {
            $chain[$this->getId()] = true;
            return $p->isActive($chain);
        }
        else {
            return $this->isactive;
        }
    }

    function isPublic() {
        return ($this->ispublic);
    }

    function getHashtable() {
        return $this->ht;
    }

    function getInfo() {
        $base = $this->getHashtable();
        return $base;
    }

    function getTranslateTag($subtag) {
        return _H(sprintf('service_sub_cat.%s.%s', $subtag, $this->getId()));
    }

    function getLocal($subtag) {
        $tag = $this->getTranslateTag($subtag);
        $T = CustomDataTranslation::translate($tag);
        return $T != $tag ? $T : $this->ht[$subtag];
    }

    function setSortOrder($i) {
        if ($i != $this->sort) {
            $this->sort = $i;
            return $this->save();
        }
        // Noop
        return true;
    }

    function delete() {
        global $cfg;

        if ($this->getId() == $cfg->getDefaultServiceSubCatId())
            return false;

        if (parent::delete()) {
            self::objects()->filter(array(
                'service_sub_cat_pid' => $this->getId()
            ))->update(array(
                'service_sub_cat_pid' => 0
            ));
            ServiceSubCat::objects()->filter(array(
                'service_sub_cat_id' => $this->getId()
            ))->delete();
            db_query('UPDATE '.TICKET_TABLE.' SET service_sub_cat_id=0 WHERE service_sub_cat_id='.db_input($this->getId()));
        }

        return true;
    }

    function __toString() {
        return (string) $this->getFullName();
    }

    /*** Static functions ***/

    static function create($vars=array()) {
        $serviceCat = new static($vars);
        $serviceCat->created = SqlFunction::NOW();
        return $serviceCat;
    }

    static function __create($vars, &$errors) {
        $serviceCat = self::create($vars);
        $vars['id'] = $vars['service_sub_cat_id'];
        $serviceCat->update($vars, $errors);
        return $serviceCat;
    }

    static function getServiceSubCategories($publicOnly=false, $disabled=false, $localize=true) {
        global $cfg;
        static $serviceCats, $names = array();

        // If localization is specifically requested, then rebuild the list.
        if (!$names || $localize) {
            $objects = self::objects()->values_flat(
                'service_sub_cat_id', 'service_sub_cat_pid', 'ispublic', 'isactive', 'service_sub_cat'
            )
                ->order_by('sort');

            // Fetch information for all service, in declared sort order
            $serviceCats = array();
            foreach ($objects as $T) {
                list($id, $pid, $pub, $act, $serviceCat) = $T;
                $serviceCats[$id] = array('pid'=>$pid, 'public'=>$pub,
                    'active'=>$act, 'category'=>$serviceCat);
            }

            $localize_this = function($id, $default) use ($localize) {
                if (!$localize)
                    return $default;

                $tag = _H("category.name.{$id}");
                $T = CustomDataTranslation::translate($tag);
                return $T != $tag ? $T : $default;
            };

            // Resolve parent names
            foreach ($serviceCats as $id=>$info) {
                $name = $localize_this($id, $info['category']);
                $names[$id] = $name;
            }
        }

        // Apply requested filters
        $requested_names = array();
        foreach ($names as $id=>$n) {
            /* $info = $services[$id];
             if ($publicOnly && !$info['public'])
                 continue;
             if (!$disabled && $info['disabled'])
                 continue;
             if ($disabled === self::DISPLAY_DISABLED && $info['disabled'])
                 $n .= " - ".__("(disabled)");*/
            $requested_names[$id] = $n;
        }

        // If localization requested and the current locale is not the
        // primary, the list may need to be sorted. Caching is ok here,
        // because the locale is not going to be changed within a single
        // request.
        if ($localize && $cfg->getServiceCatSortMode() == self::SORT_ALPHA)
            return Internationalization::sortKeyedList($requested_names);

        return $requested_names;
    }

    static function getParentCategories($parentId, $localize=true) {
        global $cfg;
        static $names = array();

        // If localization is specifically requested, then rebuild the list.
        if (!$names || $localize) {
            $objects = self::objects()->values_flat(
                'service_sub_cat_id', 'service_sub_cat_pid', 'ispublic', 'isactive', 'service_sub_cat', 'notes'
            )
                ->order_by('sort');

            // Fetch information for all service, in declared sort order
            $serviceCats = array();
            foreach ($objects as $T) {
                list($id, $pid, $pub, $act, $serviceCat, $notes) = $T;
                $serviceCats[$id] = array('id'=>$id, 'pid'=>$pid, 'public'=>$pub,
                    'active'=>$act, 'category'=>$serviceCat, 'notes'=>$notes);
            }

            // Resolve parent names
            foreach ($serviceCats as $id=>$info) {
                if ($parentId == $info['pid']) {
                    $names[$id] = $info;
                }
            }
        }

        // If localization requested and the current locale is not the
        // primary, the list may need to be sorted. Caching is ok here,
        // because the locale is not going to be changed within a single
        // request.
        if ($localize && $cfg->getServiceSubCatSortMode() == self::SORT_ALPHA)
            return Internationalization::sortKeyedList($names);

        return $names;
    }

    static function getPublicServiceSubCategories() {
        return self::getServiceSubCategories(true);
    }

    static function getAllServiceCategories($localize=false) {
        return self::getServiceSubCategories(false, true, $localize);
    }

    static function getLocalNameById($id) {
        $services = static::getServiceSubCategories(false, true);
        return $services[$id];
    }

    static function getParentById($id) {
        return ServiceCat::getServiceCatName($id);
    }

    static function getIdByName($name, $pid=0) {
        $list = self::objects()->filter(array(
            'service_sub_cat'=>$name,
            'service_sub_cat_pid'=>$pid,
        ))->values_flat('service_sub_cat_id')->first();

        if ($list)
            return $list[0];
    }

    static function getActiveFlagById($id) {
        $list = self::objects()->filter(array(
            'service_sub_cat_id'=>$id
        ))->values_flat('isactive')->first();

        if ($list) {
            return $list[0];
        } else {
            return "";
        }
    }

    static function getPublicFlagById($id) {
        $list = self::objects()->filter(array(
            'service_sub_cat_id'=>$id
        ))->values_flat('ispublic')->first();

        if ($list) {
            return $list[0];
        } else {
            return "";
        }
    }

    function update($vars, &$errors) {
        global $cfg;

        $vars['service_sub_cat'] = Format::striptags(trim($vars['service_sub_cat']));

        if (isset($this->service_sub_cat_id) && $this->getId() != $vars['id'])
            $errors['err']=__('Internal error occurred');

        if (!$vars['service_sub_cat'])
            $errors['service_sub_cat']=__('Service Sub-Category name is required');
        elseif (strlen($vars['service_sub_cat'])<5)
            $errors['service_sub_cat']=__('Service Sub-Category name is too short. Five characters minimum');
        elseif (($tid=self::getIdByName($vars['service_sub_cat'], $vars['service_sub_cat_pid']))
            && (!isset($this->service_sub_cat_id) || $tid!=$this->getId()))
            $errors['service_sub_cat']=__('Service Sub-Category already exists');

        if ($errors)
            return false;

        $this->service_sub_cat = $vars['service_sub_cat'];
        $this->service_sub_cat_pid = $vars['service_sub_cat_pid'] ?: 0;
        $this->isactive = !!$vars['isactive'];
        $this->ispublic = !!$vars['ispublic'];
        $this->notes = Format::sanitize($vars['notes']);

        $rv = false;
        if ($this->__new__) {
            if (!($rv = $this->save())) {
                $errors['err']=sprintf(__('Unable to create %s.'), __('this sub category'))
                    .' '.__('Internal error occurred');
            }
        }
        elseif (!($rv = $this->save())) {
            $errors['err']=sprintf(__('Unable to update %s.'), __('this sub category'))
                .' '.__('Internal error occurred');
        }
        if ($rv) {
            if (!$cfg || $cfg->getServiceSubCatSortMode() == 'a') {
                static::updateSortOrder();
            }
            $this->updateForms($vars, $errors);
        }
        return $rv;
    }

    function updateForms($vars, &$errors) {
        $find_disabled = function($form) use ($vars) {
            $fields = $vars['fields'];
            $disabled = array();
            foreach ($form->fields->values_flat('id') as $row) {
                list($id) = $row;
                if (false === ($idx = array_search($id, $fields))) {
                    $disabled[] = $id;
                }
            }
            return $disabled;
        };

        return true;
    }

    function save($refetch=false) {
        if ($this->dirty)
            $this->updated = SqlFunction::NOW();
        return parent::save($refetch || $this->dirty);
    }

    static function updateSortOrder() {
        global $cfg;

        // Fetch (un)sorted names
        if (!($names = static::getServiceSubCategories(false, true, false)))
            return;

        $names = Internationalization::sortKeyedList($names);

        $update = array_keys($names);
        foreach ($update as $idx=>&$id) {
            $id = sprintf("(%s,%s)", db_input($id), db_input($idx+1));
        }
        if (!count($update))
            return;

        // Thanks, http://stackoverflow.com/a/3466
        $sql = sprintf('INSERT INTO `%s` (service_id,`sort`) VALUES %s
            ON DUPLICATE KEY UPDATE `sort`=VALUES(`sort`)',
            SERVICE_SUB_CAT_TABLE, implode(',', $update));
        db_query($sql);
    }
}

// Add fields from the standard ticket form to the ticket filterable fields
Filter::addSupportedMatches(/* @trans */ 'Sub Category Template', array('serviceSubCatId' => 'Sub Category ID'), 100);

