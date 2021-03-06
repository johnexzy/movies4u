<?php

/*
 * Copyright 2020 ObaJohn.
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
namespace Src\TableGateways;
/**
 * Description of SongGateway
 *
 * @author ObaJohn
 */
use Src\Logic\MakeFile;
/**
 * Handles all video requests and db_operations, CRUD
 * 
 */
class VideoGateway {
    //put your code here
    private $db = null;
    public function __construct($db) {
        
        $this->db = $db;
    }

     /**
     * Peforms Create Operation for video
     * @param String key
     * 
     * @return Array
     * 
     */
    public function getAllWithKey($key)
        {
                $statement = "
                        SELECT
                                video_url, video_bytes
                        FROM
                                videos
                        WHERE video_key = ?;
                ";
                try {
                        $statement = $this->db->prepare($statement);
                        $statement->execute(array($key));
                        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                        return $result;
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        /**
         * Performs Create Operation for video
         * @param String $video
         * @param String $name
         * @param String $key
         * 
         * @return String
         * 
         */
        public function createvideo(Array $video, $key) {
                $statement = "
                        INSERT INTO videos
                                (video_key,	video_url,	video_bytes)
                        VALUES
                                (:video_key,	:video_url,	:video_bytes)
                ";
                try {
                        
                        $query = $this->db->prepare($statement);
                        $query->execute(array(
                                'video_key' => $key,
                                'video_url' => $video['name'],
                                'video_bytes' => $video['byte']
                        ));
                        
                        return $query->rowCount();
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
            
        }

        /**
         * Performs Create Operation for video
         * @param Array $video
         * @param String $name
         * @param String $key
         * 
         * @return String
         * 
         */
        public function createEpisodeVideo(Array $video, $name, string $key) {
                $statement = "
                        INSERT INTO videos
                                (video_key,	video_url,	video_bytes)
                        VALUES
                                (:video_key,	:video_url,	:video_bytes)
                ";
                try {
                        
                        $query = $this->db->prepare($statement);
                        $query->execute(array(
                                'video_key' => $key,
                                'video_url' => MakeFile::makeEpisodeVideo($video, $name),
                                'video_bytes' => $video['size']
                        ));
                        
                        return $query->rowCount();
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
            
        }
        public function createMultiVideo(Array $songs, $name, string $_key) {
                $statement = "
                        INSERT INTO songs
                                (song_url, song_bytes, song_key )
                        VALUES
                                (:song_url, :song_bytes, :song_key )
                ";
                try {
                        
                        $query = $this->db->prepare($statement);
                        foreach ($songs as $key => $song) {
                          $query->execute(array(
                                'song_url' => MakeFile::makesong($song, $name),
                                'song_bytes' => $song['size'],
                                'song_key' => $_key
                                ));      
                        }
                        
                        
                        return $query->rowCount();
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
            
        }
}
