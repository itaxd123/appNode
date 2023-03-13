# Formulario de Registro para WordPress API

Este es un formulario de registro desarrollado con Node.js que se conecta con la API de WordPress para enviar un nuevo registro de usuario. Este formulario utiliza el método POST para enviar los datos y utiliza la biblioteca axios para hacer la petición a la API de WordPress.

**Requisitos**
- Node.js instalado en tu computadora
- API de WordPress habilitada en tu sitio web
- Un usuario con permisos para agregar nuevos registros de usuario

**Instalación**
- Clona o descarga este repositorio en tu computadora.
- Abre una terminal y navega hasta la carpeta del proyecto.
- Ejecuta el siguiente comando para instalar las dependencias:

`npm install`


/////////////////////////////////////////////////////////////////

**Configuración de API WordPress para Registro de Usuario**

Para habilitar el registro de usuario en la API de WordPress, se debe agregar el siguiente código en el archivo functions.php del tema activo o en el archivo mu-plugins de WordPress:

`add_action( 'rest_api_init', function () { `<br>`
    // Habilitar registro de usuario`<br>`
   register_rest_route( 'wp/v2', '/users/register', array(`<br>`
        'methods' => 'POST',`<br>`
        'callback' => 'register_user',`<br>`
    ) );`<br>`
} );`<br>`

Para registrar un usuario, se debe enviar una solicitud HTTP POST a la siguiente URL: `https://tusitio.com/wp-json/wp/v2/users/register` con los siguientes parámetros:

* `name`: Nombre completo del usuario.
* `email`: Correo electrónico del usuario.
* `english_level`: Nivel de inglés del usuario.
El siguiente código muestra la función register_user que maneja el registro de usuarios a través de la API de WordPress:
`
function getUsernameApi($text) {
   $username = strtolower(explode("@", $text)[0]);
   return $username;
}`

`function register_user( $request ) {
	$username = getUsernameApi(sanitize_text_field( $request['email'] ));
	$name = sanitize_text_field( $request['name'] );
        $email = sanitize_email( $request['email'] );
	$englishLevel = sanitize_text_field( $request['english_level'] );
	$password = wp_generate_password();`
	
    $userdata = array(
        'user_login'    => $username,
        'user_email'    => $email,
        'user_pass'     => $password,
	'first_name'    => $name,
	'english_level' => $englishLevel,
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
				'code' => 200 
			);
		}
	}}`

Para agregar un nuevo campo personalizado en el perfil de usuario, se puede utilizar el filtro user_contactmethods. El siguiente código muestra cómo agregar el campo "Nivel de inglés":

`// Add new field English Level `<br>`
add_filter('user_contactmethods', 'addFieldEnglishLevel');

`function addFieldEnglishLevel($user_contactmethods){ `<br>`
  $user_contactmethods['english_level'] = __('English Level');
  return $user_contactmethods;
}`

Este código agrega un nuevo campo "Nivel de inglés" en el perfil de usuario. Para guardar el valor de este campo en la base de datos, se puede utilizar el siguiente código:

`// Save in Database
add_action('user_register', 'saveEnglishLevel');

function saveEnglishLevel($user_id){
  if ( isset($_POST['english_level']) ){
    $english_level = $_POST['english_level'];
    add_user_meta($user_id, 'english_level', $english_level);
  }
}`

Este código guarda el valor del campo "Nivel de inglés" en la base de datos cuando un usuario se registra en el sitio.

Este código muestra dos funciones para enviar correos electrónicos en diferentes escenarios.

La primera función, sendMail, se llama después de que un usuario se registra en el sitio. Genera una contraseña temporal para el usuario y envía un correo electrónico con los detalles de inicio de sesión y el nivel de inglés proporcionado por el usuario. La función utiliza la función wp_set_password para establecer la contraseña temporal para el usuario y luego utiliza la función wp_mail para enviar el correo electrónico.

La segunda función, sendMailUpdate, se llama cuando se actualiza el nivel de inglés de un usuario. Envía un correo electrónico al usuario con una felicitación y el nivel de inglés actualizado. La función también utiliza la función wp_mail para enviar el correo electrónico.

Es importante tener en cuenta que para utilizar la función wp_mail, se debe configurar la función de correo electrónico en WordPress correctamente. También se deben proporcionar los detalles correctos del correo electrónico, como la dirección de correo electrónico del remitente y las credenciales del servidor de correo electrónico, en la configuración de WordPress.


`function sendMail($user_id) {
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
}`

`function sendMailUpdate($user_id) {
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

}`







