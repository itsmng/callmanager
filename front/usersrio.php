<?php
include("../../../inc/includes.php");

Session::checkRight("user", READ);
Html::header("settings", '', "config", "plugins");
if (isset($_GET["rio_number"])) {
    $rioNumber = $_GET["rio_number"];
    $users = GlpiPlugin\CallManager\PluginCallManagerUser::getUsersByRio($rioNumber);
    echo "<h2>".__("Users associated with RIO number: ", "callmanager").$rioNumber."</h2>";
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li>".$user['name']." (ID: ".$user['id'].")</li>";
    }
    echo "</ul>";
}
Html::footer();