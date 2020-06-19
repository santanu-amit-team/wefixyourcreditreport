<?php

namespace Application\Helper;

use Admin\Controller\SettingsController;

class EventHandler
{

    public function disableDevelopmentMode()
    {
        $maxTimeLimit = 3 * 60 * 60; // In seconds
        $fileName     = STORAGE_DIR . DS . '.development_mode';

        if (!file_exists($fileName)) {
            touch($fileName);
            return;
        }

        $currentTime    = time();
        $fileModifiedAt = filemtime($fileName);

        if (($currentTime - $fileModifiedAt) > $maxTimeLimit) {
            $settiongController = new SettingsController();
            $settiongController->updateDevMode(1, 0);
        }

    }

}