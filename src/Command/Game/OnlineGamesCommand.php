<?php

namespace ChessServer\Command\Game;

use ChessServer\Command\AbstractCommand;
use ChessServer\Game\PlayMode;
use ChessServer\Socket\ChesslaBlabSocket;

class OnlineGamesCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->name = '/online_games';
        $this->description = "Returns the online games waiting to be accepted.";
    }

    public function validate(array $argv)
    {
        return count($argv) - 1 === 0;
    }

    public function run(ChesslaBlabSocket $socket, array $argv, int $id)
    {
        return $socket->getClientStorage()->sendToOne($id, [
            $this->name => $socket
                ->getGameModeStorage()
                ->decodeByPlayMode(PlayMode::STATUS_PENDING, PlayMode::SUBMODE_ONLINE),
        ]);
    }
}
