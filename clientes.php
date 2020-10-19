<?php
session_start();

/* SEGURANÇA */
if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

if ($_SESSION['priority'] < 1) {
  header('location: home.php');
}

/* CADASTRAR USUÁRIOS */
if (isset($_POST['submit-cliente'])) {
  include 'config/connection.php';

  $nome = $_POST['nome'];
  $cpf = $_POST['cpf'];
  $renda = $_POST['renda'];
  $credito = $_POST['credito'];

  // verificar se existe no banco
  $clientes = mysqli_query($conn, "SELECT * FROM clientes JOIN pessoas ON pessoas.idpessoa = clientes.fk_idpessoa WHERE nome = '$nome' AND cpf = '$cpf'");

  if (mysqli_num_rows($clientes) > 0) {
    // existe no banco
    echo "
      <script language='javascript' type='text/javascript'>
        alert('Cliente já cadastrado!');
        window.location.href = 'clientes.php';
      </script>
    ";
  } else {
    mysqli_free_result($clientes);

    $sql = "INSERT INTO pessoas (nome, cpf) VALUES ('$nome', '$cpf')";

    $sql2 = "INSERT INTO clientes (fk_idpessoa, renda, credito) VALUES ((SELECT idpessoa FROM pessoas WHERE nome = '$nome' AND cpf = '$cpf'), '$renda', '$credito')";

    if (mysqli_query($conn, $sql)) {
      if (mysqli_query($conn, $sql2)) {
        echo "
          <script language='javascript' type='text/javascript'>
            alert('Cliente cadastrado com sucesso!');
            window.location.href = 'clientes.php';
          </script>
        ";
      } else {
        echo "
          <script language='javascript' type='text/javascript'>
            alert('Não foi possível cadastrar o cliente!');
            window.location.href = 'clientes.php';
          </script>
        ";
      }
    } else {
      echo "
        <script language='javascript' type='text/javascript'>
          alert('Não foi possível cadastrar o cliente!');
          window.location.href = 'clientes.php';
        </script>
      ";
    } 
  }
  
  mysqli_close($conn);
}



?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <?php include 'templates/head.php'; ?>
  <link rel="stylesheet" href="styles/pages/clientes.css">
</head>

<body>
  <?php include 'templates/navbar.php'; ?>
  <?php include 'templates/topbar.php'; ?>
  <main>
    <section class="users bg-light">
      <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-circle" class="svg-inline--fa fa-user-circle fa-w-16 top-img" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
        <path fill="#4B5C6B" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z"></path>
      </svg>
      <h2>Cadastrar um cliente</h2>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" required>
        <label for="cpf">CPF</label>
        <input type="number" name="cpf" id="cpf" class="form-control" placeholder="CPF" required>
        <label for="renda">Renda</label>
        <input type="number" name="renda" id="renda" class="form-control" placeholder="Renda" required>
        <label for="credito">Crédito</label>
        <input type="number" name="credito" id="credito" class="form-control" placeholder="Crédito" required>
        <button type="submit" name="submit-cliente" class="btn btn-outline-info">Cadastrar</button>
      </form>
    </section>
    <section class="search bg-light">
      <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16 top-img" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path fill="#4B5C6B" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
      </svg>
      <h2>Pesquisar clientes</h2>
      <form action="#" method="GET">
        <div class="input-group">
          <input type="text" name="nome" id="nome" class="form-control" placeholder="Deixe em branco para pesquisar todos os clientes" required>
          <div class="input-group-append">
            <button class="btn btn-outline-info" type="button">
              <svg id="search-input-button" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
              </svg>
            </button>
          </div>
        </div>
        <?php
        $alfabeto = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
        $gridCount = 0; ?>
        <div class="container">
          <?php foreach ($alfabeto as $letra) {
            if ($gridCount == 0) {
              echo "<div class='row'>";
            }

            if ($gridCount < 5) {
              echo "<div class='col-2'>";
              echo "<input type='submit' name='letra' value='$letra' class='btn btn-outline-info'>";
              echo "</div>";
              $gridCount += 1;
            } else {
              echo "<div class='col-2'>";
              echo "<input type='submit' name='letra' value='$letra' class='btn btn-outline-info'>";
              echo "</div>";
              echo "</div>";
              $gridCount = 0;
            }
          } ?>
        </div>
      </form>
    </section>
  </main>
</body>

</html>