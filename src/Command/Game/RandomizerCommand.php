<?php

namespace ChessServer\Command\Game;

use Chess\Randomizer\Randomizer;
use Chess\Randomizer\Checkmate\TwoBishopsRandomizer;
use Chess\Randomizer\Endgame\PawnEndgameRandomizer;
use Chess\Variant\Classical\PGN\AN\Color;
use ChessServer\Command\AbstractCommand;
use ChessServer\Socket\AbstractChesslaBlabSocket;

class RandomizerCommand extends AbstractCommand
{
    const TYPE_P    = 'P';

    const TYPE_Q    = 'Q';

    const TYPE_R    = 'R';

    const TYPE_BB   = 'BB';

    const TYPE_BN   = 'BN';

    const TYPE_QR   = 'QR';

    public function __construct()
    {
        $this->name = '/randomizer';
        $this->description = 'Starts a random position.';
        $this->params = [
            // mandatory param
            'turn' => '<string>',
            // mandatory param
            'items' => '<string>',
        ];
    }

    public function cases()
    {
        return [
            self::TYPE_P,
            self::TYPE_Q,
            self::TYPE_R,
            self::TYPE_BB,
            self::TYPE_BN,
            self::TYPE_QR,
        ];
    }

    public function validate(array $argv)
    {
        isset($argv[1]) ? $turn = $argv[1] : $turn = null;
        isset($argv[2]) ? $items = json_decode(stripslashes($argv[2]), true) : $items = null;

        if ($turn !== Color::W && $turn !== Color::B) {
            return false;
        }

        if ($items) {
            $color = array_key_first($items);
            if ($color !== Color::W && $color !== Color::B) {
                return false;
            }
            $pieceIds = current($items);
            if (!in_array($pieceIds, self::cases())) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public function run(AbstractChesslaBlabSocket $socket, array $argv, int $id)
    {
        try {
            $items = json_decode(stripslashes($argv[2]), true);
            if (count($items) === 1) {
                $color = array_key_first($items);
                $pieceIds = str_split(current($items));
                if ($pieceIds === ['B', 'B']) {
                    $board = (new TwoBishopsRandomizer($argv[1]))->board;
                } elseif ($pieceIds === ['P']) {
                    $board = (new PawnEndgameRandomizer($argv[1]))->board;
                } else {
                    $board = (new Randomizer($argv[1], [$color => $pieceIds]))->board;
                }
            } else {
                $wIds = str_split($items[Color::W]);
                $bIds = str_split($items[Color::B]);
                $board = (new Randomizer($argv[1], [
                    Color::W => $wIds,
                    Color::B => $bIds,
                ]))->board;
            }
            return $socket->getClientStorage()->sendToOne($id, [
                $this->name => [
                    'turn' => $board->turn,
                    'fen' => $board->toFen(),
                ],
            ]);
        } catch (\Throwable $e) {
            return $socket->getClientStorage()->sendToOne($id, [
                $this->name => [
                    'message' => 'A random puzzle could not be loaded.',
                ],
            ]);
        }
    }
}
