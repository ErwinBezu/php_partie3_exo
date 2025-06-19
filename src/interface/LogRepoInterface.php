<?php
namespace src\interface;

use src\model\Log;

interface LogRepoInterface
{
    public function insert(Log $log);
    public function findAll();
    public function clear();

}