<?php
// Load our Composer-installed dependencies
require_once 'vendor/autoload.php'; 

// Create an object representing the provider.
// Note how we have to provide our client ID and secret, and also
// the /authorize and /access_token endpoints.
// We also supply the redirect URL in case we didn't specify it when we
// registered our app.

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId' => 'd3e72ffd7ae784562e04',
    'clientSecret' => 'deaf0d35384340f9318957a5fc7f17b0023c4f6f',
    'redirectUri' => 'http://localhost/oauth2/Oauth_client/index.php',
    'urlAuthorize' => 'https://github.com/login/oauth/authorize',
    'scope' => '(no scope)',
    'urlAccessToken' => 'https://github.com/login/oauth/access_token',
    'urlResourceOwnerDetails' => 'https://github.com'
]);

session_start();

// If an authorisation code was NOT supplied as a query string..
if(!isset($_GET["code"])) {

    // Get the full URL for the /authorize endpoint
    $authUrl = $provider->getAuthorizationUrl();
    
    // Get the state (see below)
    $_SESSION['state'] = $provider->getState();
    
    // Redirect to our /authorize endpoint
    header("Location: $authUrl");
} // Check that the state sent back from the server is the same as the original (see above)
elseif(empty($_GET['state']) || (isset($_SESSION['state']) && $_GET['state']!=$_SESSION['state'])) {
    unset($_SESSION['state']);
    echo "Possible security violation detected - quitting";
} else {
    
?>
<!DOCTYPE html>
<html>
<head>
<style>
body {
    max-width:1000px;
    margin:0 auto;
    font-family: helvetica, arial, sans-serif;
}
</style>
</head>
<body>
<h1>Github Oauth2 demo client</h1>

<p>This is an example OAuth Client, using githubs API.</p>

<?php
echo "<p>We have an authorisation code.</p>";
try {
    if(!isset($_SESSION["accessToken"])) {
        echo "No saved access token, getting one <br />";
        $accessToken = $provider->getAccessToken('authorization_code',
            ["code"=>$_GET["code"]]);
        $_SESSION["accessToken"] = $accessToken;
    } else {
        echo "We already have an access token, using that<br />";
        $accessToken = $_SESSION["accessToken"];
    }
    echo "Has access token expired? ".($accessToken->hasExpired() ? 'yes': 'no')."<br />";

    $request = $provider->getAuthenticatedRequest(
        'POST',
        'https://github.com/login/oauth/access_token',
        $accessToken
    );
    echo "<h2>Making a request to the github API</h2>";
    echo "<p>User needs to have granted upload permission for it to work, i.e. token needs 'upload' scope.</p>";
    $client = new GuzzleHttp\Client();
    $response = json_decode((string)$client->send($request)->getBody());
    echo "Response from API: <strong>{$response->msg}</strong>";
} catch(\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    echo "Exception: {$e->getMessage()}";
}
?>
</body>
</html>
<?php
}
?>
