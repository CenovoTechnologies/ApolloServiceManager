<?php
/**
class.resolutioncode.php

Resolution Code Helper

Melissa S Smith <melissa@cenovotechnologies.com>
Copyright (c)  2016-2017 Apollo Service Manager
http://www.cenovotechnologies.com

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.
 */
class ResolutionCode extends VerySimpleModel implements TemplateVariable {

    static $meta = array(
        'table' => RESOLUTION_CODE_TABLE,
        'pk' => array('id'),
    );

    var $_config;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->getLocal('name');
    }

    function getDescription() {
        return $this->notes;
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
        return _H(sprintf('resolutioncode.%s.%s', $subtag, $this->getId()));
    }

    function getLocal($subtag) {
        $tag = $this->getTranslateTag($subtag);
        $T = CustomDataTranslation::translate($tag);
        return $T != $tag ? $T : $this->ht[$subtag];
    }

    static function getLocalById($id, $subtag, $default) {
        $tag = _H(sprintf('resolutioncode.%s.%s', $subtag, $id));
        $T = CustomDataTranslation::translate($tag);
        return $T != $tag ? $T : $default;
    }

    static function getResCodeById($id) {
        return ResolutionCode::lookup($id);
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
            'name' => __('Resolution Code'),
            'notes' => __("Description"),
        );
    }

    function update($vars, &$errors) {

        if (!$vars['description'])
            $errors['description'] = __('Description required');

        if (!$vars['resCodeName'])
            $errors['resCodeName'] = __('Name is required');
        elseif (($sid=ResolutionCode::getIdByName($vars['resCodeName'])) && $sid!=$vars['id'])
            $errors['resCodeName'] = __('Name already exists');

        if ($errors)
            return false;

        $this->name = $vars['resCodeName'];
        $this->notes = $vars['description'];
        $this->isactive = $vars['isactive'];

        if ($this->save())
            return true;

        if (isset($this->id)) {
            $errors['err']=sprintf(__('Unable to update %s.'), __('this resolution code'))
                .' '.__('Internal error occurred');
        } else {
            $errors['err']=sprintf(__('Unable to add %s.'), __('this resolution code'))
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


        //TODO: Use ORM to delete & update
        $id=$this->getId();
        $sql='DELETE FROM '.RESOLUTION_CODE_TABLE.' WHERE id='.db_input($id).' LIMIT 1';
        if(db_query($sql) && ($num=db_affected_rows())) {
        }

        return $num;
    }

    /** static functions **/
    static function getResolutionCodes($localize=true) {
        global $cfg;
        static $names = array();

        // If localization is specifically requested, then rebuild the list.
        if (!$names) {
            $objects = self::objects()->values_flat(
                'id', 'isactive', 'notes', 'name'
            )
                ->order_by('sort');

            // Fetch information for all service, in declared sort order
            foreach ($objects as $T) {
                list($id, $act, $desc, $name) = $T;
                $names[$id] = array('id' => $id, 'active' => $act, 'description' => $desc, 'name' => $name);
            }
        }

        return $names;
    }

    static function getResolutionCodeName($id) {
        $acs = static::getResolutionCodes();
        return @$acs[$id];
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