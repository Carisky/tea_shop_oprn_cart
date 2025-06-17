<?php
// Cron script to send review reminder emails 7 days after order

// Version
define('VERSION', '3.0.2.0');

// Load configuration and OpenCart startup for autoloading
require_once(dirname(__FILE__) . '/../config.php');
require_once(DIR_SYSTEM . 'startup.php');

// Database connection
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Load store settings for default store (store_id = 0)
$settings = array();
$query = $db->query("SELECT `key`, `value`, `serialized` FROM `" . DB_PREFIX . "setting` WHERE store_id = '0'");
foreach ((array)$query->rows as $row) {
    $settings[$row['key']] = $row['serialized'] ? json_decode($row['value'], true) : $row['value'];
}

// Prepare Mail object using store configuration
$mail = new Mail($settings['config_mail_engine'] ?? 'mail');
$mail->parameter = $settings['config_mail_parameter'] ?? '';
$mail->smtp_hostname = $settings['config_mail_smtp_hostname'] ?? '';
$mail->smtp_username = $settings['config_mail_smtp_username'] ?? '';
$mail->smtp_password = isset($settings['config_mail_smtp_password']) ? html_entity_decode($settings['config_mail_smtp_password'], ENT_QUOTES, 'UTF-8') : '';
$mail->smtp_port = $settings['config_mail_smtp_port'] ?? 25;
$mail->smtp_timeout = $settings['config_mail_smtp_timeout'] ?? 5;

$from_email = $settings['config_email'] ?? '';
$store_name = $settings['config_name'] ?? '';

// Select orders from 7 days ago
$order_query = $db->query("SELECT order_id, firstname, email FROM `" . DB_PREFIX . "order` WHERE DATE(date_added) = DATE(NOW() - INTERVAL 7 DAY)");

foreach ((array)$order_query->rows as $order) {
    // Get products for this order
    $product_query = $db->query("SELECT product_id, name FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order['order_id'] . "'");

    // Build HTML message
    $html  = '<h1>Jak podobał Ci się Twój zakup? Zgarnij 5% rabatu za opinię 🌟</h1>';
    $html .= '<h2>Cześć ' . htmlspecialchars($order['firstname'], ENT_QUOTES, 'UTF-8') . ',</h2>';
    $html .= '<p>Dziękujemy za zakupy w naszym sklepie! Mamy nadzieję, że produkty, które u nas zamówiłeś/-aś, spełniły Twoje oczekiwania i sprawiły Ci dużo radości.</p>';
    $html .= '<p>Chcielibyśmy Cię poprosić o zostawienie krótkiej opinii o zakupionych produktach. Twoja opinia pomaga innym klientom w wyborze, a nam – w dalszym doskonaleniu oferty.</p>';
    $html .= '<p>🎁 W ramach podziękowania otrzymasz od nas kod rabatowy -5% na kolejne zakupy:<br /><strong>DZIĘKUJEMY5</strong></p>';
    $html .= '<p><b>Kliknij w produkt, aby dodać opinię:</b></p>';
    $html .= '<ul>';

    foreach ((array)$product_query->rows as $product) {
        $link = HTTP_SERVER . 'index.php?route=product/product&product_id=' . $product['product_id'];
        $html .= '<li><a href="' . $link . '">' . htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') . '</a></li>';
    }

    $html .= '</ul>';
    $html .= '<p>Dziękujemy, że jesteś z nami!<br />Zespół Jedwabnego szlaku.</p>';

    // Send email
    $mail->setTo($order['email']);
    $mail->setFrom($from_email);
    $mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
    $mail->setSubject('Jak podobał Ci się Twój zakup? Zgarnij 5% rabatu za opinię 🌟');
    $mail->setHtml($html);
    $mail->send();
}