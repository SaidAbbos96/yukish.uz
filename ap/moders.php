<? 
require_once "apbd.php"; 
if(!$_SESSION['id'] || ($_SESSION['roll'] != "admin")){
    header('Location: login.php');
}else{
    if($_GET["del_moder"]){
        R::exec('DELETE FROM moders WHERE id = '.$_GET['del_moder'].'');
        header('Location: moders.php');
    }else if(isset($_POST['login']) & isset($_POST['parol'])){
        if((strlen($_POST['login']) >= 5) & strlen($_POST['parol']) >= 5){
            $new_moder = R::dispense("moders");
            $new_moder->login = $_POST['login'];
            $new_moder->password = $_POST['parol'];
            $new_moder->roll = 'simple';
            R::store($new_moder);
        }else{
            $err = "Login yoki parol 5 tadan kam !";
        };
    };;
    require_once "header.php";
    $moders = R::findAll('moders', "roll = ?", ["simple"]);
 ?>
<div class="container" style="margin-top: 40px;">
    <div class="row">
        <div class="col-md-6">
            <form action="" method="post">
                <h6 class="text-center">Moderator qo'shish</h6>
                <hr>
                <p class="SendTestBtnP text-center">Ushbu sahifa faqat bosh adminga xizmat qiladi, iltimos havfsizlikni taminlang !</p>
                <? if($err){ echo '<p style="color:red;" class="text-center">'.$err.'</p>';};?>
                <div class="form-group">
                    <label for="">Login *</label>
                    <input type="text" class="form-control" name="login">
                </div>
                <div class="form-group">
                    <label for="">Parol *</label>
                    <input type="text" class="form-control" name="parol">
                </div>
                <p class="SendTestBtnP text-center">Login Parol kamida 5 ta harfdan iborat bolsin !</p>
                <div class="form-group">
                    <input type="submit" class="btn btn-warning form-control" value="Qo'shish">
                </div>
            </form>
        </div>
        <div class="col-md-6">
        <style>
            #moderstable {
                width: 100%;
                text-align: center;
            }
        </style>
            <table id="moderstable">
                <tr>
                    <th>ID</th>
                    <th>LOGIN</th>
                    <th>PAROL</th>
                    <th>*</th>
                </tr>
                <?
                    foreach ($moders as $id => $moder) {
                      echo '<tr><td>'.$moder["id"].'</td>
                            <td>'.$moder["login"].'</td>
                            <td>'.$moder["password"].'</td>
                            <td><a href="?del_moder='.$moder["id"].'"> &#9760; </a></td></tr>';
                    };
                ?>
            </table>
        </div>
    </div>
</div>
<? require_once "footer.php"; };?>