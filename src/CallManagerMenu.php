<?php

namespace GlpiPlugin\CallManager;

use CommonDBTM;

class CallManagerMenu extends CommonDBTM
{
    /**
     * Get the menu items for the Call Manager plugin
     * @return array
     */
    public static function getMenuContent(): array
    {
        $menu = [];
        $menu['title'] = __('Call Manager', 'callmanager');
        $menu['icon'] = 'fas fa-phone';
        $menu['page']  = "/plugins/callmanager/front/callmanager.php";

        return $menu;
    }
}