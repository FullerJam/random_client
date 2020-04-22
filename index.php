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
    'clientId' => '3fedffd1001543845b28d28b1e51397d', //advice https://www.oauth.com/oauth2-servers/client-registration/client-id-secret/ // used 'openssl rand -hex 32' to generate id and secret
    'clientSecret' => '0727ff911e1f4195eebd3185aed08c0a27d000c55521d7066d17c1414108eeb5',
    'redirectUri' => 'http://localhost/oauth/oauth_client/index.php',
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
} // Check given state against previously stored one to mitigate CSRF attack
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
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<style>

.row{
    display:flex;
    justify-content:center;
    flex-direction:column;
}

.imgRow{
    align-items:center;
}

.wrapper{
    height:80vh;
    margin: 0 auto;
    max-width:650px;
}
span{
    word-break: break-all;
}

</style>
</head>
<body>
<div class="container">
<div class="row">
<div class="wrapper mt-5">

<div class="col-12 imgRow">
<img class="img-responsive mb-5" src="./circle-cropped.png" alt="oauth2logo">
</div>

<div class="col-12">
<h1>Oauth client</h1>

<?php
try {
        if (!isset($_SESSION["accessToken"])) {
            echo "No saved access token, getting one <br />";
            $accessToken = $provider->getAccessToken('authorization_code', ["code" => $_GET["code"]]);
            echo '<span>Access Token: ' . $accessToken->getToken() . "<br></span>";
            $_SESSION["accessToken"] = $accessToken;
        } else {
            $accessToken = $_SESSION["accessToken"];
            echo "<span>Token already set<br/><br/><span style='font-weight:bold;'>Access Token:</span><br/>" . $accessToken . "</span><br/><br/>";
        }
        echo "<span style='font-weight:bold;'>Token Expires " . date('M d Y H:i:s', $accessToken->getExpires()) . "</span>";
        $request = $provider->getAuthenticatedRequest(
            'POST',
            'http://localhost/oauth/oauth_server/resource_server/read',
            $accessToken
        );

        echo "<h4 class='mt-2' style='color:green;'>Making a request to the Resource server API</h4><br/><p>Request: {" . (string) $request->getBody() . "}</p>";
        $client = new GuzzleHttp\Client();
        $response = json_decode((string) $client->send($request)->getBody());
        echo "Response " . (string) $client->send($request)->getBody();//this showed error in full, previously truncated in catch.. access token was being denied.
        var_dump($response);
        echo "User email from API: {$response->email}";
        // echo "Response from API: {$response->msg}";

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        
        echo "league exception".$e->getMessage();

    } catch (\GuzzleHttp\Exception\ClientException $e) {

        echo "guzzle exception".$e->getResponse()->getBody()->getContents();

    }
    ?>
  <!-- \League\OAuth2\Client\Provider\Exception\IdentityProviderException -->

</div>
</div>
</div>
</div>
</body>
</html>
<?php
}
?>
