<pre><?php

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/bootstrap.php';
require_once LIBS_DIR . '/TatraPay/Response.php';

$secretKey = '87651234';
$response = new Epayment\TatraPay\Response;

if ($response->validate() && $response->verifySignature($secretKey)) {

	switch ($response->getPaymentResponse()) {
		case Epayment\TatraPay\Response::SUCCESS:
			echo "<b>Vsetko prebehlo OK.</b>";
			break;
		
		case Epayment\TatraPay\Response::FAIL:
			echo "<b>Zapísať platbu ako neúspešnú, informovať klienta o nezbehnutí splatby.</b>";
			break;

		case Epayment\TatraPay\Response::TIMEOUT:
			echo "<b>Zapísať platbu s nedefinovaným výsledkom, príchod platby je nutné overiť manuálne.</b>";
			break;
	}

	echo "<br><br>";
	echo "VS: " . $response->getVS() . "<br>";
	echo "SS: " . $response->getSS() . "<br>";
}