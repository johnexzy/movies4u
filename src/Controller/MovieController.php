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
use Src\TableGateways\MovieGateway;

class MovieController extends MovieGateway{
    
    private $db, $requestMethod, $input, $limit, $popular, $pageNo, $short_url, $id;

    public function __construct($db, $requestMethod, Array $input = null, int $id = null, int $limit = null, int $popular = null, int $pageNo = null, String $short_url = null) {
        parent::__construct($db);
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->input = $input;
        $this->limit = $limit;
        $this->popular = $popular;
        $this->pageNo = $pageNo;
        $this->short_url = $short_url;
        $this->id = $id;
    }
    /**
     * processes all requests targetted to this controller.
     */
    public function processRequest()
    {
        switch ($this->requestMethod) {
            
            case 'POST':
                $response = $this->addNewVideoFromRequest();
                break;
            case 'GET' :
                if ($this->short_url !== null) {
                    $response = $this->getVideoByUrl($this->short_url);
                }
                elseif ($this->popular) {
                    $response = $this->getVideoByPopular($this->popular);
                }
                elseif ($this->pageNo) {
                    $response = $this->getVideoByPage($this->pageNo);
                }
                else {
                    $response = $this->getAllVideos($this->limit);
                };
                break;
            case 'PUT':
                $response = ($this->id && $this->input) ? 
                $this->updateVideoFromRequest($this->id, $this->input)
                : $this->notFoundResponse();
                break;
            case 'DELETE':
                    $response = ($this->id) ? $this->deleteVideo($this->id) : $this->notFoundResponse();
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

    /**
     * Controller Method for Create Requests. 
     */
    private function addNewVideoFromRequest() {
        $result = $this->insert($this->input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
       $response['body'] = \json_encode($result);
        return $response;
    }
    private function getVideoByUrl($short_url)
    {   
        $result = $this->findByUrl($short_url);
        if(!$result){
            return $this->notFoundResponse();
        }
        $result = $this->getByUrl($short_url);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function getAllVideos($limit)
    {
        $result = $this->getAll($limit);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function getVideoByPage($pageNo)
    {
        $result = $this->getPages($pageNo);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function getVideoByPopular($popularInt)
    {
        $result = $this->getPopular($popularInt);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function deleteVideo($id) {
        $result = $this->find($id);
        if(!$result){
            return $this->notFoundResponse();
        }
        $result = $this->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    private function updateVideoFromRequest(int $id, Array $input)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
            # code...
        }
        if (!$this->validateUpdateInput($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = \json_encode($this->find($id));
        return $response;
    }
    private function validateUpdateInput(Array $input) {
        if (! isset($input['video_name'])) {
            return false;
        }
        if (! isset($input['video_details'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }
    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
    }
