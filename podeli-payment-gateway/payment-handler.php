<?php

use Open\Api\OpenClient;

require_once __DIR__ . '/podeli-client.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/business-ru/open-api-sdk-php/src/OpenClient.php';

class WCPodeliPayment extends WC_Payment_Gateway
{
	public WC_ORDER $order;
	public PodeliClient $client;
	private string $urlPath;
	private string $login;
	private string $password;
    public string $crt_certificate;
    public string $key_certificate;
    private string $apiURL;
    private string $appID;
    private string $secretKey;
    public OpenClient $openApiClient;
	public string $activateChecks;


    public function __construct()
	{
		$this->id = 'podeli'; // ID платёжного шлюза
		$this->icon = ''; // URL иконки, которая будет отображаться на странице оформления заказа рядом с этим методом оплаты
		$this->has_fields = true; // если нужна собственная форма ввода полей карты
		$this->method_title = 'Платёжный шлюз "Подели"';
		$this->method_description = 'Оплата заказа частями'; // будет отображаться в админке
		$this->urlPath = 'https://api.podeli.ru/partners/v1/';
		$this->prefix = 'PODELI_ORDER_';

		$this->supports = [
			'products',
			'refunds'
		];

		$this->init_form_fields();

		$this->init_settings();
		$this->login = $this->get_option('login');
		$this->password = $this->get_option('password');
		$this->title = $this->get_option('title');
		$this->activity = $this->get_option('activity');
		$this->test_mode = $this->get_option('test_mode');

		$this->crt_certificate = $this->get_option('crt_certificate');
		$this->key_certificate = $this->get_option('key_certificate');

		// Чеки Бизнес.ру
		$this->activateChecks = $this->get_option('generate_checks');
		if ($this->activateChecks == "yes") {
			$this->apiURL = "https://check.business.ru/open-api/v1/";
			$this->appID = $this->get_option('app_id'); //"c6e869bc-2f53-4487-9420-36d660dd7861";
			$this->secretKey = $this->get_option('secret_key'); //"kJHMqcGmCU4IEr183du5yZ9elsFh6V7L";
			$this->openApiClient = new OpenClient($this->apiURL, $this->appID, $this->secretKey);
		}

		$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		$this->notification_url = $url . "/wp-json/podeli/notify";

		$this->fail_url = $this->get_option('fail_url');
		$this->success_url = $this->get_option('success_url');
		$this->podeli_price_limit = (int)$this->get_option('podeli_price_limit');

		if (strtolower($this->test_mode) == 'yes') {
			$this->urlPath = 'https://api-dev.podeli.ru/partners/v1/';
			$this->login = 'lex-test';
			$this->password = 'test3';
			$this->crt_certificate = file_get_contents(__DIR__ . '/test.crt');
			$this->key_certificate = file_get_contents(__DIR__ . '/test.key');
		}

		$this->client = new PodeliClient(
		    $this->urlPath,
            $this->login,
            $this->password,
            $this->crt_certificate,
            $this->key_certificate,
            strtolower($this->test_mode) == 'yes'
        );



		// Сохранение введённых данных
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, [
		    $this,
            'process_admin_options'
        ]);
	}


	public function init_form_fields()
	{
		$this->form_fields = [
			'title' => [
				'title' => 'Подели',
				'label' => 'text',
				'description' => '',
				'default' => 'Оплата через сервис "Подели',
				'desc_tip' => true,
			],
			'test_mode' => [
				'title' => 'Подели - Тестовый режим',
				'label' => 'Тестовый режим',
				'type' => 'checkbox',
                'desc_tip' => 'В тестовом режиме авторизационные данные не требуются',
				'default' => 'yes'
			],
			'login' => [
				'title' => 'Подели - Логин',
				'type' => 'text',
			],
			'password' => [
				'title' => 'Подели - Пароль',
				'type' => 'text',
			],
			'fail_url' => [
				'title' => 'Подели - Ссылка: fail_url',
                'desc_tip' => 'Ссылку на страницу в случае не успешной оплаты',
				'type' => 'text',
			],
			'success_url' => [
				'title' => 'Подели - Ссылка: success_url',
                'desc_tip' => 'Ссылку на страницу после успешной оплаты',
				'type' => 'text',
			],
			'podeli_price_limit' => [
				'title' => 'Подели - Максимальная сумма заказа',
				'type' => 'price',
			],
			'crt_certificate' => [
				'title' => 'Подели - .crt сертификат',
                'desc_tip' => 'Содержимое файла формата crt',
				'css' => 'height: 300px;',
				'type' => 'textarea',
			],
            'key_certificate' => [
				'title' => 'Подели - .key сертификат',
                'desc_tip' => 'Содержимое файла формата key',
				'css' => 'height: 300px;',
				'type' => 'textarea',
			],'generate_checks_title' => [
				'title' => 'Настройки генерации чеков Бизнес.ру',
                'desc_tip' => '',
				'css' => 'display:none;',
				'type' => 'text',
			],
			'generate_checks' => [
				'title' => 'Генерация чеков',
				'label' => 'Генирировать электронные чеки',
				'type' => 'checkbox',
				'desc_tip' => 'Включить или выключить генерацию чеков',
				'default' => 'no'
			],
			'app_id' => [
				'title' => 'ID Интеграции (appID)',
				'type' => 'text',
				'desc_tip' => 'Находится в интеграциях Бизнес.ру https://check.business.ru/integrations?route=integrations',
			],
			'secret_key' => [
				'title' => 'Секретный ключ интеграции',
				'type' => 'text',
				'desc_tip' => 'Находится в интеграциях Бизнес.ру https://check.business.ru/integrations?route=integrations',
			],

		];

	}


	public function process_payment($order_id)
	{
		$order = wc_get_order($order_id);
		$podeliOrderId = $order_id."_".wp_generate_password(6, false);

		// Случай для платной доставки
		$shipping_total = (int)$order->get_shipping_total();
		$shipping_product = [
			"id" => 9999999999,
			"article" => "",
			"name" => "Доставка",
			"quantity" => 1,
			"amount" => $shipping_total,
			"prepaidAmount" => 0
		];

		$amount = 0;
		$prepaidTotalAmount = 0;
		$items = WC()->cart->get_cart();
		$podeliItems = [];
		if ($shipping_total) {
			$podeliItems[] = $shipping_product;
		}

		// Заполнение $items_data для отправки
		foreach ($items as $item) {
			$id = $item["product_id"];
			$product = wc_get_product($id);

            $itemName = $product->get_name();
            $itemAmount = round($item["line_total"] / $item["quantity"], 2);
            $itemPrepaidAmount = round(($item["line_subtotal"] - $item["line_total"]) / $item["quantity"], 2);
            $itemQuantity = $item["quantity"];

			$podeliItems[] = [
				"id" => $id,
				"article" => "",
				"name" => $itemName,
				"quantity" => $itemQuantity,
				"amount" => $itemAmount,
				"prepaidAmount" => $itemPrepaidAmount,
			];
		}

		// Расчёт общей суммы и скидки
		foreach ($podeliItems as $item) {
			$amount += $item["amount"] * $item["quantity"];
			$prepaidTotalAmount += $item["prepaidAmount"] * $item["quantity"];
		}

		// Данные для отправки запроса на создание
		$getPhone = $order->get_billing_phone();
		$phone = ltrim($getPhone, "+");

		if ($phone[0] == "8") {
			$phone[0] = "7";
		}

		$data = [
			"order" => [
				"id" => $podeliOrderId,
				"amount" => round($amount, 2),
				"prepaidAmount" => round($prepaidTotalAmount, 2),
				"items" => $podeliItems,
			],
			"clientInfo" =>  [
				"firstName" => $order->get_billing_first_name(),
				"lastName" => $order->get_billing_last_name(),
				"phone" => $phone,
				"email" => $order->get_billing_email()
			],
			"notificationUrl" => $this->get_option('notification_url'),
			"failUrl" => $this->get_option('fail_url'),
			"successUrl" => $this->get_option('success_url')
		];

		if(!$getPhone || empty($getPhone)) {
			unset($data['clientInfo']);
		}

		$response = $this->client->orderCreate($data);

        // Успешная оплата
		if ($response && isset($response["data"]["redirectUrl"])) {
			// Сохранение x_correlation_id в заказ через мета данные
			$order->update_meta_data("podeli_order_id", $podeliOrderId);
			$order->update_meta_data("x_correlation_id", $response["x_correlation_id"]);
			$order->save_meta_data();
			$redirect_url = $response["data"]["redirectUrl"];

			return [
				"result" => "success",
				"redirect" => $redirect_url,
			];
		}
	}


	public function process_refund( $orderId, $amount = null, $reason = '' ) {
		$order = wc_get_order($orderId);
		$orderItems = $order->get_items();
		$xCorrelationId = $order->get_meta("x_correlation_id");
		$podeliOrderId = $order->get_meta("podeli_order_id");
		$orderTotal = $order->get_total();
		$orderShipping = $order->get_shipping_total() ?? 0;
		$podeliItems = [];
		$checkItems = [];

		$shippingItem = [
			"count" => 1,
			"price" => $orderShipping,
			"sum" => $orderShipping,
			"name" => "Доставка",
			"nds_value" => 20,
			"nds_not_apply" => false,
			"payment_mode" => 1,
			"item_type" => 1
		];

		if ((int)$orderShipping != 0) {
			$checkItems[] = $shippingItem;
		}

		// Формирование данных к отправке
		foreach($orderItems as $item) {
			$itemProductID = $item["product_id"];
			$itemName = $item->get_name();
			$itemQuantity = $item->get_quantity();
			$itemTotal = $item->get_total();

			$podeliItems[] = [
				"id" => $itemProductID,
				"refundedQuantity" => $itemQuantity,
			];

			$checkItems[] = [
				"count" => $itemQuantity,
				"price" => $itemTotal,
				"sum" => $itemTotal * $itemQuantity,
				"name" => $itemName,
				"nds_value" => 20,
				"nds_not_apply" => false,
				"payment_mode" => 1,
				"item_type" => 1
			];
		}

		// Данные запроса на возврат для Подели
		$podeliRefundData = [
			"order" => [
				"refund" => [
					"id" => $podeliOrderId,
					"initiator" => "client",
					"items" => $podeliItems,
					"description" => $reason,
				]
			]
		];

		$response = $this->client->orderRefund($podeliOrderId, $podeliRefundData, $xCorrelationId);

		// Успешный возврат
		if ($response && strtoupper($response["data"]["order"]["status"]) == "REFUNDED" ) {
			$billingEmail = $order->get_billing_email() ?? "";
			$billingPhone = $order->get_billing_phone() ?? "";

			// Создание чека
			$command = [
				"author" => $_SERVER["HTTP_HOST"],
				"smsEmail54FZ" => $billingEmail,
				"c_num" => $billingPhone,
				"payed_cashless" => $orderTotal,
				"goods" => $checkItems
			];

			if ($this->activateChecks == "yes"){
				$orderRefundCheckResponse = $this->openApiClient->printPurchaseReturn($command);
			}

			$order->update_status("refunded");

			return true;
		}

		return false;
	}
}