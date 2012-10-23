<pre><?php

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/bootstrap.php';
require_once LIBS_DIR . '/SporoPay/Request.php';

$secretKey = 'Z3qY08EpvLlAAoMZdnyUdQ==';
$request = new Epayment\SporoPay\Request('0013662162');
$request->setRedirectUrl('http://epaymentsimulator.monogram.sk/SLSP_SporoPay.aspx');
$request->setReturnUrl(BASE_URL . '/SporoPayResponse.php')
	->setPrice(100)
	->setVS('20120164')
	->setSS('201210')
	->setParam('myParam', '2062489842')
	->setParam('cisloObjednavky', 'XX99OO8877');

if ($request->validate()) {
	$request->signMessage($secretKey);
	$paymentRequestUrl = $request->getRedirectUrl();
	echo "<br />{$paymentRequestUrl}<br /><a href='{$paymentRequestUrl}'>PRESMEROVAT NA BRANU BANKY</a><br />";

	echo "<br><br>";
	echo "Suma: " . $request->getPrice() . "<br>";
	echo "Mena: " . $request->getCurrency() . "<br>";
	echo "VS: " . $request->getVS() . "<br>";
	echo "SS: " . $request->getSS() . "<br>";
	echo "Ucet: " . $request->getAccountPrefix() . "-" . $request->getAccount() . "/" . $request->getBankCode() . "<br>";
	echo "Parametre: ";
	print_r($request->getParams());
}