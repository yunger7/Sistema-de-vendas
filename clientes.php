<?php
session_start();

/* SECURITY */
if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

if ($_SESSION['priority'] < 1) {
  header('location: home.php');
}

/* REGISTER CLIENT */
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
        $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'clientes.php', 'text' => 'Cliente cadastrado com sucesso'];
        header('location: templates/finish-operation.php');
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'clientes.php', 'text' => 'Houve um problema ao finalizar o pedido'];
        header('location: templates/finish-operation.php');
      }
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'clientes.php', 'text' => 'Houve um problema ao finalizar o pedido'];
      header('location: templates/finish-operation.php');
    }
  }

  mysqli_close($conn);
}

/* EDIT CLIENT */
if (isset($_POST['submit-edit-client'])) {
  include 'config/connection.php';

  $idEditar = $_POST['id-editar'];
  $nome = $_POST['nome'];
  $cpf = $_POST['cpf'];
  $renda = $_POST['renda'];
  $credito = $_POST['credito'];
  $status = $_POST['status'];

  $sql = "UPDATE pessoas SET nome = '$nome', cpf = '$cpf', status = '$status' WHERE idpessoa = '$idEditar'";
  $sql2 = "UPDATE clientes SET renda = '$renda', credito = '$credito' WHERE fk_idpessoa = '$idEditar'";

  if (mysqli_query($conn, $sql)) {
    if (mysqli_query($conn, $sql2)) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'clientes.php', 'text' => 'Cliente editado com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'clientes.php', 'text' => 'Houve um problema ao editar o cliente'];
      header('location: templates/finish-operation.php');
    }
  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'clientes.php', 'text' => 'Houve um problema ao editar o cliente'];
    header('location: templates/finish-operation.php');
  }

  mysqli_close($conn);
}

/* DELETE CLIENT */
if (isset($_GET['delete-client'])) {
  include 'config/connection.php';

  $idExcluir = $_GET['id'];

  $res = mysqli_query($conn, "SELECT * FROM pessoas JOIN clientes ON pessoas.idpessoa = clientes.fk_idpessoa WHERE idpessoa = '$idExcluir'");
  $cliente = mysqli_fetch_assoc($res);
  mysqli_free_result($res);

  // Dados do cliente
  $idPessoa = $cliente['idpessoa'];
  $nome = $cliente['nome'];
  $cpf = $cliente['cpf'];
  $status = $cliente['status'];
  $senha = $cliente['senha'];
  $idCliente = $cliente['idcliente'];
  $renda = $cliente['renda'];
  $credito = $cliente['credito'];

  // Dados do usuário
  $idUsuario = $_SESSION['user-id'];

  // Mover para lixeira e excluir dados
  $sql = "INSERT INTO lixeira (idpessoa, nome, cpf, status, senha, idcliente, renda, credito, idusuario) VALUES ('$idPessoa', '$nome', '$cpf', '$status', '$senha', '$idCliente', '$renda', '$credito', '$idUsuario') ";

  if (mysqli_query($conn, $sql)) {
    if (mysqli_query($conn, "DELETE FROM pessoas WHERE idpessoa = '$idExcluir'")) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'clientes.php', 'text' => 'Cliente excluído com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'clientes.php', 'text' => 'Houve um problema ao excluir o cliente'];
      header('location: templates/finish-operation.php');
    }
  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'clientes.php', 'text' => 'Houve um problema ao excluir o cliente'];
    header('location: templates/finish-operation.php');;
  }

  mysqli_close($conn);
}

/* SEND PAGES */
if (isset($_GET['name']) || isset($_GET['letter'])) { /* Search page */ ?>
  <?php
  include 'config/connection.php';

  $cases = [$_GET['name'] ?? "", $_GET['letter'] ?? ""];

  switch ($cases) {
      // search by name
    case ($cases[0] !== "" && $cases[1] === ""):
      $name = $_GET['name'];
      $sql = "SELECT * FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE nome LIKE '%$name%'";
      break;
      // search by letter
    case ($cases[0] === "" && $cases[1] !== ""):
      $letter = $_GET['letter'];
      $sql = "SELECT * FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE nome LIKE '$letter%'";
      break;
      // empty search  
    case ($cases[0] === "" && $cases[1] === ""):
      $sql = "SELECT * FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa";
      break;
  }

  // Database search
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    $searchResults = 1;
    $clients = mysqli_fetch_all($res, MYSQLI_ASSOC);
  } else {
    $searchResults = 0;
  }

  mysqli_free_result($res);
  mysqli_close($conn);

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
    <main class="search-results">
      <?php if ($searchResults == 0) { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">Nome</th>
              <th scope="col">CPF</th>
              <th scope="col">Renda</th>
              <th scope="col">Crédito</th>
              <th scope="col">Status</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
        </table>
        <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
        <a href="clientes.php" class="btn btn-secondary mt-3">Voltar</a>
      <?php } else { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">Nome</th>
              <th scope="col">CPF</th>
              <th scope="col">Renda</th>
              <th scope="col">Crédito</th>
              <th scope="col">Status</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $client) { ?>
              <tr>
                <td><?php echo $client['nome']; ?></td>
                <td><?php echo $client['cpf']; ?></td>
                <td><?php echo $client['renda']; ?></td>
                <td><?php echo $client['credito']; ?></td>
                <td><?php echo $client['status']; ?></td>
                <td>
                  <a href="<?php echo $_SERVER['PHP_SELF']; ?>?edit-client&id=<?php echo $client['idpessoa']; ?>" class="btn btn-outline-warning">Editar</a>
                  <a href="<?php echo $_SERVER['PHP_SELF']; ?>?delete-client&id=<?php echo $client['idpessoa']; ?>" class="btn btn-outline-danger">Excluir</a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php } ?>

    </main>
  </body>

  </html>
<?php } else if (isset($_GET['edit-client'])) { /* Edit page */ ?>
  <?php
  include 'config/connection.php';

  $idEditar = $_GET['id'];

  $res = mysqli_query($conn, "SELECT * FROM clientes JOIN pessoas ON pessoas.idpessoa = clientes.fk_idpessoa WHERE idpessoa = '$idEditar'");
  $client = mysqli_fetch_assoc($res);

  mysqli_free_result($res);
  mysqli_close($conn);
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
    <main class="edit-page">
      <section class="users bg-light border rounded">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-circle" class="svg-inline--fa fa-user-circle fa-w-16 top-img" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
          <path fill="#4B5C6B" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z"></path>
        </svg>
        <h2>Editar dados do cliente</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
          <label for="nome">Nome</label>
          <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome" value="<?php echo $client['nome']; ?>" required>
          <label for="cpf">CPF</label>
          <input type="number" name="cpf" id="cpf" class="form-control" placeholder="CPF" value="<?php echo $client['cpf']; ?>" required>
          <label for="renda">Renda</label>
          <input type="number" name="renda" id="renda" class="form-control" placeholder="Renda" value="<?php echo $client['renda']; ?>" required>
          <label for="credito">Crédito</label>
          <input type="number" name="credito" id="credito" class="form-control" placeholder="Crédito" value="<?php echo $client['credito']; ?>" required>
          <div class="radio-form">
            <p>Status</p>
            <div class="option">
              <input type="radio" name="status" id="ativo" value="A" <?php if ($client['status'] == "A") { echo "checked"; } ?>>
              <label for="ativo">Ativo</label>
            </div>
            <div class="option">
              <input type="radio" name="status" id="inativo" value="I" <?php if ($client['status'] == "I") { echo "checked"; } ?>>
              <label for="inativo">Inativo</label>
            </div>
          </div>
          <input type="hidden" name="id-editar" value="<?php echo $client['idpessoa']; ?>">
          <button type="submit" name="submit-edit-client" class="btn btn-warning">Editar</button>
        </form>
      </section>
    </main>
  </body>

  </html>
<?php } else { /* Index page */ ?>
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
      <section class="users bg-light border rounded">
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
      <section class="search bg-light border rounded">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16 top-img" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
          <path fill="#4B5C6B" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
        </svg>
        <h2>Pesquisar clientes</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="input-group">
            <input type="text" name="name" id="name" class="form-control" placeholder="Deixe em branco para pesquisar todos os clientes">
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

<?php } ?>