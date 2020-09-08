<?php

/**
 * The service functionality to connect to SIS data.
 *
 * @author Charles O'Sullivan <chsoney@sfsu.edu>
 */
class At_ClassData_Service
{
    private $urlBase;

    private $apiKey;

    private $apiSecret;

    private $channel;

    public function __construct($app, $channel = 'raw')
    {
        $config = $app->configuration;
        $this->urlBase = $config->getProperty('classdata.url');
        $this->apiKey = $config->getProperty('classdata.key');
        $this->apiSecret = $config->getProperty('classdata.secret');
        $this->channel = $channel;
    }
    
    protected function signResource ($resource, $paramMap)
    {
        $url = $this->urlBase . $resource;

        $paramMap['a'] = $this->apiKey; //die($paramMap['a']);
        $paramMap['channel'] = (!isset($paramMap['channel']) ? $this->channel : $paramMap['channel']);
        uksort($paramMap, 'strcmp');
        
        $params = [];
        foreach ($paramMap as $k => $v) { $params[] = urlencode($k) . '=' . urlencode($v); }
        $url .= '?' . implode('&', $params);
        
        return $url . '&s=' . sha1($this->apiSecret . $url);
    }
    
    public function getEnrollments ($semester, $role = null)
    {
        $paramMap = [];
        
        if ($role)
        {
            $paramMap['role'] = $role;
        }
        
        $url = $this->signResource("enrollments/{$semester}", $paramMap);
        //die($url);
        $req = new HttpRequest($url, HTTP_METH_GET);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }
        return [$req->getResponseCode(), $data];
    }

    public function getUserEnrollments ($userid, $semester, $role = null)
    {
        $paramMap = [];
        if ($role)
        {
            $paramMap['role'] = $role;
        }

        $url = $this->signResource("users/{$userid}/semester/{$semester}", $paramMap);
        //die($url);
        $req = new HttpRequest($url, HTTP_METH_GET);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }

        return [$req->getResponseCode(), $data['courses']];
    }
    
    public function getChanges ($semester, $since)
    {
        $url = $this->signResource("changes/{$semester}", ['since' => $since]);
        $req = new HttpRequest($url, HTTP_METH_GET);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }

        return [$req->getResponseCode(), $data];
    }

    public function getCourse ($id)
    {
        $url = $this->signResource('courses/' . $id, ['include' => 'description,prerequisites,students,instructors,userdata']);
        $req = new HttpRequest($url, HTTP_METH_POST);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }
        
        return [$req->getResponseCode(), $data];
    }
    
    public function getCourses ($idList)
    {
        $url = $this->signResource('courses', ['include' => 'description,prerequisites']);
        $req = new HttpRequest($url, HTTP_METH_POST);
        $req->setPostFields(['ids' => implode(',', $idList)]);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body) && $req->getResponseCode() === 200)
        {
            $data = @json_decode($body, true);
            return $data['courses'];
        }
        
        return false;
    }
    
    public function getUsers ($idList)
    {
        $url = $this->signResource('users', ['include' => 'description,prerequisites']);
        $req = new HttpRequest($url, HTTP_METH_POST);
        $req->setPostFields(['ids' => implode(',', $idList)]);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body) && $req->getResponseCode() === 200)
        {
            $data = @json_decode($body, true);
            return $data['users'];
        }
        
        return false;
    }
}