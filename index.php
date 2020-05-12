<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access forum client</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

</head>

<style>
    button[type="submit"] {}

    

    .wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 80vh;
        width: 100%;
    }

    .button-wrapper{
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        width: 100%;
    }

    .logo {
        width: 80px;
    }

    .intro{
        font-family: 'Pacifico', cursive;
     
        background-color:#d4d4d4;
        padding:50px;
    }

    .login-group{
        background-color:#f4f4f4;
        padding:50px;
    }

    button{
        border-radius:5px;
        font-weight:bold;
    }

    label{
   
            font-weight:bold;
        }

</style>

<body>
    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center intro">
                    <h1>Oauth2 messageBoard.app</h1>
                    <h4 class="text-center mt-3">Welcome to third party OAuth2 Message board application</h4>
                </div>
            </div>
            <div class="row login-group">
                <div class="col-12 col-sm-6">
                    <div class="row justify-content-center">
                        <form action="client.php" style=" max-width:300px;">
                            <div class="form-group">
                                <label class="w-100" for="email">Email address</label>
                                <input class="w-100" type="email">
                                <label class="w-100" for="password">Password</label>
                                <input class="w-100" type="password">
                                <button class="btn-light mt-3">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="button-wrapper">
                        <div class="row flex-column">
                            <div class="col-12 d-flex mb-3 justify-content-center">
                                <img class="logo" src="circle-cropped.png" alt="OAuth-logo">
                            </div>
                            <a href="client.php">
                                <button class="btn-success" >Single sign on with </br> Oauth
                                    2.0
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>