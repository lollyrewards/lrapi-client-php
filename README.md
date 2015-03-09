# 1 Introduction
This document serves as a guide to 3rd party system developers who wish to integrate with Lolly Rewards Coupon API.
The Coupon API allows 3rd party systems 

- to validate coupon codes
- to mark coupon codes as used

# 2 Getting started with the Lolly Rewards Coupon API
Most interactions with lollyrewards.com require that requests are digitally signed. Using the php client library hides the technical details of the communication, however, one must generate and supply a __cryptographic keypair__ and associate it with an __authoritative email address__. 
The following sections describe how to do this. 
For the command line operations, please clone this repository 

	git clone TODO

and navigate to the top level directory.

## 2.1 Generate a key
You will need to pick an email address that will uniquely identify you (or your organisation) to lollyrewards.com.

> Please make sure, that this email inbox is sufficiently protected, as it will be used to verify your ownership of the cryptograhic key.

To generate a new cryptographic key, run
	
	./generate_key_and_cert.sh your_authoritative_email@example.com

Your new key will be saved to __your_authoritative_email@example.com.key__ .

## 2.2 Register your crypto key with your account
To be able to use this key, you first need to register it with lollyrewards.com.
Run
	
	php lr.php your_email@example.com register your_name_or_organization

On success, lollyrewards.com returns a URI that identifies the cryptographic key.
A confirmation email with a link is sent to the email address above.

## 2.3 Confirm your key
In order to use the crypto key, you need to confirm that you indeed control the email address you provided. 
Check your inbox for the confirmation email, click on __Confirm your account__ and examine the link in the address bar.
Ignore the password dialog and look for the query parameter __n=[some uuid]__ . 
Copy and paste this confirmation code, as you will need it as a parameter for the next command:
	
	./lr your_email@example.com confirm [confirmation_code]

You should get a *{"status":"ok"}* response, which indicates that you are now ready to send digitally signed requests to lollyrewards.com using the client library.

## 2.4. Changing your registered crypto key
You can change your crypto key at any point e.g if you lost it or if you think it has become compromised.
All you need to do is to repeat steps 2.1-2.3 again.

# 3 Integration test
Now that you have a registered a crypto key, you can run an automated test to verify that everything works as expected.
The library comes with a test script located in the *test/* directory.

To run it
	
	php test/test_coupon_api.php your_registered_email@example.com

If all tests are successful, you will get the
	
	ALL TESTS [PASS]

message printed to the console.
	
# 4 Using the Lolly Rewards Coupon API
At this point, you should have a crypto key registered with lollyrewards.com and successfully ran the integration test suite. (See the previous section.)
The Coupon API allows a third party to verify a status of a coupon code provided a customer at the checkout, as well as marking a coupon code as used.
The `LollyRewardsCouponAPI` class implements the two operations:
	
	$your_key_uri = http://lollyrewards.com/pubkey/12345678; // you received this in step 2.2, check the .accounts file
	$path_crypto_key_file = 'your_registered_email@example.com.key'; // the generated cryptographic key
	$lrCApi = new LollyRewardsCouponAPI($your_key_uri, $path_crypto_key_file);
	// checking the status of a coupon that belongs to an offer
	$offer_code = "TESTOFFER"; // this code identifes the offer to which the coupon code belongs to
	$coupon_code = "TXXAVAIL"; // a test coupon code that is always available - This code is normally entered by the user at checkout.
	$coupon_status = $lrCApi->check_coupon_code($offer_code, $coupon_code);	
	$coupon_status == array("result" => ":success",
             				"coupon" => "TXXAVAIL",
             			    "status" => ":coupon/available");
							
	// OR if the coupon is taken
	$coupon_status = $lrCApi->check_coupon_code($offer_code, "TXXTAKEN");	
	$coupon_status == array("result" => ":success",
	             			"coupon" => "TXXTAKEN",
	             		   	"status" => ":coupon/taken");
							
	// if there is no such coupon code known for the give offer
	$coupon_status = $lrCApi->check_coupon_code($offer_code, "TUNKNOWN");		
	$coupon_status == array("result" => ":error",
	             			"coupon" => "TUNKNOWN",
	             		   	"error" => ":coupon/unknown");
	
	// mark coupon code as used, i.e. lock coupon code
	$coupon_status = $lrCApi->lock_coupon_code($offer_code, $coupon_code);	
	// if success, the response will be
	$coupon_status == array("result" => ":success",
	             			"coupon" => "TXXAVAIL",
	             		   	"status" => ":coupon/taken");
	// if the coupon has been marked as used previously, you will receive an error
	$coupon_status = $lrCApi->lock_coupon_code($offer_code, "TXXTAKEN");	
	$coupon_status == array("result" => ":error",
	             			"coupon" => "TXXTAKEN",
	             		    "error" => ":coupon/already-taken")
	
NOTE: If the coupon is a once time use coupon it is important that the coupon is marked as used.

Please check the integration test script for a comprehensive list of sucess and error responses.

# Credits
This library comes bundled with the open source library [phpseclib](http://phpseclib.sourceforge.net/), distributed under the MIT License.