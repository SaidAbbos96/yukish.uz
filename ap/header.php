<?
function words($code = "uz"){
    return json_decode(file_get_contents("../datas/".$code.".json"), true);
};
$words = words();
?><!DOCTYPE html>
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
    .mynavbar {
        background: thistle;
        padding: 7px 15px;
    }

    .mynavbar a {
        color: #01003e;
        font-weight: 500;
        text-decoration: none;
    }

    span.logotext {
        color: black;
    }

    .list-users {
        border-right: 1px solid gray;
        /* height: 92vh; */
        padding: 10px 0;
    }

    li.itemUser {
        list-style: circle;
        margin-left: -10px;
    }

    p.SendTestBtnP {
        text-align: center;
        padding: 0 5px;
    }

    input.sendTestBtn {
        float: right;
    }

    li.itemUser input {
        float: right;
        margin-right: 10px;
        margin-top: 8px;
    }

    span.topBall {
        margin-right: 15px;
        color: #ff00b1;
        font-weight: 600;
        float: right;
        width: 30px;
    }

    p.SendTestBtnP .btn {
        float: right;
        margin: 0 10px;
    }

    .ower-auto {
        overflow: auto;
        height: 80vh;
    }

    .usertable {
        width: 96%;
        margin: 10px 2%;
        text-align: center;
    }

    table,
    th,
    td {
        border: 1px solid gray;
    }

    td.tdtools a {
        margin-right: 5px;
        cursor: pointer;
    }

    .statispan {
        box-shadow: 0 0 8px #e8e8e8;
        border-radius: 10px;
        padding: 20px 5px;
        margin: 10px;
    }

    h4.col-12 {
        text-align: center;
        font-size: large;
    }

    input.sumpayin {
        font-size: 13px;
        padding: 0px;
        padding-left: 5px;
        margin: 0;
        outline: none;
        border: none;
        width: 95px;
        background: yellow;
    }

    span.col-6 {
        font-size: 14px;
    }

    .save {
        border: none;
        padding: 0;
        font-size: 15px;
    }

    li.mesbox {
        border: 1px solid green;
        padding: 15px;
        background: #f5f563;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    h6.mesdate {
        text-align: end;
        margin-top: -10px;
        margin-bottom: 0;
        font-size: small;
        color: #000000bd;
    }

    p.btnsmes {
        margin: 0;
        text-align: end;
    }

    p.btnsmes a {
        padding: 0px 10px;
        margin: 0;
        margin-left: 5px;
    }

    .ansbox {
        border: 1px solid #ced4da;
        background: antiquewhite;
        padding: 0px 20px 10px;
    }

    h6.mesdateans {
        font-size: smaller;
        color: blue;
        margin-top: 5px;
    }

    h6.namemess {
        font-size: large;
    }

    h6.namemess a {
        color: black;
        text-decoration: none;
    }

    input[type="checkbox"] {
        margin-right: 5px;
    }

    #multiple {
        max-height: 300px;
        overflow: auto;
    }

    .alertbox {
        padding: 20px; 
        background: antiquewhite;
        font-weight: 600;
    }
    </style>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">YUKISH.UZ</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="https://yukish.uz/yukishbot/ap/">Bosh Sahifa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="sendmes.php">Aloqa bo'limi</a>
                </li>
                <?if($_SESSION['roll'] == "admin"){?>
                <li class="nav-item">
                    <a class="nav-link" href="moders.php">Moderatorlar</a>
                </li>
                <?};?>
            </ul>
            <form class="form-inline my-2 my-lg-0">
                <a class="btn btn-outline-success my-2 my-sm-0" href="logout.php">Tizimdan
                    Chiqish</a>
            </form>
        </div>
    </nav>