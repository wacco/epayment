<pre><?php

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/bootstrap.php';
require_once LIBS_DIR . '/CardPay/Request.php';

$secretKey = '43218765';
$request = new Epayment\CardPay\Request('1joa');
$request->setRedirectUrl('http://epaymentsimulator.monogram.sk/TB_CardPay.aspx');
$request->setReturnUrl(BASE_URL . '/CardPayResponse.php')
	->setPrice(100)
	->setVS('4913685428')
	->setCS('0308')
	->setClientName('Janko Hraško');

if ($request->validate()) {
	$request->signMessage($secretKey);
	$paymentRequestUrl = $request->getRedirectUrl();
	echo "<br />{$paymentRequestUrl}<br /><a href='{$paymentRequestUrl}'>PRESMEROVAT NA BRANU BANKY</a><br />";

	echo "<br><br>";
	echo "Suma: " . $request->getPrice() . "<br>";
	echo "Mena: " . $request->getCurrency() . "<br>";
	echo "VS: " . $request->getVS() . "<br>";
	echo "SS: " . $request->getSS() . "<br>";
	echo "CS: " . $request->getCS() . "<br>";
	echo "Ucet: " . $request->getAccount() . "<br>";
	echo "Parametre: ";
	print_r($request->getParams());
}