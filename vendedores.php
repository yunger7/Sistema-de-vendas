<?php
session_start();

/* SECURITY */
if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

if ($_SESSION['priority'] < 2) {
  header('location: home.php');
}

/* REGISTRAR VENDEDOR */
if (isset($_POST['submit-vendedor'])) {
  include 'config/connection.php';

  $nome = $_POST['nome'];
  $cpf = $_POST['cpf'];
  $salario = $_POST['salario'];
  $senha = base64_encode($_POST['senha']);

  // verificar se existe no banco
  $vendedores = mysqli_query($conn, "SELECT * from vendedores JOIN pessoas ON pessoas.idpessoa = vendedores.fk_idpessoa WHERE nome = '$nome' AND cpf = '$cpf'");

  if (mysqli_num_rows($vendedores) > 0) {
    // existe no banco
    echo "
      <script language='javascript' type='text/javascript'>
        alert('Vendedor já cadastrado!');
        window.location.href = 'vendedores.php';
      </script>
    ";
  } else {
    mysqli_free_result($vendedores);

    $sql = "INSERT INTO pessoas (nome, cpf, senha) VALUES ('$nome', '$cpf', '$senha')";
    $sql2 = "INSERT INTO vendedores (fk_idpessoa, salario) VALUES ((SELECT idpessoa FROM pessoas WHERE nome = '$nome' AND cpf = '$cpf'), '$salario')";

    if (mysqli_query($conn, $sql)) {
      if (mysqli_query($conn, $sql2)) {
        echo "
          <script language='javascript' type='text/javascript'>
            alert('Vendedor cadastrado com sucesso!');
            window.location.href = 'vendedores.php';
          </script>
        ";
      } else {
        echo "
          <script language='javascript' type='text/javascript'>
            alert('Houve um problema ao cadastrar o vendedor!');
            window.location.href = 'vendedores.php';
          </script>
        ";
      }
    } else {
      echo "
        <script language='javascript' type='text/javascript'>
          alert('Houve um problema ao cadastrar o vendedor!');
          window.location.href = 'vendedores.php';
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
</head>

<body>
  <?php include 'templates/navbar.php'; ?>
  <?php include 'templates/topbar.php'; ?>
  <link rel="stylesheet" href="styles/pages/vendedores.css">
  <main>
    <section class="sellers bg-light border rounded">
      <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-tie" class="svg-inline--fa fa-user-tie fa-w-14 top-img" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <path fill="#4B5C6B" d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm95.8 32.6L272 480l-32-136 32-56h-96l32 56-32 136-47.8-191.4C56.9 292 0 350.3 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-72.1-56.9-130.4-128.2-133.8z"></path>
      </svg>
      <h2>Cadastrar um vendedor</h2>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" required>
        <label for="cpf">CPF</label>
        <input type="text" name="cpf" id="cpf" class="form-control" placeholder="CPF" required>
        <label for="salario">Salário</label>
        <input type="text" name="salario" id="salario" class="form-control" placeholder="Salário" required>
        <label for="senha">Senha</label>
        <input type="password" name="senha" id="senha" class="form-control" placeholder="Senha" required>
        <button type="submit" name="submit-vendedor" class="btn btn-outline-info">Cadastrar</button>
      </form>
    </section>
    <section class="search bg-light border rounded">
      <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16 top-img" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path fill="#4B5C6B" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
      </svg>
      <h2>Pesquisar vendedores</h2>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
        <div class="input-group">
          <input type="text" name="name" id="name" class="form-control" placeholder="Deixe em branco para pesquisar todos os vendedores">
          <div class="input-group-append">
            <button type="submit" class="btn btn-outline-info">
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
              echo "<input type='submit' name='letter' value='$letra' class='btn btn-outline-info'>";
              echo "</div>";
              $gridCount += 1;
            } else {
              echo "<div class='col-2'>";
              echo "<input type='submit' name='letter' value='$letra' class='btn btn-outline-info'>";
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