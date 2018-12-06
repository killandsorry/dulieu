<?php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://dichvuthongtin.dkkd.gov.vn/egazette/Forms/Egazette/DefaultAnnouncements.aspx?h=14b",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_TIMEOUT => 90,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_FOLLOWLOCATION => 1,
  CURLOPT_CONNECTTIMEOUT => 60,
  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"array[subscribed_fields]\"\r\n\r\nmessages\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"access_token\"\r\n\r\nEAAXcQV45YIgBAJECmEYpKS3kOZCksXwjLmE3rg7tMhBDWoZBjeniuJHOy5Dy7l2bwxZBfKPv6sxxPoAVmZB9vhv18Eqj400Aeygb8miQadxnVMy8XbdiDm5XieFwSK8RZAZCsPKkdDGPW4ZAdZBOjWtHXL7B4ujPNnsVEccSXcx0uWhNI32WZAAZC3mpexnSaBD0RxIZCWcotvRfQZDZD\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
    "postman-token: 0ce255c2-b32c-b2ca-5475-8e4643fe3302",
    "host: dichvuthongtin.dkkd.gov.vn"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}