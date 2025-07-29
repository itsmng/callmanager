<?php
include("../../../inc/includes.php");

use GlpiPlugin\CallManager\PluginCallManagerConfig;

$plugin = new Plugin();

if($plugin->isActivated("callmanager")) {
    $config = new PluginCallManagerConfig();
    if(isset($_POST["update"])) {
        Session::checkRight("plugin_callmanager_config", UPDATE);
        $config::updateConfigValues($_POST);
    } else {
        if (!Session::haveRight("plugin_callmanager_config", READ | UPDATE)) {
            Html::displayRightError();
            return;
        }
        Html::header("Call Manager", $_SERVER["PHP_SELF"], "config", Plugin::class);
        $config->showConfigForm();
    }
} else {
    Html::header("settings", '', "config", "plugins");
    echo "<div class='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br>";
    echo "<b>Please enable the plugin before configuring it</b></div>";
    Html::footer();
}

Html::footer();
