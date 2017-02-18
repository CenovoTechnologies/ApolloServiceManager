<?php
/*********************************************************************
class.service.php

Service Catalogue helper

Melissa Smith <melissa@cenovotechnologies.com
Copyright (c)  2017 cenovoTechnologies
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/

require_once INCLUDE_DIR . 'class.topic.php';
require_once INCLUDE_DIR . 'class.servicecat.php';
require_once INCLUDE_DIR . 'class.sequence.php';
require_once INCLUDE_DIR . 'class.filter.php';

class Service extends VerySimpleModel
    implements TemplateVariable {

    static $meta = array(
        'table' => SERVICE_TABLE,
        'pk' => array('service_id'),
        'ordering' => array('service'),
        'joins' => array(
            'template' => array(
                'list' => false,
                'constraint' => array(
                    'service_pid' => 'Topic.topic_id',
                ),
            ),
            'faqs' => array(
                'list' => true,
                'reverse' => 'FaqTopic.topic'
            ),
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
            'priority' => array(
                'null' => true,
                'constraint' => array(
                    'priority_id' => 'Priority.priority_id',
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
            'fullname' => __('Service full path'),
            'name' => __('Service'),
            'parent' => array(
                'class' => 'Service', 'desc' => __('Parent'),
            ),
            'sla' => array(
                'class' => 'SLA', 'desc' => __('Service Level Agreement'),
            ),
        );
    }

    function getId() {
        return $this->service_id;
    }

    function getPid() {
        return $this->service_pid;
    }

    function getParent() {
        return $this->parent;
    }

    function getName() {
        return $this->service;
    }

    function getLocalName() {
        return $this->getLocal('name');
    }

    function getFullName() {
        return self::getServiceName($this->getId()) ?: $this->service;
    }

    static function getServiceName($id) {
        $names = static::getServiceCatalogue(false, true);
        return $names[$id];
    }

    function getServiceCats() {
        return ServiceCat::getParentCategories($this->getId());
    }

    function getDeptId() {
        return $this->dept_id;
    }

    function getSLAId() {
        return $this->sla_id;
    }

    function getPriorityId() {
        return $this->priority_id;
    }

    function getStatusId() {
        return $this->status_id;
    }

    function getStaffId() {
        return $this->staff_id;
    }

    function getTeamId() {
        return $this->team_id;
    }

    function getPageId() {
        return $this->page_id;
    }

    function getPage() {
        return $this->page;
    }

    function autoRespond() {
        return !$this->noautoresp;
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
        $base['custom-numbers'] = $this->hasFlag(self::FLAG_CUSTOM_NUMBERS);
        return $base;
    }

    function hasFlag($flag) {
        return $this->flags & $flag != 0;
    }

    function getNewTicketNumber() {
        global $cfg;

        if (!$this->hasFlag(self::FLAG_CUSTOM_NUMBERS))
            return $cfg->getNewTicketNumber();

        if ($this->sequence_id)
            $sequence = Sequence::lookup($this->sequence_id);
        if (!$sequence)
            $sequence = new RandomSequence();

        return $sequence->next($this->number_format ?: '######',
            array('Ticket', 'isTicketNumberUnique'));
    }

    function getTranslateTag($subtag) {
        return _H(sprintf('service.%s.%s', $subtag, $this->getId()));
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

        if ($this->getId() == $cfg->getDefaultServiceId())
            return false;

        if (parent::delete()) {
            self::objects()->filter(array(
                'service_pid' => $this->getId()
            ))->update(array(
                'service_pid' => 0
            ));
            Service::objects()->filter(array(
                'service_id' => $this->getId()
            ))->delete();
            db_query('UPDATE '.TICKET_TABLE.' SET service_id=0 WHERE service_id='.db_input($this->getId()));
        }

        return true;
    }

    function __toString() {
        return (string) $this->getFullName();
    }

    /*** Static functions ***/

    static function create($vars=array()) {
        $service = new static($vars);
        $service->created = SqlFunction::NOW();
        return $service;
    }

    static function __create($vars, &$errors) {
        $service = self::create($vars);
        if (!isset($vars['dept_id']))
            $vars['dept_id'] = 0;
        $vars['id'] = $vars['service_id'];
        $service->update($vars, $errors);
        return $service;
    }

    static function getServiceCatalogue($publicOnly=false, $disabled=false, $localize=true) {
        global $cfg;
        static $services, $names = array();

        // If localization is specifically requested, then rebuild the list.
        if (!$names || $localize) {
            $objects = self::objects()->values_flat(
                'service_id', 'service_pid', 'ispublic', 'isactive', 'service'
            )
                ->order_by('sort');

            // Fetch information for all service, in declared sort order
            $services = array();
            foreach ($objects as $T) {
                list($id, $pid, $pub, $act, $service) = $T;
                $services[$id] = array('pid'=>$pid, 'public'=>$pub,
                    'disabled'=>!$act, 'service'=>$service);
            }

            $localize_this = function($id, $default) use ($localize) {
                if (!$localize)
                    return $default;

                $tag = _H("service.name.{$id}");
                $T = CustomDataTranslation::translate($tag);
                return $T != $tag ? $T : $default;
            };

            // Resolve parent names
            foreach ($services as $id=>$info) {
                $name = $localize_this($id, $info['service']);
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
        if ($localize && $cfg->getServiceSortMode() == self::SORT_ALPHA)
            return Internationalization::sortKeyedList($requested_names);

        return $requested_names;
    }

    static function getServicesForParent($parentId, $localize=true) {
        global $cfg;
        static $services, $names = array();

        // If localization is specifically requested, then rebuild the list.
        if (!$names || $localize) {
            $objects = self::objects()->values_flat(
                'service_id', 'service_pid', 'ispublic', 'isactive', 'team_id', 'service'
            )
                ->order_by('sort');

            // Fetch information for all service, in declared sort order
            $services = array();
            foreach ($objects as $T) {
                list($id, $pid, $pub, $act, $team, $service) = $T;
                $services[$id] = array('id'=>$id, 'pid'=>$pid, 'public'=>$pub,
                    'active'=>$act, 'team'=>$team, 'service'=>$service);
            }

            // Resolve parent names
            foreach ($services as $id=>$info) {
                if ($parentId == $info['pid']) {
                    $names[$id] = $info;
                }
            }
        }

        // If localization requested and the current locale is not the
        // primary, the list may need to be sorted. Caching is ok here,
        // because the locale is not going to be changed within a single
        // request.
        if ($localize && $cfg->getServiceSortMode() == self::SORT_ALPHA)
            return Internationalization::sortKeyedList($names);

        return $names;
    }

    static function getPublicServices() {
        return self::getServiceCatalogue(true);
    }

    static function getAllServices($localize=false) {
        return self::getServiceCatalogue(false, true, $localize);
    }

    static function getLocalNameById($id) {
        $services = static::getServiceCatalogue(false, true);
        return $services[$id];
    }

    static function getIdByName($name, $pid=0) {
        $list = self::objects()->filter(array(
            'service'=>$name,
            'service_pid'=>$pid,
        ))->values_flat('service_id')->first();

        if ($list)
            return $list[0];
    }

    static function getActiveFlagById($id) {
        $list = self::objects()->filter(array(
            'service_id'=>$id
        ))->values_flat('isactive')->first();

        if ($list) {
            return $list[0];
        } else {
            return "";
        }
    }

    static function getPublicFlagById($id) {
        $list = self::objects()->filter(array(
            'service_id'=>$id
        ))->values_flat('ispublic')->first();

        if ($list) {
            return $list[0];
        } else {
            return "";
        }
    }

    function update($vars, &$errors) {
        global $cfg;

        $vars['service'] = Format::striptags(trim($vars['service']));

        if (isset($this->service_id) && $this->getId() != $vars['id'])
            $errors['err']=__('Internal error occurred');

        if (!$vars['service'])
            $errors['service']=__('Service name is required');
        elseif (strlen($vars['service'])<5)
            $errors['service']=__('Service is too short. Five characters minimum');
        elseif (($tid=self::getIdByName($vars['service'], $vars['service_pid']))
            && (!isset($this->service_id) || $tid!=$this->getId()))
            $errors['service']=__('Service already exists');

        if (!is_numeric($vars['dept_id']))
            $errors['dept_id']=__('Department selection is required');

        if ($vars['custom-numbers'] && !preg_match('`(?!<\\\)#`', $vars['number_format']))
            $errors['number_format'] =
                'Ticket number format requires at least one hash character (#)';

        if ($errors)
            return false;

        $this->service = $vars['service'];
        $this->service_pid = $vars['service_pid'] ?: 0;
        $this->dept_id = $vars['dept_id'];
        $this->team_id = preg_replace("/[^0-9]/", "", $vars['service_owner']);;
        $this->priority_id = $vars['priority_id'] ?: 0;
        $this->status_id = $vars['status_id'] ?: 0;
        $this->sla_id = $vars['sla_id'] ?: 0;
        $this->page_id = $vars['page_id'] ?: 0;
        $this->isactive = !!$vars['isactive'];
        $this->ispublic = !!$vars['ispublic'];
        $this->sequence_id = $vars['custom-numbers'] ? $vars['sequence_id'] : 0;
        $this->number_format = $vars['custom-numbers'] ? $vars['number_format'] : '';
        $this->flags = $vars['custom-numbers'] ? self::FLAG_CUSTOM_NUMBERS : 0;
        $this->noautoresp = !!$vars['noautoresp'];
        $this->notes = Format::sanitize($vars['notes']);

        //Auto assign ID is overloaded...
        if ($vars['assign'] && $vars['assign'][0] == 's') {
            $this->team_id = 0;
            $this->staff_id = preg_replace("/[^0-9]/", "", $vars['assign']);
        }
        elseif ($vars['assign'] && $vars['assign'][0] == 't') {
            /*$this->staff_id = 0;
            $this->team_id = preg_replace("/[^0-9]/", "", $vars['assign']);*/
        }
        else {
            /*$this->staff_id = 0;
            $this->team_id = 0;*/
        }

        $rv = false;
        if ($this->__new__) {
            if (!($rv = $this->save())) {
                $errors['err']=sprintf(__('Unable to create %s.'), __('this service'))
                    .' '.__('Internal error occurred');
            }
        }
        elseif (!($rv = $this->save())) {
            $errors['err']=sprintf(__('Unable to update %s.'), __('this service'))
                .' '.__('Internal error occurred');
        }
        if ($rv) {
            if (!$cfg || $cfg->getServiceSortMode() == 'a') {
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

        // Consider all the forms in the request
        $current = array();
        if (is_array($form_ids = $vars['forms'])) {
            $forms = ServiceFormModel::objects()
                ->select_related('form')
                ->filter(array('service_id' => $this->getId()));
            foreach ($forms as $F) {
                if (false !== ($idx = array_search($F->form_id, $form_ids))) {
                    $current[] = $F->form_id;
                    $F->sort = $idx + 1;
                    $F->extra = JsonDataEncoder::encode(
                        array('disable' => $find_disabled($F->form))
                    );
                    $F->save();
                    unset($form_ids[$idx]);
                }
                elseif ($F->form->get('type') != 'T') {
                    $F->delete();
                }
            }
            foreach ($form_ids as $sort=>$id) {
                if (!($form = DynamicForm::lookup($id))) {
                    continue;
                }
                elseif (in_array($id, $current)) {
                    // Don't add a form more than once
                    continue;
                }
                $tf = new ServiceFormModel(array(
                    'service_id' => $this->getId(),
                    'form_id' => $id,
                    'sort' => $sort + 1,
                    'extra' => JsonDataEncoder::encode(
                        array('disable' => $find_disabled($form))
                    )
                ));
                $tf->save();
            }
        }
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
        if (!($names = static::getServiceCatalogue(false, true, false)))
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
            SERVICE_TABLE, implode(',', $update));
        db_query($sql);
    }
}

// Add fields from the standard ticket form to the ticket filterable fields
Filter::addSupportedMatches(/* @trans */ 'Service Template', array('serviceId' => 'Service ID'), 100);

class ServiceFormModel extends VerySimpleModel {
    static $meta = array(
        'table' => SERVICE_FORM_TABLE,
        'pk' => array('id'),
        'ordering' => array('sort'),
        'joins' => array(
            'service' => array(
                'constraint' => array('service_id' => 'Service.service_id'),
            ),
            'form' => array(
                'constraint' => array('form_id' => 'DynamicForm.id'),
            ),
        ),
    );
}
