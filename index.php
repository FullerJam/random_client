<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access forum client</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<style>

    button[type="submit"]{
        background: url(circle-cropped.png);
        width:150px;
        background-size:45px;
        background-repeat:no-repeat;
        background-position:90% center;
        padding-
    }

</style>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="wrapper">
            <h3>Client Forum</h3>
            <p class="text-center mt-4">Welcome to third party client forums, where you could sign in the old fashioned way or use our authorisation server to SSO</p>
                <form action="client.php" style="max-width:320px;min-width:320px;">
                    <div class="form-group">
                        <label class="w-100" for="email">Email address</label>
                        <input class="w-100" type="email">
                        <label class="w-100" for="password">Password</label>
                        <input class="w-100" type="password">
                    </div>
                    <div class="form-group">
                        <button>Login</button>
                    </div>
                    <div class="form-group">
                        <button class="btn-lg btn-outline-success text-left pl-3" type="submit">SSO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
