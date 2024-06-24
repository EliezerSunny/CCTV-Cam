<?php
// Ensure error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// URL and headers
$url = "http://www.insecam.org/en/jsoncountries/";

$headers = array(
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
    'Cache-Control' => 'max-age=0',
    'Connection' => 'keep-alive',
    'Host' => 'www.insecam.org',
    'Upgrade-Insecure-Requests' => '1',
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36'
);

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute cURL session
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
    exit;
}

// Close cURL session
curl_close($ch);

try {
    // Decode JSON response
    $data = json_decode($response, true);
    $countries = $data['countries'];

    // Print ASCII art
    echo <<<ASCIIART
    \033[1;31m\033[1;37m 
	    _____________________________    __   _________
__  ____/__  ____/___  __/__ |  / /   __  ____/______ ________ ___
_  /     _  /     __  /   __ | / /    _  /     _  __ `/__  __ `__ \
/ /___   / /___   _  /    __ |/ /     / /___   / /_/ / _  / / / / /
\____/   \____/   /_/     _____/      \____/   \__,_/  /_/ /_/ /_/

    \033[1;31m                                                                        EliezerSunny \033[1;31m\033[1;37m
ASCIIART;

    // Display countries
    foreach ($countries as $key => $value) {
        echo "Code : ($key) - {$value['country']} / ({$value['count']})  \n\n";
    }

    // Get user input for country code
    $country = readline("Code(##) : ");

    // Fetch camera information
    $url = "http://www.insecam.org/en/bycountry/$country";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        exit;
    }

    curl_close($ch);

    // Extract last page number
    preg_match('/pagenavigator\("\?page=", (\d+)/', $response, $matches);
    $last_page = $matches[1];

    // Write IP addresses to file
    $filename = "$country.txt";
    $fp = fopen($filename, 'w');

    for ($page = 0; $page < $last_page; $page++) {
        $url = "http://www.insecam.org/en/bycountry/$country/?page=$page";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            exit;
        }

        curl_close($ch);

        // Find IP addresses using regex
        preg_match_all('/http:\/\/\d+\.\d+\.\d+\.\d+:\d+/', $response, $matches);
        foreach ($matches[0] as $ip) {
            echo "\n\033[1;31m $ip";
            fwrite($fp, "$ip\n");
        }
    }

    fclose($fp);

} catch (Exception $e) {
    echo "An error occurred: {$e->getMessage()}";
}

// Final message
echo "\033[1;37m\nSave File : $filename\n";
exit();
?>
