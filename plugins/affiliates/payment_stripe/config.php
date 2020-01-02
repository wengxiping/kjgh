<?php

if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}
require_once(JPATH_SITE . DS . 'plugins' . DS . 'affiliates' . DS . 'payment_stripe' . DS . 'stripe' . DS . 'init.php');

$stripe = array(
    "secret_key"      => trim($vars->secret_key),
    "publishable_key" => trim($vars->publishable_key)
);

\Stripe\Stripe::setApiKey($stripe['secret_key']);