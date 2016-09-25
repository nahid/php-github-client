<?php

namespace Nahid\GithubClient;

use duncan3dc\Sessions\SessionInstance;

class Github
{
    protected $config;
    protected $code = '';
    protected $errors = null;
    public $data;
    public $session;
    public $url = 'https://api.github.com';
    public $answers;

    function __construct($config = null)
    {
        $confManager = new ConfigManager($config);
        $this->session = new SessionInstance('php-github-api');
        $this->config = $confManager->config;
        $this->code = isset($_GET['code']) ? $_GET['code'] : null;
    }

    public function __call($method, $params)
    {
        $method = $this->fromCamelCase($method);
        $param = count($params) > 0 ? '/'.implode('/', $params) : '';
        $this->url .= '/'.$method.$param;

        return $this;
    }

    public function __get($key)
    {
        if(isset($this->data->$key)){
            return $this->data->$key;
        }

    }


    public function makeAuthLink($scope = '', $state = null)
    {
        $state = !is_null($state)?md5($state):'';
        $this->session->set('state', $state);
        return 'https://github.com/login/oauth/authorize?client_id='.$this->config->get('client_id').'&redirect_uri='.$this->config->get('redirect_uri').'&scope='. rawurlencode($scope).'&state='. $state;
    }

    public function getAccessToken()
    {
        if ($this->isExpired()) {
            $url = 'https://github.com/login/oauth/access_token';
            $expires = $this->session->get('expires')?:7200;

            // Initialize curl
            $ch = curl_init();

            // Set the options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                'client_id='.$this->config->get('client_id').'&client_secret='.$this->config->get('client_secret').'&code='.$this->code.'&redirect_uri='.$this->config->get('redirect_uri'). '&state='.$this->session->get('state'));

            $result = curl_exec($ch);
            //var_dump($result);
            parse_str($result);
        
            curl_close($ch);

            $this->session->set('accessToken', $access_token);
            $this->session->set('expires', time() + $expires);
            return $access_token;
        }

        return $this->session->get('accessToken');
    }


    public function get($opts = null)
    {
        $options = '';
        if(!is_null($opts) && is_array($opts)) {
            foreach ($opts as $key => $value) {
               $options .= $key . '=' . $value . '&';
            }
        }

        $accessTokenUri = $this->makeAccessTokenQueryString();

        $url = $this->url . '?' . $options . $accessTokenUri. '&token_type=bearer';

        $data = $this->getDataUsingCurl($url);
        $this->data = $data;

        return $this;
    }


    public function data()
    {
        return $this->data;
    }


    public function me()
    {
        $this->url .= '/user';

        return $this;
    }

    public function user($username)
    {
        $this->url .= '/users/'.$username;

        return $this;
    } 

    public function org($orgname)
    {
        $this->url .= '/orgs/'.$orgname;

        return $this;
    }



    public function totalStargazers()
    {
        $totalStars = 0;
        $url = 'https://api.github.com/users/' . $this->login;
        $repos = $this->repos()->get(['page'=>1, 'per_page'=>$this->public_repos]);
        foreach ($repos->data() as $repo) {
            $totalStars += $repo->stargazers_count;
        }

        $this->url = $url;
        return $totalStars;
    }

     public function totalForks()
    {
        $totalForks = 0;
        $url = 'https://api.github.com/users/' . $this->login;
        $repos = $this->repos()->get(['page'=>1, 'per_page'=>$this->public_repos]);
        foreach ($repos->data() as $repo) {
            $totalForks += $repo->forks_count;
        }

        $this->url = $url;
        return $totalForks;
    }


    protected function getDataUsingCurl($url)
    {
        $ch = curl_init();

        // Set the options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');  // Required by API
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/vnd.github.v3+json
'));

        $result = curl_exec($ch);
        curl_close($ch);

        $obj = json_decode($result);

        return $obj;
    }

    protected function makeAccessTokenQueryString()
    {
        $accessToken = '';
        $accessTokenUri = '';
        $accessToken = $this->getAccessToken();

        $accessTokenUri = 'access_token='.$accessToken;

        return $accessTokenUri;
    
    }

    public function isExpired()
    {
        if (time() > $this->session->get('expires')) {
            return true;
        }

        return false;
    }

    public function destroyAccessToken()
    {
        $this->session->set('accessToken', null);
        $this->session->set('expires', time()-3600);
        return true;
    }

    public static function fromCamelCase($str, $glue = '-')
    {
        $str[0] = strtolower($str[0]);

        return strtolower(preg_replace('/([A-Z])/', $glue . strtolower('\\1'), $str));
    }
}
