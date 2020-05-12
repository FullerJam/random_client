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
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
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
        <h2 style="font-family: 'Pacifico', cursive;">Oauth authenticated msgBoard</h2>
        <img class="img-responsive" src="./circle-cropped.png" alt="oauth2logo">
    </div>
    <div class="col-12 d-flex flex-row-reverse mt-2"><form method="POST" action="client.php" ><input type="hidden" name="logout"><button type="submit">Logout</button></form></div>
</div>



<?php
if (isset($_POST['logout'])) {
    session_destroy();
}

try {
        if (!isset($_SESSION["accessToken"])) {
            //  No saved access token, getting one
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
        var_dump($response);
        if ($response) {
            $msgRequest = $provider->getAuthenticatedRequest(
                'POST',
                'http://localhost/oauth/oauth_server/resource_server/get_messages',
                $accessToken
            );
            $msgResponse = json_decode((string) $client->send($msgRequest)->getBody());
            echo "
            <div class='row mt-2'>
            <div class='col-6 text-success'><span>User Authenticated -> $response->email </div>
            <div class='col-6 text-warning text-right'>Token Expires " . date('M d Y H:i:s', $accessToken->getExpires()) . "
            </div>
            </div>
            <div class='row'>
            <div class='col-12'>
            <div class='msg-board-wrapper mt-5 mb-5 p-1'>
            ";
            // echo "msgResponse: ".var_dump($msgResponse);
            if ($msgResponse) {
                foreach ($msgResponse as $msg) {echo "
                <div class='col-12 p-3'>
                <div class='row justify-content-between pb-2 text-info'>
                    <div class='user'>
                        " . preg_split("/[@]/", $msg->email)[0] . "
                    </div>
                    <div class='time'>
                        " . DateTime::createFromFormat('U', $msg->time)->format('Y-m-d H:i:s') . /*convert time stamp to date & convert format for string*/"
                    </div>
                </div>
                <div class='row'>
                    <div class='msg'>
                        $msg->message
                    </div>
                </div>
                </div>
                ";
                }
                ;

            }
            ;
            echo "
        </div>
        </div>
        </div>
        <div class='row'>
        <div class='col-12'>
        <form method='POST'>
        <div class='form-group'>
        <label class='w-100'>Write a message for the OAuth msgBoard</label>
        <textarea class='w-100 form-control' id='message' name='message' rows='3' required></textarea>
        <input class='mt-2' type='submit'>
        </div>
        </form>
        </div>
        </div>
        "; //name of textarea declared as array 'message[]'
            if (isset($_POST['message'])) {
                $msg = $_POST['message'];
                echo $msg;
                // var_dump($msgArray);
                $setMsg = $provider->getAuthenticatedRequest(
                    'POST',
                    'http://localhost/oauth/oauth_server/resource_server/set_message',
                    $accessToken,
                    ['form_fields' => ['message' => $msg]]
                );
                var_dump($setMsg);
                $client->send($setMsg);

            }
            ;

            // echo "<div class='row'><div class='col-12'>Response " . (string) $client->send($request)->getBody(); //this showed error in full, previously truncated in catch.. access token was being denied by resource server.
            // var_dump($response);
        } else {
            echo "Something went wrong";
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
