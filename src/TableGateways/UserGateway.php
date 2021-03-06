<?php
namespace Src\TableGateways;


class UserGateway{
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function Login($username, $password)
    {
        $statement = <<<EOS
        SELECT * FROM  user WHERE name = '$username';
    EOS;
        $statement = $this->db->prepare($statement);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($statement->rowCount()) {
            if (password_verify($password, $result["password"])) {
                return array("status" => 1, "err" => 0);
            }
            else{
                return array("status" => 0, "err" => 1);
            }
        }
        
        else{
            return array("status" => 0, "err" => 1);
        }
    }
}