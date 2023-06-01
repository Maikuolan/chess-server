<?php

namespace ChessServer\GameMode;

use Chess\Game;
use ChessServer\Command\DrawCommand;
use ChessServer\Command\LeaveCommand;
use ChessServer\Command\PlayLanCommand;
use ChessServer\Command\RematchCommand;
use ChessServer\Command\ResignCommand;
use ChessServer\Command\TakebackCommand;

class PlayMode extends AbstractMode
{
    const NAME = Game::MODE_PLAY;

    const STATUS_PENDING = 'pending';

    const STATUS_ACCEPTED = 'accepted';

    const SUBMODE_FRIEND = 'friend';

    const SUBMODE_ONLINE = 'online';

    protected $jwt;

    protected string $status;

    protected int $startedAt;

    protected array $timer;

    public function __construct(Game $game, array $resourceIds, string $jwt)
    {
        parent::__construct($game, $resourceIds);

        $this->jwt = $jwt;
        $this->hash = md5($jwt);
        $this->status = self::STATUS_PENDING;
    }

    public function getJwt()
    {
        return $this->jwt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartedAt(): int
    {
        return $this->startedAt;
    }

    public function getTimer(): array
    {
        return $this->timer;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function setStartedAt(int $timestamp)
    {
        $this->startedAt = $timestamp;

        return $this;
    }

    public function setTimer(array $timer)
    {
        $this->timer = $timer;

        return $this;
    }

    public function res($argv, $cmd)
    {
        try {
            switch (get_class($cmd)) {
                case DrawCommand::class:
                    return [
                        $cmd->name => $argv[1],
                    ];
                case LeaveCommand::class:
                    return [
                        $cmd->name => $argv[1],
                    ];
                case RematchCommand::class:
                    return [
                        $cmd->name => $argv[1],
                    ];
                case ResignCommand::class:
                    return [
                        $cmd->name => $argv[1],
                    ];
                case TakebackCommand::class:
                    return [
                        $cmd->name => $argv[1],
                    ];
                case PlayLanCommand::class:
                    $turn = $this->game->state()->turn;
                    $isLegal = $this->game->playLan($argv[1], $argv[2]);
                    $state = $this->game->state();
                    return [
                        $cmd->name => [
                            'turn' => $turn,
                            'fen' => $state->fen,
                            'movetext' => $state->movetext,
                            'pgn' => $state->pgn,
                            'isLegal' => $isLegal,
                            'isCheck' => $state->isCheck,
                            'isMate' => $state->isMate,
                            'isMate' => $state->isMate,
                            'isStalemate' => $state->isStalemate,
                            'isFivefoldRepetition' => $state->isFivefoldRepetition,
                            'variant' =>  $this->game->getVariant(),
                            // play mode information
                            'timer' => $this->timer,
                        ],
                    ];
                default:
                    return parent::res($argv, $cmd);
            }
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
