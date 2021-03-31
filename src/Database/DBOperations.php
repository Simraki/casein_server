<?php

require_once '../../src/ConfigLoader.php';
require_once '../../src/Log/LogWriter.php';
require_once '../../src/errors.php';
require_once '../../src/tables.php';


class DBOperations {

    private $conn;
    private $log;
    private $config;

    public function __construct() {
        $this->config = new ConfigLoader;
        $host = $this->config->get('db.host');
        $client = new MongoDB\Client(
            "mongodb://${host}",
            [
                //                'username' => $this->config->get('db.user'),
                //                'password' => $this->config->get('db.pass')
            ]
        );

        $this->conn = $client->selectDatabase($this->config->get('db.name'));

        $this->log = new LogWriter();
    }

    private function error($error): void {
        $this->log->logEntry(__CLASS__, debug_backtrace()[1]['function'], $error);
    }

    public function getHash($password) {
        return password_hash($password."621317", PASSWORD_BCRYPT, ['cost' => 14]);
    }

    public function verifyHash($password, $hash): bool {
        return password_verify($password, $hash);
    }

    public function register($params) {
        $lastname = $params->lastname;
        $name = $params->name;
        $login = $params->login;
        $password = $this->getHash($params->password);
        $score = $params->score;
        $curators = $params->curators;
        $tasks = $params->tasks;

        $cursor = $this->conn->selectCollection(SPECIALISTS)->find(
            [
                'login' => $login
            ]
        );

        if ($cursor->isDead()) {
            $result = $this->conn->selectCollection(SPECIALISTS)->insertOne(
                [
                    'lastname' => $lastname,
                    'name' => $name,
                    'login' => $login,
                    'password' => $password,
                    'score' => $score,
                    'curators' => $curators,
                    'tasks' => $tasks,
                ]
            );

            if (!$result->isAcknowledged()) {
                $this->error(ERR_CANT_ADD_USER);
            }
            return $result->isAcknowledged();
        }
        $this->error(ERR_DUPLICATE_USER);
        return false;
    }

    public function login($params) {
        $login = $params->login;
        $password = $params->password;

        $result = $this->conn->selectCollection(SPECIALISTS)->findOne(
            [
                'login' => $login
            ]
        );

        if (empty($result)) {
            $this->error(ERR_QUERY_USERS);
            return false;
        }

        if ($this->verifyHash($password."621317", $result['password'])) {
            return $result;
        }

        return false;
    }

}
