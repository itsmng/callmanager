<?php

namespace GlpiPlugin\CallManager\Handlers;

use GlpiPlugin\CallManager\PluginCallManagerUser;

class GetUserByRio {
    public function handle(string $rioNumber) {
        global $GLPI_CACHE;

        $cacheKey = "users_by_rio_$rioNumber";
        if ($GLPI_CACHE->has($cacheKey)) {
            return ["users" => $GLPI_CACHE->get($cacheKey)];
        }

        $users = PluginCallManagerUser::getUsersByRio($rioNumber);
        $GLPI_CACHE->set($cacheKey, $users);

        return ["users" => $users];
    }
}