<?php

namespace ChessServer\Game;

use Chess\Grandmaster;
use Chess\UciEngine\UciEngine;
use Chess\UciEngine\Details\Limit;
use Chess\Variant\Capablanca\Board as CapablancaBoard;
use Chess\Variant\CapablancaFischer\Board as CapablancaFischerBoard;
use Chess\Variant\CapablancaFischer\StartPosition as CapablancaFischerStartPosition;
use Chess\Variant\Chess960\Board as Chess960Board;
use Chess\Variant\Chess960\StartPosition as Chess960StartPosition;
use Chess\Variant\Classical\Board as ClassicalBoard;

/**
 * Game
 *
 * @author Jordi Bassagañas
 * @license GPL
 */
class Game
{
    const VARIANT_960 = Chess960Board::VARIANT;
    const VARIANT_CAPABLANCA = CapablancaBoard::VARIANT;
    const VARIANT_CAPABLANCA_FISCHER = CapablancaFischerBoard::VARIANT;
    const VARIANT_CLASSICAL = ClassicalBoard::VARIANT;

    const MODE_FEN = 'fen';
    const MODE_PLAY = 'play';
    const MODE_SAN = 'san';
    const MODE_STOCKFISH = 'stockfish';

    /**
     * Chess board.
     *
     * @var \Chess\Variant\Classical\Board
     */
    private ClassicalBoard $board;

    /**
     * Variant.
     *
     * @var string
     */
    private string $variant;

    /**
     * Mode.
     *
     * @var string
     */
    private string $mode;

    /**
     * Grandmaster.
     *
     * @var Grandmaster
     */
    private null|Grandmaster $gm;

    public function __construct(
        string $variant,
        string $mode,
        null|Grandmaster $gm = null
    ) {
        $this->variant = $variant;
        $this->mode = $mode;
        $this->gm = $gm;

        if ($this->variant === self::VARIANT_960) {
            $startPos = (new Chess960StartPosition())->create();
            $this->board = new Chess960Board($startPos);
        } elseif ($this->variant === self::VARIANT_CAPABLANCA) {
            $this->board = new CapablancaBoard();
        } elseif ($this->variant === self::VARIANT_CAPABLANCA_FISCHER) {
            $startPos = (new CapablancaFischerStartPosition())->create();
            $this->board = new CapablancaFischerBoard($startPos);
        } elseif ($this->variant === self::VARIANT_CLASSICAL) {
            $this->board = new ClassicalBoard();
        }
    }

    /**
     * Returns the Chess\Board object.
     *
     * @return \Chess\Variant\Classical\Board
     */
    public function getBoard(): ClassicalBoard
    {
        return $this->board;
    }

    /**
     * Returns the game variant.
     *
     * @return string
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * Returns the game mode.
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Sets the Chess\Board object.
     *
     * @param \Chess\Variant\Classical\Board $board
     * @return \ChessServer\Game
     */
    public function setBoard(ClassicalBoard $board): Game
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Returns the state of the board.
     *
     * @return object
     */
    public function state(): object
    {
        $history = $this->board->getHistory();
        $end = end($history);

        return (object) [
            'turn' => $this->board->getTurn(),
            'pgn' => $end ? $end->move->pgn : null,
            'castlingAbility' => $this->board->getCastlingAbility(),
            'movetext' => $this->board->getMovetext(),
            'fen' => $this->board->toFen(),
            'isCapture' => $end ? $end->move->isCapture : false,
            'isCheck' => $this->board->isCheck(),
            'isMate' => $this->board->isMate(),
            'isStalemate' => $this->board->isStalemate(),
            'isFivefoldRepetition' => $this->board->isFivefoldRepetition(),
            'mode' => $this->getMode(),
        ];
    }

    /**
     * Returns a computer generated response to the current position.
     *
     * @param array $options
     * @param array $params
     * @return object|null
     */
    public function ai(array $options = [], array $params = []): ?object
    {
        if ($this->gm) {
            if ($move = $this->gm->move($this->board)) {
                return $move;
            }
        }

        $limit = (new Limit())->setDepth($params['depth']);
        $stockfish = (new UciEngine('/usr/games/stockfish'))->setOption('Skill Level', $options['Skill Level']);
        $analysis = $stockfish->analysis($this->board, $limit);

        $clone = unserialize(serialize($this->board));
        $clone->playLan($this->board->getTurn(), $analysis['bestmove']);
        $history = $clone->getHistory();
        $end = end($history);

        return (object) [
            'move' => $end->move->pgn,
        ];
    }

    /**
     * Makes a move.
     *
     * @param string $color
     * @param string $pgn
     * @return bool true if the move can be made; otherwise false
     */
    public function play(string $color, string $pgn): bool
    {
        return $this->board->play($color, $pgn);
    }

    /**
     * Makes a move in long algebraic notation.
     *
     * @param string $color
     * @param string $lan
     * @return bool true if the move can be made; otherwise false
     */
    public function playLan(string $color, string $lan): bool
    {
        return $this->board->playLan($color, $lan);
    }
}
