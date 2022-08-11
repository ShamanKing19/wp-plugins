<?php
/*
 * Plugin Name: Платёжный шлюз "Подели"
 * Plugin URI: None
 * Description: Оплата частями через сервис "Подели"
 * Author: Skillline
 * Author URI: None
 * Version: 1.0.0
 */

add_filter('woocommerce_payment_gateways', 'podeli_payment_gateway_class');
function podeli_payment_gateway_class($gateways)
{
	$gateways[] = 'WCPodeliPayment';
	return $gateways;
}


add_filter("woocommerce_admin_html_order_item_class", "hide_refund_amount", 10, 3);
function hide_refund_amount($class, $item, $order){
	$style = <<<EOF
		<style>	
			#woocommerce-order-items .refund-actions .do-api-refund .wc-order-refund-amount .woocommerce-Price-amount{
				display: none;
			}
			#woocommerce-order-items .refund-actions .do-api-refund .wc-order-refund-amount::before{
				content: "всей суммы";
			}
		</style>
	EOF;
	echo $style;
	return $class;
}


add_action('plugins_loaded', 'WCPodeliPayment');
function WCPodeliPayment()
{
	require_once plugin_dir_path( __FILE__ ) . '/payment-handler.php';

	// Регистрация маршрута
	add_action( 'rest_api_init', "register_routes");
	function register_routes()
	{
		register_rest_route("podeli", "notify", [
			'methods'  => 'POST',
			"callback" => "after_payment_webhook"
		]);
	}

	// Отключение платёжного шлюза по условию
	add_action("woocommerce_available_payment_gateways", "unset_podeli_gateway");
	function unset_podeli_gateway($available_gateways){
		if (!WC()->cart) {
		    return;
		}

		$cart_total_sum = (float)WC()->cart->get_total($context = null);
		$client = new WCPodeliPayment();
		$limit = $client->podeli_price_limit;

		if ($cart_total_sum > $limit) {
		    unset($available_gateways["podeli"]);
		}

		return $available_gateways;
	}


	// Отмена заказа или возврат при установки статуса "Отменён" у заказа
	add_action( 'woocommerce_order_status_changed', 'cancel_podeli_order', 10, 4);
	function cancel_podeli_order($id, $status_transition_from, $status_transition_to, $instance) {
		if (strtoupper($status_transition_to) != "CANCELLED") {
            return;
		}

		$gateway = new WCPodeliPayment();
		$client = $gateway->client;

		$order = wc_get_order($id);
		$xCorrelationId = $order->get_meta("x_correlation_id");
		$podeliOrderId = $order->get_meta("podeli_order_id");

		$infoResponse = $client->orderInfo($podeliOrderId);
        $podeliOrderStatus = $infoResponse["data"]["order"]["status"];

		// REFUND if was paid and then cancelled
		if (strtoupper($podeliOrderStatus) == "COMPLETED") {
			$reason = "shop";
			$orderItems = $order->get_items();
			foreach($orderItems as $item) {
				$itemsData[] = [
					"id" => $item["product_id"],
					"refundedQuantity" => $item->get_quantity(),
				];
			}

			// Данные запроса на возврат
			$refundData = [
				"order" => [
					"refund" => [
						"id" => $podeliOrderId,
						"initiator" => "client",
						"items" => $itemsData,
						"description" => $reason,
					]
				]
			];

			$refundResponse = $client->orderRefund($podeliOrderId, $refundData, $xCorrelationId);
            $refundStatus = $refundResponse["data"]["order"]["status"];

			if ($refundResponse && strtoupper($refundStatus) == "REFUNDED") {
				$order->update_status( "cancelled" );
			}
		}

		// CANCEL if wasn't paid and then cancelled
		$cancelAllowedStatuses = ["CREATED", "SCORING", "APPROVED", "WAIT_FOR_COMMIT"];
		if (in_array(strtoupper($podeliOrderStatus), $cancelAllowedStatuses)) {
			$cancelResponse = $client->orderCancel($podeliOrderId, $xCorrelationId);

            if ($cancelResponse["data"]["status"] == "CANCELLED") {
			    $order->update_status("cancelled");
			}
		}
	}


	// Сюда прилетает нотификация после завершения оплаты
	function after_payment_webhook() {
		$postBody = json_decode(file_get_contents('php://input'), true);
		if($postBody && isset($postBody['order'])) {
			$gateway = new WCPodeliPayment();
			$client =$gateway->client;

			$curOrder = $postBody["order"];
            $orderItems = $curOrder["items"];
			$podeliOrderId = $curOrder["id"];
			$clearOrderId = (int)explode("_", $podeliOrderId)[0];
			$amount = $curOrder["amount"];
			$prepaidAmount = $curOrder["prepaidAmount"];
			$order = wc_get_order($clearOrderId);
			$billingEmail = $order->get_billing_email();
			$billingPhone = $order->get_billing_phone();

			if(strtoupper($curOrder['statusCode']) == 'COMPLETED') {
				$order->update_status("processing");
            }

            // IF WAIT_FOR_COMMIT
			if(strtoupper($curOrder['statusCode']) == 'WAIT_FOR_COMMIT') {
				$commitRequest = [
					"order" => [
						"amount" => $amount,
						"prepaidAmount" => $prepaidAmount,
					]
				];

				$commitResponse = $client->orderCommit($podeliOrderId, $commitRequest);
            }

			// IF REJECTED
			if(strtoupper($curOrder['statusCode']) == 'REJECTED' || strtoupper($curOrder['statusCode']) == 'ARCHIVED') {
				$order->update_status("cancelled");
            }
		}
		return rest_ensure_response('true');
	}
}