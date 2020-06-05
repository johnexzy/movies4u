<?php

/*
 * Copyright 2020 hp.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Src\Controller;

/**
 * Description of MusicController
 *
 * @author Oba John
 */
use Src\TableGateWays\AlbumGateWay;

class AlbumController extends AlbumGateWay{
    
    private $db;
    private $requestMethod;
    public $input;
    private $limit;
    private $short_url;
    private $id = null;
    public function __construct($db, $requestMethod, Array $input = null, $id = null, $limit = null, $short_url = null) {
        parent::__construct($db);
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->input = $input;
        $this->limit = $limit;
        $this->short_url = $short_url;
        $this->id = $id;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            
            case 'POST':
                $response = $this->addNewAlbumFromRequest();
                break;
            case 'GET' :
                if ($this->short_url !== null) {
                    $response = $this->getAlbumByUrl($this->short_url);
                }
                else {
                    $response = $this->getAllAlbums($this->limit);
                };
                break;
            case 'DELETE':
                if ($this->id) {
                    $response = $this->deleteAlbum($this->id);
                }
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    
    private function addNewAlbumFromRequest() {
        $result = $this->insert($this->input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
       $response['body'] = \json_encode($result);
        return $response;
    }
    private function getAlbumByUrl($short_url)
    {
        $result = $this->getByUrl($short_url);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function getAllAlbums($limit)
    {
        $result = $this->getAll($limit);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function deleteAlbum($id) {
        $result = $this->find($id);
        if(!$result){
            return $this->notFoundResponse();
        }
        $result = $this->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
    }