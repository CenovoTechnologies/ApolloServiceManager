<?php
/**
class.autoclosure.php

Auto-Closure Helper

Melissa S Smith <melissa@cenovotechnologies.com>
Copyright (c)  2016-2017 osTicket
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.
 */
class AutoClosure extends VerySimpleModel implements TemplateVariable {

    static $meta = array(
        'table' => AUTO_CLOSURE_TABLE,
        'pk' => array('id'),
    );

    var $_config;

    const DISPLAY_DISABLED = 2;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->getLocal('name');
    }

    function getTimePeriod() {
        return $this->time_period;
    }

    function getInfo() {
        $base = $this->ht;
        $base['isactive'] = $this->isactive;
        return $base;
    }

    function getCreateDate() {
        return $this->created;
    }

    function getUpdateDate() {
        return $this->updated;
    }

    function isActive() {
        return $this->isactive;
    }

    function getTranslateTag($subtag) {
        return _H(sprintf('autoclosure.%s.%s', $subtag, $this->getId()));
    }

    function getLocal($subtag) {
        $tag = $this->getTranslateTag($subtag);
        $T = CustomDataTranslation::translate($tag);
        return $T != $tag ? $T : $this->ht[$subtag];
    }

    static function getLocalById($id, $subtag, $default) {
        $tag = _H(sprintf('autoclosure.%s.%s', $subtag, $id));
        $T = CustomDataTranslation::translate($tag);
        return $T != $tag ? $T : $default;
    }

    static function getAutoCloseById($id) {
        return AutoClosure::lookup($id);
    }

    function __toString() {
        return (string) $this->getName();
    }

    // TemplateVariable interface
    function asVar() {
        return $this->getName();
    }

    static function getVarScope() {
        return array(
            'name' => __('Auto-Close Plan'),
            'time_period' => __("Time Period (hrs)"),
        );
    }

    function update($vars, &$errors) {

        if (!$vars['time_period'])
            $errors['time_period'] = __('Time period required');
        elseif (!is_numeric($vars['time_period']))
            $errors['time_period'] = __('Numeric value required (in hours)');

        if (!$vars['autoCloseName'])
            $errors['autoCloseName'] = __('Name is required');
        elseif (($sid=AutoClosure::getIdByName($vars['autoCloseName'])) && $sid!=$vars['id'])
            $errors['autoCloseName'] = __('Name already exists');

        if ($errors)
            return false;

        $this->name = $vars['autoCloseName'];
        $this->time_period = $vars['time_period'];
        $this->isactive = $vars['isactive'];

        if ($this->save())
            return true;

        if (isset($this->id)) {
            $errors['err']=sprintf(__('Unable to update %s.'), __('this Auto-Close plan'))
                .' '.__('Internal error occurred');
        } else {
            $errors['err']=sprintf(__('Unable to add %s.'), __('this Auto-Close plan'))
                .' '.__('Internal error occurred');
        }

        return false;
    }

    function save($refetch=false) {
        if ($this->dirty)
            $this->updated = SqlFunction::NOW();

        return parent::save($refetch || $this->dirty);
    }

    function delete() {
        global $cfg;

        if(!$cfg || $cfg->getDefaultSLAId()==$this->getId())
            return false;

        //TODO: Use ORM to delete & update
        $id=$this->getId();
        $sql='DELETE FROM '.AUTO_CLOSURE_TABLE.' WHERE id='.db_input($id).' LIMIT 1';
        if(db_query($sql) && ($num=db_affected_rows())) {
            db_query('UPDATE '.TICKET_TABLE.' SET auto_close_id='.db_input($cfg->getDefaultSLAId()).' WHERE auto_close_id='.db_input($id));
        }

        return $num;
    }

    /** static functions **/
    static function getAutoClosures($publicOnly=false, $disabled=false, $localize=true) {
        global $cfg;
        static $names = array();

        // If localization is specifically requested, then rebuild the list.
        if (!$names || $localize) {
            $objects = self::objects()->values_flat(
                'id', 'isactive', 'name', 'time_period'
            )
                ->order_by('sort');

            // Fetch information for all topics, in declared sort order
            foreach ($objects as $T) {
                list($id, $act, $ac, $time) = $T;
                $names[$id] = array('active'=>$act, 'name'=>$ac, 'time'=>$time);
            }

            $localize_this = function($id, $default) use ($localize) {
                if (!$localize)
                    return $default;

                $tag = _H("auto_closure.name.{$id}");
                $T = CustomDataTranslation::translate($tag);
                return $T != $tag ? $T : $default;
            };
        }

        return $names;
    }

    static function getAutoClosureName($id) {
        $acs = static::getAutoClosures();
        return $acs[$id];
    }

    static function getIdByName($name) {
        $row = static::objects()
            ->filter(array('name'=>$name))
            ->values_flat('id')
            ->first();

        return $row ? $row[0] : 0;
    }

    static function create($vars=false, &$errors=array()) {
        $ac = new static($vars);
        $ac->created = SqlFunction::NOW();
        return $ac;
    }

    static function __create($vars, &$errors=array()) {
        $ac = self::create($vars);
        $ac->save();
        return $ac;
    }
}
?>