<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../src/Methods.php';
require_once '../../src/Check/DataCheck.php';
require_once '../../src/RPC/JSON_RPC.php';
require_once '../../src/errors.php';

require_once '../../vendor/autoload.php';

$methods = new Methods();
$check = new DataCheck();
$rpc = new JSON_RPC();

mb_internal_encoding("UTF-8");

function error($error, $method = "empty") {
    global $rpc;
    return $rpc->makeErrorResponse("index.php", $error, $method);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $raw_data = json_decode(file_get_contents("php://input"), false, 512, JSON_THROW_ON_ERROR);
    } catch(JsonException $e) {
        error(ERR_DECODE);
    }

    if (!empty($raw_data)) {
        $params = $raw_data->params;

        if ($rpc->checkRequestFormat($raw_data)) {
            $method = $raw_data->method;
            if ($method == "register") {
                if ($check->checkDataRegisterUser($params)) {
                    echo $methods->register($params);
                } else {
                    echo error(ERR_INVALID_PARAMS, $method);
                }
            } elseif ($method == "login") {
                if ($check->checkDataLoginUser($params)) {
                    echo $methods->login($params);
                } else {
                    echo error(ERR_INVALID_PARAMS, $method);
                }
            } elseif ($method == "getScore") {
                if ($check->checkDataGetScoreUser($params)) {
                    echo $methods->getScore($params);
                } else {
                    echo error(ERR_INVALID_PARAMS, $method);
                }
            } elseif ($method == "getTasks") {
                if ($check->checkDataGetTasksUser($params)) {
                    echo $methods->getTasks($params);
                } else {
                    echo error(ERR_INVALID_PARAMS, $method);
                }
            } elseif ($method == "getCurators") {
                if ($check->checkDataGetCuratorsUser($params)) {
                    echo $methods->getCurators($params);
                } else {
                    echo error(ERR_INVALID_PARAMS, $method);
                }
            } else {
                echo error(ERR_METHOD_NOT_FOUND, $method);
            }
        } else {
            echo error(ERR_INVALID_REQUEST);
        }
    } else {
        echo error(ERR_PARSE);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo 'User module ready';
}


