<?php
class SeiEvm {
private $rpcUrl, $chainId, $privateKey;


public function __construct($rpcUrl, $chainId, $privateKey) {
$this->rpcUrl = $rpcUrl;
$this->chainId = $chainId;
$this->privateKey = preg_replace('/^0x/i','',$privateKey);
}


public function rpc($method, $params = []) {
$body = ['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>$params];
$ch = curl_init($this->rpcUrl);
curl_setopt_array($ch, [CURLOPT_POST => 1, CURLOPT_RETURNTRANSFER => 1, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode($body)]);
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res,true);
if(isset($data['error'])) throw new Exception($data['error']['message']);
return $data['result'];
}


public function getAddress() {
// Warning: simplified, for real deployment use proper library
return '0xYOURADDRESSHERE';
}


public function balanceWei($address) {
return $this->rpc('eth_getBalance', [$address,'latest']);
}


public static function weiToEth($weiHex) {
return hexdec($weiHex)/1e18;
}
}