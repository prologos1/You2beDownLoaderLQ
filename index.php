<?php
require_once __DIR__ . '/DownloadHelper.php';
use classes\DownloadHelper;

class You2beDownLoaderLQ extends DownloadHelper
{
    private $time_out;

    function __construct($is_browser_downloading, $is_using_curl)
    {
        $this->time_out = 1800; // 30 min store files

        if (!isset($_REQUEST['url_id'])) {
            echo $this->output_form();
            $this->delete_old_files($this->time_out);
            exit;
        } else {
            $url_id = trim($_REQUEST['url_id']);
            // Extract video ID from URL
            parse_str(parse_url($url_id, PHP_URL_QUERY), $params);
            if (isset($params['v'])) {
                $videoId = trim($params['v']);
            } else {
                $videoId = $url_id;
            }

            echo $this->download_init($videoId, [
                'is_browser_downloading' => $is_browser_downloading,
                'is_using_curl' => $is_using_curl,
            ]);

        }
    }

    function __destruct()
    {
        $this->browser_downloading = null;
        $this->using_curl = null;
    }

    private function delete_old_files($time_out = 3600)
    {
        // deleting all old files with extension mp4 which created one hour before ($time_out - 3600 seconds in an hour):
        $output = '';
        $current_time = time();
        $one_hour_ago = $current_time - $time_out;

        $path_directory = __DIR__;
        $files = glob($path_directory . '/*.mp4');
        foreach ($files as $file) {
            if (filemtime($file) < $one_hour_ago) {
                unlink($file);
                $output .= "Deleted old file: " . $file . "<br>";
            }
        }
        return $output;
    }

    private function output_form()
    {
        $styles = '<style>
			#php-form {
			  margin: 20px;
			}
			label {
			  display: block;
			  margin-bottom: 5px;
			}
			.form-control {
			  width: 100%;
			  padding: 10px;
			  margin: 6px 0;
			  display: inline-block;
			  border: 1px solid #ccc;
			  border-radius: 4px;
			  box-sizing: border-box;
			}
			#execute-button {
			  background-color: #4CAF50;
			  color: white;
			  padding: 14px 20px;
			  margin: 8px 0;
			  border: none;
			  border-radius: 4px;
			  cursor: pointer;
			}
			#execute-button:hover {
			  background-color: #45a049;
			}
		</style>
		';
        $form = '
			<form id="php-form">
				<div class="form-group">
					<label for="dl-youtube">Enter url Youtube </label>
					<input name="url_id" id="dl-youtube" class="form-control" autocomplete="off"></input>
				</div>
				<button type="submit" id="execute-button" class="btn btn-primary">Execute</button>
			</form>
		';
        return sprintf('<html><head><title>You2be DownLoader</title> %s </head><body> %s </body></html>', $styles, $form);
    }


    function removeSpecialChars($inputString)
    {
        // Remove all characters except letters and numbers and - _
        $result = preg_replace('/[^A-Za-z0-9_-]/', '', $inputString);
        return $result;
    }

    private function download_init($videoId, $options)
    {
        $output = '';

        $browser_downloading = !(!$options['is_browser_downloading'] || !isset($options['is_browser_downloading']));
        $using_curl = !(!$options['is_using_curl'] || !isset($options['is_using_curl']));

        $videoUrl = sprintf('https://www. [Y 0 u T u b @] .com/watch?v=%s', $videoId); // as U know... :)

        $html = $using_curl ? $this->curl_get_contents($videoUrl) : @file_get_contents($videoUrl);
        if (!$html) {
            $output = 'no html';
            return $output;
        }
        preg_match('/ytInitialPlayerResponse\s*=\s*({.*?});/', $html, $matches); // from script where ytInitialPlayerResponse

        // Decode the JSON object into an associative array
        $arr = json_decode($matches[1], true);
        if (empty($arr["streamingData"]["adaptiveFormats"][0]["url"])) {
            $output = 'no streamingData';
            return $output;
        }
        // $downloadUrl = $arr["streamingData"]["adaptiveFormats"][0]["url"]; // getting video without audio, but full HD
        $downloadUrl = $arr["streamingData"]["formats"][0]["url"];

        $titleUrl = $arr["videoDetails"]["title"];
        $shortDescriptionUrl = $arr["videoDetails"]["shortDescription"];
        $thumbnailUrl = $arr["microformat"]["playerMicroformatRenderer"]["thumbnail"]["thumbnails"][0]["url"];

        if ($browser_downloading) {
            // Download the video from browser

            // Set headers to force download
            header("Content-Type: application/octet-stream");
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $videoId . ".mp4\"");
            // Output the download file
            readfile($downloadUrl);
        } else {
            // Download the video from server-side
            if ($using_curl) {
                $output = $this->curl_download_file($downloadUrl, $this->removeSpecialChars($videoId) . '_downloaded_video.mp4', $videoUrl);
            } else {
                file_put_contents($this->removeSpecialChars($videoId) . '_downloaded_video.mp4', file_get_contents($downloadUrl));
            }
        }

        return $output;
    }


}

new You2beDownLoaderLQ(false, true); // run!
