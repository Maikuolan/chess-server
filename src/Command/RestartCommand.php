<?php

namespace ChessServer\Command;

use Chess\Game;
use Chess\Variant\Chess960\FEN\StrToBoard as Chess960FenStrToBoard;
use ChessServer\Socket;
use ChessServer\GameMode\PlayMode;
use Firebase\JWT\JWT;
use Ratchet\ConnectionInterface;

class RestartCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->name = '/restart';
        $this->description = 'Restarts a game.';
        $this->params = [
            'hash' => '<string>',
        ];
    }

    public function validate(array $argv)
    {
        return count($argv) - 1 === count($this->params);
    }

    public function run(Socket $socket, array $argv, ConnectionInterface $from)
    {
        if ($gameMode = $socket->getGameModeStorage()->getByHash($argv[1])) {
            $jwt = $gameMode->getJwt();
            $decoded = JWT::decode($jwt, $_ENV['JWT_SECRET'], array('HS256'));
            $decoded->iat = time();
            $decoded->exp = time() + 3600; // one hour by default
            if ($decoded->variant === Game::VARIANT_960) {
                $startPos = str_split($decoded->startPos);
                $board = (new Chess960FenStrToBoard($decoded->fen, $startPos))->create();
                $game = (new Game($decoded->variant, Game::MODE_PLAY))->setBoard($board);
            } else if ($decoded->variant === Game::VARIANT_CAPABLANCA_80) {
                $game = new Game($decoded->variant, Game::MODE_PLAY);
            } else {
                $game = new Game($decoded->variant, Game::MODE_PLAY);
            }
            $newJwt = JWT::encode($decoded, $_ENV['JWT_SECRET']);
            $newGameMode = new PlayMode(
                $game,
                $gameMode->getResourceIds(),
                $newJwt
            );
            $newGameMode->setStatus(PlayMode::STATUS_ACCEPTED);
            $socket->getGameModeStorage()->set($newGameMode);

            return $socket->sendToMany($newGameMode->getResourceIds(), [
                $this->name => [
                    'jwt' => $newJwt,
                    'hash' => md5($newJwt),
                ],
            ]);
        }
    }
}
