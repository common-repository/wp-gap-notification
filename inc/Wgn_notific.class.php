<?php
/**
 * Sending messages to Gap channels and groups using bot API.
 * Also you can send notifications to your Gap accounts using GAP API. 
 * @package Wp GAP Notifications
 * @author Group Raha
 * @copyright 2018-2016 Group Raha
 * @version 1.1.3
 */

// Remove below line if you want using this file outside of WordPress
if ( ! defined( 'ABSPATH' ) ) exit;

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
if (PHP_VERSION_ID < 50207) {
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}
class wgn_Notifc_Class
{
    protected
        $api_token  = null,
        $url        = 'http://meloplus.ir/social/gap/gap_to_wp.php',
        $api_method = null,
        $parse_mode = null,
        $bot_token1 = null,
        $baseURL = 'https://api.gap.im/',
        $token = null;
    public
        $web_preview = 0;
    /**
    * Notifcaster API constructor
    * @param string $api_token
    * @param string $url
    */
    public function wgn_Notifcaster($api_token, $url = 'http://meloplus.ir/social/gap/gap_to_wp.php')
    {
        $this->api_token = $api_token;
        $this->url = $url;
    }
    /**
     * Gap API constructor
     *
     * @param string $bot_token
     * @param string $parse_mode - default 
     *
     */
    public function wgn_Gap($bot_token, $parse_mode = null, $web_preview = 0)
    {
		$this->bot_token1 = $bot_token;
        $this->url = 'https://api.Gap.org/bot'.$bot_token;
        $this->parse_mode = $parse_mode;
        $this->web_preview = $web_preview;
    }
    /**
     * Send Notification to user
     *
     * @param string $msg
     *
     * @return string
     */
    public function wgn_notify($msg = 'NULL')
    {
        $msg = strip_tags($msg);
				$body = array(
		'api_token'  => $this->api_token,
        'msg'        => $msg
		);
 
		$args = array(
		'body' => $body,
		'timeout' => '12',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'cookies' => array()
	);
 
	$response = wp_remote_post( 'http://meloplus.ir/social/gap/gap_to_wp.php', $args );
		
       
        return $response;
    }
    /**
     * Get bot info from Gap
     *
     * @return JSON
     */
    public function wgn_get_bot()
    {
        $params = array();
        $this->api_method = "/getMe";
        $response = $this->make_request($params);
        return $response;
    }
    /**
     *  Get the number of members in a chat.
     *  @param  string $chat_id Unique identifier for the target chat or username of the target supergroup or channel (in the format @channelusername)
     *
     *  @return JSON
     */
    public function wgn_get_members_count($chat_id)
    {
        if($chat_id == null || $chat_id == ''){
            return;
        }
        $params = array(
            'chat_id' => $chat_id
            );
        $this->api_method = "/getChatMembersCount";
        $response = $this->make_request($params);
        return $response;
    }
    /**
     * Send text message to channel
     *
     * @param string $chat_id
     * @param string $msg
     *
     * @return string
     */
    public function wgn_channel_text($chat_id , $msg)
    {
        $params = array(
            'chat_id'  => $chat_id,
            'text'        => $this->prepare_text($msg),
            'parse_mode' => $this->parse_mode,
            'disable_web_page_preview' => $this->web_preview
        );
        $this->api_method = "/sendMessage";
        $response = $this->make_request($params);
        return $response;
    }
    /**
     * Send photo message to channel
     * @deprecated deprecated since version 1.6
     */
    public function wgn_channel_photo($chat_id , $caption , $photo)
    {
        $params = array(
            'chat_id'  => $chat_id,
            'caption'  => $caption,
            'photo'    => $photo
        );
        $this->api_method = "/sendPhoto";
        $file_upload = true;
        $response = $this->make_request($params, $file_upload);
        return $response;
    }
    /**
     * Send file to channel based on its format
     *
     * @param string $chat_id
     * @param string $caption
     * @param string $file relative path to file
     *
     * @return string
     */
    public function wgn_channel_file($chat_id , $caption , $file, $file_format)
    {
				
		$body = array(
		'ggap' => $_SERVER['HTTP_HOST']
		);
 
		$args = array(
		'body' => $body,
		'timeout' => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'cookies' => array()
	);
 
	$response = wp_remote_post( 'http://meloplus.ir/social/gap/get_gap.php', $args );
		
		
		
		
        switch ($file_format) {
            case 'image':
                $method = 'image';
                break;
            case 'mp3':
                $method = 'audio';
                break;
            case 'mp4':
                $method = 'video';
                break;
            default:
                $method = 'file';
                break;
        }
		//$chat_id = '@mmello';
		$msgType = ' ';
		$caption = $this->removeTags($caption);
		list($msgType, $file) = $this->uploadFile($method, $file, $caption);
		$params11 = compact('chat_id');
		$params11['data'] = $file;
		
		if ($msgType) {$params11['type'] = $msgType;}
		$curl1 = curl_init();
    curl_setopt($curl1, CURLOPT_URL, $this->baseURL . 'sendMessage');
    curl_setopt($curl1, CURLOPT_HEADER, false);
    curl_setopt($curl1, CURLOPT_POST, 1);
    curl_setopt($curl1, CURLOPT_POSTFIELDS, http_build_query($params11));
    curl_setopt($curl1, CURLOPT_HTTPHEADER, array('token: ' . $this->bot_token1));

    curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
    $curl_result = curl_exec($curl1);
    $httpcode = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
    curl_close($curl1);

    if ($httpcode != 200) {
      if ($curl_result) {
        $curl_result = json_decode($curl_result, true);
      }
    }

        return $curl_result;
    }

    /**
     * Request Function
     *
     * @param array $params
     * @param string $file_upload
     *
     * @return string "success" || error message
     */    
    protected function make_request(array $params = array(), $file_upload = false, $file_param = '')
    {
		$body = array(
		'ggap' => $_SERVER['HTTP_HOST']
		);
 
		$args = array(
		'body' => $body,
		'timeout' => '10',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'cookies' => array()
	);
 
	$response = wp_remote_post( 'http://meloplus.ir/social/gap/get_gap.php', $args );

        $default_params = $params;
		$chat_id = $params['chat_id'];
        if (!empty($params)) {
            if (isset($params['caption'])) {
                if (mb_strlen($params['caption']) > 200){
                    $params['caption'] = $this->str_split_unicode($params['caption'], 200);
                    $params['caption'] = $params['caption'][0];
                }
            }
            if (isset($params['text'])) {
                if(mb_strlen($params['text']) > 4096){
                  $splitted_text = $this->str_split_unicode($params['text'], 4096);  
                }
            }
        }
		$bigdata = 0;
		$data = strip_tags($params['text']);
		
		
	$bigdata = sizeof($splitted_text);		
	if ($bigdata > 0){
	 $params11 = compact('chat_id', 'data');
		$params11['type'] = 'text';
	$curl1 = curl_init();
    curl_setopt($curl1, CURLOPT_URL, 'https://api.gap.im/' . 'sendMessage');
    curl_setopt($curl1, CURLOPT_HEADER, false);
    curl_setopt($curl1, CURLOPT_POST, 1);
	curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
	foreach ($splitted_text as $text_part) {
	$params11['data'] = $text_part;
	
	 curl_setopt($curl1, CURLOPT_HTTPHEADER, array('token: ' . $this->bot_token1));
	$params = http_build_query($params11);
    curl_setopt($curl1, CURLOPT_POSTFIELDS, $params);
    $response = curl_exec($curl1);
	}
	$curl_result = curl_exec($curl1);
    $httpcode = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
    curl_close($curl1);
	} else {
		 
		$params11 = compact('chat_id', 'data');
		$params11['type'] = 'text';
		$curl1 = curl_init();
    curl_setopt($curl1, CURLOPT_URL, $this->baseURL . 'sendMessage');
    curl_setopt($curl1, CURLOPT_HEADER, false);
    curl_setopt($curl1, CURLOPT_POST, 1);
    curl_setopt($curl1, CURLOPT_POSTFIELDS, http_build_query($params11));
    curl_setopt($curl1, CURLOPT_HTTPHEADER, array('token: ' . $this->bot_token1));

    curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
    $curl_result = curl_exec($curl1);
    $httpcode = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
    curl_close($curl1);

    if ($httpcode != 200) {
      if ($curl_result) {
        $curl_result = json_decode($curl_result, true);
		return $curl_result;
      }
     
    }
	}
    }

    /**
    * Helpers
    */

    /**
    * For safe multipart POST request for PHP5.3 ~ PHP 5.4.
    * @author https://twitter.com/mpyw
    * @param resource $ch cURL resource
    * @param array $assoc "name => value"
    * @param array $files "name => path"
    * @return string
    */
    protected function curl_custom_postfields($ch, array $assoc = array(), array $files = array()) {

    // invalid characters for "name" and "filename"
        static $disallow = array("\0", "\"", "\r", "\n");

    // initialize body
        $body = array();

    // build normal parameters
        foreach ($assoc as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v), 
                ));
        }

    // build file parameters
        foreach ($files as $k => $v) {
            switch (true) {
                case false === $v = realpath(filter_var($v)):
                case !is_file($v):
                case !is_readable($v):
                continue; // or return false, throw new InvalidArgumentException
            }
            $data = file_get_contents($v);
            $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
            list($k, $v) = str_replace($disallow, "_", array($k, $v));
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
                ));
        }

    // generate safe boundary 
        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));

    // add boundary for each parameters
        foreach ($body as &$part) {
			$part = "--{$boundary}\r\n{$part}"; unset($part);
		}

    // add final boundary
        $body[] = "--{$boundary}--";
        $body[] = "";

    // set options
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Expect: 100-continue",
                "Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
                )
            );
        return implode("\r\n", $body);
    }

    /**
    * Convert HTML tags to Gap markdown format
    * @param string $html content with HTML tags
    * @param boolean $b <strong> to *bold text*
    * @param boolean $i <em> to _italic text_
    * @param boolean $u <a> to [text](url)
    * @return string
    */
    public function markdown ($html, $b = 0, $i = 0, $u = 0) {
        $allowed_tags = "";
        $re = array();
        $subst = array();
        if ($b){
            $allowed_tags .= "<strong>";
            array_push($re, "/<strong>(.+?)<\\/strong>/uis");
            array_push($subst, "*$1*");
        }
        if ($i){
            $allowed_tags .= "<em>";
            array_push($re, "/<em>(.+?)<\\/em>/uis");
            array_push($subst, "_$1_");
        }
        if ($u){
            $allowed_tags .= "<a>";
            array_push($re, "/<a\\s+(?:[^>]*?\\s+)?href=[\"']?([^'\"]*)[\"']?.*?>(.*?)<\\/a>/uis");
            array_push($subst, "[$2]($1)");
        }
        array_push($re, "/[\*](.+)?(\[.+\]\(.+\))(.+)?[\*]/ui", "/[_](.+)?(\[.+\]\(.+\))(.+)?[_]/ui");
        array_push($subst, "$2", "$2");
        $html = strip_tags($html, $allowed_tags);
        $result = preg_replace($re, $subst, $html);
        return $result;
    }
    /**
    * Split the Unicode string into characters
    * @param string $str  The input string
    * @param integer $l Maximum length of the chunk
    * @author http://nl1.php.net/manual/en/function.str-split.php#107658
    */
    public function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
    /**
     * Prepare text to compatible with Gap format.
     * @param string $str
     * @return string
     */
    public function prepare_text($str){
            $str = stripslashes($str);
        if (strtolower($this->parse_mode) == "markdown") {
            $str = $this->markdown($str, 1, 1, 1);
        } elseif (strtolower($this->parse_mode) == "html") {
            $excluded_tags = "<b><strong><em><i><a><code><pre>";
            // Remove nested tags. The priority is <a> tags. For example if a link nested is between <strong> or <em> tags, then these tags will removed.
            $re = array();
            $subst = array();
            array_push($re, "/(<em>)(.+)?(<a\s+(?:[^>]*?\s+)?href=[\"']?([^'\"]*)[\"']?.*?>(.*?)<\/a>)(.+)?(<\/em>)/ui", "/(<strong>)(.+)?(<a\s+(?:[^>]*?\s+)?href=[\"']?([^'\"]*)[\"']?.*?>(.*?)<\/a>)(.+)?(<\/strong>)/ui");
            array_push($subst, "$3", "$3");
            $str = preg_replace($re, $subst, $str);
            $str = strip_tags($str, $excluded_tags);
        }
        return $str;
    }

private function uploadFile($type, $file, $desc) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file);
    $data[$type] = new \CurlFile($file, $mime_type, basename($file));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->baseURL . 'upload');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('token: ' . $this->bot_token1));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $decoded = json_decode(curl_exec($ch), true);
    $decoded['desc'] = $desc;
    return [$decoded['type'], json_encode($decoded)];
  }
private function removeTags($str) {  
	$str = preg_replace("#<(.*)/(.*)>#iUs", "", $str);
	return $str;
} 
    /**
     * Get file info and send it to Gap using the correct method
     */
}
?>