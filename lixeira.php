<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

if ($_SESSION['priority'] < 1) {
  header('location: home.php');
}

/* SEND PAGES */
if (isset($_GET['products'])) { /* Products page */ ?>
  <?php
  if ($_SESSION['type'] !== "admin") {
    header('location: home.php');
  }

  /* RESTORE PRODUCT */
  if (isset($_POST['submit-restore-product'])) {
    include 'config/connection.php';

    $id = $_POST['id'];
    $productId = $_POST['idproduto'];
    $description = $_POST['descricao'];
    $stock = $_POST['estoque'];
    $value = $_POST['valor'];
    $status = $_POST['status'];

    if (mysqli_query($conn, "INSERT INTO produtos (idproduto, descricao, estoque, valor, status) VALUES ('$productId', '$description', '$stock', '$value', '$status')")) {
      if (mysqli_query($conn, "DELETE FROM lixeira WHERE id = '$id'")) {
        $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'lixeira.php?products', 'text' => 'Produto restaurado com sucesso'];
        header('location: templates/finish-operation.php');
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?products', 'text' => 'Houve um problema ao restaurar o produto'];
        header('location: templates/finish-operation.php');
      }
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?products', 'text' => 'Houve um problema ao restaurar o produto'];
      header('location: templates/finish-operation.php');
    }

    mysqli_close($conn);
  }

  /* DELETE PRODUCT PERMANENTLY */
  if (isset($_POST['submit-delete-product'])) {
    include 'config/connection.php';

    $id = $_POST['id'];

    if (mysqli_query($conn, "DELETE FROM lixeira WHERE id = '$id'")) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'lixeira.php?products', 'text' => 'Produto excluído com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?products', 'text' => 'Houve um problema ao excluir o produto'];
      header('location: templates/finish-operation.php');
    }

    mysqli_close($conn);
  }

  /* GET DATA FROM DATABASE */
  include 'config/connection.php';

  $sql = "SELECT id, idproduto, descricao, estoque, valor, status, data_exclusao, idusuario FROM lixeira WHERE idproduto IS NOT NULL AND descricao IS NOT NULL AND estoque IS NOT NULL AND valor IS NOT NULL AND status IS NOT NULL";
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    // There is at least one deleted product
    $exist = 1;
    $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

    mysqli_free_result($res);
  } else {
    // There are no deleted products
    $exist = 0;
  }

  mysqli_close($conn);

  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/lixeira.css">
  </head>

  <body class="trash-page">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <?php if ($exist == 0) { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">Nome</th>
              <th scope="col">Estoque</th>
              <th scope="col">Valor</th>
              <th scope="col">Status</th>
              <th scope="col">Data de exclusão</th>
              <th scope="col">Usuário</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
        </table>
        <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
        <a href="lixeira.php" class="btn btn-secondary mt-3">Voltar</a>
      <?php } else if ($exist == 1) { ?>
      <?php } ?>
      <table class="table table-hover border text-center">
        <thead>
          <tr>
            <th scope="col">Nome</th>
            <th scope="col">Estoque</th>
            <th scope="col">Valor</th>
            <th scope="col">Status</th>
            <th scope="col">Data de exclusão</th>
            <th scope="col">Usuário</th>
            <th scope="col">Opções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product) : ?>
            <tr>
              <td><?php echo $product['descricao']; ?></td>
              <td><?php echo $product['estoque']; ?></td>
              <td><?php echo $product['valor']; ?></td>
              <td><?php echo $product['status']; ?></td>
              <td><?php echo $product['data_exclusao']; ?></td>
              <td>
                <?php
                include 'config/connection.php';

                $userId = $product['idusuario'];
                $res = mysqli_query($conn, "SELECT nome FROM pessoas WHERE idpessoa = '$userId'");
                $userName = mysqli_fetch_assoc($res);
                echo $userName['nome'];

                mysqli_close($conn);
                ?>
              </td>
              <td class="buttons">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?products" method="POST">
                  <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                  <input type="hidden" name="idproduto" value="<?php echo $product['idproduto']; ?>">
                  <input type="hidden" name="descricao" value="<?php echo $product['descricao']; ?>">
                  <input type="hidden" name="estoque" value="<?php echo $product['estoque']; ?>">
                  <input type="hidden" name="valor" value="<?php echo $product['valor']; ?>">
                  <input type="hidden" name="status" value="<?php echo $product['status']; ?>">
                  <button type="submit" name="submit-restore-product" class="btn btn-outline-success">Restaurar</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?products" method="POST">
                  <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                  <button type="submit" name="submit-delete-product" class="btn btn-outline-danger">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['orders'])) { /* Orders page */ ?>
  <?php
  if ($_SESSION['priority'] < 1) {
    header('location: home.php');
  }

  /* RESTORE ORDER */
  if (isset($_POST['submit-restore-order'])) {
    include 'config/connection.php';

    $id = $_POST['id'];
    $orderId = $_POST['idpedido'];
    $date = $_POST['data'];
    $value = $_POST['valor'];
    $status = $_POST['status'];
    $sellerId = $_POST['idvendedor'];
    $clientId = $_POST['idcliente'];

    if (mysqli_query($conn, "INSERT INTO pedidos (idpedido, data, valor, status, fk_idvendedor, fk_idcliente) VALUES ('$orderId', '$date', '$value', '$status', '$sellerId', '$clientId')")) {
      if (mysqli_query($conn, "DELETE FROM lixeira WHERE id = '$id'")) {
        $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'lixeira.php?orders', 'text' => 'Pedido restaurado com sucesso'];
        header('location: templates/finish-operation.php');
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?orders', 'text' => 'Houve um problema ao restaurar o pedido'];
        header('location: templates/finish-operation.php');
      }
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?orders', 'text' => 'Houve um problema ao restaurar o pedido'];
      header('location: templates/finish-operation.php');
    }

    mysqli_close($conn);
  }

  /* DELETE ORDER PERMANENTLY */
  if (isset($_POST['submit-delete-order'])) {
    include 'config/connection.php';

    $id = $_POST['id'];

    if (mysqli_query($conn, "DELETE FROM lixeira WHERE id = '$id'")) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'lixeira.php?orders', 'text' => 'Pedido excluído com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?orders', 'text' => 'Houve um problema ao excluir o pedido'];
      header('location: templates/finish-operation.php');
    }

    mysqli_close($conn);
  }

  /* GET DATA FROM DATABASE */
  include 'config/connection.php';

  if ($_SESSION['type'] == "admin") {
    $sql = "SELECT id, idpedido, data, valor, status, idvendedor, idcliente, data_exclusao, idusuario FROM lixeira WHERE idpedido IS NOT NULL AND data IS NOT NULL AND valor IS NOT NULL AND status IS NOT NULL AND idvendedor IS NOT NULL AND idcliente IS NOT NULL";
  } else if ($_SESSION['type'] == "vendedor") {
    // Get seller id
    $userId = $_SESSION['user-id'];
    $res = mysqli_query($conn, "SELECT idvendedor FROM vendedores WHERE fk_idpessoa = '$userId'");
    $sellerId = mysqli_fetch_assoc($res);

    $sql = "SELECT id, idpedido, data, valor, status, idvendedor, idcliente, data_exclusao, idusuario FROM lixeira WHERE idvendedor = '$userId' AND idpedido IS NOT NULL AND data IS NOT NULL AND valor IS NOT NULL AND status IS NOT NULL AND idvendedor IS NOT NULL AND idcliente IS NOT NULL";
  }

  $res = mysqli_query($conn, $sql);
  
  if (mysqli_num_rows($res) > 0) {
    // There is at least one deleted order
    $orders = mysqli_fetch_all($res, MYSQLI_ASSOC);
    $exist = 1;

    mysqli_free_result($res);
  } else {
    // There are no deleted orders
    $exist = 0;
  }

  mysqli_close($conn);
  ?>

  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/lixeira.css">
  </head>

  <body class="trash-page">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <?php if ($exist == 0) { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Data</th>
              <th scope="col">Valor</th>
              <th scope="col">Status</th>
              <th scope="col">Cliente</th>
              <th scope="col">Vendedor</th>
              <th scope="col">Data de exclusão</th>
              <th scope="col">Usuário</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
        </table>
        <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
        <a href="lixeira.php" class="btn btn-secondary mt-3">Voltar</a>
      <?php } else if ($exist == 1) { ?>
        <table class="table table-hover border text-center">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Data</th>
            <th scope="col">Valor</th>
            <th scope="col">Status</th>
            <th scope="col">Cliente</th>
            <th scope="col">Vendedor</th>
            <th scope="col">Data de exclusão</th>
            <th scope="col">Usuário</th>
            <th scope="col">Opções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order) : ?>
            <tr>
              <td><?php echo $order['idpedido']; ?></td>
              <td><?php echo $order['data']; ?></td>
              <td><?php echo $order['valor']; ?></td>
              <td><?php echo $order['status']; ?></td>
              <td>
                <?php
                include 'config/connection.php';

                $clientId = $order['idcliente'];
                $res = mysqli_query($conn, "SELECT nome FROM pessoas JOIN clientes ON pessoas.idpessoa = clientes.fk_idpessoa WHERE idcliente = '$clientId'");
                $clientName = mysqli_fetch_assoc($res);
                echo $clientName['nome'];

                mysqli_close($conn);
                ?>
              </td>
              <td>
                <?php
                include 'config/connection.php';

                $sellerId = $order['idvendedor'];
                $res = mysqli_query($conn, "SELECT nome FROM pessoas JOIN vendedores ON pessoas.idpessoa = vendedores.fk_idpessoa WHERE idvendedor = '$sellerId'");
                $sellerName = mysqli_fetch_assoc($res);
                echo $sellerName['nome'];

                mysqli_close($conn);
                ?>
              </td>
              <td><?php echo $order['data_exclusao']; ?></td>
              <td>
                <?php
                include 'config/connection.php';

                $userId = $order['idusuario'];
                $res = mysqli_query($conn, "SELECT nome FROM pessoas WHERE idpessoa = '$userId'");
                $userName = mysqli_fetch_assoc($res);
                echo $userName['nome'];

                mysqli_close($conn);
                ?>
              </td>
              <td class="buttons">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?orders" method="POST">
                  <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                  <input type="hidden" name="idpedido" value="<?php echo $order['idpedido']; ?>">
                  <input type="hidden" name="data" value="<?php echo $order['data']; ?>">
                  <input type="hidden" name="valor" value="<?php echo $order['valor']; ?>">
                  <input type="hidden" name="status" value="<?php echo $order['status']; ?>">
                  <input type="hidden" name="idvendedor" value="<?php echo $order['idvendedor']; ?>">
                  <input type="hidden" name="idcliente" value="<?php echo $order['idcliente']; ?>">
                  <button type="submit" name="submit-restore-order" class="btn btn-outline-success">Restaurar</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?orders" method="POST">
                  <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                  <button type="submit" name="submit-delete-order" class="btn btn-outline-danger">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php } ?>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['clients'])) { /* Clients page */ ?>
  <?php
  if ($_SESSION['priority'] < 1) {
    header('location: home.php');
  }

  /* RESTORE CLIENT */
  if (isset($_POST['submit-restore-client'])) {
    include 'config/connection.php';

    $id = $_POST['id'];
    $idPessoa = $_POST['idpessoa'];
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $status = $_POST['status'];
    $senha = $_POST['senha'];
    $idCliente = $_POST['idcliente'];
    $renda = $_POST['renda'];
    $credito = $_POST['credito'];

    if (mysqli_query($conn, "INSERT INTO pessoas (idpessoa, nome, cpf, status, senha) VALUES ('$idPessoa', '$nome', '$cpf', '$status', '$senha') ")) {
      if (mysqli_query($conn, "INSERT INTO clientes (idcliente, renda, credito, fk_idpessoa) VALUES ('$idCliente', '$renda', '$credito', '$idPessoa') ")) {
        if (mysqli_query($conn, "DELETE FROM lixeira WHERE id = '$id'")) {
          $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'lixeira.php?clients', 'text' => 'Cliente restaurado com sucesso'];
          header('location: templates/finish-operation.php');
        } else {
          $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?clients', 'text' => 'Houve um problema ao restaurar o cliente'];
          header('location: templates/finish-operation.php');
        }
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?clients', 'text' => 'Houve um problema ao restaurar o cliente'];
        header('location: templates/finish-operation.php');
      }
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?clients', 'text' => 'Houve um problema ao restaurar o cliente'];
      header('location: templates/finish-operation.php');
    }

    mysqli_close($conn);
  }

  /* PERMANENTLY DELETE CLIENT */
  if (isset($_POST['submit-delete-client'])) {
    include 'config/connection.php';

    $id = $_POST['id'];

    if (mysqli_query($conn, "DELETE FROM lixeira WHERE id = '$id'")) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'lixeira.php?clients', 'text' => 'Cliente excluído com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?clients', 'text' => 'Houve um problema ao excluir o cliente'];
      header('location: templates/finish-operation.php');
    }

    mysqli_close($conn);
  }

  /* GET DATA FROM DATABASE */
  include 'config/connection.php';

  $sql = "SELECT id, idpessoa, nome, cpf, status, senha, idcliente, renda, credito, data_exclusao, idusuario FROM lixeira WHERE idpessoa IS NOT NULL AND nome IS NOT NULL AND cpf IS NOT NULL AND status IS NOT NULL AND idcliente IS NOT NULL";
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    // There is at least one deleted client
    $exist = 1;
    $clients = mysqli_fetch_all($res, MYSQLI_ASSOC);

    mysqli_free_result($res);
  } else {
    // There are no deleted clients
    $exist = 0;
  }

  mysqli_close($conn);
  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/lixeira.css">
  </head>

  <body class="trash-page">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <?php if ($exist == 0) { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">Nome</th>
              <th scope="col">CPF</th>
              <th scope="col">Status</th>
              <th scope="col">Renda</th>
              <th scope="col">Crédito</th>
              <th scope="col">Data de exclusão</th>
              <th scope="col">Usuário</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
        </table>
        <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
        <a href="lixeira.php" class="btn btn-secondary mt-3">Voltar</a>
      <?php } else if ($exist == 1) { ?>
        <table class="table table-hover border text-center">
        <thead>
          <tr>
            <th scope="col">Nome</th>
            <th scope="col">CPF</th>
            <th scope="col">Status</th>
            <th scope="col">Renda</th>
            <th scope="col">Crédito</th>
            <th scope="col">Data de exclusão</th>
            <th scope="col">Usuário</th>
            <th scope="col">Opções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clients as $client) : ?>
            <tr>
              <td><?php echo $client['nome']; ?></td>
              <td><?php echo $client['cpf']; ?></td>
              <td><?php echo $client['status']; ?></td>
              <td><?php echo $client['renda']; ?></td>
              <td><?php echo $client['credito']; ?></td>
              <td><?php echo $client['data_exclusao']; ?></td>
              <td>
                <?php
                include 'config/connection.php';

                $userId = $client['idusuario'];
                $res = mysqli_query($conn, "SELECT nome FROM pessoas WHERE idpessoa = '$userId'");
                $userName = mysqli_fetch_assoc($res);
                echo $userName['nome'];

                mysqli_close($conn);
                ?>
              </td>
              <td class="buttons">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?clients" method="POST">
                  <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                  <input type="hidden" name="idpessoa" value="<?php echo $client['idpessoa']; ?>">
                  <input type="hidden" name="nome" value="<?php echo $client['nome']; ?>">
                  <input type="hidden" name="cpf" value="<?php echo $client['cpf']; ?>">
                  <input type="hidden" name="status" value="<?php echo $client['status']; ?>">
                  <input type="hidden" name="senha" value="<?php echo $client['senha']; ?>">
                  <input type="hidden" name="idcliente" value="<?php echo $client['idcliente']; ?>">
                  <input type="hidden" name="renda" value="<?php echo $client['renda']; ?>">
                  <input type="hidden" name="credito" value="<?php echo $client['credito']; ?>">
                  <button type="submit" name="submit-restore-client" class="btn btn-outline-success">Restaurar</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?clients" method="POST">
                  <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                  <button type="submit" name="submit-delete-client" class="btn btn-outline-danger">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php } ?>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['sellers'])) { /* Sellers page */ ?>
  <?php
  if ($_SESSION['type'] !== "admin")   {
    header('location: home.php');
  }

  /* RESTORE SELLER */
  if (isset($_POST['submit-restore-seller'])) {
    include 'config/connection.php';

    $id = $_POST['id'];
    $idPessoa = $_POST['idpessoa'];
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $status = $_POST['status'];
    $senha = $_POST['senha'];
    $idVendedor = $_POST['idvendedor'];
    $salario = $_POST['salario'];

    if (mysqli_query($conn, "INSERT INTO pessoas (idpessoa, nome, cpf, status, senha) VALUES ('$idPessoa', '$nome', '$cpf', '$status', '$senha') ")) {
      if (mysqli_query($conn, "INSERT INTO vendedores (idvendedor, salario, fk_idpessoa) VALUES ('$idVendedor', '$salario', '$idPessoa') ")) {
        if (mysqli_query($conn, "DELETE FROM lixeira WHERE id = '$id'")) {
          $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'lixeira.php?sellers', 'text' => 'Vendedor restaurado com sucesso'];
          header('location: templates/finish-operation.php');
        } else {
          $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?sellers', 'text' => 'Houve um problema ao excluir o vendedor'];
          header('location: templates/finish-operation.php');
        }
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?sellers', 'text' => 'Houve um problema ao excluir o vendedor'];
        header('location: templates/finish-operation.php');
      }
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'lixeira.php?sellers', 'text' => 'Houve um problema ao excluir o vendedor'];
      header('location: templates/finish-operation.php');
    }

    mysqli_close($conn);
  }

  /* GET DATA FROM DATABASE */
  include 'config/connection.php';

  $sql = "SELECT id, idpessoa, nome, cpf, status, senha, idvendedor, salario, data_exclusao, idusuario FROM lixeira WHERE idpessoa IS NOT NULL AND nome IS NOT NULL AND cpf IS NOT NULL AND status IS NOT NULL AND idvendedor IS NOT NULL AND salario IS NOT NULL";
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    // There is at least one deleted seller
    $exist = 1;
    $sellers = mysqli_fetch_all($res, MYSQLI_ASSOC);

    mysqli_free_result($res);
  } else {
    // There are no deleted sellers
    $exist = 0;
  }

  mysqli_close($conn);
  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/lixeira.css">
  </head>

  <body class="trash-page">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <?php if ($exist == 0) { ?>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col">Nome</th>
              <th scope="col">CPF</th>
              <th scope="col">Status</th>
              <th scope="col">Salário</th>
              <th scope="col">Data de exclusão</th>
              <th scope="col">Usuário</th>
              <th scope="col">Opções</th>
            </tr>
          </thead>
        </table>
        <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
        <a href="lixeira.php" class="btn btn-secondary mt-3">Voltar</a>
      <?php } else if ($exist == 1) { ?>
        <table class="table table-hover border text-center">
        <thead>
          <tr>
            <th scope="col">Nome</th>
            <th scope="col">CPF</th>
            <th scope="col">Status</th>
            <th scope="col">Salário</th>
            <th scope="col">Data de exclusão</th>
            <th scope="col">Usuário</th>
            <th scope="col">Opções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sellers as $seller) : ?>
            <tr>
              <td><?php echo $seller['nome']; ?></td>
              <td><?php echo $seller['cpf']; ?></td>
              <td><?php echo $seller['status']; ?></td>
              <td><?php echo $seller['salario']; ?></td>
              <td><?php echo $seller['data_exclusao']; ?></td>
              <td>
                <?php
                include 'config/connection.php';

                $userId = $seller['idusuario'];
                $res = mysqli_query($conn, "SELECT nome FROM pessoas WHERE idpessoa = '$userId'");
                $userName = mysqli_fetch_assoc($res);
                echo $userName['nome'];

                mysqli_close($conn);
                ?>
              </td>
              <td class="buttons">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?sellers" method="POST">
                  <input type="hidden" name="id" value="<?php echo $seller['id']; ?>">
                  <input type="hidden" name="idpessoa" value="<?php echo $seller['idpessoa']; ?>">
                  <input type="hidden" name="nome" value="<?php echo $seller['nome']; ?>">
                  <input type="hidden" name="cpf" value="<?php echo $seller['cpf']; ?>">
                  <input type="hidden" name="status" value="<?php echo $seller['status']; ?>">
                  <input type="hidden" name="senha" value="<?php echo $seller['senha']; ?>">
                  <input type="hidden" name="idvendedor" value="<?php echo $seller['idvendedor']; ?>">
                  <input type="hidden" name="salario" value="<?php echo $seller['salario']; ?>">
                  <button type="submit" name="submit-restore-seller" class="btn btn-outline-success">Restaurar</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?sellers" method="POST">
                  <input type="hidden" name="id" value="<?php echo $seller['id']; ?>">
                  <button type="submit" name="submit-delete-seller" class="btn btn-outline-danger">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
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
    <link rel="stylesheet" href="styles/pages/lixeira.css">
  </head>

  <body class="index">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>

    <?php if ($_SESSION['type'] == "admin") { ?>
      <main class="admin">
        <div class="container">
          <div class="row justify-content-around">
            <div class="products trash-item col-6 bg-light border rounded">
              <div class="label">
                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="shopping-basket" class="svg-inline--fa fa-shopping-basket fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                  <path fill="currentColor" d="M576 216v16c0 13.255-10.745 24-24 24h-8l-26.113 182.788C514.509 462.435 494.257 480 470.37 480H105.63c-23.887 0-44.139-17.565-47.518-41.212L32 256h-8c-13.255 0-24-10.745-24-24v-16c0-13.255 10.745-24 24-24h67.341l106.78-146.821c10.395-14.292 30.407-17.453 44.701-7.058 14.293 10.395 17.453 30.408 7.058 44.701L170.477 192h235.046L326.12 82.821c-10.395-14.292-7.234-34.306 7.059-44.701 14.291-10.395 34.306-7.235 44.701 7.058L484.659 192H552c13.255 0 24 10.745 24 24zM312 392V280c0-13.255-10.745-24-24-24s-24 10.745-24 24v112c0 13.255 10.745 24 24 24s24-10.745 24-24zm112 0V280c0-13.255-10.745-24-24-24s-24 10.745-24 24v112c0 13.255 10.745 24 24 24s24-10.745 24-24zm-224 0V280c0-13.255-10.745-24-24-24s-24 10.745-24 24v112c0 13.255 10.745 24 24 24s24-10.745 24-24z"></path>
                </svg>
                <h2>Produtos</h2>
              </div>
              <a href="lixeira.php?products" class="btn btn-outline-info">Entrar</a>
            </div>
            <div class="orders trash-item col-6 bg-light border rounded">
              <div class="label">
                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="box-open" class="svg-inline--fa fa-box-open fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                  <path fill="currentColor" d="M425.7 256c-16.9 0-32.8-9-41.4-23.4L320 126l-64.2 106.6c-8.7 14.5-24.6 23.5-41.5 23.5-4.5 0-9-.6-13.3-1.9L64 215v178c0 14.7 10 27.5 24.2 31l216.2 54.1c10.2 2.5 20.9 2.5 31 0L551.8 424c14.2-3.6 24.2-16.4 24.2-31V215l-137 39.1c-4.3 1.3-8.8 1.9-13.3 1.9zm212.6-112.2L586.8 41c-3.1-6.2-9.8-9.8-16.7-8.9L320 64l91.7 152.1c3.8 6.3 11.4 9.3 18.5 7.3l197.9-56.5c9.9-2.9 14.7-13.9 10.2-23.1zM53.2 41L1.7 143.8c-4.6 9.2.3 20.2 10.1 23l197.9 56.5c7.1 2 14.7-1 18.5-7.3L320 64 69.8 32.1c-6.9-.8-13.5 2.7-16.6 8.9z"></path>
                </svg>
                <h2>Pedidos</h2>
              </div>
              <a href="lixeira.php?orders" class="btn btn-outline-info">Entrar</a>
            </div>
          </div>
          <div class="row justify-content-around">
            <div class="clients trash-item col-6 bg-light border rounded">
              <div class="label">
                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-circle" class="svg-inline--fa fa-user-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                  <path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z"></path>
                </svg>
                <h2>Clientes</h2>
              </div>
              <a href="lixeira.php?clients" class="btn btn-outline-info">Entrar</a>
            </div>
            <div class="sellers trash-item col-6 bg-light border rounded">
              <div class="label">
                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-tie" class="svg-inline--fa fa-user-tie fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                  <path fill="currentColor" d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm95.8 32.6L272 480l-32-136 32-56h-96l32 56-32 136-47.8-191.4C56.9 292 0 350.3 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-72.1-56.9-130.4-128.2-133.8z"></path>
                </svg>
                <h2>Vendedores</h2>
              </div>
              <a href="lixeira.php?sellers" class="btn btn-outline-info">Entrar</a>
            </div>
          </div>
        </div>
      </main>
    <?php } else if ($_SESSION['type'] == "vendedor") { ?>
      <main class="seller">
        <div class="seller container">
          <div class="row">
            <div class="orders trash-item col-6 bg-light border rounded">
              <div class="label">
                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="box-open" class="svg-inline--fa fa-box-open fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                  <path fill="currentColor" d="M425.7 256c-16.9 0-32.8-9-41.4-23.4L320 126l-64.2 106.6c-8.7 14.5-24.6 23.5-41.5 23.5-4.5 0-9-.6-13.3-1.9L64 215v178c0 14.7 10 27.5 24.2 31l216.2 54.1c10.2 2.5 20.9 2.5 31 0L551.8 424c14.2-3.6 24.2-16.4 24.2-31V215l-137 39.1c-4.3 1.3-8.8 1.9-13.3 1.9zm212.6-112.2L586.8 41c-3.1-6.2-9.8-9.8-16.7-8.9L320 64l91.7 152.1c3.8 6.3 11.4 9.3 18.5 7.3l197.9-56.5c9.9-2.9 14.7-13.9 10.2-23.1zM53.2 41L1.7 143.8c-4.6 9.2.3 20.2 10.1 23l197.9 56.5c7.1 2 14.7-1 18.5-7.3L320 64 69.8 32.1c-6.9-.8-13.5 2.7-16.6 8.9z"></path>
                </svg>
                <h2>Pedidos</h2>
              </div>
              <a href="lixeira.php?orders" class="btn btn-outline-info">Entrar</a>
            </div>
            <div class="clients trash-item col-6 bg-light border rounded">
              <div class="label">
                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-circle" class="svg-inline--fa fa-user-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                  <path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z"></path>
                </svg>
                <h2>Clientes</h2>
              </div>
              <a href="lixeira.php?clients" class="btn btn-outline-info">Entrar</a>
            </div>
          </div>
        </div>
      </main>
    <?php } ?>


  </body>

  </html>
<?php } ?>