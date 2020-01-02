<?php

// This snippet (and some of the curl code) due to the Facebook SDK.
if (!function_exists('curl_init')) {
  throw new Exception('Stripe needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Stripe needs the JSON PHP extension.');
}

// Stripe singleton
require(dirname(__FILE__) . '/Stripe/Stripe.php');

// Utilities
require(dirname(__FILE__) . '/Stripe/Util/AutoPagingIterator.php');
require(dirname(__FILE__) . '/Stripe/Util/LoggerInterface.php');
require(dirname(__FILE__) . '/Stripe/Util/DefaultLogger.php');
require(dirname(__FILE__) . '/Stripe/Util/RandomGenerator.php');
require(dirname(__FILE__) . '/Stripe/Util/RequestOptions.php');
require(dirname(__FILE__) . '/Stripe/Util/Set.php');
require(dirname(__FILE__) . '/Stripe/Util/Util.php');

// HttpClient
require(dirname(__FILE__) . '/Stripe/HttpClient/ClientInterface.php');
require(dirname(__FILE__) . '/Stripe/HttpClient/CurlClient.php');

// Errors
require(dirname(__FILE__) . '/Stripe/Error/Base.php');
require(dirname(__FILE__) . '/Stripe/Error/Api.php');
require(dirname(__FILE__) . '/Stripe/Error/ApiConnection.php');
require(dirname(__FILE__) . '/Stripe/Error/Authentication.php');
require(dirname(__FILE__) . '/Stripe/Error/Card.php');
require(dirname(__FILE__) . '/Stripe/Error/Idempotency.php');
require(dirname(__FILE__) . '/Stripe/Error/InvalidRequest.php');
require(dirname(__FILE__) . '/Stripe/Error/Permission.php');
require(dirname(__FILE__) . '/Stripe/Error/RateLimit.php');
require(dirname(__FILE__) . '/Stripe/Error/SignatureVerification.php');

// OAuth errors
require(dirname(__FILE__) . '/Stripe/Error/OAuth/OAuthBase.php');
require(dirname(__FILE__) . '/Stripe/Error/OAuth/InvalidClient.php');
require(dirname(__FILE__) . '/Stripe/Error/OAuth/InvalidGrant.php');
require(dirname(__FILE__) . '/Stripe/Error/OAuth/InvalidRequest.php');
require(dirname(__FILE__) . '/Stripe/Error/OAuth/InvalidScope.php');
require(dirname(__FILE__) . '/Stripe/Error/OAuth/UnsupportedGrantType.php');
require(dirname(__FILE__) . '/Stripe/Error/OAuth/UnsupportedResponseType.php');

// API operations
require(dirname(__FILE__) . '/Stripe/ApiOperations/All.php');
require(dirname(__FILE__) . '/Stripe/ApiOperations/Create.php');
require(dirname(__FILE__) . '/Stripe/ApiOperations/Delete.php');
require(dirname(__FILE__) . '/Stripe/ApiOperations/NestedResource.php');
require(dirname(__FILE__) . '/Stripe/ApiOperations/Request.php');
require(dirname(__FILE__) . '/Stripe/ApiOperations/Retrieve.php');
require(dirname(__FILE__) . '/Stripe/ApiOperations/Update.php');

// Plumbing
require(dirname(__FILE__) . '/Stripe/ApiResponse.php');
require(dirname(__FILE__) . '/Stripe/StripeObject.php');
require(dirname(__FILE__) . '/Stripe/ApiRequestor.php');
require(dirname(__FILE__) . '/Stripe/ApiResource.php');
require(dirname(__FILE__) . '/Stripe/SingletonApiResource.php');

// Stripe API Resources
require(dirname(__FILE__) . '/Stripe/Account.php');
require(dirname(__FILE__) . '/Stripe/AlipayAccount.php');
require(dirname(__FILE__) . '/Stripe/ApplePayDomain.php');
require(dirname(__FILE__) . '/Stripe/ApplicationFee.php');
require(dirname(__FILE__) . '/Stripe/ApplicationFeeRefund.php');
require(dirname(__FILE__) . '/Stripe/Balance.php');
require(dirname(__FILE__) . '/Stripe/BalanceTransaction.php');
require(dirname(__FILE__) . '/Stripe/BankAccount.php');
require(dirname(__FILE__) . '/Stripe/BitcoinReceiver.php');
require(dirname(__FILE__) . '/Stripe/BitcoinTransaction.php');
require(dirname(__FILE__) . '/Stripe/Card.php');
require(dirname(__FILE__) . '/Stripe/Charge.php');
require(dirname(__FILE__) . '/Stripe/Collection.php');
require(dirname(__FILE__) . '/Stripe/CountrySpec.php');
require(dirname(__FILE__) . '/Stripe/Coupon.php');
require(dirname(__FILE__) . '/Stripe/Customer.php');
require(dirname(__FILE__) . '/Stripe/Dispute.php');
require(dirname(__FILE__) . '/Stripe/EphemeralKey.php');
require(dirname(__FILE__) . '/Stripe/Event.php');
require(dirname(__FILE__) . '/Stripe/ExchangeRate.php');
require(dirname(__FILE__) . '/Stripe/FileUpload.php');
require(dirname(__FILE__) . '/Stripe/Invoice.php');
require(dirname(__FILE__) . '/Stripe/InvoiceItem.php');
require(dirname(__FILE__) . '/Stripe/LoginLink.php');
require(dirname(__FILE__) . '/Stripe/Order.php');
require(dirname(__FILE__) . '/Stripe/OrderReturn.php');
require(dirname(__FILE__) . '/Stripe/Payout.php');
require(dirname(__FILE__) . '/Stripe/Plan.php');
require(dirname(__FILE__) . '/Stripe/Product.php');
require(dirname(__FILE__) . '/Stripe/Recipient.php');
require(dirname(__FILE__) . '/Stripe/RecipientTransfer.php');
require(dirname(__FILE__) . '/Stripe/Refund.php');
require(dirname(__FILE__) . '/Stripe/SKU.php');
require(dirname(__FILE__) . '/Stripe/Source.php');
require(dirname(__FILE__) . '/Stripe/SourceTransaction.php');
require(dirname(__FILE__) . '/Stripe/Subscription.php');
require(dirname(__FILE__) . '/Stripe/SubscriptionItem.php');
require(dirname(__FILE__) . '/Stripe/ThreeDSecure.php');
require(dirname(__FILE__) . '/Stripe/Token.php');
require(dirname(__FILE__) . '/Stripe/Topup.php');
require(dirname(__FILE__) . '/Stripe/Transfer.php');
require(dirname(__FILE__) . '/Stripe/TransferReversal.php');

// OAuth
require(dirname(__FILE__) . '/Stripe/OAuth.php');

// Webhooks
require(dirname(__FILE__) . '/Stripe/Webhook.php');
require(dirname(__FILE__) . '/Stripe/WebhookSignature.php');
