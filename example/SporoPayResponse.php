<pre><?php

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/bootstrap.php';
require_once LIBS_DIR . '/SporoPay/Response.php';

$secretKey = 'Z3qY08EpvLlAAoMZdnyUdQ==';
$response = new Epayment\SporoPay\Response;

if ($response->validate() && $response->verifySignature($secretKey)) {

	switch ($response->getPaymentResponse()) {
		case Epayment\SporoPay\Response::SUCCESS:
			echo "<b>Vsetko prebehlo OK.</b>";
			break;
		
		case Epayment\SporoPay\Response::FAIL:
			echo "<b>Zapísať platbu ako neúspešnú, informovať klienta o nezbehnutí splatby.</b>";
			break;

		case Epayment\SporoPay\Response::TIMEOUT:
			echo "<b>Zapísať platbu s nedefinovaným výsledkom, príchod platby je nutné overiť manuálne.</b>";
			break;
	}

	echo "<br><br>";
	echo "Suma: " . $response->getPrice() . "<br>";
	echo "Mena: " . $response->getCurrency() . "<br>";
	echo "VS: " . $response->getVS() . "<br>";
	echo "SS: " . $response->getSS() . "<br>";
	echo "CS: " . $response->getCS() . "<br>";
	echo "Ucet: " . $response->getClientAccountPrefix() . "-" . $response->getClientAccount() . "/" . $response->getClientBankCode() . "<br>";
	echo "Parametre: ";
	print_r($response->getParams());
}