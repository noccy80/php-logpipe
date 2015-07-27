<?php

namespace LogPipe\Plugin\BugBasket;

use NoccyLabs\LogPipe\Message\MessageInterface;

class BugStash
{
    
    protected $pdo;
    
    protected $queryInsert;
    
    protected $querySelectAll;
    
    public function __construct($stashFile)
    {
        $this->pdo = new \PDO("sqlite:{$stashFile}");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS stash (id INTEGER PRIMARY KEY, timestamp DATETIME, type TEXT, channel TEXT, level INTEGER, message BLOB)");
        
        $this->queryInsert = $this->pdo->prepare("INSERT INTO stash (timestamp, type, channel, level, message) VALUES (:timestamp, :type, :channel, :level, :message)");
        $this->querySelectAll = $this->pdo->prepare("SELECT * FROM stash");
        
        
    }
    
    public function addToStash(MessageInterface $message, $type)
    {
        $this->queryInsert->execute([
            "timestamp"     => $message->getTimestamp(),
            "type"          => $type,
            "channel"       => $message->getChannel(),
            "level"         => $message->getLevel(),
            "message"       => serialize($message)
        ]);
    }

    public function getAll()
    {
        if ($this->querySelectAll->execute()) {
            return $this->querySelectAll->fetchAll(\PDO::FETCH_ASSOC);
        }
        return [];
    }
    
}