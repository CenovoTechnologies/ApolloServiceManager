<?php
/*********************************************************************
class.class.class.urgency.php

Impact handle
Melissa Smith <melissa@cenovotechnologies.com>
Copyright (c)  2017

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/

class Urgency extends VerySimpleModel
    implements TemplateVariable {

    static $meta = array(
        'table' => URGENCY_TABLE,
        'pk' => array('urgency_id'),
        'ordering' => array('-urgency_level')
    );

    function getId() {
        return $this->urgency_id;
    }

    function getTag() {
        return $this->urgency;
    }

    function getDesc() {
        return $this->urgency_desc;
    }

    function getColor() {
        return $this->urgency_color;
    }

    function getUrgency() {
        return $this->urgency_level;
    }

    function isPublic() {
        return $this->ispublic;
    }

    // TemplateVariable interface
    function asVar() { return $this->getDesc(); }
    static function getVarScope() {
        return array(
            'desc' => __('Urgency Level'),
        );
    }

    function __toString() {
        return (string) $this->getDesc();
    }

    /* ------------- Static ---------------*/
    static function getUrgencies( $publicOnly=false) {
        $urgencies=array();

        $objects = static::objects()->values_flat('urgency_id', 'urgency_desc');
        if ($publicOnly)
            $objects->filter(array('ispublic'=>1));

        foreach ($objects as $row) {
            $urgencies[$row[0]] = $row[1];
        }

        return $urgencies;
    }

    function getPublicUrgencies() {
        return self::getUrgencies(true);
    }
}
?>
