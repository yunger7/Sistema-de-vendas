<?php
/* LOGIN SYSTEM */
if (isset($_POST['submit-login'])) {
  session_start();
  include 'config/connection.php';

  $login = $_POST['login'];
  $password = base64_encode($_POST['password']);

  $checkDB = mysqli_query($conn, "SELECT * FROM pessoas WHERE nome = '$login' AND senha = '$password'");

  if (mysqli_num_rows($checkDB) > 0) {
    // user exists in database
    $user = mysqli_fetch_array($checkDB);

    $_SESSION['user'] = $user['nome'];
    $_SESSION['status'] = "logged";

    $userId = $user['idpessoa'];
    $vendedor = mysqli_query($conn, "SELECT * FROM vendedores WHERE fk_idpessoa = '$userId'");
    $cliente = mysqli_query($conn, "SELECT * FROM clientes WHERE fk_idpessoa = '$userId'");

    if ($user['nome'] === "admin" and $user['senha'] === "YWRtaW4=") {
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
/* REGISTER PASSWORD */
if (isset($_POST['submit-password'])) {
  $cpf = $_POST['cpf'];
  $password = base64_encode($_POST['password']);

  // check if user exists in database
  include 'config/connection.php';
  $cliente = mysqli_query($conn, "SELECT * FROM pessoas WHERE cpf = '$cpf'");

  if (mysqli_num_rows($cliente) > 0) {
    // user exists
    mysqli_free_result($cliente);

    $sql = "UPDATE pessoas SET senha = '$password' WHERE cpf = '$cpf'";

    if (mysqli_query($conn, $sql)) {
      echo "
        <script language='javascript' type='text/javascript'>
          alert('Senha cadastrada com sucesso');
          location.href = 'index.php';
        </script>
      ";
    } else {
      echo "
        <script language='javascript' type='text/javascript'>
          alert('Houve um problema ao cadastrar a senha');
        </script>
      ";
    }
  } else {
    echo "
      <script language='javascript' type='text/javascript'>
        alert('Não existe um usuário com esse CPF');
      </script>
    ";
  }

  mysqli_free_result($cliente);
  mysqli_close($conn);
}

/* SEND PAGES */
// register page
if (isset($_GET['registrar'])) { ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/pages/index.css">
  </head>

  <body id="register" class="d-flex flex-column justify-content-center align-items-center">
    <main class="bg-light border rounded m-0">
      <div id="top" class="text-center">
        <img src="assets/shopping.svg" alt="logo" width="80px" height="80px">
        <h1>Sistema de vendas</h1>
        <div id="info">
          <p>Contate um vendedor ou administrador para cadastrar seu dados.</p>
          <p>Caso já tenha feito isso, registre sua senha para fazer o login.</p>
        </div>
      </div>
      <form id="registrar-senha" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
        <label for="cpf">Insira seu CPF</label>
        <input type="number" name="cpf" id="cpf" class="form-control" placeholder="CPF" required>
        <label for="password">Insira sua senha</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Senha" required>
        <button type="submit" name="submit-password" class="btn btn-success w-50 mx-auto">Registrar senha</button>
        <a href="index.php">Voltar</a>
      </form>
    </main>
    <footer class="text-center fixed-bottom">
      <p>&copy; Sistema de vendas 2020 | Criado por <a href="https://github.com/yunger7" target="_blank">Luís Galete Faldão</a></p>
    </footer>
  </body>

  </html>

<?php } else { /* INDEX DEFAULT */ ?>
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
        <button type="submit" name="submit-login" class="btn btn-success w-50 mx-auto">Entrar</button>
        <a href="index.php?registrar">Registrar-se</a>
      </form>
    </main>
    <footer class="text-center fixed-bottom">
      <p>&copy; Sistema de vendas 2020 | Criado por <a href="https://github.com/yunger7" target="_blank">Luís Galete Faldão</a></p>
    </footer>
  </body>

  </html>
<?php } ?>