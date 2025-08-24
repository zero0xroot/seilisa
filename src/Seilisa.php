<?php
class Seilisa {
public function respond($text){
$text = strtolower($text);
if(strpos($text,'hello')!==false) return 'Hello! How are you today?';
if(strpos($text,'sei')!==false) return 'SEI is looking strong today!';
if(strpos($text,'price')!==false) return 'You can check price with the /price command.';
return 'Tell me more...';
}
}