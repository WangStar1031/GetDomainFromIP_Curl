<?php
	set_time_limit(600);
	require_once("simple_html_dom.php");

	function __call_safe_url__($__url) {
	    $__url = str_replace("&amp;", "&", $__url);

	    $curl = curl_init();

	    curl_setopt_array($curl, array(
	      CURLOPT_URL => $__url,
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => "",
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 3,
	      CURLOPT_HEADER => 1,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_SSL_VERIFYPEER => false,
	      CURLOPT_CUSTOMREQUEST => "GET",
	      CURLOPT_HTTPHEADER => array(
	        "cache-control: no-cache",
	        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.37"
	      ),
	    ));

	    $response = curl_exec($curl);

	    return $response;
	}
	function _multiRequest($urls) {
		$curly = array();
		$result = array();

		$mh = curl_multi_init();
		foreach ($urls as $id => $url) {

			$curly[$id] = curl_init();
			curl_setopt_array($curly[$id], array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 3,
				// CURLOPT_PROXY => $proxy,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36"
				),
			));
			// curl_setopt($curly[$id], CURLOPT_URL, $url);
			// curl_setopt($curly[$id], CURLOPT_HEADER, 0);
			curl_multi_add_handle($mh, $curly[$id]);
		}

		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while($running > 0);

		foreach($curly as $id => $c) {
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}

		curl_multi_close($mh); 
		return $result;
	}

	function __get_domain_names($__ip) {
		$str_url = "https://www.bing.com/search?q=ip:".$__ip;
		$data = __call_safe_url__($str_url);
		$html = str_get_html($data);
		$records = $html->find("#b_results a");
		$result = [];
		foreach ($records as $record) {
			$href_val = $record->href;
			if(strpos($href_val, "http") === false) continue;
			if(strpos($href_val, "microsofttranslator.com") !== false) continue;
			$href_val = str_replace("https://", "", $href_val);
			$href_val = str_replace("http://", "", $href_val);
			if(strpos($href_val, "/") !== false) {
				$href_val = substr($href_val, 0, strpos($href_val, "/"));
			}
			if(in_array($href_val, $result)) continue;
			$result[] = $href_val;
		}
		return $result;
	}

	$ipContents = file_get_contents('ips.txt');
	$arrIps = explode(PHP_EOL, $ipContents);
	$results = '';
	$urls = array();
	for( $i = 0; $i < count($arrIps); $i ++){
		array_push($urls, "https://www.bing.com/search?q=ip:".$arrIps[$i]);
	}
	echo "string<br/>";
	$results = _multiRequest($urls);
	echo "string<br/>";
	echo count($results) . "<br/>";

	$rst = [];
	for( $i = 0; $i < count($results); $i ++){
		$result = $results[$i];
		if(!$result)continue;
		$html = str_get_html($result);
		$records = $html->find("#b_results a");
		foreach ($records as $record) {
			$href_val = $record->href;
			if(strpos($href_val, "http") === false) continue;
			if(strpos($href_val, "microsofttranslator.com") !== false) continue;
			$href_val = str_replace("https://", "", $href_val);
			$href_val = str_replace("http://", "", $href_val);
			if(strpos($href_val, "/") !== false) {
				$href_val = substr($href_val, 0, strpos($href_val, "/"));
			}
			if(in_array($href_val, $rst)) continue;
			$rst[] = $href_val;
		}
	}
	print_r($rst);
	// exit();
	$fileContents = '';
	for( $i = 0; $i < count($rst); $i++){
		$fileContents .= $rst[$i] . '
';
	}
	file_put_contents('result.txt', $fileContents);
?>