<?php

require_once 'library' . DIRECTORY_SEPARATOR . 'bootstrap.php';

use Application\Response;

try
{
    Bootstrap::initialize('apiSimulator');
}
catch (\Exception $ex)
{
    Response::$disableLog = true;
    return Response::send(
            array(
                'error' => $ex->getMessage()
            )
    );
    exit;
}