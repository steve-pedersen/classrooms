<?php

if (isset($_SERVER['BSS_APP_CONFIG_FILE']))
{
    define('BSS_APP_CONFIG_FILE', $_SERVER['BSS_APP_CONFIG_FILE']);
}
else
{
    require_once 'bss.init.php';
}

require_once 'bss/core/Application.php';
require_once 'bss/core/Request.php';
require_once 'bss/core/Response.php';

if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

function Bss_App_Start ()
{
    $app = Bss_Core_Application::initApplication(BSS_APP_CONFIG_FILE);
    $app->configuration->runMode = Bss_Core_Application::RUN_MODE_DEBUG; // Setup debug mode.
    $app->initEnvironment();
    
    $app->request = $request = new Bss_Core_Request($app);
    $app->response = $response = new Bss_Core_Response($request);
    
    $app->frontController->dispatchRequest($request, $response);
}

Bss_App_Start();
