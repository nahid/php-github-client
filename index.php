<?php

require 'vendor/autoload.php';
require 'src/Github.php';
$config = include('config/github.php');
use Nahid\GithubClient\Github;


$api = new Github($config);
$api->session->destroy();
echo '<a href="'. $api->makeAuthLink('user, public_repo').'">Auth</a>';

