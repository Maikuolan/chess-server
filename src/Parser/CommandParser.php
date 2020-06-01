<?php

namespace PgnChessServer\Parser;

use PgnChessServer\Command\Captures;
use PgnChessServer\Command\Help;
use PgnChessServer\Command\History;
use PgnChessServer\Command\Metadata;
use PgnChessServer\Command\Piece;
use PgnChessServer\Command\Pieces;
use PgnChessServer\Command\Play;
use PgnChessServer\Command\Quit;
use PgnChessServer\Command\Start;
use PgnChessServer\Command\Status;

class CommandParser
{
    public static $argv;

    public static function filter($string)
    {
        return array_map('trim', explode(' ', $string));
    }

    public static function validate($string)
    {
        self::$argv = self::filter($string);

        switch (self::$argv[0]) {
            case Captures::$name:
                return count(self::$argv) -1 === 0;
            case Help::$name:
                return count(self::$argv) -1 === 0;
            case History::$name:
                return count(self::$argv) -1 === 0;
            case Metadata::$name:
                return count(self::$argv) -1 === 0;
            case Piece::$name:
                return count(self::$argv) -1 === count(Piece::$params);
            case Pieces::$name:
                return count(self::$argv) -1 === count(Pieces::$params) &&
                    in_array(self::$argv[1], Pieces::$params['color']);
            case Play::$name:
                return count(self::$argv) -1 === count(Play::$params) &&
                    in_array(self::$argv[1], Play::$params['color']);
            case Quit::$name:
                return count(self::$argv) -1 === 0;
            case Start::$name:
                return count(self::$argv) -1 === count(Start::$params) &&
                    in_array(self::$argv[1], Start::$params['mode']);
            case Status::$name:
                return count(self::$argv) -1 === 0;
            default:
                return false;
        }
    }

    public static function argv($string)
    {
        return self::filter($string);
    }
}