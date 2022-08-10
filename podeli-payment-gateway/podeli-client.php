<?php

class PodeliClient {
	private string $urlPath;
	private string $login;
	private string $password;
	private string $crt_certificate;
    private string $key_certificate;
    private bool $test_mode;

    public function __construct(
        $urlPath,
        $login,
        $password,
        $crt_certificate,
        $key_certificate,
        $testMode
    ) {
		$this->urlPath = $urlPath;
		$this->login = $login;
		$this->password = $password;
		$this->crt_certificate = $crt_certificate;
		$this->key_certificate = $key_certificate;
		$this->test_mode = $testMode;
	}

	private function createRequest(string $url, array $headers = [], bool $post = false, array $data = []): array
	{
		$uuidString = $this->generateUUID();
		$headersList = [
			'accept: application/json',
			'Content-Type: application/json',
			'x_correlation_id' => 'X-Correlation-ID: ' . $uuidString,
		];

		if (array_key_exists('x_correlation_id', $headers)) {
			unset($headersList['x_correlation_id']);
		}

		$headersList = array_merge($headersList, $headers);

		$ch = curl_init();
		$urlFull = $this->urlPath . $url;
		curl_setopt($ch, CURLOPT_URL, $urlFull);
		curl_setopt($ch, CURLOPT_USERPWD, $this->login . ":" . $this->password);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headersList);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		if ($post) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_PORT, 443);

		if($this->test_mode) {
            $crt = __DIR__ . '/test.crt';
            $key = __DIR__ . '/test.key';
        } else {
            $crt = __DIR__ . '/crt.txt';
            $fp = fopen($crt, "w");
            fwrite($fp, $this->crt_certificate);
            fclose($fp);

            $key = __DIR__ . '/key.txt';
            $fp = fopen($key, "w");
            fwrite($fp, $this->key_certificate);
            fclose($fp);
        }

		curl_setopt($ch, CURLOPT_SSLCERT, $crt);
        curl_setopt($ch, CURLOPT_SSLKEY, $key);

		$response = curl_exec($ch);

		if (curl_exec($ch) === false) {
			$error = curl_error($ch);
		}

		$httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$respData = json_decode($response, true);
		if(!isset($respData['redirectUrl'])) {
            file_put_contents(__DIR__ . "/error_log.txt",
                'Response error: '.print_r($respData, true));
        }

		$result = [
			'url' => $urlFull,
			'status' => $httpReturnCode,
			'x_correlation_id' => $headers['x_correlation_id'] ?? $uuidString,
			'data' => json_decode($response, true)
		];

		curl_close($ch);

		return $result;
	}


	public function orderCreate(array $data): array
	{
		return $this->createRequest('orders/create', [], true, $data);
	}


	public function orderInfo($orderId): array
	{
		return $this->createRequest('orders/' . $orderId . '/info');
	}


	public function orderCommit($orderId, array $data, $correlationId = false): array
	{
		$headers = [];

		if ($correlationId) {
			$headers = [
				'x_correlation_id' => 'X-Correlation-ID: ' . $correlationId
			];
		}

		return $this->createRequest('orders/' . $orderId . '/commit', $headers, true, $data);
	}


	public function orderRefund($orderId, array $data, $correlationId = false): array
	{
		$headers = [];

		if ($correlationId) {
			$headers = [
				'x_correlation_id' => 'X-Correlation-ID: ' . $correlationId
			];
		}

		return $this->createRequest('orders/' . $orderId . '/refund', $headers, true, $data);
	}


	public function orderCancel($orderId, $correlationId = false): array
	{
		$headers = [];

		if ($correlationId) {
			$headers = [
				'x_correlation_id' => 'X-Correlation-ID: ' . $correlationId
			];
		}

		return $this->createRequest('orders/' . $orderId . '/cancel', $headers, true, [
			"cancellationInitiator" => "shop",
		]);
	}


	private function generateUUID(): string
	{
		$data = openssl_random_pseudo_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}