<?php

add_action( 'rest_api_init', function () {
    // Habilitar registro de usuario
    register_rest_route( 'wp/v2', '/users/register', array(
        'methods' => 'POST',
        'callback' => 'register_user',
    ) );
} );

function getUsernameApi($text) {
   $username = strtolower(explode("@", $text)[0]);
   return $username;
}

function register_user( $request ) {
	$username = getUsernameApi(sanitize_text_field( $request['email'] ));
	$name = sanitize_text_field( $request['name'] );
    $email = sanitize_email( $request['email'] );
	$englishLevel = sanitize_text_field( $request['english_level'] );
	$password = wp_generate_password();
	
    $userdata = array(
        'user_login'    => $username,
        'user_email'    => $email,
        'user_pass'     => $password,
		'first_name'    => $name,
		'english_level'    => $englishLevel,
        'role'          => 'subscriber',
    );

	// Comparacion si existe el usuario
	$user = get_user_by('login', $username);

	if ( $user ) {
		// Si el usuario existe, hacer algo con él
		//echo 'El usuario existe';
		$user_id = $user->ID;
		$user_data = array(
			'ID' => $user_id,
			'english_level' => $englishLevel
		);
		$updated = wp_update_user( $user_data );

        if ( is_wp_error( $updated ) ) {
            $error = $updated->get_error_message();
            return new WP_Error( 'update_error', $error, array( 'status' => 400 ) );
        } else {
			
			sendMailUpdate($user_id);
			
            return array( 
                'status' => 'success',
                'message' => 'The update has been completed successful',
                'code' => 200
            );
		}
	} else {
		// Si el usuario no existe, hacer algo más
		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			$error = $user_id->get_error_message();
			return new WP_Error( 'registration_error', $error, array( 'status' => 400 ) );
		} else {
			
			sendMail($user_id);
			return array( 
				'status' => 'success',
				'message'=> 'Registration has been completed successfully',
				'code' => 200 );
		}
	}
}

// Add new field English Level 

add_filter('user_contactmethods', 'addFieldEnglishLevel');
 
function addFieldEnglishLevel($user_contactmethods){
  $user_contactmethods['english_level'] = __('English Level');
  return $user_contactmethods;
}

// Save in Database

add_action('user_register', 'saveEnglishLevel');

function saveEnglishLevel($user_id){
  if ( isset($_POST['english_level']) ){
    $english_level = $_POST['english_level'];
    add_user_meta($user_id, 'english_level', $english_level);
  }
}




// send email
 
function sendMail($user_id) {
    $user = get_userdata($user_id);
    $email = $user->user_email;
    $username = $user->user_login;
	$englishLevel = $user->english_level;
	$password = wp_generate_password();
	
    wp_set_password($password, $user_id); // create a temporal password

    $subject = 'Login credentials';
    $message = 'Welcome '.$username.',<br><br>';
    $message .= 'Your English level is: '.$englishLevel.'<br><br>';
    $message .= 'Below are your login credentials:<br><br>';
    $message .= 'Username: '.$username.'<br>';
    $message .= 'Password: '.$password.'<br><br>';
    $message .= 'Access your account here:  '.get_site_url().'. <br><br>';
    $message .= 'Best regards,<br>';

    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($email, $subject, $message, $headers);
}

function sendMailUpdate($user_id) {
    $user = get_userdata($user_id);
    $email = $user->user_email;
    $username = $user->user_login;
	$englishLevel = $user->english_level;

    $subject = 'Congratulations!';
    $message = 'Congratulations!  '.$username.',<br><br>';
    $message .= 'Your English level is: '.$englishLevel.' <br><br>';
    $message .= 'Access your account here:  '.get_site_url().'. <br><br>';
    $message .= 'Best regards,<br>';

    $headers = array('Content-Type: text/html; charset=UTF-8');

	
    wp_mail($email, $subject, $message, $headers);

}