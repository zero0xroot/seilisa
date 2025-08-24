<?php
// ----------------- REQUIRE CLASSES -----------------
require __DIR__.'/../src/Telegram.php';
require __DIR__.'/../src/SeiEvm.php';
require __DIR__.'/../src/PriceService.php';
require __DIR__.'/../src/AlertStore.php';
require __DIR__.'/../src/Seilisa.php';

// ----------------- CONFIG -----------------
$TELEGRAM_BOT_TOKEN = 'YOUR_TELEGRAM_BOT_TOKEN'; // <-- replace with your token before use
$ALLOWED_CHATS = ''; // empty = allow all users
$DB_PATH = __DIR__.'/../data/alerts.sqlite';
$SEI_RPC = 'https://evm-rpc-testnet.sei-apis.com';
$CHAIN_ID = 1328;

// ----------------- INIT -----------------
$telegram = new Telegram($TELEGRAM_BOT_TOKEN, $ALLOWED_CHATS);
$evm = new SeiEvm($SEI_RPC, $CHAIN_ID, null); // no private key
$prices = new PriceService();
$alerts = new AlertStore($DB_PATH);
$seilisa = new Seilisa();

// ----------------- READ TELEGRAM UPDATE -----------------
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(200);
    exit;
}

$msg = $update['message'] ?? [];
$chatId = $msg['chat']['id'] ?? 0;
$text = trim($msg['text'] ?? '');

if (!$text) {
    http_response_code(200);
    exit;
}

// ----------------- CHECK ALLOWED -----------------
if (!$telegram->checkAllowed($chatId)) {
    http_response_code(200);
    exit;
}

// ----------------- NORMALIZE COMMAND -----------------
$parts = explode(' ', $text);
$cmd = strtolower(trim(explode('@', $parts[0])[0]));

// ----------------- HANDLE COMMANDS -----------------
try {
    // /start
    if($cmd === '/start'){
        $telegram->sendMessage($chatId, 'Hi! I am Seilisa! How may I /help you today?');
        exit;
    }

    // /help
    if($cmd === '/help'){
        $telegram->sendMessage($chatId, 
        "Prompts available:
<b>/balance &lt;wallet_address&gt;</b> - Check SEI balance
<b>/price &lt;symbol&gt;</b> - Get token price
<b>/watch &lt;wallet&gt;</b> - Add wallet to watchlist
<b>/watchlist</b> - Show your watched wallets"
        );
        exit;
    }

    // /balance
    if($cmd === '/balance'){
        $addr = $parts[1] ?? null;
        if(!$addr){
            $telegram->sendMessage($chatId, "Usage: /balance <wallet_address>");
            exit;
        }
        try {
            $wei = $evm->balanceWei($addr);
            $eth = SeiEvm::weiToEth($wei);
            $usd = $prices->priceUSD('SEI');
            $line = $usd ? ' (~$'.number_format($eth*$usd,2).')' : '';
            $telegram->sendMessage($chatId,"Balance of $addr: $eth SEI $line");
        } catch(Exception $e){
            $telegram->sendMessage($chatId,"Error fetching balance: ".$e->getMessage());
        }
        exit;
    }

    // /price
    if($cmd === '/price'){
        $id = $parts[1] ?? null; // user-provided token ID
        $price = $prices->priceUSD($id);
        if($price === null){
            $telegram->sendMessage($chatId, "Token not found. Please check the ID.");
        } else {
            $tokenName = $id ?: 'SEI';
            $telegram->sendMessage($chatId, "$tokenName price: $".number_format($price,6));
        }
        exit;
    }

    // /watch
    if($cmd === '/watch'){
        if(count($parts) < 2){
            $telegram->sendMessage($chatId, "Usage: /watch <wallet_address>");
            exit;
        }
        $addr = trim($parts[1]);
        $alerts->addWallet($chatId,$addr);
        $telegram->sendMessage($chatId,"✅ Watching wallet: $addr for new transactions.");
        exit;
    }

    // /watchlist
    if($cmd === '/watchlist'){
        $list = $alerts->listWallets($chatId);
        if(!$list){
            $telegram->sendMessage($chatId,"No wallets being watched.");
        } else {
            $out = "👀 Your watched wallets:\n";
            foreach($list as $w){
                $out .= "- ".$w['address']."\n";
            }
            $telegram->sendMessage($chatId,$out);
        }
        exit;
    }

    // SEILISA fallback
    $resp = $seilisa->respond($text);
    $telegram->sendMessage($chatId, $resp);

} catch(Exception $e){
    $telegram->sendMessage($chatId, "⚠️ Error: ".$e->getMessage());
}

// Always respond 200 OK to Telegram
http_response_code(200);
exit;
?>
