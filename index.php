<?php
// login system
if (isset($_POST['submit'])) {
  session_start();
  include 'config/connection.php';

  $login = $_POST['login'];
  $password = $_POST['password'];

  $checkDB = mysqli_query($conn, "SELECT * FROM pessoas WHERE nome = '$login' AND senha = '$password'");

  if (mysqli_num_rows($checkDB) > 0) {
    // user exists in database
    $user = mysqli_fetch_array($checkDB);

    $_SESSION['user'] = $user['nome'];
    $_SESSION['status'] = "logged";

    $userId = $user['idpessoa'];
    $vendedor = mysqli_query($conn, "SELECT * FROM vendedores WHERE fk_idpessoa = '$userId'");
    $cliente = mysqli_query($conn, "SELECT * FROM clientes WHERE fk_idpessoa = '$userId'");

    if ($user['nome'] === "admin" and $user['senha'] === "admin") {
      $_SESSION['type'] = "admin";
      $_SESSION['priority'] = 2;
    } else if (mysqli_num_rows($vendedor) > 0) {
      $_SESSION['type'] = "vendedor";
      $_SESSION['priority'] = 1;
    } else if (mysqli_num_rows($cliente) > 0) {
      $_SESSION['type'] = "cliente";
      $_SESSION['priority'] = 0;
    }

    header('location: home.php');
  } else {
    echo "
      <script language='javascript' type='text/javascript'>
        alert('Login ou senha incorretos! Tente novamente');
        location.href = 'index.php';
      </script>
    ";
  }

  mysqli_free_result($checkDB);
  mysqli_free_result($vendedor);
  mysqli_free_result($cliente);

  mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php include 'templates/head.php'; ?>
<link rel="stylesheet" href="styles/main.css">
<link rel="stylesheet" href="styles/pages/index.css">
</head>
<body class="d-flex flex-column justify-content-center align-items-center">
  <main class="bg-light border rounded m-0">
    <div id="top" class="text-center">
      <img src="assets/shopping.svg" alt="logo" width="80px" height="80px">
      <h1>Sistema de vendas</h1>
    </div>
    <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
      <label for="login">Insira seu login</label>
      <input type="text" name="login" id="login" class="form-control" placeholder="Login" required>
      <label for="password">Insira sua senha</label>
      <input type="password" name="password" id="password" class="form-control" placeholder="Senha" required>
      <button type="submit" name="submit" class="btn btn-success w-50 mx-auto">Entrar</button>
    </form>
  </main>
  <footer class="text-center fixed-bottom">
    <p>&copy; Sistema de vendas 2020 | Criado por <a href="https://github.com/yunger7" target="_blank">Luís Galete Faldão</a></p>
  </footer>
</body>
</html>