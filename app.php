<? 
require_once "config.php";
require_once "funcs.php";

bot_manager();

$update = json_decode(file_get_contents('php://input'));

// message variables
$message = $update->message;
$text = html($message->text);
$chat_id = $message->chat->id;
$chat_type = $message->chat->type;
$from_id = $message->from->id;
$message_id = $message->message_id;
$first_name = $message->from->first_name;
$last_name = $message->from->last_name;
$full_name = html($first_name . " " . $last_name);

// call back
$call = $update->callback_query;
$call_from_id = $call->from->id;
$call_id = $call->id; 
$call_data = $call->data;
$call_message_id = $call->message->message_id;

if($call){
    $chat_type = $call->message->chat->type;
    $chat_id = $call->message->chat->id;
    $message_id = $call->message->message_id;
};

$langKB = json_encode([
    'disable_web_page_preview' => true,
    'one_time_keyboard' => true,
    'resize_keyboard' => true,
    'inline_keyboard' => [
        [['text' => "O'zbek tili 🇺🇿", 'callback_data' => 'LANG=uz'],['text' => "Узбек тили 🇺🇿", 'callback_data' => 'LANG=kuz']],
        [['text' => "Русский язык 🇷🇺", 'callback_data' => 'LANG=ru'], ['text' => "Tojik tili 🇹🇯", 'callback_data' => 'LANG=tj'],]
    ]
]);

if($chat_type == "private"){
    if(in_array($text, ["/start", "/new"]) || in_array($call_data, ["back=start", "new"])){
        get_lang(['id' => $chat_id, 'name' => $full_name]);
        if($call_data){
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => $langs_menu_text,
                'parse_mode' => 'HTML',
                'reply_markup' => $langKB
            ]);
        }else{
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $langs_menu_text,
                'parse_mode' => 'HTML',
                'reply_markup' => $langKB
            ]);
        };
    }else if(mb_stripos($call_data, "LANG") !== false || $call_data == "back=region"){
        if(mb_stripos($call_data, "LANG") !== false) {
            $lang_code = explode("=", $call_data)[1];
            $words = get_words($lang_code);
            get_lang(['id' => $chat_id], $lang_code); 
        }else if($call_data == "back=region"){
            $words = get_words(get_lang(['id' => $chat_id]));
            post(["post_reset" => true]);
        };

        $i = 0;
        foreach ($words['regions'] as $index => $region) {
            if(in_array($index, range(3, 30, 3))){$i++;};
            $regions[$i][] = ['text' => $region, 'callback_data' => "region=".$index];
        };
        $regions[][] = ['text' => $words['prev_text'], 'callback_data' => "back=start"]; 
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $words['hi_text'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => $regions
            ])
        ]);

    }else if(in_array($text, ["/faq"])){
        $words = get_words(get_lang(['id' => $chat_id, 'faq' => "?"]));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $words['faq'],
            'parse_mode' => 'HTML'
        ]);
    }else if(mb_stripos($call_data, "region") !== false){
        $words = get_words(get_lang(['id' => $chat_id]));
        $region_index = explode("=", $call_data)[1];

        $i = 0;
        foreach ($words['zones'][$region_index] as $index => $zone) {
            if(in_array($index, range(3, 30, 3))){$i++;};
            $zones[$i][] = ['text' => $zone, 'callback_data' => "zone=".$index];
        };
        $zones[][] = ['text' => $words['prev_text'], 'callback_data' => "back=region"]; 
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $words['hi_text'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => $zones
            ])
        ]);
        post(["region" => $region_index + 1]);

    }else if(mb_stripos($call_data, "zone") !== false){
        $words = get_words(get_lang(['id' => $chat_id]));
        $zone_index = explode("=", $call_data)[1];

        $i = 0;
        foreach ($words['regions'] as $index => $region) {
            if(in_array($index, range(3, 30, 3))){$i++;};
            $regions[$i][] = ['text' => $region, 'callback_data' => "end_regio=".$index];
        };
        $regions[][] = ['text' => $words['prev_text'], 'callback_data' => "back=region"]; 
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $words['two_step'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => $regions
            ])
        ]);
        post(["zone" => $zone_index + 1]);

    }else if(mb_stripos($call_data, "end_regio") !== false){
        $words = get_words(get_lang(['id' => $chat_id]));
        $region_index = explode("=", $call_data)[1];

        $i = 0;
        foreach ($words['zones'][$region_index] as $index => $zone) {
            if(in_array($index, range(3, 30, 3))){$i++;};
            $zones[$i][] = ['text' => $zone, 'callback_data' => "end_zon=".$index];
        };
        $zones[][] = ['text' => $words['prev_text'], 'callback_data' => "back=region"]; 
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $words['two_step'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => $zones
            ])
        ]);
        post(["end_region" => $region_index + 1]);

    }else if(mb_stripos($call_data, "end_zon") !== false){
        $words = get_words(get_lang(['id' => $chat_id]));
        $zone_index = explode("=", $call_data)[1];
        $types = $words['types'];
        $keyboard = [
            [
                ['text' => $types[0], 'callback_data' => "type=A"],['text' => $types[1], 'callback_data' => "type=B"]
            ],
            [
                ['text' => $types[2], 'callback_data' => "type=D"],['text' => $types[3], 'callback_data' => "type=E"]
            ],
            [
                ['text' => $types[4], 'callback_data' => "type=F"],['text' => $types[5], 'callback_data' => "type=G"]
            ],
            [
                ['text' => $words['prev_text'], 'callback_data' => "back=zone"]
            ]
        ];

        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $words['types_text'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => $keyboard
            ])
        ]);
        post(["end_zone" => $zone_index + 1, "sts"=> 1]);
    }else if(mb_stripos($call_data, "type") !== false){
        $words = get_words(get_lang(['id' => $chat_id]));
        $type = explode("=", $call_data)[1];
        $post = post(["type" => $type, "sts"=> 1, "post_reset" => true]);
        post_generate($post);

    }else if(mb_stripos($call_data, "delpost=") !== false){
        $words = get_words(get_lang(['id' => $chat_id]));
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $words['succ_del_post'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'disable_web_page_preview' => true,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'inline_keyboard' => [
                    [
                        ['text' => $words['new_post_btn'], 'callback_data' => "new"]
                    ]
                ]
            ])
        ]);
        $id = explode("=", $call_data)[1];
        $post = post(["id" => $id, "del_post" => true]);
    }else if(mb_stripos($call_data, "runpost=") !== false){
        $post_uid = explode("=", $call_data)[1];
        $post = post(["id" => $post_uid, "status" => 2]);
        post_generate($post);
    }else if(mb_stripos($call_data, "changests=") !== false){
        $post = post(["id" => explode("=", $call_data)[1], "changests" => true]);
        post_generate($post);
    }else if($call_data == "fast_tag"){
        $post = post(["fast_tag" => 1]);
        post_generate($post);
    }else if(!in_array($text, ["/start", "/new", "/faq"]) & isset($text)){
        $post = post(["re_post" => 1]);
        $user = get_lang(['id' => $chat_id, 'faq' => "??"]);
        $words = get_words($user['lang']);
        if($user["faq"] == "?"){
            $mess = faq(["text" => $text]);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $words['faq_succ']."<b>N".$mess['id']."</b>.",
                'parse_mode' => 'HTML'
            ]);
            get_lang(['id' => $chat_id, 'faq' => "ok"]);
        }else if(!$post["contact"]){
            $post = post(["id" => $post["id"], "contact" => $text, "q_step" => 1]);
            delmes($message_id);
            post_generate($post);
        }else if(!$post["price"]){
            $post = post(["id" => $post["id"], "price" => $text, "q_step" => 2]);
            delmes($message_id);
            post_generate($post);
        }else if(!$post["description"]){
            $post = post(["id" => $post["id"], "description" => $text, "q_step" => 3]);
            delmes($message_id);
            post_generate($post);
        };
    };
}else if($chat_id == $admins_group & isset($call_data)){
    if(mb_stripos($call_data, "delpost=") !== false){
        $post_uid = post(["id" => explode("=", $call_data)[1], "del_post" => true, "moder" => 2]);
        $post_uid_arr = explode("-p-", $post_uid);
        $u_words = get_words(get_lang(['id' => $post_uid_arr[0]]));
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $u_words['succ_del_post_admin'],
            'parse_mode' => 'HTML'
        ]);
        if($post_uid_arr[0] != $admins_group) {
            bot('editMessageText', [
                'chat_id' => $post_uid_arr[0],
                'message_id' => $post_uid_arr[1],
                'text' => $u_words['warr_del_post_admin']."",
                'parse_mode' => 'HTML'
            ]);
        };
    }else if(mb_stripos($call_data, "checkpost=") !== false){
        $post = post(["id" => explode("=", $call_data)[1], "checkpost" => true, "moder" => 1]);

        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "Elon kanalga yuborildi !",
            'parse_mode' => 'HTML',
        ]);
        post_generate($post);
    };
};


