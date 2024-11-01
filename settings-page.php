    <?php
    if ( ! defined( 'ABSPATH' ) ) exit;
    if ( ! is_admin() ) die;
    global $tdata;
    ?>
    <style type="text/css">
        label {vertical-align: middle;}
        #wgn-wrap div {transition:opacity linear .15s;}
        #wgn-wrap h1, h2, h3, h4, h5, h6 {line-height: normal;}
        #wgn-wrap .howto span {color:#6495ED;font-weight:700;font-style: normal;}
        #wgn-wrap input, button {vertical-align: middle !important;}
        #wgn-wrap a {text-decoration: none !important; border-bottom: 1px solid #0091CD;padding-bottom: 2px;} #wgn-wrap code {padding: 2px 4px; font-size: 90%; color: #c7254e; background-color: #f9f2f4; border-radius: 4px;font-style: normal;}
        #wgn-wrap a:hover {border-bottom: 2px solid #0091EA;}
        #wgn-wrap input[type=text] {font-size: 1.5em; font-family: monospace; font-weight: 300; }
        #wgn-wrap table {width: 100%}
        #wgn-wrap tr, #wgn-wrap th,#wgn-wrap td {vertical-align: baseline !important;padding-top: 0 !important;}
        #floating_save_button {width: 65px;height: 65px; position: absolute;background: #CFD8DC; border-radius: 50%; right: 25px;text-align: center;opacity:0.5;cursor: pointer;}
        #floating_save_button img {position: relative; top: 20px; width: 24px !important; height: 24px !important;} 
        #floating_save_button:hover{opacity: 1;}
        .tabs{width:100%;display:inline-block}
        ul.tab-links {margin-bottom: 0px !important}
        .tab-links:after{display:block;clear:both;content:''}
        .tab-links li{margin:0 5px;float:left;list-style:none}
        .tab-links img {width: 24px !important;height: auto !important;vertical-align: -0.4em !important;margin: 0 5px !important;}
        .tab-links a{padding:9px 15px !important;display:inline-block;border-bottom:0 !important;border-radius:3px 3px 0 0;background:#E0E0E0;font-size:16px;font-weight:600;color:#4c4c4c;transition:all linear .15s;outline: 0 !important;box-shadow: none !important;}
        .tab-links a:hover{background:#D6D6D6;text-decoration:none}
        li.active a,li.active a:hover{background:#fff;color:#4c4c4c}
        .tab-content{padding:15px;border-radius:3px;box-shadow:-1px 1px 1px rgba(0,0,0,0.15);background:#fff}
        .tab{display:none}
        .tab.active{display:block}
        .form-table tr {
            border-bottom: 1px solid #ddd;
        }
        .form-table tr:last-child {
            border-bottom: 0 !important;
        }
        textarea#wgn_channel_pattern {resize: vertical; width: 48%; height: auto;min-height: 128px;}
        div#output {width: 48%; display: inline-block; vertical-align: top; white-space: pre; border: 1px solid #ddd; height: 122px; background: #F1F1F1; cursor: not-allowed;padding: 2px 6px; overflow-y: auto } 
        .wgn-radio-group {line-height: 2em;}
        .wgn-radio-group input {margin-top:1px;}
        .emojione{font-size:inherit;height:3ex;width:3.1ex;min-height:20px;min-width:20px;display:inline-block;margin:-.2ex .15em .2ex;line-height:normal;vertical-align:middle}
        img.emojione{width:auto}
        @media screen and (max-width: 415px){
            .wgn_label {display: none;}
            .tab-links img {width: 32px !important;height: auto !important;}
        }
    </style>
    <?php if(is_rtl()){
        echo '<style type="text/css"> 
        #floating_save_button{left: 25px !important; right: auto !important;}
        .tab-links li {float:right !important;}
        </style>
        ';
    }

    ?>
    <div id="wgn-wrap" class="wrap">
        <h1><?php  echo __("Wp GAP Notifications", "wgn-plugin") ?></h1>
        <p> <?php printf(__("Join our channel in Gap: %s", "wgn-plugin"), "<a href='https://Gap.im/gap_to_wordpress' >@gap_to_wordpress</a>"); ?> </p>
        <?php if( isset($_GET['settings-updated']) ) { ?>
        <div id="message" class="updated">
            <p><strong><?php _e('Settings saved.') ?></strong></p>
        </div>
        <?php } ?>
        <hr>
        <form method="post" action="options.php" id="wgn_form">
        <div id="floating_save_button" title="<?php _e('Save Changes') ?>">&#x1f4be;</div>
            <?php settings_fields( 'wgn-settings-group' ); ?>
            <div class="tabs">
                <ul class="tab-links">
                    <li class="active"><a href="#wgn_tab1">&#128276;<span class="wgn_label"><?php echo __("Notifications", "wgn-plugin") ?></span></a></li>
                    <li><a href="#wgn_tab2">&#x1f4e3;<span class="wgn_label"><?php echo __("Post to Channel", "wgn-plugin") ?></span></a></li>
                </ul>
                <div class="tab-content">
                    <div id="wgn_tab1" class="tab active">
                        <p style="font-size:14px;">
                            <?php echo __("You will receive messages in Gap with the contents of every emails that sent from your WordPress site.", "wgn-plugin"); ?>
                            <br>
                            <?php echo __("For example, once a new comment has been submitted, you will receive the comment in your Gap account.", "wgn-plugin"); ?>
                            <br>
                        </p>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><h3><?php echo __("Instructions", "wgn-plugin") ?></h3></th>
                                <td><br>
                                    <p>
                                      
                                        <ol>
                                            <li><?php printf(__("In Gap app start a new chat with %s .", "wgn-plugin"), "<a href='https://Gap.im/gap_to_wordpress' id='nc_bot' target='_blank' style='text-decoration:none !important' title='Pay attention to underline! NotifcasterBot (without underline) is not the correct bot'>@gap_to_wordpress</a>"); ?><code><?php echo __("Pay attention to underline!", "wgn-plugin") ?></code></li>
                                            <li><?php echo __("Send <code>token</code> command and the bot will give you an API token for the user.", "wgn-plugin") ?></li>
                                            <li><?php echo __("Copy and paste it in the below field and hit the save button!", "wgn-plugin") ?></li> 
                                            <li><?php echo __("Kaboom! You are ready to go.", "wgn-plugin") ?></li>
                                        </ol>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><h3>API Token</h3></th>
                                <td>
                                    <input id="wgn_api_token" type="text" name="wgn_api_token" maxlength="34" size="32" value="<?php echo $tdata['wgn_api_token']->option_value; ?>" dir="auto"/>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><h3><?php  echo __("Send a test Message", "wgn-plugin") ?></h3></th>
                                <td><button id="sendbtn" type="button" class="button-primary" onclick="sendTest();"> <?php  echo __("Send now!", "wgn-plugin") ?> </button></td>
                            </tr>
                            <tr>
                                <th scope="row"><h3><?php  echo __("Hashtag (optional)", "wgn-plugin") ?></h3></th>
                                <td><input id="wgn_hashtag" type="text" name="wgn_hashtag" size="32" value="<?php echo $tdata['wgn_hashtag']->option_value; ?>" dir="auto" />
                                    <p class="howto">
                                        <?php echo __("Insert a custom hashtag at the beginning of the messages.", "wgn-plugin") ?><br>
                                        <?php echo __("Don't forget <code>#</code> at the beginning", "wgn-plugin") ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div id="wgn_tab2">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><h3><?php  echo __("Introduction", "wgn-plugin") ?></h3></th>
                                <td>
                                    <p style="font-weight:700;font-size: 16px;">
                                        <?php echo __("Gap channel is a great way for attracting people to your site.", "wgn-plugin");?>
                                        <br> 
                                        <?php echo __("This option allows you to send posts to your Gap channel. Intresting, no?", "wgn-plugin"); ?>
                                        <br>
                                        <?php echo __("So let's start!", "wgn-plugin"); ?> 
                                    </p>
                                    <ol>
                                        <li><?php printf(__("In Gap app Enter the <code>Platform Bot</code> <code>sector business</code> with %s .", "wgn-plugin"), "<a href='https://Gap.im/gap_to_wordpress' id='nc_bot' target='_blank' style='text-decoration:none !important'>@gap_to_wordpress</a>"); ?></li>
                                        <li><?php echo __("Create a Public service (if you don't already have one).", "wgn-plugin") ?></li>

                                        <li><?php echo __("Copy the bot token and paste it in the below field.", "wgn-plugin") ?></li>
                                        <li><?php echo __("Enter the username of the channel and hit SAVE button!!!", "wgn-plugin") ?></li>
                                        <li><?php echo __("Yes! Now, whenever you publish or update a post you can choose whether send it to Gap (from post editor page)", "wgn-plugin") ?></li>
                                    </ol>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><h3>Bot Token</h3></th>
                                <td>
                                    <input id="wgn_bot_token" type="text" name="wgn_bot_token" size="32" value="<?php echo $tdata['wgn_bot_token']->option_value; ?>" dir="auto" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><h3><?php echo __("Channel Username", "wgn-plugin") ?></h3></th>
                                <td>
                                    <input id="wgn_channel_username" type="text" name="wgn_channel_username" size="32" value="<?php echo $tdata['wgn_channel_username']->option_value; ?>" onblur="getMembersCount()" dir="auto" />
                                    <button id="channelbtn" type="button" class="button-secondary" onclick="channelTest();"> <?php  echo __("Send now!", "wgn-plugin") ?></button>
                                    
                                    <p class="howto"><?php echo __("Don't forget <code>@</code> at the beginning", "wgn-plugin") ?></p>
                                </td>
                            </tr>
                            <tr>
                                <?php
                                $preview = $tdata['wgn_web_preview']->option_value;
                                $excerpt_length = $tdata['wgn_excerpt_length']->option_value;
                                $tstc = $tdata['wgn_send_to_channel']->option_value;
                                $tstc = $tstc != "" ? $tstc : 0 ;
                                $cp = $tdata['wgn_channel_pattern']->option_value;
                                $s = $tdata['wgn_send_thumb']->option_value;
                                $m = $tdata['wgn_markdown']->option_value;
                                $ipos = $tdata['wgn_img_position']->option_value;
                                ?>
                                <th scope="row"><h3><?php echo __("Send to Gap Status (Global)", "wgn-plugin") ?></h3></th>
                                <td>
                                    <p class="howto"><?php echo __("By checking one of the below options, you don't need to set \"Send to Gap Status\" every time you create a new post.", "wgn-plugin") ?></p><br>
                                    <div class="wgn-radio-group">
                                        <input type="radio" id="wgn_send_to_channel_yes" name="wgn_send_to_channel" value="1" <?php checked( '1', $tstc ); ?>/><label for="wgn_send_to_channel"><?php echo __('Always send', 'wgn-plugin' ) ?> </label><br>
                                        <input type="radio" id="wgn_send_to_channel_no" name="wgn_send_to_channel" value="0" <?php checked( '0', $tstc ); ?>/><label for="wgn_send_to_channel"><?php echo __('Never Send', 'wgn-plugin' ) ?> </label>
                                    </div>
                                    <p class="howto"><?php echo __("You can override the above settings in post-edit screen", "wgn-plugin") ?></p>
                                    <br>
                                </td>
                            </tr>

                            <?php require_once(wgn_PLUGIN_DIR."/inc/composer.php"); ?>

                            
                            <tr>
                                <th scope="row"><h3><?php echo __("Excerpt length", "wgn-plugin") ?></h3></th>
                                <td>
                                    <p class="howto"><?php echo __("By default, the plugin replaces {excerpt} tag with 55 first words of your post content. You can change the number of words here:", "wgn-plugin") ?></p><br>
                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" id="wgn_excerpt_length" name="wgn_excerpt_length" value="<?php echo $excerpt_length ?>"/>
                                    <label for="wgn_excerpt_length"><?php echo __('words in excerpt', 'wgn-plugin' ) ?> </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
        <div id="support">
        <?php  
        $message = sprintf (__('If you like this plugin, please rate it in %1$s. You can also support us by %2$s', "wgn-plugin"), '<a href="https://wordpress.org/plugins/wgn" target="_blank">'.__('Wp GAP Notifications page in wordpress.org', "wgn-plugin").'</a>', '<a href="https://zarinp.al/@wgn" target="_blank" title="donate">'.__("donating", "wgn-plugin").'</a>' );
        echo $message; ?>
        </div>
    </div>
    <script type="text/javascript">
    function sendTest() {
        var api_token = jQuery('input[name=wgn_api_token]').val(), h = '';
        if(api_token != '' ) {
            jQuery('#sendbtn').prop('disabled', true);
            jQuery('#sendbtn').text('<?php echo __("Please wait...", "wgn-plugin") ?> ');
            if(jQuery("#wgn_hashtag").val() != ''){
                var h = '<?php  echo $tdata["wgn_hashtag"]->option_value; ?>';
            }
            var msg = h +'\n'+'<?php echo __("This is a test message", "wgn-plugin") ?>'+ '\n' + document.URL;
            jQuery.post(ajaxurl, 
            { 
                msg: msg , api_token: api_token, subject: 'm', action:'wgn_ajax_test'
            }, function( data ) {
                alert(data.description);
                jQuery('#sendbtn').prop('disabled', false);
                jQuery('#sendbtn').text('<?php  echo __("Send now!", "wgn-plugin") ?>');
            }, 
            'json'); 
        } else {
            alert(' <?php  echo __("api_token field is empty", "wgn-plugin") ?>') 
        }
    };
    function channelTest() {
        var bot_token = jQuery('input[name=wgn_bot_token]').val(), channel_username = jQuery('input[name=wgn_channel_username]').val(), pattern = jQuery("#wgn_channel_pattern").val();
        if(bot_token != '' && channel_username != '' ) {
            var c = confirm('<?php echo __("This will send a test message to your channel. Do you want to continue?", "wgn-plugin") ?>');
            if( c == true ){ 
                jQuery('#channelbtn').prop('disabled', true);
                jQuery('#channelbtn').text('<?php echo __("Please wait...", "wgn-plugin") ?> '); 
                var msg = '<?php echo __("This is a test message", "wgn-plugin") ?>'+'\n';
                if (pattern != null || pattern != ''){
                    msg += pattern;
                }
                jQuery.post(ajaxurl, 
                { 
                    channel_username: channel_username, msg: msg , bot_token: bot_token, markdown: jQuery('input[name=wgn_markdown]:checked').attr("data-markdown"), web_preview: jQuery('#wgn_web_preview').prop('checked'), subject: 'c', action:'wgn_ajax_test'
                }, function( data ) {
                    jQuery('#channelbtn').prop('disabled', false);
                    jQuery('#channelbtn').text('<?php  echo __("Send now!", "wgn-plugin") ?>'); 
                    alert((data.ok == true ? 'The message sent succesfully.' : data.description))}, 'json');
            }
        } else {
            alert(' <?php  echo __("bot token/channel username field is empty", "wgn-plugin") ?>') 
        }
    }
    function botTest() {
        if(jQuery('input[name=wgn_bot_token]').val() != '' ) {
            var bot_token = jQuery('input[name=wgn_bot_token]').val();
            jQuery('#checkbot').prop('disabled', true);
            jQuery('#checkbot').text('<?php echo __("Please wait...", "wgn-plugin") ?> ');
            jQuery.post(ajaxurl, 
            { 
                bot_token: bot_token, subject: 'b', action:'wgn_ajax_test'
            }, function( data ) {
                if (data != undefined && data.ok != false){
                    jQuery('#bot_name').text(data.result.first_name + ' ' + (data.result.last_name == undefined ? ' ' :  data.result.last_name ) + '(@' + data.result.username + ')')
                }else {
                    jQuery('#bot_name').text(data.description)
                }
                jQuery('#checkbot').prop('disabled', false);
                jQuery('#checkbot').text('<?php echo __("Check bot token", "wgn-plugin") ?>');
            }, 'json'); 
        } else {
            alert(' <?php  echo __("bot token field is empty", "wgn-plugin") ?>') 
        }
    }
    function getMembersCount() {
        var channel_username = jQuery('input[name=wgn_channel_username]').val();
        var bot_token = jQuery('input[name=wgn_bot_token]').val();
        if(channel_username !== '' && bot_token !== '') {
            jQuery.post(ajaxurl, 
            { 
                bot_token: bot_token, channel_username: channel_username, subject: 'gm', action:'wgn_ajax_test'
            }, function( data ) {
                if (data != undefined && data.ok != false){
                    jQuery('#members_count').text(data.result+ " " +"<?php echo __("members", "wgn-plugin"); ?>");
                }else {
                    jQuery('#members_count').text(data.description);
                }
            }, 'json'); 
        } else {
            return;
        }
    }
    </script>