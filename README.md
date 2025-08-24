Seilisa — Real‑Time Sei EVM Telegram Bot
Lightweight Telegram bot to monitor Sei EVM in real time.

What it does
Check SEI wallet balance with USD estimate

Get token prices via CoinGecko

Watch wallets and receive instant transaction alerts

Manage a personal watchlist

Commands
/start, /help

/balance <wallet_address>

/price <coingecko_id>

/watch <wallet_address>

/watchlist

Tech
Telegram Bot API (webhook + replies)

Sei EVM JSON‑RPC (eth_getBalance), wei→eth helper

CoinGecko Simple Price API

SQLite for wallets + last_tx_id

Cron worker for polling and alerts

Setup
PHP 8+ with curl and SQLite

Set webhook to webhook.php (HTTPS)

Run cron-tx.php via cron (e.g., every minute)

Configure: TELEGRAM_BOT_TOKEN, SEI_RPC_URL, optional TELEGRAM_ALLOWED_CHATS

Security
Don’t commit secrets; use env vars

Restrict access with allowed chat IDs
