<?php

namespace ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

abstract class CompletionAbstract implements ApiActionInterface
{
    const MAX_LIMIT = 50;

    /** @var ConnectionInterface */
    protected $db;
    protected $limit;
    protected $textForCompletion;

    public function __construct(ConnectionInterface $db, $textForCompletion, $limit)
    {
        $this->db      = $db;
        $this->limit   = $limit ?: static::MAX_LIMIT;

        $this->textForCompletion = $textForCompletion;
    }
}
