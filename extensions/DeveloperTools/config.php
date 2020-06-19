<?php

return array(
    'hooks'  => array(),
    'routes' => array(
        array(
            'slug'     => 'patch',
            'callback' => 'Extension\DeveloperTools\DeveloperTools@runNightlyPatches'
        ),
        array(
            'slug'     => 'commit-pull',
            'callback' => 'Extension\DeveloperTools\Upstream@commitAndPull'
        ),
        array(
            'slug'     => 'download-vendor',
            'callback' => 'Extension\DeveloperTools\Download@downloadVendor'
        ),
    ),
    'options' => array(
        array(
            'label'   => 'Update Framework',
            'handler' => 'commit-pull',
            'confirm' => 'Do you want to update the Framework?',
        ),
        array(
            'label'   => 'Update Framework Vendor',
            'handler' => 'download-vendor',
            'confirm' => 'Do you want to update the Framework Vendor?',
        ),
    ),
    'actions'  => array(
        'activate'   => "Extension\\DeveloperTools\\DeveloperTools@activate"
    ),
    'crons'   => array(
        array(
            'every'   => '0 22 * * *',
            'handler' => 'Extension\DeveloperTools\DeveloperTools@runNightlyPatches',
        ),
    )
);

