<?php

include('../../../inc/includes.php');

Session::checkRight("user", UPDATE);

use GlpiPlugin\CallManager\PluginCallManagerUser;

if (isset($_POST['update_rio'])) {
    // Update RIO number
    PluginCallManagerUser::updateRIO($_POST);
    
    Html::back();
}

Html::back();
