<?php

declare(strict_types=1);

namespace Kratz\Database;

use PDO;

//we willen niet dat mensen dit kunnen uitbreiden dus we gebruiken een final class.
final class ConnectionFactory
{
    public static function create() : PDO //return type is PDO, netjes.
    {
        //hardcoded, normaal niet netjes maar dit is een demo niet op een productieserver.
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'kratz_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        // DSN: welke driver, server, database en charset. dan kunnen we ook emoji's (💜) gebruiken, eigenlijk best onnodig 
        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // fouten als exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // rijen als ['kolom' => waarde]
            PDO::ATTR_EMULATE_PREPARES => false,                 // echte prepared statements + echte types
        ]);
    }
}