<?php
require __DIR__.'/../src/Telegram.php';
require __DIR__.'/../src/AlertStore.php';

$TELEGRAM_BOT_TOKEN = 'YOUR_TELEGRAM_BOT_TOKEN'; // <-- replace with your token before use
$ALLOWED_CHATS      = '';
$DB_PATH            = __DIR__.'/../data/alerts.sqlite';
$LOCK_FILE          = __DIR__.'/cron-tx.lock';
$LOCK_TIMEOUT       = 300; // 5 minutes timeout for stale lock files

// Prevent concurrent runs
if(file_exists($LOCK_FILE)){
    $lockTime = (int)file_get_contents($LOCK_FILE);
    if(time() - $lockTime < $LOCK_TIMEOUT){
        exit; // script already running
    } else {
        unlink($LOCK_FILE); // remove stale lock
    }
}
file_put_contents($LOCK_FILE, time());

try {
    $telegram = new Telegram($TELEGRAM_BOT_TOKEN, $ALLOWED_CHATS);
    $alerts   = new AlertStore($DB_PATH);

    foreach($alerts->allWallets() as $w){
        $addr = $w['address'];
        $url  = "https://atlantic-2-gateway.seitrace.com/api/v1/addresses/$addr/transactions";

        $json = @file_get_contents($url);
        if(!$json) continue;

        $data = json_decode($json, true);
        if(empty($data['items'])) continue;

        $txs = $data['items'];

        // First time watching
        if($w['last_tx_id'] === null){
            $alerts->updateLastTx($w['id'], $txs[0]['hash']);
            continue;
        }

        // Find new transactions
        $newTxs = [];
        foreach($txs as $tx){
            if($tx['hash'] === $w['last_tx_id']) break;
            $newTxs[] = $tx;
        }
        if(!$newTxs) continue;

        // Send alerts
        foreach(array_reverse($newTxs) as $tx){
            if($tx['hash'] === $w['last_tx_id']) continue;

            $rawValue = isset($tx['value']) ? $tx['value'] : null;

            if(function_exists('bcdiv') && is_numeric($rawValue)){
                $value = bcdiv($rawValue, '1000000000000000000', 6);
            } else {
                $value = (is_numeric($rawValue)) ? number_format($rawValue / 1000000000000000000, 6) : 'N/A';
            }

            $hash  = $tx['hash'];
            $date  = isset($tx['timestamp']) ? date('Y-m-d H:i:s', strtotime($tx['timestamp'])) : date('Y-m-d H:i:s');
            $link  = "https://seitrace.com/tx/$hash?chain=atlantic-2";

            $msg = "💸 <b>New Transaction Detected!</b>\n".
                   "Wallet: <code>$addr</code>\n".
                   "Value: $value SEI\n".
                   "Tx: <a href=\"$link\">$hash</a>\n".
                   "Date: $date";

            try {
                $telegram->sendMessage($w['chat_id'], $msg);
            } catch (Exception $e) {
                continue;
            }
        }

        // Update last_tx_id
        $alerts->updateLastTx($w['id'], $txs[0]['hash']);
    }
} catch (Exception $e) {
    // fail silently in production version
} finally {
    if(file_exists($LOCK_FILE)){
        unlink($LOCK_FILE);
    }
}
