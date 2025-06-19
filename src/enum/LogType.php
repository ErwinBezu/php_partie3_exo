<?php

namespace src\enum;

enum LogType: string
{
    case DEBUG = 'DEBUG';
    case WARN = 'WARN';
    case ERR = 'ERR';
}