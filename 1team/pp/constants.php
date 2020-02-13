<?php
/****************************************************
constants.php

This file
defines the parameters needed to make a PayPal API call.

Called by CallerService.php.
TO DO: Redo this based on: https://github.com/paypal/ipn-code-samples/tree/master/php
****************************************************/

// Endpoint: this is the server URL which you have to connect for submitting your API request
// Live: https://api-3t.paypal.com/nvp
// Sandbox: https://api-3t.sandbox.paypal.com/nvp
define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');

// USE_PROXY: Set this variable to TRUE to route all the API requests through proxy.
// like define('USE_PROXY',TRUE);

define('USE_PROXY',FALSE);
// PROXY_HOST: Set the host name or the IP address of proxy server.
// PROXY_PORT: Set proxy port.
//
// PROXY_HOST and PROXY_PORT will be read only if USE_PROXY is set to TRUE
define('PROXY_HOST', '127.0.0.1');
define('PROXY_PORT', '808');

/* Define the PayPal URL. This is the URL that the buyer is
   first sent to to authorize payment with their paypal account
   change the URL depending if you are testing on the sandbox
   or going to the live PayPal site
   For the sandbox, the URL is
   https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
   For the live site, the URL is
   https://www.paypal.com/webscr&cmd=_express-checkout&token=
   */
define('PAYPAL_URL', 'https://www.paypal.com/webscr&cmd=_express-checkout&token=');

// # Version: this is the API version in the request.
// # It is a mandatory parameter for each API request.
// # The only supported value at this time is 2.3

define('VERSION', '59.0');

?>
