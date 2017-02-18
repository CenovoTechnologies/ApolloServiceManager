<?php
/*********************************************************************
class.servicetype.php

Service Type helper

Melissa Smith <melissa@cenovotechnologies.com
Copyright (c)  2017 cenovoTechnologies
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/

require_once INCLUDE_DIR . 'class.service.php';
require_once INCLUDE_DIR . 'class.sequence.php';
require_once INCLUDE_DIR . 'class.filter.php';

class ServiceType extends VerySimpleModel
    implements TemplateVariable {

    static $meta = array(
        'table' => SERVICE_TYPE_TABLE,
        'pk' => array('service_type_id'),
        'ordering' => array('service_type'),
        'joins' => array(
            'page' => array(
                'null' => true,
                'constraint' => array(
                    'page_id' => 'Page.id',
                ),
            ),
            'dept' => array(
                'null' => true,
                'constraint' => array(
                    'dept_id' => 'Dept.id',
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
            'dept' => array(
                'class' => 'Dept', 'desc' => __('Department'),
            ),
            'fullname' => __('Service type full path'),
            'name' => __('Service Type'),
        );
    }

    function getId() {
        return $this->service_type_id;
    }

    function getName() {
        return $this->service_type;
    }

    function getLocalName() {
        return $this->getLocal('name');
    }

    function getFullName() {
        return self::getServiceTypeName($this->getId()) ?: $this->service_type;
    }

    static function getServiceTypeName($id) {
        $names = static::getServiceTypes(false, true);
        return $names[$id];
    }

    function getDeptId() {
        return $this->dept_id;
    }

    function getPageId() {
        return $this->page_id;
    }

    function getPage() {
        return $this->page;
    }

    function getServices() {
        $id = $this->getId();
        $services = static::getServiceCatalogue($id);
        return $services;
    }

    function isEnabled() {
        return $this->isActive();
    }

    /**
     * Determine if the service type is currently enabled. The ancestry of
     * this service type will be considered to see if any of the parents are
     * disabled. If any are disabled, then this service type will be considered
     * disabled.
     *
     * Parameters:
     * $chain - array<id:bool> recusion chain used to detect loops. The
     *      chain should be maintained and passed to a parent's ::isActive()
     *      method. When consulting a parent, if the local service type ID is a key
     *      in the chain, then this service type has already been considered, and
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
        return _H(sprintf('service_type.%s.%s', $subtag, $this->getId()));
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

        if ($this->getId() == $cfg->getDefaultServiceTypeId())
            return false;

        return true;
    }

    function __toString() {
        return (string) $this->getFullName();
    }

    /*** Static functions ***/

    static function create($vars=array()) {
        $servicetype = new static($vars);
        $servicetype->created = SqlFunction::NOW();
        return $servicetype;
    }

    static function __create($vars, &$errors) {
        $servicetype = self::create($vars);
        /*if (!isset($vars['dept_id']))
            $vars['dept_id'] = 0;*/
        $vars['id'] = $vars['service_type_id'];
        $servicetype->update($vars, $errors);
        return $servicetype;
    }

    static function getServiceTypes($publicOnly=false, $disabled=false, $localize=true) {
        global $cfg;
        static $servicetypes, $names = array();

        // If localization is specifically requested, then rebuild the list.
        if (!$names || $localize) {
            $objects = self::objects()->values_flat(
                'service_type_id', 'ispublic', 'isactive', 'service_type'
            )
                ->order_by('sort');

            // Fetch information for all service types, in declared sort order
            $servicetypes = array();
            foreach ($objects as $T) {
                list($id, $pub, $act, $servicetype) = $T;
                $servicetypes[$id] = array('public'=>$pub,
                    'disabled'=>!$act, 'service_type'=>$servicetype);
            }

            $localize_this = function($id, $default) use ($localize) {
                if (!$localize)
                    return $default;

                $tag = _H("service_type.name.{$id}");
                $T = CustomDataTranslation::translate($tag);
                return $T != $tag ? $T : $default;
            };

            foreach ($servicetypes as $id=>$info) {
                $name = $localize_this($id, $info['service_type']);
                $names[$id] = $name;
            }
        }

        // Apply requested filters
        $requested_names = array();
        foreach ($names as $id=>$n) {
            $info = $servicetypes[$id];
             /*if ($publicOnly && !$info['public'])
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
        if ($localize && $cfg->getServiceTypeSortMode() == self::SORT_ALPHA)
            return Internationalization::sortKeyedList($requested_names);

        return $requested_names;
    }

    static function getPublicServiceTypes() {
        return self::getServiceTypes(true);
    }

    static function getAllServiceTypes($localize=false) {
        return self::getServiceTypes(false, true, $localize);
    }

    static function getLocalNameById($id) {
        $servicetypes = static::getServiceTypes(false, true);
        return $servicetypes[$id];
    }

    static function getIdByName($name, $pid=0) {
        $list = self::objects()->filter(array(
            'service_type'=>$name,
        ))->values_flat('service_type_id')->first();

        if ($list)
            return $list[0];
    }

    function update($vars, &$errors) {
        global $cfg;

        $vars['service_type'] = Format::striptags(trim($vars['service_type']));

        if (isset($this->service_type_id) && $this->getId() != $vars['id'])
            $errors['err']=__('Internal error occurred');

        if (!$vars['service_type'])
            $errors['service_type']=__('Service Type name is required');
        elseif (strlen($vars['service_type'])<5)
            $errors['service_type']=__('Service Type name is too short. Five characters minimum');
        elseif (($tid=self::getIdByName($vars['service_type']))
            && (!isset($this->service_type_id) || $tid!=$this->getId()))
            $errors['service_type']=__('Service Type already exists');

        if (!is_numeric($vars['dept_id']))
            $errors['dept_id']=__('Department selection is required');

        if ($errors)
            return false;

        $this->service_type = $vars['service_type'];
        $this->dept_id = $vars['dept_id'];
        $this->page_id = $vars['page_id'] ?: 0;
        $this->isactive = !!$vars['isactive'];
        $this->ispublic = !!$vars['ispublic'];
        $this->notes = Format::sanitize($vars['notes']);

        $rv = false;
        if ($this->__new__) {
            if (!($rv = $this->save())) {
                $errors['err']=sprintf(__('Unable to create %s.'), __('this service type'))
                    .' '.__('Internal error occurred');
            }
        }
        elseif (!($rv = $this->save())) {
            $errors['err']=sprintf(__('Unable to update %s.'), __('this service type'))
                .' '.__('Internal error occurred');
        }
        if ($rv) {
            if (!$cfg || $cfg->getServiceTypeSortMode() == 'a') {
                static::updateSortOrder();
            }
        }
        return $rv;
    }

    function save($refetch=false) {
        if ($this->dirty)
            $this->updated = SqlFunction::NOW();
        return parent::save($refetch || $this->dirty);
    }

    static function updateSortOrder() {
        global $cfg;

        // Fetch (un)sorted names
        if (!($names = static::getServiceTypes(false, true, false)))
            return;

        $names = Internationalization::sortKeyedList($names);

        $update = array_keys($names);
        foreach ($update as $idx=>&$id) {
            $id = sprintf("(%s,%s)", db_input($id), db_input($idx+1));
        }
        if (!count($update))
            return;

        // Thanks, http://stackoverflow.com/a/3466
        $sql = sprintf('INSERT INTO `%s` (service_type_id,`sort`) VALUES %s
            ON DUPLICATE KEY UPDATE `sort`=VALUES(`sort`)',
            SERVICE_TYPE_TABLE, implode(',', $update));
        db_query($sql);
    }

    static function getServiceCatalogue($id) {
        return Service::getServicesForParent($id, false);
    }
}

// Add fields from the standard ticket form to the ticket filterable fields
Filter::addSupportedMatches(/* @trans */ 'Service Type', array('service_type_id' => 'Service ID'), 100);

