<?php

namespace ChessServer\Command\Game\Mode;

use Chess\Variant\Classical\PGN\AN\Color;
use ChessServer\Db;
use ChessServer\Command\Game\Game;
use ChessServer\Command\Game\PlayLanCommand;
use ChessServer\Repository\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PlayMode extends AbstractMode
{
    const NAME = Game::MODE_PLAY;

    const STATUS_PENDING = 'pending';

    const STATUS_ACCEPTED = 'accepted';

    const SUBMODE_FRIEND = 'friend';

    const SUBMODE_ONLINE = 'online';

    protected string $jwt;

    protected Db $db;

    protected string $status;

    protected int $startedAt;

    protected int $updatedAt;

    protected array $timer;

    public function __construct(Game $game, array $resourceIds, string $jwt, Db $db)
    {
        parent::__construct($game, $resourceIds);

        $this->jwt = $jwt;
        $this->db = $db;
        $this->hash = hash('adler32', $jwt);
        $this->status = self::STATUS_PENDING;
    }

    public function getJwt()
    {
        return $this->jwt;
    }

    public function getJwtDecoded()
    {
        return JWT::decode($this->jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartedAt(): int
    {
        return $this->startedAt;
    }

    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }

    public function getTimer(): array
    {
        return $this->timer;
    }

    public function setJwt(array $payload)
    {
        $this->jwt = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
        $this->hash = hash('adler32', $this->jwt);

        return $this;
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

    public function setUpdatedAt(int $timestamp)
    {
        $this->updatedAt = $timestamp;

        return $this;
    }

    public function setTimer(array $timer)
    {
        $this->timer = $timer;

        return $this;
    }

    protected function updateTimer(string $color)
    {
        $now = time();
        $diff = $now - $this->updatedAt;
        if ($this->game->getBoard()->turn === Color::B) {
            $this->timer[Color::W] -= $diff;
            $this->timer[Color::W] += $this->getJwtDecoded()->increment;
        } else {
            $this->timer[Color::B] -= $diff;
            $this->timer[Color::B] += $this->getJwtDecoded()->increment;
        }

        $this->updatedAt = $now;
    }

    public function res($params, $cmd)
    {
        switch (get_class($cmd)) {
            case PlayLanCommand::class:
                $isValid = $this->game->playLan($params['color'], $params['lan']);
                if ($isValid) {
                    if (isset($this->game->state()->end)) {
                        (new User($this->db))->updateElo(
                            $this->game->state()->end['result'],
                            $this->getJwtDecoded()
                        );
                    } else {
                        $this->updateTimer($params['color']);
                    }
                }
                return [
                    $cmd->name => [
                      ...(array) $this->game->state(),
                      'variant' =>  $this->game->getVariant(),
                      'timer' => $this->timer,
                      'isValid' => $isValid,
                    ],
                ];

            default:
                return parent::res($params, $cmd);
        }
    }
}
