<?php
    echo "Ya";	// mensaje que se muestra al ingresar al sistema, aquí haces tu diseño
		// y puedes usar los datos del usuario logueado, ya los tienes en las variables de sesión
    session_start();	// siempre que empieces con una nueva aplicación debes iniciar sesión
    # a partir de este momento puedes programar tu sistema
    # y puedes hacer uso de los datos del usuario logueado
    session_destroy();  // con esto destruyes las variables de sesion
?>
