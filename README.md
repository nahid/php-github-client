# php-github-client

php-github-client is a php SDK for github api. Now you can easily access and handle all of github Apis. Lets enjoy :)


#How to use

```git clone git@github.com:nahid/php-github-client.git```

```php
require 'src/Github.php';
$config = include('config/github.php');
use Nahid\GithubClient\Github;


$api = new Github($config);
$api->session->destroy();
echo '<a href="'. $api->makeAuthLink('user, public_repo').'">Auth</a>';
