<? 
require_once "apbd.php"; 

if(!$_SESSION['id']){
    header('Location: login.php');
}else{
    
    require_once "header.php";
    if(isset($_POST['delpost'])){
        $post = R::findOne('posts','id = ?', [$_POST["id"]]);
          R::trash($post);
          echo '<script> window.location.href = "../ap/";</script>';
    }else if(isset($_POST['changepost'])){
        $post = R::findOne('posts','id = ?', [$_POST["id"]]);
        $post->contact = $_POST['contact'];
        $post->price = $_POST['price'];
        $post->status = $_POST['status'];
        $post->type = ["A","B","D","E","F","G"][$_POST['toifasi']];
        $post->fastTag = $_POST['fasttag'];
        R::store($post);
        $uschid = explode("-p-", $post['post'])[0]; 
        $words = words(R::findOne('users', 'tmeid = ?', [$uschid])["lang"]);
        if($post['position'] & $post['status'] > 2){
            if($post["fast_tag"]){$fast = $words["fast_text"]."⚡️";};
                $reply = "<b>".$words["post_baza"][0]." ".$post["type"]."".$post["id"]."</b> ".$fast."\n";
                $reply .= "📌 <code>".$words["post_baza"][1]."</code> <b>".$words['sts_templates'][$post["status"]]."</b>.\n";
                $reply .= "<b>".$words["post_baza"][7]."</b>\n";
                $reply .= "🚩 <code>".$words["post_baza"][2]."</code><b> #".str_replace(["'", "`", "-"], "", $words["regions"][$post["region"] -1].", #".$words["zones"][$post["region"]-1][$post["zone"]-1])."</b>\n";
                $reply .= "🏁 <code>".$words["post_baza"][3]."</code> <b>#".str_replace(["'", "`", "-"], "", $words["regions"][$post["end_region"]-1].", #".$words["zones"][$post["end_region"]-1][$post["end_zone"]-1])."</b>\n";
                $reply .= "💰 <code>".$words["post_baza"][4]."</code> <b>".$post["price"]."</b>.\n";
                $reply .= "🧳 <code>".$words["post_baza"][5]."</code> <b>".$words['types'][array_search($post["type"], ["A","B","D","E","F","G"])]."</b>.\n";
                $reply .= "☎️ <code>".$words["post_baza"][6]."</code> <b>".$post["contact"]."</b>.\n\n";
                $reply .= "🟢 <code>".$words["post_baza"][8]."</code>\n💬 <b>".$post["description"]."</b>";

                bot('editMessageText', [
                    'chat_id' => $channel,
                    'message_id' => $post["position"],
                    'text' => $reply,
                    'parse_mode' => 'HTML'
                ]);
        };
    };
    if($_GET['postid']){
        $post = R::findOne('posts','id = ?', [$_GET['postid']]);
    };
 ?>
<style>
    .container-fluid {
        padding-top: 50px;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <form action="" method="post">
                <h5 class="text-center">Elonni tahrirlash</h5>
                <div class="row">
                    <div class="form-group col-md-1">
                        <label for="uid">S/N:</label>
                        <input type="hidden" class="form-control" id="id" name="id" value="<?=$post['id']?>">
                        <input type="text" class="form-control" id="uid" name="uid" value="<?=$post['type'].''.$post['id']?>"
                            disabled>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Aloqa:</label>
                        <input type="text" class="form-control" value="<?=$post['contact']?>" name="contact">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="tel">Narx:</label>
                        <input type="text" class="form-control" value="<?=$post['price']?>" name="price">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="status">Status:</label>
                        <select class="form-control" name="status" >
                            <?  $i = 1;
                                while($i <= 5){
                                    if($post['status'] == $i){$dop = "selected";}else{$dop = "";};
                                    echo '<option value="'.$i.'" '.$dop.'>'.$words['sts_templates'][$i].'</option>';
                                    $i++;
                                };
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="toifasi">Elon toifasi:</label>
                        <select class="form-control" name="toifasi">
                            <? 
                                foreach ($words['types'] as $key => $value) {
                                    if(array_search($post["type"], ["A","B","D","E","F","G"]) == $key){$dop = "selected";}else{$dop = "";};
                                    echo '<option value="'.$key.'" '.$dop.'>'.$value.'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="">Yunalish(start):</label>
                        <input type="text" disabled class="form-control" value="<?=$words["regions"][$post["region"]-1].' > '.$words["zones"][$post["region"]-1][$post["zone"]-1]?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="">Yunalish(Finish):</label>
                        <input type="text" disabled class="form-control" value="<?=$words["regions"][$post["end_region"]-1].' > '.$words["zones"][$post["end_region"]-1][$post["end_zone"]-1]?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fasttag">Qo'shimcha tag:</label>
                        <select class="form-control" name="fasttag">
                                <option value="0">Oddiy</option>
                                <option value="1" <? if($post["fast_tag"] == 1){ echo "selected";}; ?>><?=$words['fast_text']?></option>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="">*</label>
                        <input type="submit" class="form-control btn btn-danger" name="delpost" value="O'chirish">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="">*</label>
                        <input type="submit" class="form-control btn btn-success" name="changepost" value="Saqlash">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<? require_once "footer.php";?>
<?};?>