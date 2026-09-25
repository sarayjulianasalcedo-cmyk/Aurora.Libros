<?php
    session_start();

    include 'conexion_be.php';
    include 'verificar_captcha.php';

    // =======================================================
    // 0. VALIDACIÓN DEL CAPTCHA
    // =======================================================
    $captcha_token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if (!verificar_captcha($captcha_token)) {
        echo '
            <script>
                alert("Por favor, confirma que no eres un robot.");
                window.location = "../index.php";
            </script>
        ';
        exit;
    }

    // Obtenemos los valores y usamos trim() para descartar si solo escribieron espacios en blanco
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // =======================================================
    // 1. VALIDACIÓN DE CAMPOS EN BLANCO
    // =======================================================
    if (empty($email) && empty($password)) {
        echo '
            <script>
                alert("Por favor, completa todos los campos (correo y contraseña).");
                window.location = "../index.php";
            </script>
        ';
        exit;
    } elseif (empty($email)) {
        echo '
            <script>
                alert("Por favor, ingresa tu correo electrónico.");
                window.location = "../index.php";
            </script>
        ';
        exit;
    } elseif (empty($password)) {
        echo '
            <script>
                alert("Por favor, ingresa tu contraseña.");
                window.location = "../index.php";
            </script>
        ';
        exit;
    }

    // =======================================================
    // 2. CONSULTA DEL USUARIO (Consulta preparada)
    // =======================================================
    $stmt = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    // =======================================================
    // 3. VALIDACIÓN DE EXISTENCIA Y CONTRASEÑA
    // =======================================================
    if (mysqli_num_rows($resultado) > 0) {
        $usuario_datos = mysqli_fetch_assoc($resultado);
        $login_valido = false;
        // CASO A: El usuario ya tiene el formato nuevo (password_hash)
        if (password_verify($password, $usuario_datos['password'])) {
            $login_valido = true;
        } 
        // CASO B: Es un usuario antiguo con hash SHA-512
        elseif ($usuario_datos['password'] === hash('sha512', $password)) {
            $login_valido = true;
            // --- MIGRACIÓN EN SILENCIO ---
            // Generamos su nuevo hash seguro y actualizamos su registro en la BD
            $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
            $id_usuario = $usuario_datos['id'];
            mysqli_query($conexion, "UPDATE usuarios SET password = '$nuevo_hash' WHERE id = '$id_usuario'");
        }
        // Si pasó cualquiera de las dos comprobaciones:
        if ($login_valido) {
            $_SESSION['usuario'] = $email;
            $_SESSION['id_usuario'] = $usuario_datos['id']; 
            $_SESSION['ultima_actividad'] = time();
            header("location: ./bienvenida.php");
            exit;
        } else {
            // Contraseña incorrecta (falló en ambos métodos)
            echo '
               <script>
                    alert("Contraseña incorrecta. Por favor, inténtalo de nuevo.");
                    window.location = "../index.php";
                </script>
            ';
            exit;
        }
    } else {
        // Usuario / correo no encontrado
        echo '
           <script>
                alert("El usuario o correo electrónico no existe. Verifica tus datos.");
                window.location = "../index.php";
            </script>
        ';
        exit;
    }
?>
