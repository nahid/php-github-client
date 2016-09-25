<?php

if(!function_exists('get_github_auth_url')) {
    function get_github_auth_url()
    {
        return app('Github')->makeAuthLink();
    }
}

