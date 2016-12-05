<?php

if(($tabs=$nav->getTabs()) && is_array($tabs)){
    foreach($tabs as $name =>$tab) {
        if ($tab['href'][0] != '/')
            echo "<div class='card'>";
            echo "<div role='tab' id='heading.$name'>";
        echo sprintf('<li><a class="btn btn-block %s" data-toggle="%s" data-parent="#nav" href="%s" aria-expanded="%s" aria-controls="%s">%s</a></li>',
            @$tab['class'] ?: '',
            $tab['data-toggle'],
            $tab['href'],
            $tab['aria-expanded'],
            $tab['aria-controls'],
            $tab['desc']);
        echo "\n</div>\n";
        if($subnav=$nav->getSubMenu($name)){
            if($name == 'dashboard') {
                echo "<div id='$name' class='collapse in' role='tabpanel' aria-labelledby='heading.$name'>\n";
                echo "<ul class='inactive' >\n";
            } else {
                echo "<div id='$name' class='collapse' role='tabpanel' aria-labelledby='heading.$name'>\n";
                echo "<ul class='inactive'>\n";
            }
            foreach($subnav as $k => $item) {
                if (!($id=$item['id']))
                    $id="nav$k";
                if ($item['href'][0] != '/')
                    $item['href'] = ROOT_PATH . 'scp/' . $item['href'];

                echo sprintf(
                    '<li><a class="%s" href="%s" title="%s" id="%s">%s</a></li>',
                    $item['iconclass'],
                    $item['href'], $item['title'],
                    $id, $item['desc']);
            }
            echo "\n</ul>\n";
        }
        echo "\n</div>\n";
        echo "\n</div>\n";
    }
}
?>
