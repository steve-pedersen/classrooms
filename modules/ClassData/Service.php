<?php

/**
 * The service functionality to connect to SIS data.
 *
 * @author Charles O'Sullivan <chsoney@sfsu.edu>
 */
class Classrooms_ClassData_Service
{
    private $urlBase;

    private $apiKey;

    private $apiSecret;

    private $channel;

    public function __construct($app, $channel = 'raw')
    {
        $config = $app->configuration;
        $this->urlBase = $config->getProperty('classdata.url') ?? 'https://classdata.sfsu.edu/';
        $this->apiKey = $config->getProperty('classdata.key') ?? 'ca1a3f6f-7cac-4e52-9a0a-5cbf82b16bc9';
        $this->apiSecret = $config->getProperty('classdata.secret') ?? '4af2614e-142d-4db8-8512-b3ba13dd0143';
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
    
    public function getFacilities ()
    {
        $url = $this->signResource("facilities", []);
        list($code, $data) = $this->request($url);

        if ($code == 200)
        {
            return $data;
        }

        return [$code, $data];
    }

    public function getUserSchedules ($semester='', $userId='')
    {
        $url = $this->signResource("userschedule/$userId/semester/$semester", []);
        list($code, $data) = $this->request($url);

        if ($code == 200)
        {
            return $data;
        }

        return [$code, $data];
    }

    public function getSchedules ($semester='', $facilityId='')
    {
        $url = $this->signResource("schedules", ['term' => $semester, 'facility' => $facilityId]);
        list($code, $data) = $this->request($url);

        if ($code == 200)
        {
            return $data;
        }

        return [$code, $data];
    }

    public function getEnrollments ($semester, $role = null)
    {
        $paramMap = [];
        
        if ($role)
        {
            $paramMap['role'] = $role;
        }
        
        $url = $this->signResource("enrollments/{$semester}", $paramMap);
        list($code, $data) = $this->request($url);

        if (!empty($data))
        {
            $data = @json_decode($data, true);
        }

        return [$code, $data];
    }

    public function getUserEnrollments ($userid, $semester, $role = null)
    {
        $paramMap = [];
        if ($role)
        {
            $paramMap['role'] = $role;
        }

        $url = $this->signResource("users/{$userid}/semester/{$semester}", $paramMap);
        list($code, $data) = $this->request($url);

        if (!empty($data))
        {
            $data = @json_decode($data, true)['courses'];
        }

        return [$code, $data];
    }
    
    public function getChanges ($semester, $since)
    {
        $url = $this->signResource("changes/{$semester}", ['since' => $since]);
        list($code, $data) = $this->request($url);

        return [$code, $data];
    }

    public function getCourse ($id)
    {
        $url = $this->signResource('courses/' . $id, ['include' => 'description,prerequisites,students,instructors,userdata']);
        list($code, $data) = $this->request($url);

        if (!empty($data))
        {
            $data = @json_decode($data, true);
        }

        return [$code, $data];
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

    public function getOrganizations ()
    {
        $paramMap = ['include' => 'college'];
        $url = $this->signResource('organizations', $paramMap);
        list($code, $data) = $this->request($url);

        return [$code, $data];
    }

    public function getDepartments ()
    {
        $paramMap = [];
        $url = $this->signResource('departments', $paramMap);
        list($code, $data) = $this->request($url);
        
        return [$code, $data];
    }

    public function getPersonnel ()
    {
        $paramMap = [];
        $url = $this->signResource('personnel', $paramMap);
        list($code, $data) = $this->request($url);

        return [$code, $data];
    }

    // TODO: POST needs testing of implementation
    public function request ($url, $post=false, $postData=[])
    {
        $data = null;
        
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($post) 
        { 
            curl_setopt($ch, CURLOPT_POST, true); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
        } 
        $rawData = curl_exec($ch);
        
        if (!curl_error($ch)) {
            $data = json_decode($rawData, true);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        
        return [$httpCode, $data];
    }
}