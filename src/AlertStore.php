<?php

class AlertStore {

    private $pdo;

    public function __construct($dbPath) {
        if(!file_exists($dbPath)) { touch($dbPath); }
        $this->pdo = new PDO('sqlite:'.$dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        // Only wallets table now
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS wallets (
            id INTEGER PRIMARY KEY,
            chat_id TEXT,
            address TEXT,
            last_tx_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    // Add wallet for a user
    public function addWallet($chatId, $address){
        $st = $this->pdo->prepare('INSERT INTO wallets(chat_id,address,last_tx_id) VALUES(?,?,NULL)');
        $st->execute([$chatId, strtolower($address)]);
        return $this->pdo->lastInsertId();
    }

    // List wallets for a specific chat/user
    public function listWallets($chatId){
        $st = $this->pdo->prepare('SELECT * FROM wallets WHERE chat_id=?');
        $st->execute([$chatId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // List all wallets (for cron job)
    public function allWallets(){
        return $this->pdo->query('SELECT * FROM wallets')->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update last seen transaction hash for a wallet
    public function updateLastTx($id,$txId){
        $st=$this->pdo->prepare('UPDATE wallets SET last_tx_id=? WHERE id=?');
        $st->execute([$txId,$id]);
    }
}
