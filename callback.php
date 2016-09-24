<?php

require 'vendor/autoload.php';
require 'src/Github.php';
$config = include('config/github.php');
use Nahid\GithubClient\Github;

$api = new Github($config);
//$api->destroyAccessToken();
//$api->session->destroy();
echo '<pre>';
 print_r($api->user('nahid')->get()->data());
echo '</pre>';