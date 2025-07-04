<?php
session_start();       // Inicias la sesión
session_unset();       // Eliminas todas las variables de sesión
session_destroy();     // Destruyes la sesión
header("Location: ../Diseño_Proce_de_Login/login.html");  // Rediriges al login o donde quieras
exit();
?>