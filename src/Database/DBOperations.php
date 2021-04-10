<?php

use MongoDB\BSON\ObjectId;

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

    public function getScore($params) {
        $queryParams = [];

        if (!empty($params->lastname)) {
            $queryParams += [
                'lastname' => $params->lastname
            ];
        }
        if (!empty($params->name)) {
            $queryParams += [
                'name' => $params->name
            ];
        }

        $options = [
            'projection' => [
                'name' => 1,
                'lastname' => 1,
                'score' => 1,
            ]
        ];

        $cursor = $this->conn->selectCollection(SPECIALISTS)->find($queryParams, $options);

        $results = $cursor->toArray();

        if (!empty($results)) {
            return $results;
        }

        return false;
    }

    public function getTasks($params) {
        $queryParams = [
            'login' => $params->login
        ];

        $options = [
            'projection' => [
                'tasks' => 1
            ]
        ];

        $array = $this->conn->selectCollection(SPECIALISTS)->findOne($queryParams, $options);

        $results = clone $array;

        $results["curators"] = [];

        $curators = [];

        $key = "done";
        while (true) {
            $results["tasks"][$key] = [];
            foreach ($array->tasks[$key] as $taskDone) {
                $res_curator = null;
                if (array_key_exists((string)$taskDone->from['$id']['_id'], $curators)) {
                    $res_curator = $curators[(string)$taskDone->from['$id']['_id']];
                } else {
                    $res_curator = $this->conn->selectCollection(CURATORS)->findOne(
                        [
                            '_id' => new ObjectId($taskDone->from['$id']['_id'])
                        ],
                        [
                            'projection' => [
                                'lastname'=>1,
                                'name'=>1,
                            ]
                        ]
                    );
                    if (empty($res_curator)) {
                        continue;
                    }
                    $curators += [
                        (string) $taskDone->from['$id']['_id'] => [
                            'name' => $res_curator->name,
                            'lastname' => $res_curator->lastname,
                        ]
                    ];

                }

                $results["tasks"][$key][] = [
                    'name' => $taskDone->task['$id']->name,
                    'desc' => $taskDone->task['$id']->desc,
                    'from' => [
                        'name' => $res_curator['name'],
                        'lastname' => $res_curator['lastname'],
                    ],
                    'start_date' => (string) $taskDone->start_date,
                    'pass_date' => (string) $taskDone->pass_date,
                ];
            }
            if ($key === "done") {
                $key = "coming";
            } else {
                break;
            }
        }

        if (!empty($results)) {
            return $results;
        }

        return false;
    }

    public function getCurators($params) {
        $queryParams = [
            'login' => $params->login
        ];

        $options = [
            'projection' => [
                'curators' => 1,
            ]
        ];

        $array = $this->conn->selectCollection(SPECIALISTS)->findOne($queryParams, $options);

        $results = clone $array;

        $results["curators"] = [];

        foreach ($array->curators as $curator) {
            $res = $this->conn->selectCollection(CURATORS)->findOne(
                [
                    '_id' => $curator->id['$id']
                ],
                [
                    'projection' => [
                        'lastname'=>1,
                        'name'=>1,
                    ]
                ]
            );
            if (empty($res)) {
                continue;
            }
            $results["curators"][] = [
                'name' => $res->name,
                'lastname' => $res->lastname,
            ];
        }

        if (!empty($results)) {
            return $results;
        }

        return false;
    }

}
