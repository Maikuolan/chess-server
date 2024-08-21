<?php

namespace ChessServer\Socket\Ratchet;

use ChessServer\Socket\TextClientStorageInterface;
use Monolog\Logger;

class TextClientStorage extends \SplObjectStorage implements TextClientStorageInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function detachById(int $id): void
    {
        $this->rewind();
        while ($this->valid()) {
            if ($id === $this->current()->resourceId) {
                $this->detach($this->current());
            }
            $this->next();
        }
    }

    public function sendToOne(int $id, array $res): void
    {
        $this->rewind();
        while ($this->valid()) {
            if ($id === $this->current()->resourceId) {
                $this->current()->send(json_encode($res));
                $this->logger->info('Sent message', [
                    'id' => $id,
                    'cmd' => array_keys($res),
                ]);
            }
            $this->next();
        }
    }

    public function sendToMany(array $ids, array $res): void
    {
        $this->rewind();
        while ($this->valid()) {
            if (in_array($this->current()->resourceId, $ids)) {
                $this->current()->send(json_encode($res));
                $this->logger->info('Sent message', [
                    'ids' => $ids,
                    'cmd' => array_keys($res),
                ]);
            }
            $this->next();
        }
    }

    public function sendToAll(array $res): void
    {
        $this->rewind();
        while ($this->valid()) {
            $this->current()->send(json_encode($res));
            $this->next();
        }
    }
}
