<?
function dump($what){
    echo '<pre>'; 
        print_r($what); 
    echo '</pre>';
};

function get_data($url){
    return json_decode(file_get_contents($url), true);
};

function bot($method, $datas = []){
    $url = "https://api.telegram.org/bot".API_KEY."/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    curl_close($ch);
    if (!curl_error($ch)) return json_decode($res, true);
};

function html($text){
    return str_replace(['<','>'],['&#60;','&#62;'],$text);
};

function reformat($json){
    return json_encode(json_decode($json, true), JSON_PRETTY_PRINT);
};

function user_is_followed($user_id){
    global $channels;
    $count = 0;
    $count_verf = 0;
    foreach ($channels as $channel){
        if($channel["required"] == 1){
            $count++;
            $stss = ['creator', 'administrator', 'member'];
            $res = get_data('https://api.telegram.org/bot'.API_KEY.'/getChatMember?chat_id='.$channel["chan_id"].'&user_id=' . $user_id)['result'];
            if(in_array($res['status'], $stss)){
                $count_verf++;
            };
        };
    };
    return ($count_verf == $count) ? true : false;
};

function get_chans(){
    global $channels;
    $list_channels = [];
    foreach($channels as $channel){
        $list_channels[][] = ['text' => $channel['btn_text'], 'url'=> "https://t.me/".$channel['username'].""];
    };
    array_push($list_channels, [
        [
            'text' => "Obuna bo'ldim ✅",
            'callback_data' => "followed"
        ]
    ]);
    return $list_channels;
};
if($logging){
    if(file_get_contents('php://input')){
        file_put_contents("log.json",reformat(file_get_contents('php://input')));
    };
    
};

function bot_manager(){
    if(isset($_GET['reset'])){
        $protokol = $_SERVER['REQUEST_SCHEME'];
        if($protokol != "https"){
            echo "Xatolik, So'rov HTTPS protokolida bo'lishi shart !<br> SSl sertifekat kerak domainga !";
        }else{
            echo $webhook_url = "https://api.telegram.org/bot".API_KEY."/setWebHook?url=".$protokol."://".$_SERVER['HTTP_HOST']."".$_SERVER['SCRIPT_NAME'];
            dump(json_decode(file_get_contents($webhook_url),true));
        };
    }else if(isset($_GET['info'])){
        dump(json_decode(file_get_contents("https://api.telegram.org/bot".API_KEY."/getWebhookInfo"),true));
    }else if(isset($_GET['kill'])){
        dump(json_decode(file_get_contents("https://api.telegram.org/bot".API_KEY."/deleteWebhook?drop_pending_updates=true"),true));
    }else if(isset($_GET['test'])){
        global $Manager;
        $res = bot('sendmessage', [
            'chat_id' => $Manager,
            'text' => "Test xabar !",
            'parse_mode' => 'HTML'
        ]);
        if($res["ok"]){echo "Test Xabr yuborildi";};
        dump($res);
    };
};

function test_url(){
    $res = explode("/", $_SERVER['SCRIPT_NAME']);
    $res[array_key_last($res)] = "app.php";
    $res = implode("/", $res);
    return $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST']."".$res."?test";
};

function db_conn_sqlite(){
    global $local_db_sqlite;
    if($local_db_sqlite['status']){
        if(!file_exists($local_db_sqlite['db_src'])){
            $db = new SQLite3($local_db_sqlite['db_src']);
            $db->exec('CREATE TABLE "users" (
                "id"	INTEGER UNIQUE,
                "tmeid"	INTEGER,
                "name"	TEXT,
                "lang"	TEXT,
                PRIMARY KEY("id" AUTOINCREMENT)
            );');
            $db->exec('CREATE TABLE "posts" ( 
                "id"         INTEGER UNIQUE, 
                "post"       TEXT UNIQUE, 
                "region"     INTEGER, 
                "zone"       INTEGER, 
                "end_region" INTEGER, 
                "end_zone"   INTEGER, 
                "status"     INTEGER,
                "type"       TEXT,
                "options"    TEXT,
                PRIMARY KEY("id" AUTOINCREMENT) 
            );');
        };
        require_once "rb.php";
        if(!R::testConnection()){
            R::setup('sqlite:'.$local_db_sqlite['db_src']);
        };
        // if(R::testConnection()){echo "DB Bor";}else{ echo "DB Yuq";};
    };
};

function db_mysql(){
    global $db_mysql, $admins_group;
    if($db_mysql['status']){
        require_once "rb.php";
        if(!R::testConnection()){
            R::setup('mysql:host='.$db_mysql["host"].';dbname='.$db_mysql["db_name"].'', $db_mysql["login"], $db_mysql["password"]);
        };
        // if(R::testConnection()){echo "Global DB Bor";}else{ echo "Global DB Yuq";};
    };
};


function get_lang($user_obj, $lang = false){
    // db_conn_sqlite();
    global $admins_group, $chat_id;
    if($chat_id == $admins_group){
        return "uz";
    }
    db_mysql();
    $user = R::findOne('users', 'tmeid = ?', [$user_obj["id"]]);
    if($user){
        if($lang){
            $user->lang = $lang;
        }else if($user["lang"]){
            $lang_kod = $user["lang"];
        };
        if($user_obj["faq"] == "??"){
            return $user;
        }else if($user_obj["faq"]){
            $user->faq = $user_obj["faq"];
        };
        R::store($user);
        return $user["lang"];
    }else if(strlen($user_obj["id"]) > 5){
        $user = R::dispense('users');
        $user->tmeid = $user_obj["id"];
        if($user_obj["name"]){ $user->name = $user_obj["name"];};
        if($lang){$user->lang = $lang;};
        R::store($user);
    };
    R::close();
};

function get_words($lang){
    if($lang){
        return get_data("datas/".$lang.".json");
    };
};


function delmes($mid){
    global $chat_id;
    bot('deleteMessage',[
        'chat_id' => $chat_id,
        'message_id' => $mid
    ]);
};

function post($params = false){
    global $message_id, $chat_id,$admins_group, $call_id;
    $post_uid = $chat_id."-p-".$message_id;
    db_mysql();
    if($params["changests"]){
        $post = R::findOne('posts','id = ?', [$params["id"]]);
        if($post["status"] == 2){
                bot('answerCallbackQuery', [
                    'callback_query_id' => $call_id,
                    'text' => "Elon tasdiqlanmagan !"
                ]);
        }else if($post["status"] == 5){
            $post->status = 3;
            R::store($post);
            return $post;
        }else if($post["status"] == 3){
            $post->status = 4;
            R::store($post);
            return $post;
        };
    }elseif($params["moder"]){
        $post = R::findOne('posts','id = ?', [$params["id"]]);
        if($params["moder"] == 2 & $post){
            if($params["del_post"]){
                $res = $post["post"];
                R::trash($post);
                return $res;
            };
        }else if($params["moder"] == 1 & $post){
            $post->status = 5;
            R::store($post);
            return $post;
        };
    }else if($params["id"]){
        $post = R::findOne('posts','id = ?', [$params["id"]]);
        if($params["status"]){$post->status = $params["status"];};
        if($params["del_post"] & $post) {
            R::trash($post);
            return true;
        };
    }else if($params["re_post"]){
        return $post = R::findOne('posts', 'WHERE status = ? AND post LIKE ? ORDER BY id DESC', [$params["re_post"], "%".$chat_id."%"]);
    }else{
        $post = R::findOne( 'posts', 'post = ?', [$post_uid]);
    };
    if(!$params & $post){
        return $post;
    }else if(!$post & $params){
        if($chat_id == $admins_group){
            return false;
        };
        $npost = R::dispense('posts');
        $npost->post = $post_uid;
        $npost->region = $params["region"];
        R::store($npost);
        return $post_uid;
    }else if($post & $params){
        if($params["region"]){$post->region = $params["region"];};
        if($params["zone"]){$post->zone = $params["zone"];};
        if($params["end_region"]){$post->endRegion = $params["end_region"];};
        if($params["end_zone"]){$post->endZone = $params["end_zone"];};
        if($params["sts"]){$post->status = $params["sts"];};
        if($params["type"]){$post->type = $params["type"];};
        if($params["contact"]){$post->contact = $params["contact"];};
        if($params["price"]){$post->price = $params["price"];};
        if($params["q_step"]){$post->qStep = $params["q_step"];};
        if($params["fast_tag"]){$post->fastTag = $params["fast_tag"];};
        if($params["description"]){$post->description = $params["description"];};
        if($params["post_reset"]){
            $post->qStep = "";
            $post->contact = "";
            $post->price = "";
            $post->description = "";
            $post->fastTag = "";
            $post->status = 1;
            $post->position = "";
        };
        R::store($post);
        return $post;
    }else if(!$post & !$params){
        return false;
    };
    R::close();
};

function faq($params = false){
    global $chat_id, $admins_group, $datasign, $full_name;
    db_mysql();
    if($params['text']){
        $mess = R::dispense('faq');
        $mess->chatid = $chat_id;
        $mess->status = 1;
        $mess->neme = $full_name;
        $mess->time = $datasign;
        $mess->text = $params["text"];
        R::store($mess);
        return $mess;
    };
};

function ldb(){
    db_mysql();
    dump(R::findAll( 'posts'));
};

function sender($chatids, $text){
    if(is_array($chatids)){
        foreach($chatids as $chid) {
            bot('sendMessage', [
                'chat_id' => $chid['tmeid'],
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);
        };
    }else{
        bot('sendMessage', [
            'chat_id' => $chatids,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    };
};

function post_generate($post){
    global $words, $message_id, $chat_id, $admins_group, $channel;
    if(!$post["contact"]){$post["contact"] = "?";};
    if(!$post["price"]){$post["price"] = "?";};
    if($post["post"]){$message_id = explode("-p-", $post["post"])[1];};
    if(!$post["description"]){$post["description"] = "...";};
    $fast = "";
    // if($post["fast_tag"]){$fast = $words["fast_text"]."⚡️";};
    // $reply = "<b>".$words["post_baza"][0]."</b> ".$fast."\n";
    // $reply .= "📙 <code>Elon raqami:</code> <b>".$post["type"]."".$post["id"]."</b>.\n";
    // $reply .= "📌 <code>Status:</code> <b>".$words['sts_templates'][$post["status"]]."</b>.\n";
    // $reply .= "☎️ <code>Aloqa:</code> <b>".$post["contact"]."</b>.\n";
    // $reply .= "💰 <code>Xizmat narxi:</code> <b>".$post["price"]."</b>.\n";
    // $reply .= "🧳 <code>Elon toifasi:</code> <b>".$words['types'][array_search($post["type"], ["A","B","D","E","F","G"])]."</b>.\n";
    // $reply .= "🚩 <code>Qayerdan:</code><b> #".str_replace(["'", "`", "-"], "", $words["regions"][$post["region"] -1].", #".$words["zones"][$post["region"]-1][$post["zone"]-1])."</b>\n";
    // $reply .= "🏁 <code>Qayergacha:</code> <b>#".str_replace(["'", "`", "-"], "", $words["regions"][$post["end_region"]-1].", #".$words["zones"][$post["end_region"]-1][$post["end_zone"]-1])."</b>\n";
    // $reply .= "🟢 <code>Qo'shimcha tavsif:</code>\n💬 <b>".$post["description"]."</b>";
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

    if($post["status"] == 5 & $chat_id == $admins_group){
        $post_position = bot('sendMessage', [
            'chat_id' => $channel,
            'text' => $reply,
            'parse_mode' => 'HTML'
        ]);
        R::exec( 'UPDATE posts SET position="'.$post_position['result']["message_id"].'" WHERE id = '. $post["id"]);
        $post_uid_arr = explode("-p-", $post["post"]);
        $message_id = $post_uid_arr[1];
        $chat_id = $post_uid_arr[0];
        $keyboard = [
            [
                ['text' => $words['del_post'], 'callback_data' => "delpost=".$post["id"].""],
                ['text' => $words['sts_templates'][3], 'callback_data' => "changests=".$post["id"].""]
            ]
        ];
    }else if($post["status"] == 2){
        $keyboard = [
            [
                ['text' => $words['del_post'], 'callback_data' => "delpost=".$post["id"].""],
                ['text' => $words['sts_templates'][3], 'callback_data' => "changests=".$post["id"].""]
            ]
        ];
    }else if($post["status"] == 3){
        $keyboard = [
            [
                ['text' => $words['del_post'], 'callback_data' => "delpost=".$post["id"].""],
                ['text' => $words['sts_templates'][4], 'callback_data' => "changests=".$post["id"].""]
            ]
        ];
    }else{
        $keyboard = [
            [
                ['text' => $words['del_post'], 'callback_data' => "delpost=".$post["id"].""],
                ['text' => $words['prev_text'], 'callback_data' => "back=region"]
            ]
        ];
        if(!$post["q_step"]){$post["q_step"] = 0;};
        if($post["q_step"] < 3){
            $reply .= "\n\n⚠️ <b>".$words["q_steps"][$post["q_step"]]."</b>.";
        }else{
            if(!$post["fast_tag"]){$keyboard[][] = ['text' => "⚡️ ".$words['fast_text'], 'callback_data' => "fast_tag"];};
            $keyboard[][] = ['text' => $words['run_post'], 'callback_data' => "runpost=".$post["id"]];
        };
    };
    if($post["status"] == 4){
        bot('editMessageText', [
            'chat_id' => $channel,
            'message_id' => $post["position"],
            'text' => $reply,
            'parse_mode' => 'HTML'
        ]);
        $reply .= "\n\n".$words["succ_post"];
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $reply,
            'parse_mode' => 'HTML'
        ]);
    }else{
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $reply,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => $keyboard
            ])
        ]);
    };


    if($post["status"] == 2){
        $keyboard = [
            [
                ['text' => $words['del_post'], 'callback_data' => "delpost=".$post["id"].""],
                ['text' => $words['check_post'], 'callback_data' => "checkpost=".$post["id"].""]
            ]
        ];
        bot('sendMessage', [
            'chat_id' => $admins_group,
            'text' => $reply,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => $keyboard
            ])
        ]);
    }else if($post["status"] == 3){
        bot('editMessageText', [
            'chat_id' => $channel,
            'message_id' => $post["position"],
            'text' => $reply,
            'parse_mode' => 'HTML'
        ]);
    };
};



$words = [
    "hi_text" => "Elon qoldirish uchun barcha kerakli ma'lumotlar kiritilgach <b>'Elon berish'</b> tugmasini bosing !\nSavol yoki reklama masalasida /faq buyrug'i orqali murojat qoldiring !\n<code>📍 Hududni tanlang: (Qayerdan ?) </code> 👇",
    "two_step" => "<code>📍 Manzilni tanlang: (Qayerga ?) </code> 👇",
    "regions" => [
        "Andijon",
        "Buxoro",
        "Jizzax",
        "Navoiy",
        "Namangan",
        "Samarqand",
        "Sirdaryo",
        "Surxondaryo",
        "Toshkent Sh.",
        "Toshkent",
        "Farg'ona",
        "Xorazm",
        "Qashqadaryo",
        "Qoraqalpog'iston"
    ],
    "zones" => [
        ["Andijon sh.","Andijon t.","Asaka t.","Baliqchi t.","Bo`z t.","Buloqboshi t.","Izboskan t.","Jalaquduq t.","Marxamat t.","Oltinko`l t.","Paxtaobod t.","Qo`rg`ontepa t.","Qorasuv sh.","Shahrixon t.","Ulug`nor t.","Xo`jaobod t.","Xonobod sh."],
        ["Buxoro sh.","Buxoro t.","G`ijduvon t.","Jondor t.","Kogon sh.","Kogon t.","Olot t.","Peshku t.","Qorako`l t.","Qoravulbozor t.","Romitan t.","Shofirkon t.","Vobkent t."],
        ["Jizzax sh.","Arnasoy t.","Baxmal t.","Do`stlik t.","Forish t.","G`allaorol t.","Jizzax t.","Mirzacho`l t.","Paxtakor t.","Yangiobod t.","Zafarobod t.","Zarbdor t.","Zomin t."],
        ["Karmana t.","Konimex t.","Navbahor t.","Navoiy sh.","Nurota t.","Qiziltepa t.","Tomdi t.","Uchquduq t.","Xatirchi t.","Zarafshon sh."],
        ["Namangan sh.","Namangan t.","To`raqo`rg`on t.","Chust t.","Pop t.","Kosonsoy t.","Chortoq t.","Yangiqo`rg`on t.","Uychi t.","Uchqo`rg`on t.","Norin t.","Mingbuloq t."],
        ["Bulung`ur t.","Ishtixon t.","Jomboy t.","Kattaqo`rg`on t.","Narpay t.","Nurobod t.","Oqdaryo t.","Pastdarg`om t.","Paxtachi t.","Payariq t.","Qo`shrabot t.","Samarqand t.", "Samarqand sh.", "Tayloq t.", "Urgut t."],
        ["Boyovut t.","Guliston sh.","Guliston t.","Mirzaobod t.","Oqoltin t.","Sardoba t.","Sayhunobod t.","Shirin sh.","Sirdaryo t.","Xovos t.","Yangiyer sh."],
        ["Angor t.","Boysun t.","Denov t.","Jarqo`rg`on t.","Muzrabot t.","Oltinsoy t.","Qiziriq t.","Qumqo`rg`on t.","Sariosiyo t.","Sherobod t.","Sho`rchi t.","Termiz sh.","Termiz t.","Uzun t."],
        ["Bektemir t.","Mirzo-Ulug`bek t.","Yunusobod t.","Yakkasaroy t.","Shayxontohur t.","Chilonzor t.","Sergeli t.","Yashnobod t.","Olmazor t.","Uchtepa t.","Mirobod t."],
        ["Angren sh.","Bekobod sh.","Olmaliq sh.","Chirchiq sh.","Bekobod t.","Bo`ka t.","Bo`stonliq t.","Zangiota t.","Ohangaron t.","Oqqo`rg`on t.","Piskent t.","Parkent t.","Chinoz t.","O`rtachirchiq t.","Quyichirchiq t.","Qibray t.","Yuqorichirchiq t.","Yangiyo`l t."],
        ["Beshariq t.","Bog`dod t.","Buvayda t.","Dang`ara t.","Farg`ona sh.","Furqat t.","Farg`ona t.","Marg`ilon sh.","Oltiariq t.","O`zbekiston t.","Qo`qon sh.","Qo`shtepa t.","Quvasoy sh.","Quva t.","Rishton t.","So`x t.","Toshloq t.","Uchko`prik t.","Yozyovon t."],
        ["Bog`ot t.","Gurlan t.","Qo`shko`pir t.","Shovot t.","Urganch sh.","Urganch t.","Yangibozor t.","Yangiariq t.","Xiva t.","Xazorasp t.","Xonqa t."],
        ["Dehqonobod t.","G`uzor t.","Kasbi t.","Kitob t.","Koson t.","Mirishkor t.","Muborak t.","Nishon t.","Qamashi t.","Qarshi sh.","Qarshi t.","Shahrisabz t.","Yakkabog` t."],
        ["Nukus t.","Nukus sh.","Mo`ynoq t.","Kegayli t.","Ellikqal`a t.","Chimbay t.","Beruniy t.","Amudaryo t.","Qo`ng`irot t.","Qonliko`l t.","Qorao`zak t.","Shumanay t.","Taxiyatosh sh.","Taxtako`pir t.","To`rtko`l t.","Xo`jayli t."]
    ],
    "prev_text" => "❎ Bekor qilish ❎",
    "del_post" => "❌ O'chirish ❌",
    "run_post" => "✅ E'lon qilish ✅",
    "check_post" => "Elon qilish 📣",
    "sts_templates" => ["",
        "Tahrirda 📝",
        "Kutush holatida ⏳",
        "Bajarilmoqda ⏱",
        "Bajarildi ✅",
        "Aktiv 📣"
    ],
    "types_text" => "<code>Talab etilayotgan xizmat toifasini tanlang:</code> 👇",
    "types" => [
        "🚕 Yengil mashina",
        "🚙 1 Tonnagacha",
        "🚐 5 Tonnagacha",
        "🚚 10 Tonnagacha",
        "🚛 30 Tonnagacha",
        "🧱 60 Tonnagacha"
    ],
    "q_steps" => [
        "Telefon raqamingizni kiriting: (991234567)",
        "Taxminiy narxni kiriting (35000 sum) yoki (Kelishilgan)",
        "Elon kartasiga qo'shimcha sifatida istalgan matn yoki fikrni kiriting !"
    ],
    "post_baza" => [
        "Transport xizmati:",
        "Ish Holati:",
        "Qayerdan:",
        "Qayergacha:",
        "Xizmat Narxi:",
        "Yuk Vazni:",
        "Telefon:",
        "Manzil:",
        "Qo'shimcha Ma'lumot:"
    ],
    "fast_text" => "#Shoshilinch",
    "new_post_btn" => "🆕 Yangi elon yaratish 📝",
    "succ_del_post" => "🔥 Elon o'chirildi, yangi elon joylashtirish uchun /new buyrug'ini yuboring !",
    "succ_post" => "✅ Elon yakunlandi, yangi elon joylashtirish uchun /new buyrug'ini yuboring !",
    "succ_del_post_admin" => "🔥 Elon o'chirildi !",
    "warr_del_post_admin" => "🔥 Sizning e'loningiz admin tomonidan o'chirildi !\nYangi elon yaratish uchun /new tugmasini bosing !",
    "faq" => "Murojat qoldiring, kordinatorlar qisqa vaqt ichida murojat yo'llashadi !",
    "faq_succ" => "Sizning murojatingiz qabul qilindi, murojat raqami: "
];

 if($reword){
     file_put_contents("datas/uz.json", json_encode($words, JSON_PRETTY_PRINT));
     $words["hi_text"] = "tojikcha";
     file_put_contents("datas/tj.json", json_encode($words, JSON_PRETTY_PRINT));
     $words["hi_text"] = "prevet";
     file_put_contents("datas/ru.json", json_encode($words, JSON_PRETTY_PRINT));
     $words["hi_text"] = "kreluzb";
     file_put_contents("datas/kuz.json", json_encode($words, JSON_PRETTY_PRINT));
 };