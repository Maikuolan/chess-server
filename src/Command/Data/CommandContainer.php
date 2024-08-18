<?php

namespace ChessServer\Command\Data;

use ChessServer\Command\AbstractCommandContainer;
use Monolog\Logger;

class CommandContainer extends AbstractCommandContainer
{
    private Db $db;

    public function __construct(Db $db, Logger $logger)
    {
        parent::__construct($logger);

        $this->db = $db;
        $this->commands->attach(new AnnotationsGameCommand($db));
        $this->commands->attach(new AutocompleteBlackCommand($db));
        $this->commands->attach(new AutocompleteEventCommand($db));
        $this->commands->attach(new AutocompleteWhiteCommand($db));
        $this->commands->attach(new SearchCommand($db));
        $this->commands->attach(new StatsEventCommand($db));
        $this->commands->attach(new StatsOpeningCommand($db));
        $this->commands->attach(new StatsPlayerCommand($db));
    }
}
