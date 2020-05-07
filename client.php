<?php
error_reporting(E_ALL & ~E_NOTICE);
// Load our Composer-installed dependencies
require_once 'vendor/autoload.php';

// Create an object representing the provider.
// Note how we have to provide our client ID and secret, and also
// the /authorize and /access_token endpoints.
// We also supply the redirect URL in case we didn't specify it when we
// registered our app.

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId' => '75f94cab74c57248e9bd6b772649fcf6', //advice https://www.oauth.com/oauth2-servers/client-registration/client-id-secret/ // used 'openssl rand -hex 32' to generate id and secret
    'clientSecret' => 'a5181c909739e73084015ef356283a79bc5ff00fc8d4189a65bd972a8a34313b',
    'redirectUri' => 'http://localhost/oauth/random_client/client.php',
    'urlAuthorize' => 'http://localhost/oauth/oauth_server/authorisation_server/authorize',
    'scope' => 'read',
    'urlAccessToken' => 'http://localhost/oauth/oauth_server/authorisation_server/access_token',
    'urlResourceOwnerDetails' => 'http://localhost/oauth/oauth_server/resource_server',
]);

session_start();

// If we don't have an authorization code then get one
if (!isset($_GET["code"])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header("Location: $authUrl");
} // Check given state against previously stored one to mitigate Cross-Site Request Forgery (CSRF) attack
elseif (empty($_GET['state']) || (isset($_SESSION['state']) && $_GET['state'] != $_SESSION['state'])) {
    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }

    exit('Invalid state');
} else {

    ?>
<!DOCTYPE html>
<html>
<head>
<title>OAuth Forums</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<style>

.wrapper{
    height:80vh;
    max-width:1000px;
}
span{
    word-break: break-all;
}
img{
    width:70px;
}
.msg-board-wrapper{
    background-color:#f4f4f4;
    min-height:150px;
    width:100%;
}

</style>
</head>
<body>
<div class="container">
<div class="wrapper mt-5">

<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4>Oauth authenticated msgBoard</h4>
        <img class="img-responsive" src="./circle-cropped.png" alt="oauth2logo">
    </div>
</div>



<?php
try {
        if (!isset($_SESSION["accessToken"])) {
            //    No saved access token, getting one
            $accessToken = $provider->getAccessToken('authorization_code', ["code" => $_GET["code"]]);
            $_SESSION["accessToken"] = $accessToken;
        } else {
            // Token already set
            $accessToken = $_SESSION["accessToken"];
        }

        $request = $provider->getAuthenticatedRequest(
            'POST',
            'http://localhost/oauth/oauth_server/resource_server/read',
            $accessToken
        );

        $client = new GuzzleHttp\Client();
        //Making a request to the Resource server API using access token
        $response = json_decode((string) $client->send($request)->getBody());

        if ($response) {
            $msgRequest = $provider->getAuthenticatedRequest(
                'POST',
                'http://localhost/oauth/oauth_server/resource_server/messages',
                $accessToken
            );
            $msgResponse = json_decode((string) $client->send($msgRequest)->getBody());
            echo "
            <div class='row mt-2'>
            <div class='col-6 text-success'><span>User Authenticated -> $response->email </div>
            <div class='col-6 text-warning text-right'>Token Expires " . date('M d Y H:i:s', $accessToken->getExpires())."
            </div>
            </div>
            <div class='row'>
            <div class='col-12'>
            <div class='msg-board-wrapper mt-5 mb-5'>
            ";
            // echo "msgResponse: ".var_dump($msgResponse);
            if($msgResponse){
                foreach($msgResponse as $msg){ "
                <div class='col-12'>
                <div class='row justify-content-between'>
                    <div class='user'>
                        $msg->email
                    </div>
                    <div class='time'>
                        $msg->time
                    </div>
                </div>
                <div class='row'>
                    <div class='msg'>
                        $msg->message
                    </div>
                </div>
                </div>
                ";
                };
            };
            echo "
            </div>
            </div>
            </div>
            <div class='row'>
            <div class='col-12'>
            <form action='' method='POST'>
            <div class='form-group'>
            <label class='w-100'for='message'>Write a message for the OAuth msgBoard</label>
            <textarea class='w-100 form-control' name='message' id='' rows='4'></textarea>
            </div>
            </form>
            </div>
            </div>
            ";


            // echo "<div class='row'><div class='col-12'>Response " . (string) $client->send($request)->getBody(); //this showed error in full, previously truncated in catch.. access token was being denied by resource server.
            // var_dump($response);
        }
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        echo "league exception" . $e->getMessage();

    } catch (\GuzzleHttp\Exception\ClientException $e) {

        echo "guzzle exception" . $e->getResponse()->getBody()->getContents();

    }
    ?>
  <!-- \League\OAuth2\Client\Provider\Exception\IdentityProviderException -->

</div>
</div>
</body>
</html>
<?php
}
?>
