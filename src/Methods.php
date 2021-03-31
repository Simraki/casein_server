<?php

require_once '../../src/Database/DBOperations.php';
require_once '../../src/MBFunctions.php';
require_once '../../src/RPC/JSON_RPC.php';
require_once '../../src/errors.php';
require_once '../../src/Check/regex.php';


class Methods {

    private $db;
    private $mb;
    private $rpc;

    public function __construct() {
        $this->db = new DBOperations();
        $this->mb = new MBFunctions();
        $this->rpc = new JSON_RPC();
    }

    private function error($error) {
        return $this->rpc->makeErrorResponse(__CLASS__, $error, debug_backtrace()[1]['function']);
    }

    public function register($params) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->register($params));
    }

    public function login($params) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->login($params));
    }
}
