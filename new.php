<?php
$accessToken = '************************************************************************************************';

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);
 
$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
//メッセージ取得
// $DN = $jsonObj->{"events"}[0]->{"message"}->{"text"};
//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

// $DN	= mb_convert_kana($DN, "a", "utf-8"); //半角に変換

//緯度取得
$latitude = $jsonObj->{"events"}[0]->{"message"}->{"latitude"};
//経度取得
$longitude = $jsonObj->{"events"}[0]->{"message"}->{"longitude"};

$laW = $latitude;
$lnW = $longitude;

// $address = "〒105-0011 東京都港区芝公園４丁目２−８";
$ANKLONGITUDE = $laW; //テスト用定数：皇居
$ANKPARALLEL = $lnW; //テスト用定数：皇居
$bearer_token='g*********************************lCjiZvqijASt1uxmWarx-1fd-NFcSypyuTok-3HdH5flgMx7qL3N-_zWDRay6KXOb-0RWG2hupYzfmSif0yyBnu3VkIKzDCVLqvrK0jn46Io1Xrfx2APo1Z3TK4ZWkzZabr4Sov-OH5mOZr6BoAognafJIUny5XFH5vLA_RA9YWI0z_29_lTqu4jdJ7aBUFDN26dD4Uf0BQT_CuVhPpiSxvEDS66I2zICMXYN_LisYG1hC0ls1KnuxQcn1z1qEr5_vKCN3f3p6e_-VqRKTV9cp28c_SRid3PLBo0S2cIOxcIxfau4WQjJP-GQPKgiAs0UpDqvfQo_ufIXxLlItywhvGm6e0c0nGVUZD1rArBufhglS408xxD2Qy2wTKiWEjt6eeWI7xtvjVUSDy8rQMP7qc3yeFIIJ5YfJMX7SI';
$keyid='';

$url='http://hackathon.support-cloud-projects.com/LaundromatWebApi/api/shopinfo?ANKLONGITUDE=';
$url.=floor($event->message->longitude);
$url.='&ANKPARALLEL='.floor($event->message->latitude);
//header('Content-type: application/json; charset=utf-8');
$header = [
'Authorization: Bearer '.$bearer_token,
'Content-Type: application/json',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $bearer_token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($ch);
curl_close($ch);

$shopName;
$shopAddress;
$shopLon;

$result = json_decode($response);

foreach ($result->DataModel as $res) {
    $shopName.=$res->KNJSHOPNAME;
    $shopAddress.=$res->KNJADDRESS;
    $shopLon.=$res->ANKLONGITUDE;
    $shopLat.=$res->ANKPARALLEL;
}


//食べログの処理を叩く
$gnaviaccesskey = '******************************';

//ユーザーからのメッセージ取得
// $json_string = file_get_contents('php://input');
// $jsonObj = json_decode($json_string);

$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};

//メッセージ取得
$text2 = $jsonObj->{"events"}[0]->{"message"}->{"text"};

//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

// //緯度取得
// $latitude = $jsonObj->{"events"}[0]->{"message"}->{"latitude"};

// //経度取得
// $longitude = $jsonObj->{"events"}[0]->{"message"}->{"longitude"};

//エンドポイントのURIとフォーマットパラメータを変数に入れる
$uri   = "http://api.gnavi.co.jp/RestSearchAPI/20150630/";
//APIアクセスキーを変数に入れる
$acckey= $gnaviaccesskey;
//返却値のフォーマットを変数に入れる
$format= "json";
//緯度・経度、範囲を変数に入れる

// 業態がラーメン屋さんを意味するぐるなびのコード(大業態マスタ取得APIをコールして調査)
$category_s = "RSFST08008";

$hit_per_page = "5";

//緯度経度は日本測地系で日比谷シャンテのもの。範囲はrange=2で500m以内を指定している。
$range = 2;

//URL組み立て
$url  = sprintf("%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s", $uri, "?format=", $format, "&keyid=", $acckey, "&latitude=", $latitude,"&longitude=",$longitude,"&category_s=",$category_s,"&range=",$range,"&hit_per_page=",$hit_per_page);
//API実行
$json = file_get_contents($url);
//取得した結果をオブジェクト化
$obj  = json_decode($json);

$total_hit_count = $obj->{'total_hit_count'};
$result = "";

//店舗情報の格納配列
$i = 1;

//イケてないけど、$response_format_textにループで配列データ格納しようとしても
//失敗してしまうのでベタうちにする。
$get_name1 ="該当なし";
$get_url1 ="http://www.yahoo.co.jp/";

$get_name2 ="該当なし";
$get_url2 ="http://www.yahoo.co.jp/";

$get_name3 ="該当なし";
$get_url3 ="http://www.yahoo.co.jp/";

$get_name4 ="該当なし";
$get_url4 ="http://www.yahoo.co.jp/";

$get_name5 ="該当なし";
$get_url5 ="http://www.yahoo.co.jp/";

$get_address1;
$guru_lat;
$guru_lon;

//結果をパース
if ($total_hit_count === null) {
    $result .= "近くにラーメン屋さんはありません。";
}else{
    $result .= "近くにあるラーメン屋さんです。\n\n";

    foreach((array)$obj as $key => $val){
      if(strcmp($key, "rest") == 0){
          foreach((array)$val as $restArray){
              //最寄駅入れようかと思ったけど、任意位置で計測するから駅近とは限らないことに気づいた
              //$station ="";
              //$station .= $restArray->{"access"}->{"station"};
              //$station .= $restArray->{"access"}->{"exit"};
              //$station .= $restArray->{"access"}->{"walk"} . "分";

              //switch文だとLineにレスが返らないのでif文で対応
                if($i===1){
                  $get_name1 = $restArray->{"name"};
                  $get_url1 = $restArray->{"url"};
                  $get_address1 = $restArray->{"address"};
                  $get_image = $restArray->{"image_url"};
                  $guru_lat = $restArray->{"latitude"};
                  $guru_lon = $restArray->{"longitude"};
                }

                if($i===2){
                  $get_name2 =$restArray->{"name"};
                  $get_url2 =$restArray->{"url"};
                  $get_address2 = $restArray->{"address"};
                }

                if($i===3){
                  $get_name3 =$restArray->{"name"};
                  $get_url3 =$restArray->{"url"};
                  $get_address3 = $restArray->{"address"};
                }

                if($i===4){
                  $get_name4 =$restArray->{"name"};
                  $get_url4 =$restArray->{"url"};
                  $get_address4 = $restArray->{"address"};
                }

                if($i===5){
                  $get_name5 =$restArray->{"name"};
                  $get_url5 =$restArray->{"url"};
                  $get_address5 = $restArray->{"address"};
                }

              $i++;
          }

          }
    }
}

//ここで画像を出す


$response_format_text = [ 
    'type' => 'template', 
    'altText' => 'カルーセル', 
    'template' => [
         'type' => 'carousel', 
        'columns' => [ 
            [ 
                "thumbnailImageUrl" => "https://cdn-ak.f.st-hatena.com/images/fotolife/m/makotomarron/20171031/20171031121953.png",
      "title" => $shopName,
      "text" => "住所:" . $shopAddress,
      "actions" => [
          [
            "type" => "uri",
            "label" => "Google Mapsで開く",
            "uri" => "https://maps.google.com/maps?q=".$shopLat.",".$shopLon
          ],
          [
            "type" => "uri",
            "label" => "ストリートビュー",
            "uri" => "http://maps.google.co.jp/maps?f=q&source=s_q&hl=ja&geocode=&q=".$shopLat.",".$shopLon."&sll=".$shopLat.",".$shopLon."&sspn=0,0&brcurrent=3,0x0:0x0,0&ie=UTF8&ll=".$shopLat.",".$shopLon."&spn=0,0&z=17&layer=c&cbll=".$shopLat.",".$shopLon."&cbp=0,0,0,0,0"
          ],          [
            "type" => "uri",
            "label" => "現在地からナビ",
            "uri" => "https://maps.google.com/maps?saddr=現在地&daddr=".$shopLat.",".$shopLon."&dirflg=d"
          ]
      ]
            ],
             [ 
                "thumbnailImageUrl" => "https://img03.hamazo.tv/usr/t/a/k/takahashi01/2013-11-21s15.24.57.jpg",
                "title" => $get_name1,
                "text" => $get_address1,
                "actions" => [
                    [
                      "type" => "uri",
                      "label" => "Google Mapsで開く",
                      "uri" => "https://maps.google.com/maps?q=".$guru_lat.",".$guru_lon
                    ],
                    [
                      "type" => "uri",
                      "label" => "ストリートビュー",
                      "uri" => "http://maps.google.co.jp/maps?f=q&source=s_q&hl=ja&geocode=&q=".$guru_lat.",".$guru_lon."&sll=".$guru_lat.",".$guru_lon."&sspn=0,0&brcurrent=3,0x0:0x0,0&ie=UTF8&ll=".$guru_lat.",".$guru_lon."&spn=0,0&z=17&layer=c&cbll=".$guru_lat.",".$guru_lon."&cbp=0,0,0,0,0"
                    ],          [
                      "type" => "uri",
                      "label" => "現在地からナビ",
                      "uri" => "https://maps.google.com/maps?saddr=現在地&daddr=".$guru_lat.",".$guru_lon."&dirflg=d"
                    ]
                ]
                ], 
            ] 
        ] 
];


$post_data = [
    "replyToken" => $replyToken,
    "messages" => [$response_format_text]
    ];

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json; charser=UTF-8',
      'Authorization: Bearer ' . $accessToken
    ));
  $result = curl_exec($ch);
  curl_close($ch);
  ?>
