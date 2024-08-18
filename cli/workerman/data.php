<?php

namespace ChessServer\Cli\Workerman;

use ChessServer\Command\CommandParser;
use ChessServer\Command\Data\CommandContainer;
use ChessServer\Command\Data\Db;
use ChessServer\Socket\WorkermanClientStorage;
use ChessServer\Socket\WorkermanWebSocket;
use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Workerman\Timer;
use Workerman\Worker;

require __DIR__  . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

$db = new Db([
   'driver' => $_ENV['DB_DRIVER'],
   'host' => $_ENV['DB_HOST'],
   'database' => $_ENV['DB_DATABASE'],
   'username' => $_ENV['DB_USERNAME'],
   'password' => $_ENV['DB_PASSWORD'],
]);

$logger = new Logger('data');
$logger->pushHandler(new StreamHandler(__DIR__.'/../../storage' . '/data.log', Logger::INFO));

$commandContainer = new CommandContainer($db, $logger);
$parser = new CommandParser($commandContainer);

$clientStorage = new WorkermanClientStorage($logger);

$socketName = "websocket://{$_ENV['WSS_ADDRESS']}:{$_ENV['WSS_DATA_PORT']}";

$context = [
    'ssl' => [
        'local_cert'  => __DIR__  . '/../../ssl/fullchain.pem',
        'local_pk' => __DIR__  . '/../../ssl/privkey.pem',
        'verify_peer' => false,
    ],
];

$server = (new WorkermanWebSocket($socketName, $context, $parser))->init($clientStorage);

$worker = $server->getWorker();

$worker->onWorkerStart = function(Worker $worker) use (&$db, $logger, $server) {
    Timer::add(5, function() use (&$db, $logger, $server) {
        try {
            $db->getPdo()->getAttribute(\PDO::ATTR_SERVER_INFO);
        } catch(\PDOException $e) {
            try {
                $db = new Db([
                   'driver' => $_ENV['DB_DRIVER'],
                   'host' => $_ENV['DB_HOST'],
                   'database' => $_ENV['DB_DATABASE'],
                   'username' => $_ENV['DB_USERNAME'],
                   'password' => $_ENV['DB_PASSWORD'],
                ]);
                $parser = new CommandParser(new CommandContainer($db, $logger));
                $server->setParser($parser);
                $logger->info('Successfully reconnected to Chess Data');
            } catch(\PDOException $e) {
                // Trying to connect to Chess Data...
            }
        }
    });
};

$server->run();
