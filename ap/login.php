<?php
require_once "apbd.php"; 
if(!$_SESSION['id']){
if(isset($_POST['Login'])){
    if($user = R::findOne('moders', 'login = ?', [trim($_POST['Login'])])){
        if($user['password'] == trim($_POST['password'])){
            foreach ($user as $key => $value) {
                $_SESSION[$key] = $value;
            };
            header('Location: ../ap/');
        }else{
            $error = "Hatolik, Parol hato kiritilgan !";
        };
    }else{
        $error = "Hatolik, Login hato kiritilgan !";
    };
};
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YUKISH.UZ - Boshqaruv paneli !</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>

<body>
    <style>
    .login-form {
        margin: 20px 0;
        padding: 20px 50px;
        border-radius: 8px;
        background: cornsilk;
    }

    h3.title {
        font-size: larger;
    }

    .errors {
        color: red;
        text-align: center;
    }
    </style>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5 login-form">
                <h3 class="title text-center">YUKISH.UZ Boshqaruv Paneliga xush kelibsiz !</h3>
                <p class="errors">
                    <? if($error) echo $error;?>
                </p>
                <form method="POST" action="login.php">
                    <input type="hidden" name="antereload" value="<? echo time();?>">
                    <div class="form-group">
                        <label for="Login">Login: <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" id="Login" name="Login" placeholder="Loginni kiriting:"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="password">Parol: <span style="color:red;">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required
                            placeholder="Parolni kiriting:">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary form-control">Tasdiqlash</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<? require_once "footer.php";
}else{ ?>
<script>
    window.location.href = "../ap";
</script>
<?};?>