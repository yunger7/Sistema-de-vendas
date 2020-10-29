<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* DELETE ORDER */
if (isset($_GET['delete-order'])) {
  include 'config/connection.php';

  $id = $_GET['id'];

  $res = mysqli_query($conn, "SELECT * FROM pedidos WHERE idpedido = '$id'");
  
  // Fetch order data
  $order = mysqli_fetch_assoc($res);
  $date = $order['data'];
  $value = $order['valor'];
  $status = $order['status'];
  $sellerId = $order['fk_idvendedor'];
  $clientId = $order['fk_idcliente'];

  // Fetch user data
  $userId = $_SESSION['user-id'];

  // Insert into trash bin
  if (mysqli_query($conn, "INSERT INTO lixeira(idpedido, data, valor, status, idvendedor, idcliente, idusuario) VALUES('$id', '$date', '$value', '$status', '$sellerId', '$clientId', '$userId')")) {

    $res = mysqli_query($conn, "SELECT * FROM itens_pedidos WHERE fk_idpedido = '$id'");
    $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

    foreach ($products as $product) {
      $productId = $product['fk_idproduto'];
      $quant = $product['qtd'];
      $value = $product['valor'];
      if (mysqli_query($conn, "INSERT INTO lixeira(idpedido, idproduto, qtd, valor, idusuario) VALUES('$id', '$productId', '$quant', '$value', '$userId')")) {
        $error = 0;
      } else {
        $error = 1;
      }
    }
  } else {
    $error = 1;
  }

  // Check for errors and delete records
  if ($error === 0) {
    if (mysqli_query($conn, "DELETE FROM pedidos WHERE idpedido = '$id'")) {
      if (mysqli_query($conn, "DELETE FROM itens_pedidos WHERE fk_idpedido = '$id'")) {
        $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'pedidos.php', 'text' => 'Pedido excluído com sucesso'];
        header('location: templates/finish-operation.php');
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'pedidos.php', 'text' => 'Houve um problema ao excluir o pedido'];
        header('location: templates/finish-operation.php');
      }
    }
  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'pedidos.php', 'text' => 'Houve um problema ao excluir o pedido'];
    header('location: templates/finish-operation.php');
  }


  mysqli_close($conn);
}

if (isset($_GET['view-order'])) { /* View order page */ ?>
  <?php
  $id = $_GET['id'];

  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT * FROM pedidos WHERE idpedido = '$id'");
  $order = mysqli_fetch_assoc($res);

  mysqli_free_result($res);
  mysqli_close($conn);
  ?>
  <!DOCTYPE html>
  <html lang="pt-br">
  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/pedidos.css">
  </head>
  <body class="view-order">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="order bg-light border rounded">
        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-archive-fill top-icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15h9.286zM5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8H.8z"/>
        </svg>
        <h2>Pedido nº <?php echo $order['idpedido']; ?></h2>
        <div class="data-box">
          <div class="date data">
            <div class="label">
              <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar-week" fill="#4B5C6B" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
              </svg>
              <p>Data</p>
            </div>
            <span><?php echo $order['data']; ?></span>
          </div>
          <div class="value data">
            <div class="label">
              <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-cash" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M15 4H1v8h14V4zM1 3a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H1z"/>
                <path d="M13 4a2 2 0 0 0 2 2V4h-2zM3 4a2 2 0 0 1-2 2V4h2zm10 8a2 2 0 0 1 2-2v2h-2zM3 12a2 2 0 0 0-2-2v2h2zm7-4a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
              </svg>
              <p>Valor</p>
            </div>
            <span>R$ <?php echo $order['valor'] ?></span>
          </div>
          <div class="status data">
            <div class="label">
              <svg svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check2-square" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M15.354 2.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L8 9.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                <path fill-rule="evenodd" d="M1.5 13A1.5 1.5 0 0 0 3 14.5h10a1.5 1.5 0 0 0 1.5-1.5V8a.5.5 0 0 0-1 0v5a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V3a.5.5 0 0 1 .5-.5h8a.5.5 0 0 0 0-1H3A1.5 1.5 0 0 0 1.5 3v10z"/>
              </svg>
              <p>Status</p>
            </div>
            <span><?php echo $order['status']; ?></span>
          </div>
          <div class="client data">
            <?php
            include 'config/connection.php';

            $res = mysqli_query($conn, "SELECT pessoas.nome FROM pedidos JOIN clientes on pedidos.fk_idcliente = clientes.idcliente JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE pedidos.idpedido = '$id'");

            $clientName = mysqli_fetch_assoc($res);

            mysqli_free_result($res);
            mysqli_close($conn);
            ?>
            <div class="label">
              <svg width="1em" height="1em" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-circle" class="svg-inline--fa fa-user-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                <path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z"></path>
              </svg>
              <p>Cliente</p>
            </div>
            <span><?php echo $clientName['nome']; ?></span>
          </div>
          <div class="seller data">
            <?php
              include 'config/connection.php';

              $res = mysqli_query($conn, "SELECT pessoas.nome FROM pedidos JOIN vendedores on pedidos.fk_idvendedor = vendedores.idvendedor JOIN pessoas ON vendedores.fk_idpessoa = pessoas.idpessoa WHERE pedidos.idpedido = '$id'");

              $sellerName = mysqli_fetch_assoc($res);

              mysqli_free_result($res);
              mysqli_close($conn);
              ?>
            <div class="label">
              <svg width="1em" height="1em" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-tie" class="svg-inline--fa fa-user-tie fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path fill="currentColor" d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm95.8 32.6L272 480l-32-136 32-56h-96l32 56-32 136-47.8-191.4C56.9 292 0 350.3 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-72.1-56.9-130.4-128.2-133.8z"></path>
              </svg>
              <p>Vendedor</p>
            </div>
            <span><?php echo $sellerName['nome']; ?></span>
          </div>
        </div>
      </section>
      <section class="products">
        <h2>Produtos</h2>
        <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">Nome</th>
                <th scope="col">Quantidade</th>
                <th scope="col">Valor do produto</th>
                <th scope="col">Valor no pedido</th>
              </tr>
            </thead>
            <tbody>
              <?php
              include 'config/connection.php';

              $res = mysqli_query($conn, "SELECT descricao, produtos.valor AS valor_produto, qtd, itens_pedidos.valor AS valor_venda FROM itens_pedidos JOIN produtos ON itens_pedidos.fk_idproduto = produtos.idproduto WHERE fk_idpedido = '$id'
              ");
              $productList = mysqli_fetch_all($res, MYSQLI_ASSOC);

              mysqli_free_result($res);
              mysqli_close($conn);
              ?>
              <?php foreach ($productList as $product) { ?>
                <tr>
                  <td><?php echo $product['descricao']; ?></td>
                  <td><?php echo $product['qtd']; ?></td>
                  <td><?php echo $product['valor_produto']; ?></td>
                  <td><?php echo $product['valor_venda']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
      </section>
    </main>
  </body>
  </html>
<?php } else if (isset($_GET['edit-order'])) { /* Edit order page */ ?>
  <?php
  $id = $_GET['id'];
  $error = 0;

  /* EDIT ORDER DETAILS */
  if (isset($_POST['submit-edit-order'])) {
    include 'config/connection.php';

    $date = $_POST['date'];
    $status = $_POST['status'];
    $clientId = $_POST['client'];

    if (mysqli_query($conn, "UPDATE pedidos SET data = '$date', status = '$status', fk_idcliente = '$clientId' WHERE idpedido = '$id'")) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'pedidos.php', 'text' => 'Pedido editado com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'pedidos.php', 'text' => 'Houve um problema ao editar o pedido'];
      header('location: templates/finish-operation.php');

      echo mysqli_error($conn);
    }

    mysqli_close($conn);
  }

  /* CHANGE PRODUCT VALUE */
  if (isset($_POST['product-id'])) {
    $optionId = $_POST['product-id'];

    include 'config/connection.php';

    $res = mysqli_query($conn, "SELECT valor FROM produtos WHERE idproduto = '$optionId'");
    $productValue = mysqli_fetch_assoc($res);

    mysqli_close($conn);
  }

  /* CHANGE IN-ORDER PRODUCT VALUE */
  if (isset($_POST['quantity'])) {
    $quantity = $_POST['quantity'];

    if (isset($_POST['product-id'])) {
      // Option is selected
      include 'config/connection.php';

      $res = mysqli_query($conn, "SELECT valor FROM produtos WHERE idproduto = '$optionId'");
      $productValue = mysqli_fetch_assoc($res);

      if ($quantity == 1) {
        $inOrderValue = $productValue['valor'];
      } else if ($quantity > 1) {
        $inOrderValue = $productValue['valor'] * $quantity;
      }

      mysqli_close($conn);
    }
  }

  /* ADD PRODUCT */
  if (isset($_POST['submit-add-product'])) {
    include 'config/connection.php';

    $productId = $_POST['product-id'];
    $quantity = $_POST['quantity'];
    $inOrderValue = $_POST['in-order-value'];

    $res = mysqli_query($conn, "SELECT idproduto FROM itens_pedidos JOIN produtos ON itens_pedidos.fk_idproduto = produtos.idproduto WHERE fk_idpedido = '$id'");
    $addedProducts = mysqli_fetch_all($res, MYSQLI_ASSOC);

    // Check if product is already added
    $error = 0;
    foreach ($addedProducts as $addedProduct) {
      if ($addedProduct['idproduto'] == $productId) {
        $error = 1;
        break;
      }
    }

    // Add item to order
    if ($error === 0) {
      mysqli_query($conn, "INSERT INTO itens_pedidos(fk_idpedido, fk_idproduto, qtd, valor) VALUES('$id', '$productId', '$quantity', '$inOrderValue')");
    }

    // Calculate and change new order price
    $res = mysqli_query($conn, "SELECT valor FROM itens_pedidos WHERE fk_idpedido = '$id'");
    $orderValueList = mysqli_fetch_all($res, MYSQLI_ASSOC);

    $sum = 0;
    foreach ($orderValueList as $item) {
      $sum += $item['valor'];
    }

    mysqli_query($conn, "UPDATE pedidos SET valor = '$sum' WHERE idpedido = '$id'");

    mysqli_close($conn);
  }

  /* REMOVE PRODUCT */
  if (isset($_GET['remove-item'])) {
    $productId = $_GET['product-id'];
    include 'config/connection.php';

    $res = mysqli_query($conn, "SELECT * FROM itens_pedidos WHERE fk_idpedido = '$id' AND fk_idproduto = '$productId'");

    if (mysqli_num_rows($res) > 0) {
      // Product is added
      mysqli_query($conn, "DELETE FROM itens_pedidos WHERE fk_idpedido = '$id' AND fk_idproduto = '$productId'");
    }

    // Calculate and change new order price
    $res = mysqli_query($conn, "SELECT valor FROM itens_pedidos WHERE fk_idpedido = '$id'");
    $orderValueList = mysqli_fetch_all($res, MYSQLI_ASSOC);

    $sum = 0;
    foreach ($orderValueList as $item) {
      $sum += $item['valor'];
    }

    mysqli_query($conn, "UPDATE pedidos SET valor = '$sum' WHERE idpedido = '$id'");

    mysqli_close($conn);
  }

  /* GET RECORDS FROM DATABASE */
  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT * FROM pedidos WHERE idpedido = '$id'");
  $order = mysqli_fetch_assoc($res);

  mysqli_free_result($res);
  mysqli_close($conn);
  ?>
  <!DOCTYPE html>
  <html lang="pt-br">
  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/pedidos.css">
  </head>
  <body class="edit-order view-order">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="order bg-light border rounded">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?edit-order&id=<?php echo $id; ?>" method="POST">
          <svg viewBox="0 0 16 16" class="bi bi-pencil-fill top-icon" fill="#4B5C6B" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"></path>
          </svg>
          <h2>Pedido nº <?php echo $order['idpedido']; ?></h2>
          <div class="data-box">
            <div class="date data">
              <div class="label">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar-week" fill="#currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                  <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                </svg>
                <p>Data</p>
              </div>
              <input type="datetime" name="date" value="<?php echo $order['data']; ?>" class="form-control">
            </div>
            <div class="value data">
              <div class="label">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-cash" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M15 4H1v8h14V4zM1 3a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H1z"/>
                  <path d="M13 4a2 2 0 0 0 2 2V4h-2zM3 4a2 2 0 0 1-2 2V4h2zm10 8a2 2 0 0 1 2-2v2h-2zM3 12a2 2 0 0 0-2-2v2h2zm7-4a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                </svg>
                <p>Valor</p>
              </div>
              <input type="number" name="value" value="<?php echo $order['valor'] ?>" class="form-control" disabled>
            </div>
            <div class="status data">
              <div class="label">
                <svg svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check2-square" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M15.354 2.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L8 9.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                  <path fill-rule="evenodd" d="M1.5 13A1.5 1.5 0 0 0 3 14.5h10a1.5 1.5 0 0 0 1.5-1.5V8a.5.5 0 0 0-1 0v5a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V3a.5.5 0 0 1 .5-.5h8a.5.5 0 0 0 0-1H3A1.5 1.5 0 0 0 1.5 3v10z"/>
                </svg>
                <p>Status</p>
              </div>
                <div class="radio-form">
                  <div class="option">
                    <input type="radio" name="status" id="ativo" value="A" <?php if ($order['status'] == "A") { echo "checked"; } ?>>
                    <label for="ativo">Ativo</label>
                  </div>
                  <div class="option">
                    <input type="radio" name="status" id="inativo" value="I" <?php if ($order['status'] == "I") { echo "checked"; } ?>>
                    <label for="inativo">Inativo</label>
                  </div>
                </div>
            </div>
            <div class="client data">
              <?php
              include 'config/connection.php';

              $res = mysqli_query($conn, "SELECT idcliente FROM pedidos JOIN clientes on pedidos.fk_idcliente = clientes.idcliente WHERE pedidos.idpedido = '$id'");
              $defaultClientId = mysqli_fetch_assoc($res);

              $res = mysqli_query($conn, "SELECT nome, idcliente FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa");
              $clients = mysqli_fetch_all($res, MYSQLI_ASSOC);

              mysqli_free_result($res);
              mysqli_close($conn);
              ?>
              <div class="label">
                <svg width="1em" height="1em" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-circle" class="svg-inline--fa fa-user-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                  <path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z"></path>
                </svg>
                <p>Cliente</p>
              </div>
              <select name="client" class="form-control">
                <?php foreach($clients as $client) { ?>
                  <?php if ($client['idcliente'] == $defaultClientId['idcliente']) { ?>
                    <option value="<?php echo $client['idcliente'] ?>" selected><?php echo $client['nome']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $client['idcliente']; ?>"><?php echo $client['nome']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </div>
            <div class="seller data">
              <?php
                include 'config/connection.php';

                $res = mysqli_query($conn, "SELECT pessoas.nome FROM pedidos JOIN vendedores on pedidos.fk_idvendedor = vendedores.idvendedor JOIN pessoas ON vendedores.fk_idpessoa = pessoas.idpessoa WHERE pedidos.idpedido = '$id'");

                $sellerName = mysqli_fetch_assoc($res);

                mysqli_free_result($res);
                mysqli_close($conn);
                ?>
              <div class="label">
                <svg width="1em" height="1em" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-tie" class="svg-inline--fa fa-user-tie fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                  <path fill="currentColor" d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm95.8 32.6L272 480l-32-136 32-56h-96l32 56-32 136-47.8-191.4C56.9 292 0 350.3 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-72.1-56.9-130.4-128.2-133.8z"></path>
                </svg>
                <p>Vendedor</p>
              </div>
              <span><?php echo $sellerName['nome']; ?></span>
            </div>
          </div>
          <button type="submit" name="submit-edit-order" class="btn btn-warning">Editar</button>
        </form>
      </section>
      <section class="products">
        <h2>Produtos</h2>
        <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th></th>
                <th scope="col">Nome</th>
                <th scope="col">Quantidade</th>
                <th scope="col">Valor do produto</th>
                <th scope="col">Valor no pedido</th>
                <th>Opções</th>
              </tr>
            </thead>
            <tbody>
              <?php
              include 'config/connection.php';

              $res = mysqli_query($conn, "SELECT idproduto, descricao, produtos.valor AS valor_produto, qtd, itens_pedidos.valor AS valor_venda FROM itens_pedidos JOIN produtos ON itens_pedidos.fk_idproduto = produtos.idproduto WHERE fk_idpedido = '$id'
              ");
              $productList = mysqli_fetch_all($res, MYSQLI_ASSOC);

              mysqli_free_result($res);
              mysqli_close($conn);
              ?>
              <?php foreach ($productList as $product) { ?>
                <tr>
                  <td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?edit-order&id=<?php echo $id; ?>&remove-item&product-id=<?php echo $product['idproduto']; ?>" class="btn btn-danger">X</a></td>
                  <td><?php echo $product['descricao']; ?></td>
                  <td><?php echo $product['qtd']; ?></td>
                  <td>R$ <?php echo $product['valor_produto']; ?></td>
                  <td>R$ <?php echo $product['valor_venda']; ?></td>
                  <td></td>
                </tr>
              <?php } ?>
              <tr class="add-product">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?edit-order&id=<?php echo $id; ?>" method="POST">
                  <td></td>
                  <td>
                    <select name="product-id" class="form-control" onchange="this.form.submit()" required>
                      <option value="" disabled selected>Selecionar</option>
                      <?php
                      include 'config/connection.php';

                      $res = mysqli_query($conn, "SELECT idproduto, descricao FROM produtos");
                      $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

                      mysqli_free_result($res);
                      mysqli_close($conn);
                      ?>
                      <?php foreach ($products as $product) { ?>
                        <?php if ($product['idproduto'] == $optionId) { ?>
                          <option value="<?php echo $product['idproduto']; ?>" selected><?php echo $product['descricao']; ?></option>
                        <?php } else { ?>
                          <option value="<?php echo $product['idproduto']; ?>"><?php echo $product['descricao']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </td>
                  <td><input type="number" name="quantity" min="1" class="form-control" <?php if (isset($quantity)) { echo "value='" . $quantity . "'"; } ?> onchange="this.form.submit()" placeholder="1" required></td>
                  <td><input type="number" name="product-value" value="<?php if (isset($productValue)) { echo $productValue['valor']; } ?>" class="form-control" placeholder="R$ 0,00" disabled required></td>
                  <td><input type="number" name="in-order-value" class="form-control" <?php if (isset($inOrderValue)) { echo "value='" . $inOrderValue . "'"; } ?> step="any" placeholder="R$ 0,00" required></td>
                  <td><button type="submit" name="submit-add-product" class="btn btn-outline-info">Adicionar</button></td>
                </form>
              </tr>
              <?php if ($error == 1) { ?>
                <div class="alert alert-danger" role="alert">
                  Esse produto já foi cadastrado
                </div>
              <?php } ?>
            </tbody>
          </table>
      </section>
    </main>
  </body>
  </html>
<?php } else { ?>
<?php if ($_SESSION['priority'] >= 1) { /* Search and results (Seller or admin) */ ?>
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

    <body class="search-result">
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

                  $res = mysqli_query($conn, "SELECT nome, idvendedor FROM vendedores JOIN pessoas ON vendedores.fk_idpessoa = pessoas.idpessoa WHERE idvendedor = '$sellerId'");
                  $seller = mysqli_fetch_assoc($res);

                  mysqli_free_result($res);
                  mysqli_close($conn);
                  ?>
                  <td><?php echo $seller['nome']; ?></td>
                  <td>
                    <a href="pedidos.php?view-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-success">Detalhes</a>
                    <?php if ($_SESSION['type'] == "admin") { ?>
                      <a href="pedidos.php?edit-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-warning">Editar</a>
                      <a href="pedidos.php?delete-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-danger">Excluir</a>
                      <?php } else {
                      include 'config/connection.php';

                      $orderId = $order['idpedido'];
                      $userId = $_SESSION['user-id'];

                      $res = mysqli_query($conn, "SELECT idpedido FROM pedidos WHERE fk_idvendedor = (SELECT idvendedor FROM vendedores WHERE fk_idpessoa = '$userId') AND idpedido = '$orderId' ");

                      if (mysqli_num_rows($res) > 0) { ?>
                        <a href="pedidos.php?edit-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-warning">Editar</a>
                        <a href="pedidos.php?delete-order&id=<?php echo $order['idpedido']; ?>" class="btn btn-outline-danger">Excluir</a>
                    <?php
                      }
                      mysqli_close($conn);
                    }
                    ?>
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
<?php } else if ($_SESSION['priority'] == 0 ) { /* Results (Client) */ ?>
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
<?php } ?>