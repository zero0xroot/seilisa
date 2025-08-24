# Seilisa — Real-Time Sei EVM Telegram Bot

A lightweight Telegram bot to monitor **Sei EVM** wallets in real time.  

---

## 🚀 Features

- 🔹 **Check SEI wallet balance** with USD estimate  
- 🔹 **Fetch token prices** via CoinGecko  
- 🔹 **Watch wallets** and receive instant transaction alerts  
- 🔹 **Manage a personal watchlist** (add/remove wallets)  

---

## 📖 Commands

| Command | Description |
|---------|-------------|
| `/start`, `/help` | Show help message |
| `/balance <wallet_address>` | Check wallet balance in SEI + USD |
| `/price <coingecko_id>` | Get token price from CoinGecko |
| `/watch <wallet_address>` | Add wallet to personal watchlist |
| `/watchlist` | View all watched wallets |

---

## 🛠️ Tech Stack

- **Telegram Bot API** → webhook + reply commands  
- **Sei EVM JSON-RPC** → `eth_getBalance`, wei → SEI helper  
- **CoinGecko API** → live token prices  
- **SQLite** → store watchlists + last transaction IDs  
- **Cron Worker** → polls Sei EVM and pushes alerts  

---

## ⚙️ Setup

### Requirements
- PHP 8+ with `curl` and `sqlite3` extensions  
- HTTPS server for webhook  

### Steps
1. Clone the repo and install dependencies.  
2. Configure environment variables:
   ```bash
   TELEGRAM_BOT_TOKEN=your_bot_token
   SEI_RPC_URL=https://sei-evm-rpc-url
   TELEGRAM_ALLOWED_CHATS=123456789,987654321
   ```
3. Set webhook for your bot to `webhook.php`.  
4. Run `cron-tx.php` every minute via cron:  
   ```bash
   * * * * * php /path/to/cron-tx.php
   ```

---

## 🔒 Security Notes

- Never commit secrets (bot tokens, RPC URLs, etc.). Use **environment variables**.  
- Restrict usage with **allowed chat IDs** to prevent abuse.  

---

## 📌 Example

```bash
/user ➝ /balance 0x1234abcd...
/bot ➝ Balance: 50.123 SEI ($12.45)
```

---

## 📜 License
MIT  
