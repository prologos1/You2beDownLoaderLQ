<?php 
namespace classes;
class DownloadHelper
{
    // file_get_contents analog
    function curl_get_contents($host, $referer = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0");
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 seconds trying
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $html = curl_exec($ch);
        echo curl_error($ch);
        curl_close($ch);
        return $html;
    }

    // Function to download a file using cURL
    function curl_download_file($url, $fileName, $referer = null)
    {
        $variant_downloading = 2;
        switch ($variant_downloading) {
            case 1:
                // variant 1
                $ch = curl_init($url);
                $file = fopen($fileName, 'wb');

                curl_setopt($ch, CURLOPT_FILE, $file);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 120 seconds trying

                curl_exec($ch);
                curl_close($ch);
                fclose($file);
                break;
            case 2:
                // variant 2

                // Initialize cURL session
                $curl = curl_init($url); // if curl_init(); then needs to add row: curl_setopt($curl, CURLOPT_URL, $url);

                // Set cURL options
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_HEADER, 0);

                curl_setopt($curl, CURLOPT_TIMEOUT, 120); // 120 seconds trying

                // Execute the cURL session and get the response
                $response = curl_exec($curl);

                // Save the response to a file
                file_put_contents($fileName, $response);

                // Close the cURL session
                curl_close($curl);
                break;
            default:
                file_put_contents($fileName, file_get_contents($url));
        }


        // Check if the file exists
        if (file_exists($fileName)) {
            return "File " . $fileName . " downloaded successfully! ";
        } else {
            return "Failed to download file. ";
        }

    }
}
?>
