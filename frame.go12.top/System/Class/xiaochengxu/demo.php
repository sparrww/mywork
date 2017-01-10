<?php

include_once "wxBizDataCrypt.php";


$appid = 'wx6dc27ecef35b9ceb';
$sessionKey = '69eb5a53cdfc62d0b9507a6718c320eb';

$encryptedData="S5NzDUoeY7uI+xThtM/mYwV18bfSgyEgHbLm4tiMtP4rkIh0a7mKclZViuvExOkrrAFnmHBwEStz5eq7TsmOJ6Bs0YZGzypGs0/dD0km3AIPqg+ooZAG9NVpLXgpBkQuZC9GbKoYwUIUCvDRgfyUMcIjZZE4ZCFl/QwISLpc9eZpfj4zi0RgNbqmP8LfMewLgJIsNhRSy7rQiLpHZ40xjn1d/XV36yUI+35weHdwa8GYn1IU2hXfMnV9OEJ5P31ozvTCaRH+M6uXmzmMWrQDzpY+zKygqMC5XrsagGAw+FAzhga9nfBEp6pNwKuP5i9VM4jxBa52Ey+Z5YfOeA7ljh+tezZIJt6/11u6PARbjo8Efvr1ZSjhwVw2W7gxngkc6yx3CuhoFrVEwKrOAn6m28l9oVsPNSiZ9qWUmSOVOZMR0avbApJRxIJo+1RLIslbIvV+BA9z81GBTirvfRmO4Q==";

$iv = "rZCcPiOtCjFV8gBhb+XIfA==";

$pc = new WXBizDataCrypt($appid, $sessionKey);
$errCode = $pc->decryptData($encryptedData, $iv, $data );

if ($errCode == 0) {
    print($data . "\n");
} else {
    print($errCode . "\n");
}
