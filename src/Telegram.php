<?php
class Telegram {
    private $token;
    private $allowed;

    public function __construct($token, $allowedChatsCsv = null) {
        // trim removes any hidden newline or space
        $this->token = trim($token);
        $this->allowed = $allowedChatsCsv ? array_map('trim', explode(',', $allowedChatsCsv)) : null;
    }

    public function checkAllowed($chatId) {
        if (!$this->allowed || $this->allowed === ['']) return true;
        return in_array((string)$chatId, $this->allowed, true);
    }

    public function sendMessage($chatId, $text, $replyMarkup = null) {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => $payload
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
}
?>