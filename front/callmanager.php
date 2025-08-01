<?php

include("../../../inc/includes.php");

// Check if plugin is activated...
if (!(new Plugin())->isActivated('callmanager')) {
    Html::displayNotFoundError();
}

$console = new GlpiPlugin\CallManager\CallManagerMenu();

Session::checkRight('plugin_callmanager_access', READ);

Html::header(
    __('Call Manager', 'callmanager'),
    $_SERVER['PHP_SELF'],
    'plugins',
    GlpiPlugin\CallManager\CallManagerMenu::class,
    'option'
);

echo "<script type='module' src='" . Plugin::getWebDir('callmanager') . "/js/console.js'></script>";
echo "<div id='plugin_callmanager_ui'></div>";

Html::footer();