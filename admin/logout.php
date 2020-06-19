<?php 
/*
 * To clear session and logout
 */
require_once '..' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'bootstrap.php';
Bootstrap::initialize('static');
use Application\Session;

Session::clear();

header('location: ../admin/');
exit;
?>