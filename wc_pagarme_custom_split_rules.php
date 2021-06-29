<?php

/**
 * Plugin Name: Custom Split Rules - Pagar.me for WooCommerce
 * Plugin URI: 
 * Description: Custom Split Rules - Pagar.me for WooCommerce
 * Author: 
 * Author URI: 
 * Version: 1.0
 */

/**
 * Split rules for Pagar.me.
 *
 * @param  array    $data  Transacion data.
 * @return array
 */
 function wc_pagarme_custom_split_rules($data) {
	// Ideia principal da função: receber os dados que serão enviados à API da Pagar.me ($data), 
	// adicionar as split_rules, que é um array de arrays 
	// e retornar esse $data atualizado ao final da função
	 
	// Dados do pedido do Woocommerce
	$order_id = $data['metadata']['order_number'];
	$order = new WC_Order($order_id);
	$items = $order->get_items();	
	$order_total = $order->get_total('pagarme-split'); 
	$total_left = $order_total;
	
	// Log to a WC logger
	//$log = new WC_Logger();
 	//$log_entry = 'Teste split...' . implode(",", $items);	
	//$log->add('woocommerce-pagarme-split', $log_entry);
	
	$split_rules = array();
	
	foreach ($items as $item) {
		$product_id = $item['product_id'];
		
		// Melhoria necessária: ler a configuração de cada produto
		if ($product_id == 24 || $product_id == 21) {
			if ($product_id == 24) { 
				$recipient_id = 're_ck69zvoif0cdg4a6eh69dup31'; 
				$amount = $item['total'] * 0.25;
			}
			
			if ($product_id == 21) { 
				$recipient_id = 're_ck69zvg5v0cd84a6elk9x2b3z'; 
				$amount = $item['total'] * 0.25;
			}
			
			$total_left -= $amount;  // Desconta do valor do recebedor principal.
			
			$split_rules[] = array(
				'recipient_id' => $recipient_id, 
				'amount' => $amount * 100,  // valor em centavos
				'liable' => true, 
				'charge_processing_fee' => true
			);
		}	
	}
	
	// Recebedor principal
	// Melhoria necessária: ler uma configuração geral da loja - recebedor principal
	$recipient_id = 're_ck69zzntl0cm14f6eup5y9dqs'; 
	$amount = $total_left;
	
	$split_rules[] = array(
		'recipient_id' => $recipient_id, 
		'amount' => $amount * 100,  // valor em centavos
		'liable' => true, 
		'charge_processing_fee' => true
	);
	
	
	$data['split_rules'] = $split_rules;											 

	return $data;
}

// Faz com que essa função seja chamada nas actions do pelo plugin da Pagar.me
add_action('wc_pagarme_checkout_data', 'wc_pagarme_custom_split_rules', 10, 1); // action utilizada no Checkout Pagar.me (modal)
add_action('wc_pagarme_transaction_data', 'wc_pagarme_custom_split_rules', 10, 1); // action utilizada no Checkout Transparente
