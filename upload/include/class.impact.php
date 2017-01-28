<?php
/*********************************************************************
class.impact.php

Impact handle
Melissa Smith <melissa@cenovotechnologies.com>
Copyright (c)  2017

Released under the GNU General Public License WITHOUT ANY WARRANTY.
See LICENSE.TXT for details.

vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/

class Impact extends VerySimpleModel
    implements TemplateVariable {

    static $meta = array(
        'table' => IMPACT_TABLE,
        'pk' => array('impact_id'),
        'ordering' => array('-impact_level')
    );

    function getId() {
        return $this->impact_id;
    }

    function getTag() {
        return $this->impact;
    }

    function getDesc() {
        return $this->impact_desc;
    }

    function getColor() {
        return $this->impact_color;
    }

    function getUrgency() {
        return $this->impact_level;
    }

    function isPublic() {
        return $this->ispublic;
    }

    // TemplateVariable interface
    function asVar() { return $this->getDesc(); }
    static function getVarScope() {
        return array(
            'desc' => __('Impact Level'),
        );
    }

    function __toString() {
        return (string) $this->getDesc();
    }

    /* ------------- Static ---------------*/
    static function getImpacts( $publicOnly=false) {
        $impacts=array();

        $objects = static::objects()->values_flat('impact_id', 'impact_desc');
        if ($publicOnly)
            $objects->filter(array('ispublic'=>1));

        foreach ($objects as $row) {
            $impacts[$row[0]] = $row[1];
        }

        return $impacts;
    }

    function getPublicImpacts() {
        return self::getImpacts(true);
    }
}
?>
