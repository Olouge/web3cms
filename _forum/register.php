<?php

/*---

	Copyright (C) 2008-2009 FluxBB.org
	based on code copyright (C) 2002-2005 Rickard Andersson
	License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher

---*/

require SHELL_PATH . 'include/common.php';
// If we are logged in, we shouldn't be here
if (!$pun_user['is_guest'])
{
    Yii::app()->request->redirect(Yii::app()->createUrl('forum/'));
}
// Load the register.php language file
require SHELL_PATH . 'lang/' . $pun_user['language'] . '/register.php';
// Load the register.php/profile.php language file
require SHELL_PATH . 'lang/' . $pun_user['language'] . '/prof_reg.php';

if ($pun_config['o_regs_allow'] == '0')
    message($lang_register['No new regs']);
// User pressed the cancel button
if (isset($_GET['cancel']))
    redirect('index.php', $lang_register['Reg cancel redirect']);

else if ($pun_config['o_rules'] == '1' && !isset($_GET['agree']) && !isset($_POST['form_sent']))
{
    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_register['Register'];
    require SHELL_PATH . 'header.php';

    ?>
<div class="blockform">
	<h2><span><?php echo $lang_register['Forum rules'] ?></span></h2>
	<div class="box">
		<?php echo CHtml::form('register', 'GET');?>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Rules legend'] ?></legend>
					<div class="infldset">
						<div class="usercontent"><?php echo $pun_config['o_rules_message'] ?></div>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="agree" value="<?php echo $lang_register['Agree'] ?>" /> <input type="submit" name="cancel" value="<?php echo $lang_register['Cancel'] ?>" /></p>
		</form>
	</div>
</div>
<?php

    require SHELL_PATH . 'footer.php';
}
// Start with a clean slate
$errors = array();

if (isset($_POST['form_sent']))
{
    // Check that someone from this IP didn't register a user within the last hour (DoS prevention)
    $db->setQuery('SELECT 1 FROM ' . $db->tablePrefix . 'users WHERE registration_ip=\'' . get_remote_address() . '\' AND registered>' . (time() - 3600)) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

    if ($db->num_rows())
        message($lang_register['Registration flood']);

    $username = pun_trim($_POST['req_username']);
    $email1 = strtolower(trim($_POST['req_email1']));

    if ($pun_config['o_regs_verify'] == '1')
    {
        $email2 = strtolower(trim($_POST['req_email2']));

        $password1 = random_pass(8);
        $password2 = $password1;
    }
    else
    {
        $password1 = trim($_POST['req_password1']);
        $password2 = trim($_POST['req_password2']);
    }
    // Convert multiple whitespace characters into one (to prevent people from registering with indistinguishable usernames)
    $username = preg_replace('#\s+#s', ' ', $username);
    // Validate username and passwords
    if (strlen($username) < 2)
        $errors[] = $lang_prof_reg['Username too short'];
    else if (pun_strlen($username) > 25) // This usually doesn't happen since the form element only accepts 25 characters
        $errors[] = $lang_prof_reg['Username too long'];
    else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest']))
        $errors[] = $lang_prof_reg['Username guest'];
    else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username) || preg_match('/((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))/', $username))
        $errors[] = $lang_prof_reg['Username IP'];
    else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
        $errors[] = $lang_prof_reg['Username reserved chars'];
    else if (preg_match('/(?:\[\/?(?:b|u|i|h|colou?r|quote|code|img|url|email|list)\]|\[(?:code|quote|list)=)/i', $username))
        $errors[] = $lang_prof_reg['Username BBCode'];
    // Check username for any censored words
    if ($pun_config['o_censoring'] == '1')
    {
        // If the censored username differs from the username
        if (censor_words($username) != $username)
            $errors[] = $lang_register['Username censor'];
    }
    // Check that the username (or a too similar username) is not already registered
    $db->setQuery('SELECT username FROM ' . $db->tablePrefix . 'users WHERE UPPER(username)=UPPER(\'' . $db->escape($username) . '\') OR UPPER(username)=UPPER(\'' . $db->escape(preg_replace('/[^\w]/', '', $username)) . '\')') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

    if ($db->num_rows())
    {
        $busy = $db->result($result);
        $errors[] = $lang_register['Username dupe 1'] . ' ' . pun_htmlspecialchars($busy) . '. ' . $lang_register['Username dupe 2'];
    }

    foreach ($pun_bans as $cur_ban)
    {
        if ($cur_ban['username'] != '' && utf8_strtolower($username) == utf8_strtolower($cur_ban['username']))
        {
            $errors[] = $lang_prof_reg['Banned username'];
            break;
        }
    }

    if (strlen($password1) < 4)
        $errors[] = $lang_prof_reg['Pass too short'];
    else if ($password1 != $password2)
        $errors[] = $lang_prof_reg['Pass not match'];
    // Validate email
    require SHELL_PATH . 'include/email.php';

    if (!is_valid_email($email1))
        $errors[] = $lang_common['Invalid email'];
    else if ($pun_config['o_regs_verify'] == '1' && $email1 != $email2)
        $errors[] = $lang_register['Email not match'];
    // Check if it's a banned email address
    if (is_banned_email($email1))
    {
        if ($pun_config['p_allow_banned_email'] == '0')
            $errors[] = $lang_prof_reg['Banned email'];

        $banned_email = true; // Used later when we send an alert email
    }
    else
        $banned_email = false;
    // Check if someone else already has registered with that email address
    $dupe_list = array();

    $db->setQuery('SELECT username FROM ' . $db->tablePrefix . 'users WHERE email=\'' . $db->escape($email1) . '\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows())
    {
        if ($pun_config['p_allow_dupe_email'] == '0')
            $errors[] = $lang_prof_reg['Dupe email'];

        while ($cur_dupe = $db->fetch_assoc())
        $dupe_list[] = $cur_dupe['username'];
    }
    // Make sure we got a valid language string
    if (isset($_POST['language']))
    {
        $language = preg_replace('#[\.\\\/]#', '', $_POST['language']);
        if (!file_exists(SHELL_PATH . 'lang/' . $language . '/common.php'))
            message($lang_common['Bad request']);
    }
    else
        $language = $pun_config['o_default_lang'];

    $timezone = round($_POST['timezone'], 1);

    $dst = isset($_POST['dst']) ? '1' : '0';

    $email_setting = intval($_POST['email_setting']);
    if ($email_setting < 0 || $email_setting > 2) $email_setting = $pun_config['o_default_email_setting'];
    // Did everything go according to plan?
    if (empty($errors))
    {
        // Insert the new user into the database. We do this now to get the last inserted ID for later use
        $now = time();

        $intial_group_id = ($pun_config['o_regs_verify'] == '0') ? $pun_config['o_default_user_group'] : PUN_UNVERIFIED;
        $password_hash = pun_hash($password1);
        // Add the user
        $db->setQuery('INSERT INTO ' . $db->tablePrefix . 'users (username, group_id, password, email, email_setting, timezone, dst, language, style, registered, registration_ip, last_visit) VALUES(\'' . $db->escape($username) . '\', ' . $intial_group_id . ', \'' . $password_hash . '\', \'' . $db->escape($email1) . '\', ' . $email_setting . ', ' . $timezone . ' , ' . $dst . ', \'' . $db->escape($language) . '\', \'' . $pun_config['o_default_style'] . '\', ' . $now . ', \'' . get_remote_address() . '\', ' . $now . ')')->execute() or error('Unable to create user', __FILE__, __LINE__, $db->error());
        $new_uid = $db->insert_id();
        // If we previously found out that the email was banned
        if ($banned_email && $pun_config['o_mailing_list'] != '')
        {
            $mail_subject = $lang_common['Banned email notification'];
            $mail_message = sprintf($lang_common['Banned email register message'], $username, $email1) . "\n";
            $mail_message .= sprintf($lang_common['User profile'], $pun_config['o_WEB_PATH'] . '/profile.php?id=' . $new_uid) . "\n";
            $mail_message .= "\n" . '--' . "\n" . $lang_common['Email signature'];

            pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
        }
        // If we previously found out that the email was a dupe
        if (!empty($dupe_list) && $pun_config['o_mailing_list'] != '')
        {
            $mail_subject = $lang_common['Duplicate email notification'];
            $mail_message = sprintf($lang_common['Duplicate email register message'], $username, implode(', ', $dupe_list)) . "\n";
            $mail_message .= sprintf($lang_common['User profile'], $pun_config['o_WEB_PATH'] . '/profile.php?id=' . $new_uid) . "\n";
            $mail_message .= "\n" . '--' . "\n" . $lang_common['Email signature'];

            pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
        }
        // Should we alert people on the admin mailing list that a new user has registered?
        if ($pun_config['o_regs_report'] == '1')
        {
            $mail_subject = $lang_common['New user notification'];
            $mail_message = sprintf($lang_common['New user message'], $username, $pun_config['o_WEB_PATH'] . '/') . "\n";
            $mail_message .= sprintf($lang_common['User profile'], $pun_config['o_WEB_PATH'] . '/profile.php?id=' . $new_uid) . "\n";
            $mail_message .= "\n" . '--' . "\n" . $lang_common['Email signature'];

            pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
        }
        // Must the user verify the registration or do we log him/her in right now?
        if ($pun_config['o_regs_verify'] == '1')
        {
            // Load the "welcome" template
            $mail_tpl = trim(file_get_contents(SHELL_PATH . 'lang/' . $pun_user['language'] . '/mail_templates/welcome.tpl'));
            // The first row contains the subject
            $first_crlf = strpos($mail_tpl, "\n");
            $mail_subject = trim(substr($mail_tpl, 8, $first_crlf - 8));
            $mail_message = trim(substr($mail_tpl, $first_crlf));

            $mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
            $mail_message = str_replace('<WEB_PATH>', $pun_config['o_WEB_PATH'] . '/', $mail_message);
            $mail_message = str_replace('<username>', $username, $mail_message);
            $mail_message = str_replace('<password>', $password1, $mail_message);
            $mail_message = str_replace('<login_url>', $pun_config['o_WEB_PATH'] . '/login.php', $mail_message);
            $mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'] . ' ' . $lang_common['Mailer'], $mail_message);

            pun_mail($email1, $mail_subject, $mail_message);

            message($lang_register['Reg email'] . ' ' . CHtml::link($pun_config['o_admin_email'], 'mailto:' . $pun_config['o_admin_email']), true);
        }

        pun_setcookie($new_uid, $password_hash, time() + $pun_config['o_timeout_visit']);

        redirect('index.php', $lang_register['Reg complete']);
    }
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_register['Register'];
$required_fields = array('req_username' => $lang_common['Username'], 'req_password1' => $lang_common['Password'], 'req_password2' => $lang_prof_reg['Confirm pass'], 'req_email1' => $lang_common['Email'], 'req_email2' => $lang_common['Email'] . ' 2');
$focus_element = array('register', 'req_username');
require SHELL_PATH . 'header.php';

$timezone = isset($timezone) ? $timezone : $pun_config['o_default_timezone'];
$dst = isset($dst) ? $dst : $pun_config['o_default_dst'];
$email_setting = isset($email_setting) ? $email_setting : $pun_config['o_default_email_setting'];

?>
<?php
// If there are errors, we display them
if (!empty($errors))
{?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_register['Registration errors'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $lang_register['Registration errors info'] ?></p>
			<ul>
<?php

    while (list(, $cur_error) = each($errors))
    echo "\t\t\t\t" . '<li><strong>' . $cur_error . '</strong></li>' . "\n";

    ?>
			</ul>
		</div>
	</div>
</div>

<?php

}

?>
<div class="blockform">
	<h2><span><?php echo $lang_register['Register'] ?></span></h2>
	<div class="box">
		<?php echo CHtml::form(array('register','action'=>'register'), 'POST', array('id'=>'register','onsubmit'=>'this.register.disabled=true;if(process_form(this)){return true;}else{this.register.disabled=false;return false;}'));?>
			<div class="inform">
				<div class="forminfo">
					<h3><?php echo $lang_common['Important information'] ?></h3>
					<p><?php echo $lang_register['Desc 1'] ?></p>
					<p><?php echo $lang_register['Desc 2'] ?></p>
				</div>
				<fieldset>
					<legend><?php echo $lang_register['Username legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<label><strong><?php echo $lang_common['Username'] ?></strong><br /><input type="text" name="req_username" value="<?php if (isset($_POST['req_username'])) echo pun_htmlspecialchars($_POST['req_username']); ?>" size="25" maxlength="25" /><br /></label>
					</div>
				</fieldset>
			</div>
<?php if ($pun_config['o_regs_verify'] == '0'): ?>			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Pass legend'] ?></legend>
					<div class="infldset">
						<label class="conl"><strong><?php echo $lang_common['Password'] ?></strong><br /><input type="password" name="req_password1" value="<?php if (isset($_POST['req_password1'])) echo pun_htmlspecialchars($_POST['req_password1']); ?>" size="16" maxlength="16" /><br /></label>
						<label class="conl"><strong><?php echo $lang_prof_reg['Confirm pass'] ?></strong><br /><input type="password" name="req_password2" value="<?php if (isset($_POST['req_password2'])) echo pun_htmlspecialchars($_POST['req_password2']); ?>" size="16" maxlength="16" /><br /></label>
						<p class="clearb"><?php echo $lang_register['Pass info'] ?></p>
					</div>
				</fieldset>
			</div>
<?php endif; ?>			<div class="inform">
				<fieldset>
					<legend><?php echo ($pun_config['o_regs_verify'] == '1') ? $lang_prof_reg['Email legend 2'] : $lang_prof_reg['Email legend'] ?></legend>
					<div class="infldset">
<?php if ($pun_config['o_regs_verify'] == '1'): ?>						<p><?php echo $lang_register['Email info'] ?></p>
<?php endif; ?>						<label><strong><?php echo $lang_common['Email'] ?></strong><br />
						<input type="text" name="req_email1" value="<?php if (isset($_POST['req_email1'])) echo pun_htmlspecialchars($_POST['req_email1']); ?>" size="50" maxlength="50" /><br /></label>
<?php if ($pun_config['o_regs_verify'] == '1'): ?>						<label><strong><?php echo $lang_register['Confirm email'] ?></strong><br />
						<input type="text" name="req_email2" value="<?php if (isset($_POST['req_email2'])) echo pun_htmlspecialchars($_POST['req_email2']); ?>" size="50" maxlength="50" /><br /></label>
<?php endif; ?>					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_prof_reg['Localisation legend'] ?></legend>
					<div class="infldset">
						<p><?php echo $lang_prof_reg['Time zone info'] ?></p>
						<label><?php echo $lang_prof_reg['Time zone'] ?>
						<br /><select id="time_zone" name="timezone">
							<option value="-12"<?php if ($timezone == - 12) echo ' selected="selected"' ?>>-12</option>
							<option value="-11"<?php if ($timezone == - 11) echo ' selected="selected"' ?>>-11</option>
							<option value="-10"<?php if ($timezone == - 10) echo ' selected="selected"' ?>>-10</option>
							<option value="-9.5"<?php if ($timezone == - 9.5) echo ' selected="selected"' ?>>-09.5</option>
							<option value="-9"<?php if ($timezone == - 9) echo ' selected="selected"' ?>>-09</option>
							<option value="-8.5"<?php if ($timezone == - 8.5) echo ' selected="selected"' ?>>-08.5</option>
							<option value="-8"<?php if ($timezone == - 8) echo ' selected="selected"' ?>>-08 PST</option>
							<option value="-7"<?php if ($timezone == - 7) echo ' selected="selected"' ?>>-07 MST</option>
							<option value="-6"<?php if ($timezone == - 6) echo ' selected="selected"' ?>>-06 CST</option>
							<option value="-5"<?php if ($timezone == - 5) echo ' selected="selected"' ?>>-05 EST</option>
							<option value="-4"<?php if ($timezone == - 4) echo ' selected="selected"' ?>>-04 AST</option>
							<option value="-3.5"<?php if ($timezone == - 3.5) echo ' selected="selected"' ?>>-03.5</option>
							<option value="-3"<?php if ($timezone == - 3) echo ' selected="selected"' ?>>-03 ADT</option>
							<option value="-2"<?php if ($timezone == - 2) echo ' selected="selected"' ?>>-02</option>
							<option value="-1"<?php if ($timezone == - 1) echo ' selected="selected"' ?>>-01</option>
							<option value="0"<?php if ($timezone == 0) echo ' selected="selected"' ?>>00 GMT</option>
							<option value="1"<?php if ($timezone == 1) echo ' selected="selected"' ?>>+01 CET</option>
							<option value="2"<?php if ($timezone == 2) echo ' selected="selected"' ?>>+02</option>
							<option value="3"<?php if ($timezone == 3) echo ' selected="selected"' ?>>+03</option>
							<option value="3.5"<?php if ($timezone == 3.5) echo ' selected="selected"' ?>>+03.5</option>
							<option value="4"<?php if ($timezone == 4) echo ' selected="selected"' ?>>+04</option>
							<option value="4.5"<?php if ($timezone == 4.5) echo ' selected="selected"' ?>>+04.5</option>
							<option value="5"<?php if ($timezone == 5) echo ' selected="selected"' ?>>+05</option>
							<option value="5.5"<?php if ($timezone == 5.5) echo ' selected="selected"' ?>>+05.5</option>
							<option value="6"<?php if ($timezone == 6) echo ' selected="selected"' ?>>+06</option>
							<option value="6.5"<?php if ($timezone == 6.5) echo ' selected="selected"' ?>>+06.5</option>
							<option value="7"<?php if ($timezone == 7) echo ' selected="selected"' ?>>+07</option>
							<option value="8"<?php if ($timezone == 8) echo ' selected="selected"' ?>>+08</option>
							<option value="9"<?php if ($timezone == 9) echo ' selected="selected"' ?>>+09</option>
							<option value="9.5"<?php if ($timezone == 9.5) echo ' selected="selected"' ?>>+09.5</option>
							<option value="10"<?php if ($timezone == 10) echo ' selected="selected"' ?>>+10</option>
							<option value="10.5"<?php if ($timezone == 10.5) echo ' selected="selected"' ?>>+10.5</option>
							<option value="11"<?php if ($timezone == 11) echo ' selected="selected"' ?>>+11</option>
							<option value="11.5"<?php if ($timezone == 11.5) echo ' selected="selected"' ?>>+11.5</option>
							<option value="12"<?php if ($timezone == 12) echo ' selected="selected"' ?>>+12</option>
							<option value="13"<?php if ($timezone == 13) echo ' selected="selected"' ?>>+13</option>
							<option value="14"<?php if ($timezone == 14) echo ' selected="selected"' ?>>+14</option>
						</select>
						<br /></label>
						<div class="rbox">
							<label><input type="checkbox" name="dst" value="1"<?php if ($dst == '1') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['DST'] ?><br /></label>
						</div>
<?php

                                                                                                                                                                $languages = array();
                                                                                                                                                            $d = dir(SHELL_PATH . 'lang');
                                                                                                                                                            while (($entry = $d->read()) !== false)
                                                                                                                                                            {
                                                                                                                                                                if ($entry != '.' && $entry != '..' && is_dir(SHELL_PATH . 'lang/' . $entry) && file_exists(SHELL_PATH . 'lang/' . $entry . '/common.php'))
                                                                                                                                                                    $languages[] = $entry;
                                                                                                                                                            }
                                                                                                                                                            $d->close();
                                                                                                                                                            // Only display the language selection box if there's more than one language available
                                                                                                                                                            if (count($languages) > 1)
                                                                                                                                                            {?>
							<label><?php echo $lang_prof_reg['Language'] ?>: <?php echo $lang_prof_reg['Language info'] ?>
							<br /><select name="language">
<?php

                                                                                                                                                                while (list(, $temp) = @each($languages))
                                                                                                                                                                {
                                                                                                                                                                    if ($pun_config['o_default_lang'] == $temp)
                                                                                                                                                                        echo "\t\t\t\t\t\t\t\t" . '<option value="' . $temp . '" selected="selected">' . $temp . '</option>' . "\n";
                                                                                                                                                                    else
                                                                                                                                                                        echo "\t\t\t\t\t\t\t\t" . '<option value="' . $temp . '">' . $temp . '</option>' . "\n";
                                                                                                                                                                }

                                                                                                                                                                ?>
							</select>
							<br /></label>
<?php

                                                                                                                                                            }

                                                                                                                                                            ?>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_prof_reg['Privacy options legend'] ?></legend>
					<div class="infldset">
						<p><?php echo $lang_prof_reg['Email setting info'] ?></p>
						<div class="rbox">
							<label><input type="radio" name="email_setting" value="0"<?php if ($email_setting == '0') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['Email setting 1'] ?><br /></label>
							<label><input type="radio" name="email_setting" value="1"<?php if ($email_setting == '1') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['Email setting 2'] ?><br /></label>
							<label><input type="radio" name="email_setting" value="2"<?php if ($email_setting == '2') echo ' checked="checked"' ?> /><?php echo $lang_prof_reg['Email setting 3'] ?><br /></label>
						</div>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="register" value="<?php echo $lang_register['Register'] ?>" /></p>
		</form>
	</div>
</div>
<?php

                                                                                                                                                                        require SHELL_PATH . 'footer.php';