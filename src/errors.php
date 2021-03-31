<?php

//Reserved errors
const ERR_PARSE = ["code" => "-32700", "message" => "Ошибка анализа запроса"];
const ERR_INVALID_REQUEST = ["code" => "-32600", "message" => "Некорректный запрос"];
const ERR_METHOD_NOT_FOUND = ["code" => "-32601", "message" => "Метод не найден"];
const ERR_INVALID_PARAMS = ["code" => "-32602", "message" => "Неверные параметры"];
const ERR_INTERNAL = ["code" => "-32603", "message" => "Внутрення ошибка"];

//Encode and decode errors
const ERR_DECODE = ["code" => "-32048", "message" => "JSON decode error"];
const ERR_ENCODE = ["code" => "-32049", "message" => "JSON encode error"];

//Login and registration errors
const ERR_DUPLICATE_USER = ["code" => "-32010", "message" => "Такой пользователь уже существует"];
const ERR_REGISTRATION = ["code" => "-32011", "message" => "Ошибка регистрации"];
const ERR_USER_DATA = ["code" => "-32012", "message" => "Неверные email или пароль"];
const ERR_INVALID_EMAIL = ["code" => "-32013", "message" => "Некорректный email"];
const ERR_EMPTY_FIELDS = ["code" => "-32014", "message" => "Заполните пустые поля"];

//DB errors
const ERR_DATA_NOT_FOUND = ["code" => "-32030", "message" => "Данные не найдены"];
const ERR_CANT_ADD_USER = ["code" => "-32031", "message" => "Пользователь не был добавлен"];
const ERR_QUERY_USERS = ["code" => "-32034", "message" => "Ошибка запроса в таблице users"];

//Other
const ERR_EMPTY_METHOD = ["code" => "-32015", "message" => "Пустой метод"];