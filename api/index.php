<?php

include_once "WXBizMsgCrypt.php";

// 假设企业号在公众平台上设置的参数如下
$encodingAesKey = "5nYYP7Wp15hVTEqum3rdH56P8DRstd6hRjgvrqBuewW";
$token = "TaisOsOulxb";
$corpId = "wx965351f4462ae3ba";

// $sVerifyMsgSig = $_GET["msg_signature"];
// $sVerifyTimeStamp = $_GET["timestamp"];
// $sVerifyNonce = $_GET["nonce"];
// $sVerifyEchoStr = $_GET["echostr"];

// $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
// $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
// if ($errCode == 0) {
// 	//
// 	// 验证URL成功，将sEchoStr返回
// 	print $sEchoStr;
// } else {
// 	print("ERR: " . $errCode . "\n\n");
// }

// $sReqData = $_POST[]

				    $url = "http://i.ihongwen.com/school/index.php/www/text/text/11111";  
					$ch = curl_init($url);  
			        $output = curl_exec($ch);  
			        curl_close($ch);  
					$output = stripslashes($output);//获取到json格式的token和时间限制数据
					$msg =json_decode($output, true);//转成数组
					// var_dump($msg);


?>