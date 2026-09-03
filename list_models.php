<?php
$apiKey = 'AIzaSyCpLMQMW6qnwNr8om8_YF1qIa-lkTJqexc';
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;
$response = file_get_contents($url);
file_put_contents('c:/xampp/htdocs/PPDB-SD-MUHAMMADIYAH/models_list.json', $response);
echo "DONE";
