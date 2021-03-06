<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Src\TableGateways;

/**
 * Description of MusicGateway
 *
 * @author ObaJohn
 */

use Src\Logic\MakeFile;
use Src\TableGateways\SongGateway;
use Src\TableGateways\CommentsGateway;
use Src\TableGateways\ImageGateway;
class MusicGateway extends SongGateway {
    private $db = null;
    private $imageInherited = null;
    private $comment = null;
    const LIMIT_PER_PAGE = 10;
    public function __construct($db)
        {       
                parent::__construct($db);
                $this->db = $db;
                $this->imageInherited = new ImageGateway($db);
                $this->comment = new CommentsGateway($db);
        }
        
        public function insert(Array $input)
        {
                $statement = "INSERT INTO music
                                (music_name, music_details, artist, music_key, short_url, popular, uploaded_by)
                        VALUES
                                (:music_name, :music_details, :artist, :music_key, :short_url, :popular, :uploaded_by)";
                try {
                        $_key = md5($input['music_name'].mt_rand());
                        if($this->imageInherited->createImage($input['images'], $_key) == null) {
                                throw new \PDOException("Error Processing Request", 1);
                        }
                        $query = $this->db->prepare($statement);
                        $query->execute(array(
                                'music_name' => $input['music_name'],
                                'music_details' => $input['music_details'],
                                'artist' => $input['artist'],
                                'music_key' => $_key,
                                'uploaded_by' => $input['author'],
                                'popular' => $input['popular'],
                                'short_url' => MakeFile::normalizeString($input['music_name']."-").mt_rand()

                        ));
                        $this->createSong($input['song'], $input['music_name']."-".$input['artist'], $_key);
                        return $query->rowCount();
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        /**
         * get all records
         */
        public function getAll($lim = null)
        {
                $statement = ($lim == null) ? "
                        SELECT
                                *
                        FROM
                                music
                        ORDER 
                            BY id DESC;
                " : "
                        SELECT
                                *
                        FROM
                                music
                        ORDER 
                            BY id DESC LIMIT $lim
                        ;
                ";
                try {   
                        $result = array();
                        $statement = $this->db->query($statement);
                        while ($res = $statement->fetch(\PDO::FETCH_ASSOC)) {
                                $comm = $this->comment->findAllWithKey($res["music_key"]);
                                $songs = $this->getAllWithKey($res["music_key"]);
                                $images = $this->imageInherited->getPostImages($res["music_key"]);
                                $res += ["audio" => $songs]; //pnly one file is needed. just incase
                                $res += ["images" => $images];
                                $res += ["comments" => $comm];
                                $result[] = $res;
                        }
                        return $result;
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        /**
         * get song by pages
         * 
         */
        public function getPages($pageNo)
        {
                $limit = self::LIMIT_PER_PAGE;
                $startFrom = ($pageNo - 1) * $limit;
                $totalRecord = self::getTotalRecord($this->db);
                $totalPages = \ceil($totalRecord / $limit);
                $statement = "
                        SELECT
                                *
                        FROM
                                music
                        ORDER BY music_name 
                            DESC LIMIT $startFrom, $limit;";
                try {   
                        $data = array();
                        $statement = $this->db->query($statement);
                        while ($res = $statement->fetch(\PDO::FETCH_ASSOC)) {
                                $comm = $this->comment->findAllWithKey($res["music_key"]);
                                $songs = $this->getAllWithKey($res["music_key"]);
                                $images = $this->imageInherited->getPostImages($res["music_key"]);
                                $res += ["audio" => $songs]; //pnly one file is needed. just incase
                                $res += ["images" => $images];
                                $res += ["comments" => $comm];
                                $data[] = $res;
                        }
                        $result = ["data" => $data];
                        $result += ["links" => [
                                "first" => "pages/1",
                                "last" => "pages/$totalPages",
                                "prev" =>(($pageNo - 1) > 0) ? "pages/".($pageNo - 1) : null,
                                "next" => ($pageNo == $totalPages) ? null : "pages/".($pageNo + 1)
                        ]];
                        $result += ["meta" => [
                                "current_page" => (int) $pageNo,
                                "total_pages" => $totalPages
                        ]];
                        return $result;
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }

        /**
         * get popular songs
         */
        public function getPopular($popularInt)
        {
                $statement = "
                        SELECT
                                *
                        FROM
                                music
                        WHERE popular > 0
                        ORDER 
                            BY id DESC LIMIT $popularInt;
                ";
                try {   
                        $result = array();
                        $statement = $this->db->query($statement);
                        while ($res = $statement->fetch(\PDO::FETCH_ASSOC)) {
                                $comm = $this->comment->findAllWithKey($res["music_key"]);
                                $songs = $this->getAllWithKey($res["music_key"]);
                                $images = $this->imageInherited->getPostImages($res["music_key"]);
                                $res += ["audio" => $songs]; //pnly one file is needed. just incase
                                $res += ["images" => $images];
                                $res += ["comments" => $comm];
                                $result[] = $res;
                        }
                        return $result;
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        /**
         * get Song by short url
         */
        public function getByUrl($short_url)
        {
                $statement = "
                        SELECT
                                *
                        FROM
                                music
                        WHERE short_url = ?;
                ";
                
                try {   
                        $result = null;
                        $statement = $this->db->prepare($statement);
                        $statement->execute(array($short_url));
                        $res = $statement->fetch(\PDO::FETCH_ASSOC);
                        $comm = $this->comment->findAllWithKey($res["music_key"]);
                        $songs = $this->getAllWithKey($res["music_key"]);
                        $images = $this->imageInherited->getPostImages($res["music_key"]);
                        $res += ["audio" => $songs]; //pnly one file is needed. just incase
                        $res += ["images" => $images];
                        $res += ["comments" => $comm];
                        $result = $res;
                        return $result;
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        
        public function findByUrl($short_url)
        {
                $statement = "
                        SELECT
                                *
                        FROM
                                music
                        WHERE short_url = ?;
                ";
                
                try {   
                        
                        $statement = $this->db->prepare($statement);
                        $statement->execute(array($short_url));
                        $res = $statement->fetch(\PDO::FETCH_ASSOC);
                        
                        return $res;
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        public function find($id)
        {
                
                $statement = "
                        SELECT
                                *
                        FROM
                                music
                        WHERE id = ?;
                ";
                try {   
                        $result = null;
                        $statement = $this->db->prepare($statement);
                        $statement->execute(array($id));
                        $res = $statement->fetch(\PDO::FETCH_ASSOC);
                        $comm = $this->comment->findAllWithKey($res["music_key"]);
                        $songs = $this->getAllWithKey($res["music_key"]);
                        $images = $this->imageInherited->getPostImages($res["music_key"]);
                        $res += ["audio" => $songs]; //pnly one file is needed. just incase
                        $res += ["images" => $images];
                        $res += ["comments" => $comm];
                        $result = $res;
                        return $result;
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        public function update($id, Array $input)
        {       $statement = "
                        UPDATE `music` 
                        SET 
                                `music_name` = :music_name, 
                                `music_details` = :music_details,
                                `artist` = :artist,
                                `popular` = :popular,
                                `updated_at` = CURRENT_TIMESTAMP
                        WHERE `music`.`id` = :id;
                ";
                
                try {
                        $statement = $this->db->prepare($statement);
                        $statement->execute(array(
                                'id' => (int) $id,
                                'music_name' => $input['music_name'],
                                'music_details' => $input['music_details'],
                                'artist' => $input['artist'],
                                'popular' => $input['popular'],
                        ));
                        return $statement->rowCount();
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
        /**
         * return total records as integer
         * @return int
         */
        public static function getTotalRecord($db, $param = [])
        {
                $statement = "SELECT music.id FROM music";
                foreach ($param as $key=> $clause) {
                        if ($key == 'popular') {
                                $statement .= " WHERE popular > 0";
                        }
                        // if ($key == "created_at") {
                        //         if (substr_count($statement, "WHERE") > 0) {
                        //                 $statement .= " AND created_at >"
                        //         }
                        // }

                }
                
                try {
                        $statement = $db->query($statement);
                        $result = $statement->fetchAll(\PDO::FETCH_COLUMN);
                        return $result = count($result);
                } catch (\PDOException $th) {
                        exit($th->getMessage());
                }
        }
        /**
         * Deletes a record from db. unlink all raw files
         * @param int $id
         * @return int
         */
        public function delete($id)
        {
                $res = $this->find($id);
                $key = $res["music_key"];
                $statement = <<<EOS
                        DELETE FROM `music` WHERE `music`.`id` = $id;
                        DELETE FROM `images` WHERE `images`.`image_key` = $key;
                        DELETE FROM `songs` WHERE `songs`.`song_key` = $key;
                        DELETE FROM `comment` WHERE `comment`.`comment_key` = $key;
                EOS;

                try {
                        $statement=$this->db->prepare($statement);
                        if($statement->execute()){
                                foreach ($res["images"] as $images) {
                                        unlink("../$images");
                                }
                                foreach ($res["audio"] as $audio) {
                                        unlink("../$audio[song_url]");
                                }
                        }
                        return $statement->rowCount();
                } catch (\PDOException $e) {
                        exit($e->getMessage());
                }
        }
}
