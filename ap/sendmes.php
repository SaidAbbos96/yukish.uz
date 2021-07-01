<? 
require_once "apbd.php"; 
if(!$_SESSION['id']){
    header('Location: login.php');
}else{
    if(isset($_POST['chats'])){
        bot('sendMessage', [
            'chat_id' => $_POST['chats'],
            'text' => $_POST['sendText'],
            'parse_mode' => 'HTML'
        ]);
        R::exec('UPDATE faq SET status = 2 WHERE id = '.$_POST['mesid'].'');
        header('Location: sendmes.php');
    }else if($_GET['delmes']){
        R::exec('DELETE FROM faq WHERE id = '.$_GET['delmes'].'');
        header('Location: sendmes.php');
    }else if($_POST['typesender'] == "admins_group"){
        sender($admins_group, $_POST['sendText']);
    }else if($_POST['typesender'] == "channel"){
        sender($channel, $_POST['sendText']);
    }else if($_POST['typesender'] == "bot_users"){
        $users = R::findAll('users');
        sender($users, $_POST['sendText']);
    };
    require_once "header.php";
    $mesgs = R::findAll('faq', "WHERE status = 1 ORDER BY id");
 ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <form action="" method="post">
                <h6 class="text-center">Habar yo'llash</h6>
                <hr>
                <p class="SendTestBtnP text-center">Habar muvaffaqqiyatli yuborilishi uchun sinalmagan emojilar va (""<>) belgilardan foydalanmang !</p>
                <?
                if($alerttext){echo '<div class="alertbox">'.$alerttext.'</div>';};
            if($_GET['ansid']){
                $ames = $mesgs[$_GET['ansid']];
                echo '<div class="form-group"><p>Murojatga javob berish: yoki <a href="?delmes='.$ames['id'].'" title="O\'chirish">O\'chirib yuborish</a></p>
                <div class="ansbox">
                    <h6 class="mesdateans">'.$ames['id'].' | '.$ames['time'].'</h6>
                    <h6 class="namemess">'.$ames['neme'].'</h6>
                    <p class="textmes">'.$ames['text'].'</p>
                </div>
                <input type="hidden" value="'.$ames['chatid'].'" name="chats">
                <input type="hidden" value="'.$ames['id'].'" name="mesid"></div>
                ';
            }else if($_GET['user']){
                $us = $usersall[$_GET['user']];
                echo '<div class="form-group" style="background: bisque; padding: 15px;">
                    <label> <b>'.$us['usname'].'</b> ga habar yo\'llash</label>
                    <input type="hidden" value="'.$us['ustmeid'].'" name="singus">
                </div>';
            }else{?>
                <div class="form-group">
                    <label for="typesender">Yuborish shaklini tanlang:</label>
                    <select class="form-control" id="typesender" name="typesender">
                        <option value="admins_group">Adminlar guruhiga test uchun</option>
                        <? if($_SESSION['roll'] == "admin"){echo '<option value="channel">Asosiy kanalga</option>';};?>
                        <option value="bot_users">Botning barcha foydalanuvchilariga</option>
                    </select>
                </div>
                <?};?>

                <div class="form-group">
                    <label for="sendText">Habar matni: </label>
                    <textarea class="form-control" id="sendText" name="sendText" required rows="3"></textarea>
                    <small class="form-text text-muted">Ushbu habar tanlangan foydalanuvchilarga oddiy habar sifatida
                        hoziroq yuboriladi !</small>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-warning form-control" value="Habar yuborish">
                </div>
            </form>
            <h6>Mumkin bo'lgan html taglar(Faqat juda etiborli bo'ling bitta belgi qolib ketsa ham xabar yuborilmaydi)</h6>
            <p id="codebox"></p>
        </div>
        <div class="col-md-4">
            <ul>
                <?
                foreach ($mesgs as $mes) {
                    echo '<li class="mesbox">
                        <h6 class="mesdate">N:'.$mes['id'].' | '.$mes['time'].'</h6>
                        <h6 class="neme">'.$mes['neme'].'</h6>
                        <p>'.$mes['text'].'</p>
                        <p class="btnsmes">
                            <a href="?delmes='.$mes['id'].'" class="btn btn-danger" title="O\'chirish">X</a><a href="sendmes.php?ansid='.$mes['id'].'"
                                title="Javob berish" class="btn btn-success">Javob berish</a>
                        </p>
                    </li>';
                };
                ?>
            </ul>
        </div>
    </div>
</div>
<script>
document.getElementById("codebox").innerText = `<b>bold</b>, <strong>bold</strong>
<i>italic</i>, <em>italic</em>
<u>underline</u>, <ins>underline</ins>
<s>strikethrough</s>, <strike>strikethrough</strike>, <del>strikethrough</del>
<b>bold <i>italic bold <s>italic bold strikethrough</s> <u>underline italic bold</u></i> bold</b>
<a href="http://www.example.com/">inline URL</a>
<a href="tg://user?id=123456789">inline mention of a user</a>
<code>inline fixed-width code</code>
<pre>pre-formatted fixed-width code block</pre>
<pre><code class="language-python">pre-formatted fixed-width code block written in the Python programming language</code></pre>`;
</script>
<? require_once "footer.php"; };?>