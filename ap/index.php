<? 
require_once "apbd.php"; 
if(!$_SESSION['id']){
    header('Location: login.php');
}else{
    require_once "header.php";
    $posts = R::findAll('posts', "WHERE status > ? ORDER BY id DESC LIMIT 100",[1]);
 ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="statispan row">
                        <h4 class="col-12">Foydalanuvchilar bo'yicha malumot.</h4>
                        <span class="col-6">Kanalda obunachilar:</span> <span
                            class="col-6"><?=bot('getChatMemberCount', ['chat_id' => $channel])['result']?> ta.</span>
                        <span class="col-6">Botdan foydalanuvchilar:</span> <span class="col-6"><?=R::count('users');?>
                            ta.</span>
                        <span class="col-6">Guruhda Moderatorlar:</span> <span
                            class="col-6"><?=bot('getChatMemberCount', ['chat_id' => $admins_group])['result']?>
                            ta.</span>
                        <span class="col-6">Panel adminlari:</span> <span class="col-6"><?=R::count('moders');?>
                            ta.</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="statispan row">
                        <h4 class="col-12">Elonlar bo'yicha malumot.</h4>
                        <span class="col-6">Kutush holatida:</span> <span
                            class="col-6"><?=R::count('posts', "status = ?",[2]);?> ta.</span>
                        <span class="col-6">Bajarilayotganlar:</span> <span class="col-6"><?=R::count('posts', "status = ?",[3]);?> ta.</span>
                        <span class="col-6">Bajarilganlar:</span> <span
                            class="col-6"><?=R::count('posts', "status = ?",[4]);?>
                            ta.</span>
                        <span class="col-6">Barchasi:</span> <span class="col-6"><?=R::count('posts', "status > ?",[1]);?>
                            ta.</span>
                    </div>
                </div>
                <div class="col-md-12 list-users">
                    <h6 class="text-center">Barcha elonlar ro'yxati:</h6>
                    <table class="usertable">
                        <thead>
                            <tr style="background: antiquewhite;">
                                <th>S/R</th>
                                <th>Aloqa</th>
                                <th>Narx</th>
                                <th>Toifa</th>
                                <th>Yunalish</th>
                                <th>Status</th>
                                <th>Boshqaruv</th>
                                <th>Qo'shimcha tavsif</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                 if($posts){
                    foreach ($posts as $post) {
                        $res .= '<tr>
                            <td>'.$post['type'].''.$post['id'].'</td>
                            <td>'.$post['contact'].'</td>
                            <td>'.$post['price'].'</td>
                            <td>'.$words['types'][array_search($post["type"], ["A","B","D","E","F","G"])].'</td>
                            <td>'.$words["zones"][$post["region"]-1][$post["zone"]-1].' => '.$words["zones"][$post["end_region"]-1][$post["end_zone"]-1].'</td>
                            <td>'.$words['sts_templates'][$post["status"]].'</td>
                            <td class="tdtools">
                            <a href="singpost.php?postid='.$post['id'].'">👁</a>
                            </td>
                            <td>'.$post["description"].'</td>
                            </tr>' ;
                    };
                    echo $res;
                 }else{
                     echo '<tr style="background: antiquewhite;"><td colspan="8">Elonlar Topilmadi !</td></tr>';
                 };
                ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<? require_once "footer.php"; };?>