<?php
header('Content-type: application/json');
$url = $_GET['url'];
$appid = ""; //put your app id here!
//
//
//
//
function curl($URL1) {
$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_URL, $URL1);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch1, CURLOPT_USERAGENT, 'Mozilla/5.0 (Android 11; Mobile; rv:87.0) Gecko/87.0 Firefox/87.0');
curl_setopt($ch1, CURLOPT_ENCODING, 'gzip, deflate');
curl_setopt ($ch1, CURLOPT_POST, 0);
$headers1 = [
'Host: www.vlive.tv',
'User-Agent: Mozilla/5.0 (Android 11; Mobile; rv:87.0) Gecko/87.0 Firefox/87.0',
'Accept: application/json, text/plain, */*',
'Accept-Language: en-US,en;q=0.5',
'Accept-Encoding: gzip, deflate, br',
'Pragma: no-cache',
'X-V-Device-Id: 368a3584-b2d0-4a47-952a-8399deb27173',
'Origin: https://m.vlive.tv',
'Connection: keep-alive',
'Referer: https://m.vlive.tv/',
'Sec-Fetch-Dest: empty',
'Sec-Fetch-Mode: cors',
'Sec-Fetch-Site: same-site',
'Cache-Control: no-cache',
'TE: trailers'
];
 curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers1);
$jalan1 = curl_exec($ch1);
return $jalan1;
}
function backslash($values){
    $add_backslash = str_replace("'","\'","$values");
    $add_backslash = str_replace('"','\"',"$values");
    return $add_backslash;
}
$dataraw = file_get_contents("$url");
    $get_id = explode('{"videoSeq":', $dataraw);
    $get_id2 = explode(',', $get_id[1]);
    $id = $get_id2[0];
	$pieces = explode('{"videoSeq":'.$id.',"type":"', "$dataraw");
	$pieces2 = explode('"', "$pieces[1]");
	$gettype = $pieces2[0];
	
if($gettype == 'LIVE'){
$url1 = "https://www.vlive.tv/globalv-web/vam-web/old/v3/live/$id/playInfo?appId=$appid&platformType=PC&ad=true&gcc=ID&locale=en_US";
$data1 = curl($url1);
$data = json_decode($data1);
$linkhls = $data->result->adaptiveStreamUrl;
	echo '{ "playlist": [
    {"title": "Live",
      "image":
        "",
      "sources": [
        {
          "file":
            "'.$linkhls.'",
          "default": true
        }
      ]
    }
  ]}';
	
} else{
	$url1 = "https://www.vlive.tv/globalv-web/vam-web/video/v1.0/vod/$id/inkey?appId=$appid&platformType=PC&gcc=ID&locale=en_US";
$data1 = curl($url1);
	$data = json_decode($data1);
	$inkey = $data->inkey;
	//explode liveChatId":"278494","vodId":"
	$dataraw = file_get_contents("https://www.vlive.tv/video/$id");
	$pieces = explode('"vodId":"', "$dataraw");
	$pieces2 = explode('"', "$pieces[31]");
	$req2 = curl("https://apis.naver.com/rmcnmv/rmcnmv/vod/play/v2.0/$pieces2[0]?key=$inkey");
	$data2 = json_decode($req2);
	$judul = backslash($data2->meta->subject);
	$cover = $data2->meta->cover->source;
	echo '{ "playlist": [
    {"title": "'.$judul.'",
      "image":
        "'.$cover.'",
      "sources": [';
	foreach($data2->videos->list as $mydatavideo)
    {
		$resolusinya = $mydatavideo->encodingOption->name;
		$urlmp4nya = $mydatavideo->source;
		echo ' {
          "file":
            "'.$urlmp4nya.'",
          "label": "'.$resolusinya.'"
        },';
		
	}
	$checkercapt = $data2->captions->list[0]->language;
	if($checkercapt === NULL) {
		echo '{}]
    }
  ]';
	}else {
		echo '{}],
      "captions": [
';
		foreach($data2->captions->list as $mydatacaption)
    	{
			$captlang = backslash($mydatacaption->label);
			$captlink = $mydatacaption->source;
			echo ' {
          "file":
            "'.$captlink.'",
          "label": "'.$captlang.'",
          "kind": "captions"
        },';
			
		}
		echo ' {}]
    }
  ]';
	}
echo '}';
	
	

}
?>
