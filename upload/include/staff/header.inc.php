<?php
header("Content-Type: text/html; charset=UTF-8");
if (!isset($_SERVER['HTTP_X_PJAX'])) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html<?php
if (($lang = Internationalization::getCurrentLanguage())
        && ($info = Internationalization::getLanguageInfo($lang))
        && (@$info['direction'] == 'rtl'))
    echo ' dir="rtl" class="rtl"';
if ($lang) {
    echo ' lang="' . Internationalization::rfc1766($lang) . '"';
}
?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="x-pjax-version" content="<?php echo GIT_VERSION; ?>">
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:'Apollo Service Manager :: '.__('Agent Control Panel'); ?></title>
    <!--[if IE]>
    <style type="text/css">
        .tip_shadow { display:block !important; }
    </style>
    <![endif]-->
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/tether/utils.js?907ec36"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/tether/tether.js?907ec36"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.11.2.min.js?907ec36"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/bootstrap.js?907ec36"></script>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/bootstrap.css?907ec36" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/thread.css?907ec36" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/scp.css?907ec36" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?907ec36" media="screen"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/typeahead.css?907ec36" media="screen"/>
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?907ec36"
         rel="stylesheet" media="screen" />
     <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?907ec36"/>
    <!--[if IE 7]>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome-ie7.min.css?907ec36"/>
    <![endif]-->
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/dropdown.css?907ec36"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/loadingbar.css?907ec36"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/flags.css?907ec36"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/select2.min.css?907ec36"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/rtl.css?907ec36"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/translatable.css?907ec36"/>

    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }
    ?>
</head>
<body>
<div id="container" class="container-fluid">
    <?php
    if($ost->getError())
        echo sprintf('<div id="error_bar">%s</div>', $ost->getError());
    elseif($ost->getWarning())
        echo sprintf('<div id="warning_bar">%s</div>', $ost->getWarning());
    elseif($ost->getNotice())
        echo sprintf('<div id="notice_bar">%s</div>', $ost->getNotice());
    ?>
    <div class="row-fluid">    
        <div id="leftNav" class="col-sm-3 col-md-2 float-md-left">
            <div id="nav" class="nav nav-sidebar nav-list" role="tablist">
                <?php include STAFFINC_DIR . "templates/navigation.tmpl.php"; ?>
            </div>
        </div>
    </div>        
    <div class="row-fluid">
    <div id="header" class="page-header col-sm-9 offset-sm-3 col-md-10 offset-md-2">
        <div class="pull-right no-pjax">
            <div id="nav_admin" class="pull-left" style="margin: 15px 0px;">
                <?php
                if($thisstaff->isAdmin() && defined('ADMINPAGE')) { ?>
                <button type="button" class="btn btn-secondary"><a href="<?php echo ROOT_PATH ?>scp/index.php" class="no-pjax"><?php echo __('Back to Agent Panel'); ?></a></button>
                <?php } ?>
            </div>
            <!-- SEARCH FORM START -->
            <div id="nav_search" class="pull-left" style="margin: 15px 0;">
                <form action="tickets.php" method="get" onsubmit="javascript:
                  $.pjax({
                    url:$(this).attr('action') + '?' + $(this).serialize(),
                    container:'#pjax-container',
                    timeout: 2000
                  });
                return false;">
                <input type="hidden" name="a" value="search"/>
                <input type="hidden" name="search-type" value=""/>
                <div class="attached input">
                  <input type="text" class="basic-search" data-url="scp/ajax.php/tickets/lookup" name="query"
                    size="30" value="<?php echo Format::htmlchars($_REQUEST['query'], true); ?>"
                    autocomplete="off" autocorrect="off" autocapitalize="off"/>
                  <button type="submit" id="nav-button" class="attached button"><i class="icon-search"></i></button>
                </div>
                <a href="#" onclick="javascript:
                    $.dialog('ajax.php/tickets/search', 201);"
                    >[<?php echo __('advanced'); ?>]</a>
                </form>
            </div>
            <!-- SEARCH FORM END -->
            <a href="<?php echo ROOT_PATH ?>scp/profile.php">
            <div class="avatar pull-right" style="margin: 10px 15px; width: 50px; height: 50px;">
            <?php       $avatar = $thisstaff->getAvatar();
                        echo $avatar; ?>
            </div>
            </a>
        </div>
    </div>
    <div id="pjax-container" class="col-sm-9 offset-sm-3 col-md-10 offset-md-2 <?php if ($_POST) echo 'no-pjax'; ?>">
<?php } else {
    header('X-PJAX-Version: ' . GIT_VERSION);
    if ($pjax = $ost->getExtraPjax()) { ?>
    <script type="text/javascript">
    <?php foreach (array_filter($pjax) as $s) echo $s.";"; ?>
    </script>
    <?php }
    foreach ($ost->getExtraHeaders() as $h) {
        if (strpos($h, '<script ') !== false)
            echo $h;
    } ?>
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:'Apollo Service Manager'; ?></title><?php
} # endif X_PJAX ?>
    <div id="content">
        <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
        <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
        <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
        <?php }
        foreach (Messages::getMessages() as $M) { ?>
            <div class="<?php echo strtolower($M->getLevel()); ?>-banner"><?php
                echo (string) $M; ?></div>
<?php   } ?>
