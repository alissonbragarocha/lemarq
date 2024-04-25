<?php
require_once 'request.php';
try
{
    $location = 'http://git/template/rest.php';
    $parameters = array();
    $parameters['class']    = 'ApplicationAuthenticationRestService';
    $parameters['method']   = 'getToken';
    $parameters['login']    = 'user';
    $parameters['password'] = 'user';
    
    $token = request($location, 'GET', $parameters, 'Basic 123');
    print $token;
}
catch (Exception $e)
{
    echo 'Error: ' . $e->getMessage();
}
?>
