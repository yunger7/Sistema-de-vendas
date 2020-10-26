<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* CHECK USER PRIORITY */
if ($_SESSION['priority'] >= 1) { /* Seller or admin */ ?>
  <?php
  /* SEND PAGES */
  if (isset($_GET['name-id']) || isset($_GET['letter'])) { /* Search result page */ ?>
    <?php
    include 'config/connection.php';

    $cases = [$_GET['name-id'] ?? "", $_GET['letter'] ?? ""];

    switch ($cases) {
        // search by name
      case ($cases[0] !== "" && $cases[1] === ""):
        $nameId = $_GET['name-id'];
        $sql = "SELECT * FROM pedidos JOIN clientes ON pedidos.fk_idcliente = clientes.idcliente JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE idpedido = '$nameId' OR nome LIKE '%$nameId%'";
        break;
        // search by letter
      case ($cases[0] === "" && $cases[1] !== ""):
        $letter = $_GET['letter'];
        $sql = "SELECT * FROM pedidos JOIN clientes ON pedidos.fk_idcliente = clientes.idcliente JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE nome LIKE '$letter%'";
        break;
        // empty search  
      case ($cases[0] === "" && $cases[1] === ""):
        $sql = "SELECT * FROM pedidos JOIN clientes ON pedidos.fk_idcliente = clientes.idcliente JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa";
        break;
    }

    // Database search
    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) > 0) {
      $searchResults = 1;
      $orders = mysqli_fetch_all($res, MYSQLI_ASSOC);
      mysqli_free_result($res);
    } else {
      $searchResults = 0;
    }

    mysqli_close($conn);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
      <?php include 'templates/head.php'; ?>
      <link rel="stylesheet" href="styles/pages/pedidos.css">
    </head>

    <body id="search-result">
      <?php include 'templates/navbar.php'; ?>
      <?php include 'templates/topbar.php'; ?>
      <main>
        <?php if ($searchResults == 0) { ?>
          <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">ID Pedido</th>
                <th scope="col">Cliente</th>
                <th scope="col">Data</th>
                <th scope="col">Valor</th>
                <th scope="col">Status</th>
                <th scope="col">Vendedor</th>
                <th scope="col">Opções</th>
              </tr>
            </thead>
          </table>
          <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
          <a href="pedidos.php" class="btn btn-secondary mt-3">Voltar</a>
        <?php } else { ?>
          <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">ID Pedido</th>
                <th scope="col">Cliente</th>
                <th scope="col">Data</th>
                <th scope="col">Valor</th>
                <th scope="col">Status</th>
                <th scope="col">Vendedor</th>
                <th scope="col">Opções</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order) { ?>
                <tr>
                  <td><?php echo $order['idpedido']; ?></td>
                  <td><?php echo $order['nome']; ?></td>
                  <td><?php echo $order['data']; ?></td>
                  <td><?php echo $order['valor']; ?></td>
                  <td><?php echo $order['status']; ?></td>
                  <?php
                  include 'config/connection.php';

                  $sellerId = $order['fk_idvendedor'];

                  $res = mysqli_query($conn, "SELECT nome FROM vendedores JOIN pessoas ON vendedores.fk_idpessoa = pessoas.idpessoa WHERE idvendedor = '$sellerId'");
                  $sellerName = mysqli_fetch_assoc($res);

                  mysqli_free_result($res);
                  mysqli_close($conn);
                  ?>
                  <td><?php echo $sellerName['nome']; ?></td>
                  <td>
                    <a href="pedidos.php?view-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-success">Detalhes</a>
                    <a href="pedidos.php?edit-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-warning">Editar</a>
                    <a href="pedidos.php?delete-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-danger">Excluir</a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } ?>
      </main>
    </body>

    </html>
  <?php } else { /* Index page */ ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
      <?php include 'templates/head.php'; ?>
    </head>

    <body>
      <?php include 'templates/navbar.php'; ?>
      <?php include 'templates/topbar.php'; ?>
      <main>
        <!DOCTYPE html>
        <html lang="pt-br">

        <head>
          <?php include 'templates/head.php'; ?>
          <link rel="stylesheet" href="styles/pages/pedidos.css">
        </head>

        <body id="search">
          <main>
            <h1>Insira os dados do cliente ou ID do pedido</h1>
            <section class="search">
              <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                <div class="input-group">
                  <input type="text" name="name-id" class="form-control" placeholder="Deixe em branco para pesquisar todos os pedidos">
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-outline-info">
                      <svg id="search-input-button" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
                      </svg>
                    </button>
                  </div>
                </div>
                <div class="search-letters">
                  <?php
                  $alfabeto = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
                  foreach ($alfabeto as $letra) {
                    echo "<input type='submit' name='letter' value='$letra' class='btn btn-link'>";
                  }
                  ?>
                </div>
              </form>
            </section>
          </main>
        </body>

        </html>
      </main>
    </body>

    </html>
  <?php } ?>
<?php } else { /* Client */ ?>
  <?php
  include 'config/connection.php';

  // Database search
  $userId = $_SESSION['user-id'];
  $sql = "SELECT idpedido, data, valor, pedidos.status FROM pedidos JOIN clientes ON pedidos.fk_idcliente = clientes.idcliente JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE idpessoa = '$userId'";
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    $searchResults = 1;
    $orders = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_free_result($res);
  } else {
    $searchResults = 0;
  }

  mysqli_close($conn);
  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/pedidos.css">
  </head>

  <body id="search-result">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <?php if ($searchResults == 0) { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">ID Pedido</th>
              <th scope="col">Data</th>
              <th scope="col">Valor</th>
              <th scope="col">Status</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
        </table>
        <p class="text-center h5 mt-4">Você não possui nenhum pedido ＞﹏＜</p>
      <?php } else { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">ID Pedido</th>
              <th scope="col">Data</th>
              <th scope="col">Valor</th>
              <th scope="col">Status</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order) { ?>
              <tr>
                <td><?php echo $order['idpedido']; ?></td>
                <td><?php echo $order['data']; ?></td>
                <td><?php echo $order['valor']; ?></td>
                <td><?php echo $order['status']; ?></td>
                <td>
                  <a href="pedidos.php?view-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-success">Detalhes</a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php } ?>
    </main>
  </body>

  </html>
<?php } ?>