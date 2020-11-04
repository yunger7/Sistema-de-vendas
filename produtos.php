<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* SUBMIT ORDER */
if (isset($_POST['submit-order'])) {
  $orderValue = $_POST['order-value'];
  $userId = $_SESSION['user-id'];
  $clientId = $_POST['client-id'];

  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT idvendedor FROM vendedores WHERE fk_idpessoa = '$userId'");
  $res2 = mysqli_fetch_array($res);
  $sellerId = $res2['idvendedor'];

  mysqli_free_result($res);

  $sql = "INSERT INTO pedidos(valor, fk_idvendedor, fk_idcliente) VALUES ('$orderValue', '$sellerId', '$clientId')";

  if (mysqli_query($conn, $sql)) {
    $orderId = mysqli_insert_id($conn);

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      $productId = $_SESSION['cart'][$i]['id'];
      $quant = $_SESSION['cart'][$i]['quant'];
    
      // calculate product value
      if ($_SESSION['cart'][$i]['disc'] == 0) {
        if ($_SESSION['cart'][$i]['quant'] == 1) {
          $value = $_SESSION['cart'][$i]['value'];
        } else {
          $value = $_SESSION['cart'][$i]['quant'] * $_SESSION['cart'][$i]['value'];
        }
      } else {
        if ($_SESSION['cart'][$i]['quant'] == 1) {
          $price = $_SESSION['cart'][$i]['value'];
        } else {
          $price = $_SESSION['cart'][$i]['quant'] * $_SESSION['cart'][$i]['value'];
        }
        $discount = $_SESSION['cart'][$i]['disc'];
        $value = $price - ($discount / 100) * $price;
      }

      $sql2 = "INSERT INTO itens_pedidos(fk_idpedido, fk_idproduto, qtd, valor) VALUES ('$orderId', '$productId', '$quant', '$value')";

      if (mysqli_query($conn, $sql2)) {
        $success = 1;
      } else {
        $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao finalizar o pedido'];
        header('location: templates/finish-operation.php');
      }
    }

    if ($success === 1) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'produtos.php', 'text' => 'Pedido finalizado com sucesso!'];
      header('location: templates/finish-operation.php');
    }

  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao finalizar o pedido'];
    header('location: templates/finish-operation.php');
  }

  mysqli_close($conn);
}

/* ADD PRODUCT */
if (isset($_POST['submit-add-product'])) {
  include 'config/connection.php';

  $description = $_POST['descricao'];
  $stock = $_POST['estoque'];
  $value = $_POST['valor'];
  $status = $_POST['status'];

  if (mysqli_query($conn, "INSERT INTO produtos(descricao, estoque, valor, status) VALUES('$description', '$stock', '$value', '$status') ")) {
    $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'produtos.php', 'text' => 'Produto adicionado com sucesso'];
    header('location: templates/finish-operation.php');
  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao adicionar o produto'];
    header('location: templates/finish-operation.php');
  }

  mysqli_close($conn);
}

/* ADD DISCOUNT */
if (isset($_POST['submit-add-discount'])) {
  include 'config/connection.php';

  $discount = $_POST['discount'];
  $productId = $_POST['id'];

  // Check if discount is already applied
  $res = mysqli_query($conn, "SELECT desconto FROM produtos WHERE idproduto = '$productId'");
  $checkDisc = mysqli_fetch_assoc($res);

  if ($checkDisc['desconto'] !== $discount) {
    if (mysqli_query($conn, "UPDATE produtos SET desconto = '$discount' WHERE idproduto = '$productId'")) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'produtos.php?add-discount', 'text' => 'Desconto adicionado com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php?add-discount', 'text' => 'Houve um problema ao adicionar o desconto'];
      header('location: templates/finish-operation.php');
    }
  }
  
  mysqli_close($conn);
}

/* EDIT PRODUCT */
if (isset($_POST['submit-edit-product'])) {
  include 'config/connection.php';

  $idEdit = $_POST['id-editar'];
  $description = $_POST['descricao'];
  $stock = $_POST['estoque'];
  $value = $_POST['valor'];
  $status = $_POST['status'];

  if (mysqli_query($conn, "UPDATE produtos SET descricao = '$description', estoque = '$stock', valor = '$value', status = '$status' WHERE idproduto = '$idEdit'")) {
    $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'produtos.php', 'text' => 'Produto editado com sucesso'];
    header('location: templates/finish-operation.php');
  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao editar o produto'];
    header('location: templates/finish-operation.php');
  }

  mysqli_close($conn);
}

/* DELETE PRODUCT */
if (isset($_GET['delete-product'])) {
  include 'config/connection.php';

  $idDelete = $_GET['id'];

  $res = mysqli_query($conn, "SELECT * FROM produtos WHERE idproduto = '$idDelete'");
  $product = mysqli_fetch_assoc($res);

  // Product data
  $description = $product['descricao'];
  $stock = $product['estoque'];
  $value = $product['valor'];
  $status = $product['status'];

  // User data
  $userId = $_SESSION['user-id'];

  // Insert into trash bin and delete data
  $sql = "INSERT INTO lixeira(status, idproduto, descricao, estoque, valor, idusuario) VALUES('$status', '$idDelete', '$description', '$stock', '$value', '$userId')";

  if (mysqli_query($conn, $sql)) {
    if (mysqli_query($conn, "DELETE FROM produtos WHERE idproduto = '$idDelete' ")) {
      $_SESSION['finish-operation'] = ['type' => 'success', 'url' => 'produtos.php', 'text' => 'Produto excluído com sucesso'];
      header('location: templates/finish-operation.php');
    } else {
      $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao excluir o produto'];
      header('location: templates/finish-operation.php');
    }
  } else {
    $_SESSION['finish-operation'] = ['type' => 'error', 'url' => 'produtos.php', 'text' => 'Houve um problema ao excluir o produto'];
    header('location: templates/finish-operation.php');
  }

  mysqli_free_result($res);
  mysqli_close($conn);
}

/* SEND PAGES */
if (isset($_GET['cart'])) { /* Cart page */ ?>
  <?php
  // Sort cart
  sort($_SESSION['cart']);

  /* CHANGE PRODUCT QUANTITY */
  if (isset($_GET['quant'])) {
    $quant = $_POST['quant'];
    $idEdit = $_POST['id'];

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      if ($_SESSION['cart'][$i]['id'] === $idEdit) {
        include 'config/connection.php';

        $res = mysqli_query($conn, "SELECT estoque FROM produtos WHERE idproduto = '$idEdit' ");
        $estoque = mysqli_fetch_row($res);

        if ($quant > $estoque[0]) {
          echo "
            <script language='javascript' type='text/javascript'>
            alert('Valor inserido acima do estoque');
            </script>
          ";
        } else {
          $_SESSION['cart'][$i]['quant'] = $quant;

          if ($_SESSION['cart'][$i]['quant'] == 1) {
            $_SESSION['cart'][$i]['total'] = $_SESSION['cart'][$i]['value'];
          } else {
            $_SESSION['cart'][$i]['total'] = $quant * $_SESSION['cart'][$i]['value'];
          }
        }

        mysqli_free_result($res);
        mysqli_close($conn);
      }
    }
  }
  
  /* DELETE ITEMS FROM CART */
  if (isset($_GET['remove'])) {
    $idRemove = $_GET['id'];

    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
      if ($_SESSION['cart'][$i]['id'] === $idRemove) {
        unset($_SESSION['cart'][$i]);
      }
    }

    sort($_SESSION['cart']);
  }

  /* GET DATA FROM DATABASE */
  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT idproduto, descricao, valor, desconto FROM produtos");
  $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

  mysqli_free_result($res);
  mysqli_close($conn);

  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/produtos.css">
  </head>

  <body id="cart">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
        <table class="table table-hover border text-center">
          <thead>
            <tr>
              <th scope="col"></th>
              <th scope="col">Nome</th>
              <th scope="col">Quantidade</th>
              <th scope="col">Desconto</th>
              <th scope="col">Valor</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($products as $product) { ?>
              <?php for ($i = 0; $i < count($_SESSION['cart']); $i++) { ?>
                <?php if ($_SESSION['cart'][$i]['id'] === $product['idproduto']) { ?>
                  <tr>
                    <td><a href="produtos.php?cart&remove&id=<?php echo $product['idproduto']; ?>" class="btn btn-danger">X</a></td>
                    <td><?php echo $product['descricao']; ?></td>
                    <td>
                      <form action="<?php echo $_SERVER['PHP_SELF']; ?>?cart&quant" method="POST">
                        <input type="hidden" name="id" value="<?php echo $product['idproduto']; ?>">
                        <input type="number" name="quant" min="1" class="quant form-control" placeholder="1" onchange="this.form.submit()"
                        <?php
                        if (!empty($_SESSION['cart'])) {
                          if ($_SESSION['cart'][$i]['quant'] !== 1) {
                            $quant = $_SESSION['cart'][$i]['quant'];
                            echo "value='". $quant ."'";
                          }
                        }
                        ?>
                        >
                      </form>
                    </td>
                    <td><?php echo $product['desconto']; ?>%</td>
                    <td>R$ 
                      <?php
                        if (!empty($_SESSION['cart'])) {
                          $total = $_SESSION['cart'][$i]['total'];
                          echo number_format((float)$total, 2, '.', '');
                        }
                      ?>
                      <?php
                        // if (!empty($_SESSION['cart'])) {
                        //   if ($product['desconto'] == 0) {
                        //     $total = $_SESSION['cart'][$i]['total'];
                        //     echo number_format((float)$total, 2, '.', '');
                        //   } else {
                        //     $newPrice = $product['valor'] - ($product['desconto'] / 100) * $product['valor'];
                        //     $_SESSION['cart'][$i]['total'] = $newPrice;
                        //     $total = $_SESSION['cart'][$i]['total'];
                        //     echo number_format((float)$total, 2, '.', '');
                        //   }
                        // }
                      ?>
                    </td>
                  </tr>
                <?php } ?>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>
        <?php if ($_SESSION['priority'] > 0) { /* SELLER OR ADMIN */ ?>
          <section class="finish priority-2">
            <div class="total">
              <p class="h5"><strong>Total</strong></p>
              <p class="total-value">R$ 
                <?php
                if (!empty($_SESSION['cart'])) {
                  $sum = 0;
                  foreach ($_SESSION['cart'] as $cartItem) {
                    $sum += $cartItem['total'];
                  }
                  echo number_format((float)$sum, 2, '.', '');
                } else {
                  echo "0,00";
                }
                ?>
              </p>
            </div>
            <div class="buttons">
              <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
              <a href="<?php if (!empty($_SESSION['cart'])) { echo "produtos.php?final";} else { echo "#"; } ?>">
              <button type="button" class="btn btn-info" <?php if (empty($_SESSION['cart'])) { echo "disabled"; } ?>>Finalizar Pedido</button>
              </a>
            </div>
          </section>
        <?php } else { /* CLIENT */ ?>
          <section class="finish priority-0">
            <div class="total">
              <p class="h5"><strong>Total</strong></p>
              <p class="total-value">R$ 
                <?php
                if (!empty($_SESSION['cart'])) {
                  $sum = 0;
                  foreach ($_SESSION['cart'] as $cartItem) {
                    $sum += $cartItem['total'];
                  }
                  echo number_format((float)$sum, 2, '.', '');
                } else {
                  echo "0,00";
                }
                ?>
              </p>
            </div>
            <div class="buttons">
              <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
            </div>
          </section>
        <?php } ?>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['name']) || isset($_GET['letter'])) { /* Search page */ ?>
  <?php
  include 'config/connection.php';

  $cases = [$_GET['name'] ?? "", $_GET['letter'] ?? ""];

  switch ($cases) {
    // search by name
    case ($cases[0] !== "" && $cases[1] === ""):
      $name = $_GET['name'];
      $sql = "SELECT * FROM produtos WHERE descricao LIKE '%$name%'";
    break;
    // search by letter
    case ($cases[0] === "" && $cases[1] !== ""):
      $letter = $_GET['letter'];
      $sql = "SELECT * FROM produtos WHERE descricao LIKE '$letter%'";
    break;
    // empty search
    case ($cases[0] === "" && $cases[1] === ""):
      $sql = "SELECT * FROM produtos";
    break;
  }

  // Database search
  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    $searchResults = 1;
    $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

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
    <link rel="stylesheet" href="styles/pages/produtos.css">
  </head>

  <body>
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="search">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="input-group">
            <input type="text" name="name" id="product-name" class="form-control" placeholder="Pesquisar">
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
            include 'config/connection.php';

            $letrasExistentes = mysqli_query($conn, "SELECT DISTINCT LEFT(descricao, 1) AS letra FROM produtos ORDER BY letra");
            $iniciais = mysqli_fetch_all($letrasExistentes, MYSQLI_ASSOC);

            mysqli_close($conn);
            foreach ($alfabeto as $letra) {
              $existeLetra = 0;
              foreach ($iniciais as $inicial) {
                if ($letra == $inicial['letra']) {
                  $existeLetra = 1;
                }
              }
              
              if ($existeLetra == 0) {
                echo "<button type='button' class='btn btn-link text-secondary'>$letra</button>";
              } else if ($existeLetra == 1) {
                echo "<button type='submit' name='letter' value='$letra' class='btn btn-link'>$letra</button>";
              }
              
            }
            ?>
          </div>
        </form>
      </section>
      <section class="products">
        <?php if ($searchResults === 1) { ?>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?cart" class="btn btn-info">
          <svg id="cart-logo" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="shopping-cart" class="svg-inline--fa fa-shopping-cart fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="25px" height="25px">
            <path fill="currentColor" d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"></path>
          </svg>
          Ver carrinho
        </a>
        <?php } ?>
        <?php if ($searchResults === 0) { ?>
          <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
          <a href="produtos.php?back" class="btn btn-secondary">Voltar</a>
        <?php } else { ?>
          <?php
          $gridCount = 0;
          foreach ($products as $product) { ?>
            <?php if ($gridCount == 0) { ?>
              <div class="row">
              <?php } ?>
              <?php if ($gridCount < 4) { ?>
                <div class="product col-sm <?php if ($product['estoque'] > 0) { echo "border"; } ?> rounded">
                  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <p class="h6"><?php echo $product['descricao']; ?></p>
                    <p>R$ <?php echo $product['valor']; ?></p>
                    <?php if ($product['estoque'] > 0) { ?>
                      <button type="submit" name="add-to-cart" class="btn btn-info" 
                        <?php
                        if (!empty($_SESSION['cart'])) {
                          $empty = 0;
                          foreach ($_SESSION['cart'] as $cartItem) {
                            if ($cartItem['id'] === $product['idproduto']) {
                              $empty = 1;
                            }
                          }

                          if ($empty === 1) {
                            echo "disabled";
                          }
                        }

                        ?>
                      >
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "Adicionado";
                        } else {
                          echo "Adicionar";
                        }
                      } else {
                        echo "Adicionar";
                      }
                      ?>
                      </button>
                      <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                      <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                      <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
                      </p>
                    <?php } else { ?>
                      <p class="out-of-stock">Indisponível</p>
                    <?php } ?>
                  </form>
                </div>
                <?php $gridCount += 1; ?>
              <?php } else { ?>
                <div class="product col-sm <?php if ($product['estoque'] > 0) { echo "border"; } ?> rounded">
                  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <p class="h6"><?php echo $product['descricao']; ?></p>
                    <p>R$ <?php echo $product['valor']; ?></p>
                    <?php if ($product['estoque'] > 0) { ?>
                      <button type="submit" name="add-to-cart" class="btn btn-info" 
                        <?php
                        if (!empty($_SESSION['cart'])) {
                          $empty = 0;
                          foreach ($_SESSION['cart'] as $cartItem) {
                            if ($cartItem['id'] === $product['idproduto']) {
                              $empty = 1;
                            }
                          }

                          if ($empty === 1) {
                            echo "disabled";
                          }
                        }

                        ?>
                      >
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "Adicionado";
                        } else {
                          echo "Adicionar";
                        }
                      } else {
                        echo "Adicionar";
                      }
                      ?>
                      </button>
                      <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                      <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                      <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
                      </p>
                    <?php } else { ?>
                      <p class="out-of-stock">Indisponível</p>
                    <?php } ?>
                  </form>
                </div>
              </div>
              <?php $gridCount = 0; ?>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      </section>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['final']) || isset($_GET['final-name']) || isset($_GET['final-letter'])) { /* Finalize order page */ ?>
  <?php if (isset($_GET['final-name']) || isset($_GET['final-letter'])) {  /* Choose client (list) */ ?>
    <?php
    include 'config/connection.php';

    $cases = [$_GET['final-name'] ?? "", $_GET['final-letter'] ?? ""];
  
    switch ($cases) {
        // search by name
      case ($cases[0] !== "" && $cases[1] === ""):
        $name = $_GET['final-name'];
        $sql = "SELECT * FROM clientes JOIN pessoas ON clientes.fk_idpessoa = pessoas.idpessoa WHERE nome LIKE '%$name%'";
        break;
        // search by letter
      case ($cases[0] === "" && $cases[1] !== ""):
        $letter = $_GET['final-letter'];
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
      <?php include 'templates/head.php';?>
      <link rel="stylesheet" href="styles/pages/produtos.css">
    </head>
    <body id="final-list">
      <?php include 'templates/navbar.php'; ?>
      <?php include 'templates/topbar.php'; ?>
      <main>
        <?php if ($searchResults == 0) { ?>
          <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">Nome</th>
                <th scope="col">CPF</th>
                <th scope="col">Renda</th>
                <th scope="col">Crédito</th>
                <th scope="col">Opções</th>
              </tr>
            </thead>
          </table>
          <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
          <a href="produtos.php?final" class="btn btn-secondary mt-3">Voltar</a>
        <?php } else { ?>
          <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">Nome</th>
                <th scope="col">CPF</th>
                <th scope="col">Renda</th>
                <th scope="col">Crédito</th>
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
                  <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                      <?php
                      /* GET VALUES TO SUBMIT ORDER */
                      // order value
                      $orderValue = 0;
                      foreach ($_SESSION['cart'] as $cartItem) {
                        $orderValue += $cartItem['total'];
                      }

                      // client id
                      $clientId = $client['idcliente'];
                      ?>
                      <input type="hidden" name="order-value" value="<?php echo $orderValue; ?>">
                      <input type="hidden" name="client-id" value="<?php echo $clientId; ?>">
                      <button type="submit" name="submit-order" class="btn btn-outline-info">Selecionar</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } ?>
      </main>
    </body>
    </html>
  <?php } else { /* Choose client (Search) */ ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
      <?php include 'templates/head.php';?>
      <link rel="stylesheet" href="styles/pages/produtos.css">
    </head>
    <body id="final-search">
      <?php include 'templates/navbar.php'; ?>
      <?php include 'templates/topbar.php'; ?>
      <main>
        <h1>Insira os dados do cliente</h1>
        <section class="search">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="input-group">
            <input type="text" name="final-name" id="product-name" class="form-control" placeholder="Deixe em branco para pesquisar todos os clientes">
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
            include 'config/connection.php';

            $letrasExistentes = mysqli_query($conn, "SELECT DISTINCT LEFT(nome, 1) AS letra FROM pessoas JOIN clientes ON pessoas.idpessoa = clientes.fk_idpessoa ORDER BY letra");
            $iniciais = mysqli_fetch_all($letrasExistentes, MYSQLI_ASSOC);

            mysqli_close($conn);
            foreach ($alfabeto as $letra) {
              $existeLetra = 0;
              foreach ($iniciais as $inicial) {
                if ($letra == $inicial['letra']) {
                  $existeLetra = 1;
                }
              }
              
              if ($existeLetra == 0) {
                echo "<button type='button' class='btn btn-link text-secondary'>$letra</button>";
              } else if ($existeLetra == 1) {
                echo "<button type='submit' name='final-letter' value='$letra' class='btn btn-link'>$letra</button>";
              }
              
            }
            ?>
          </div>
        </form>
      </section>
      </main>
    </body>
    </html>
  <?php } ?>
<?php } else if (isset($_GET['edit-product'])) { /* Edit product page */ ?>
  <?php
  $idEdit = $_GET['id'];

  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT * FROM produtos WHERE idproduto = '$idEdit'");
  $product = mysqli_fetch_assoc($res);
  
  mysqli_free_result($res);
  mysqli_close($conn);
  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/produtos.css">
  </head>

  <body id="edit-page">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="product bg-light border rounded">
        <svg viewBox="0 0 16 16" class="bi bi-pencil-fill" fill="#4B5C6B" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
        </svg>
        <h2>Editar dados do produto</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
          <label for="descricao">Descrição</label>
          <input type="text" name="descricao" id="descricao" class="form-control" placeholder="Descrição" value="<?php echo $product['descricao']; ?>" required>
          <label for="estoque">Estoque</label>
          <input type="number" name="estoque" id="estoque" class="form-control" placeholder="Estoque" value="<?php echo $product['estoque']; ?>" min="0" required>
          <label for="valor">Valor</label>
          <input type="number" name="valor" id="valor" class="form-control" placeholder="Valor" value="<?php echo $product['valor']; ?>" step="any" required>
          <div class="radio-form">
            <p>Status</p>
            <div class="option">
              <input type="radio" name="status" id="ativo" value="A" <?php if ($product['status'] == "A") { echo "checked"; } ?>>
              <label for="ativo">Ativo</label>
            </div>
            <div class="option">
              <input type="radio" name="status" id="inativo" value="I" <?php if ($product['status'] == "I") { echo "checked"; } ?>>
              <label for="inativo">Inativo</label>
            </div>
          </div>
          <input type="hidden" name="id-editar" value="<?php echo $product['idproduto']; ?>">
          <div class="buttons">
            <a href="produtos.php?delete-product&id=<?php echo $product['idproduto']; ?>" class="btn btn-danger">Excluir</a>
            <button type="submit" name="submit-edit-product" class="btn btn-warning">Editar</button>
          </div>
        </form>
      </section>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['add-product'])) { /* Add product page */ ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/produtos.css">
  </head>

  <body id="add-product">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="product bg-light border rounded">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="cart-plus" class="svg-inline--fa fa-cart-plus fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
          <path fill="#4B5C6B" d="M504.717 320H211.572l6.545 32h268.418c15.401 0 26.816 14.301 23.403 29.319l-5.517 24.276C523.112 414.668 536 433.828 536 456c0 31.202-25.519 56.444-56.824 55.994-29.823-.429-54.35-24.631-55.155-54.447-.44-16.287 6.085-31.049 16.803-41.548H231.176C241.553 426.165 248 440.326 248 456c0 31.813-26.528 57.431-58.67 55.938-28.54-1.325-51.751-24.385-53.251-52.917-1.158-22.034 10.436-41.455 28.051-51.586L93.883 64H24C10.745 64 0 53.255 0 40V24C0 10.745 10.745 0 24 0h102.529c11.401 0 21.228 8.021 23.513 19.19L159.208 64H551.99c15.401 0 26.816 14.301 23.403 29.319l-47.273 208C525.637 312.246 515.923 320 504.717 320zM408 168h-48v-40c0-8.837-7.163-16-16-16h-16c-8.837 0-16 7.163-16 16v40h-48c-8.837 0-16 7.163-16 16v16c0 8.837 7.163 16 16 16h48v40c0 8.837 7.163 16 16 16h16c8.837 0 16-7.163 16-16v-40h48c8.837 0 16-7.163 16-16v-16c0-8.837-7.163-16-16-16z"></path>
        </svg>
        <h2>Adicionar um produto</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
          <label for="descricao">Descrição</label>
          <input type="text" name="descricao" id="descricao" class="form-control" placeholder="Descrição" required>
          <label for="estoque">Estoque</label>
          <input type="number" name="estoque" id="estoque" class="form-control" placeholder="Estoque" min="0" required>
          <label for="valor">Valor</label>
          <input type="number" name="valor" id="valor" class="form-control" placeholder="Valor" step="any" required>
          <div class="radio-form">
            <p>Status</p>
            <div class="option">
              <input type="radio" name="status" id="ativo" value="A">
              <label for="ativo">Ativo</label>
            </div>
            <div class="option">
              <input type="radio" name="status" id="inativo" value="I">
              <label for="inativo">Inativo</label>
            </div>
          </div>
          <input type="hidden" name="id-add">
          <button type="submit" name="submit-add-product" class="btn btn-success">Adicionar</button>
        </form>
      </section>
    </main>
  </body>

  </html>
<?php } else if (isset($_GET['add-discount'])) { /* Add discount page */ ?>
  <?php
  /* DATABASE SEARCH */
  include 'config/connection.php';

  $cases = [$_POST['name'] ?? "", $_POST['letter'] ?? ""];

  switch ($cases) {
    // search by name
    case ($cases[0] !== "" && $cases[1] === ""):
      $name = $_POST['name'];
      $sql = "SELECT * FROM produtos WHERE descricao LIKE '%$name%'";
    break;
    // search by letter
    case ($cases[0] === "" && $cases[1] !== ""):
      $letter = $_POST['letter'];
      $sql = "SELECT * FROM produtos WHERE descricao LIKE '$letter%'";
    break;
    // empty search
    case ($cases[0] === "" && $cases[1] === ""):
      $sql = "SELECT * FROM produtos ORDER BY descricao";
    break;
    // default
    default:
      $sql = "SELECT * FROM produtos ORDER BY descricao";
    break;
  }

  // Get data from database

  $res = mysqli_query($conn, $sql);

  if (mysqli_num_rows($res) > 0) {
    $searchResults = 1;
    $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

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
    <link rel="stylesheet" href="styles/pages/produtos.css">
  </head>

  <body id="add-discount">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="search">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?add-discount" method="POST">
          <div class="input-group">
            <input type="text" name="name" id="product-name" class="form-control" placeholder="Pesquisar">
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
            include 'config/connection.php';

            $letrasExistentes = mysqli_query($conn, "SELECT DISTINCT LEFT(descricao, 1) AS letra FROM produtos ORDER BY letra");
            $iniciais = mysqli_fetch_all($letrasExistentes, MYSQLI_ASSOC);

            mysqli_close($conn);
            foreach ($alfabeto as $letra) {
              $existeLetra = 0;
              foreach ($iniciais as $inicial) {
                if ($letra == $inicial['letra']) {
                  $existeLetra = 1;
                }
              }
              
              if ($existeLetra == 0) {
                echo "<button type='button' class='btn btn-link text-secondary'>$letra</button>";
              } else if ($existeLetra == 1) {
                echo "<button type='submit' name='letter' value='$letra' class='btn btn-link'>$letra</button>";
              }
              
            }
            ?>
          </div>
        </form>
      </section>
      <section class="list">
        <?php if ($searchResults == 0) { ?>
          <table class="table table-hover border text-center">
            <thead>
              <tr>
                <th scope="col">Nome</th>
                <th scope="col">Estoque</th>
                <th scope="col">Valor</th>
                <th scope="col">Desconto</th>
                <th scope="col">Opções</th>
              </tr>
            </thead>
          </table>
          <p class="text-center h5 mt-4">Não foram encontrados resultados para sua busca ＞﹏＜</p>
          <a href="produtos.php" class="btn btn-secondary mt-3">Voltar</a>
          <?php } else { ?>
            <table class="table table-hover border text-center">
              <thead>
                <tr>
                  <th scope="col">Nome</th>
                  <th scope="col">Estoque</th>
                  <th scope="col">Valor</th>
                  <th scope="col">Desconto</th>
                  <th scope="col">Opções</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($products as $product) { ?>
                  <tr>
                    <td><?php echo $product['descricao']; ?></td>
                    <td><?php echo $product['estoque']; ?></td>
                    <td><?php echo $product['valor']; ?></td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                      <input type="hidden" name="id" value="<?php echo $product['idproduto']; ?>">
                      <td><input type="number" name="discount" class="form-control" value="<?php echo $product['desconto']; ?>" min="1" max="100" placeholder="De 0 a 100" required></td>
                      <td><button type="submit" name="submit-add-discount" class="btn btn-outline-info">Aplicar</button></td>
                    </form>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } ?>
      </section>           
    </main>
  </body>

  </html>
<?php } else { /* Index page */ ?>
  <?php

  /* ADD PRODUCTS TO CART */
  if (isset($_POST['add-to-cart'])) {
    $id = $_POST['id-produto'];
    $price = $_POST['value'];

    // Check if product has discount
    include 'config/connection.php';

    $res = mysqli_query($conn, "SELECT desconto FROM produtos WHERE idproduto = '$id'");
    $checkDisc = mysqli_fetch_assoc($res);

    if ($checkDisc['desconto'] !== 0) {
      $discount = $checkDisc['desconto'];
      $value = $price - ($discount / 100) * $price;
    } else {
      $value = $price;
    }

    mysqli_close($conn);

    if (empty($_SESSION['cart'])) {
      $_SESSION['cart'][] = ['id' => $id, 'value' => $value, 'quant' => 1, 'total' => $value];
    } else {
      // check if product is already in the cart
      $exist = 0;
      foreach ($_SESSION['cart'] as $cartItem) {
        if ($cartItem['id'] === $id) {
          $exist = 1;
        }
      }

      if ($exist === 0) {
        $_SESSION['cart'][] = ['id' => $id, 'value' => $value, 'quant' => 1, 'total' => $value];
      }
    }
  }

  /* CLEAR CART */
  if (isset($_GET['clear-cart'])) {
    $_SESSION['cart'] = [];
  }

  /* GET PRODUCTS IN DATABASE */
  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT * FROM produtos");
  $products = mysqli_fetch_all($res, MYSQLI_ASSOC);

  mysqli_free_result($res);
  mysqli_close($conn);


  ?>
  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/produtos.css">
  </head>

  <body>
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="search">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="input-group">
            <input type="text" name="name" id="product-name" class="form-control" placeholder="Pesquisar">
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
            include 'config/connection.php';

            $letrasExistentes = mysqli_query($conn, "SELECT DISTINCT LEFT(descricao, 1) AS letra FROM produtos ORDER BY letra");
            $iniciais = mysqli_fetch_all($letrasExistentes, MYSQLI_ASSOC);

            mysqli_close($conn);
            foreach ($alfabeto as $letra) {
              $existeLetra = 0;
              foreach ($iniciais as $inicial) {
                if ($letra == $inicial['letra']) {
                  $existeLetra = 1;
                }
              }
              
              if ($existeLetra == 0) {
                echo "<button type='button' class='btn btn-link text-secondary'>$letra</button>";
              } else if ($existeLetra == 1) {
                echo "<button type='submit' name='letter' value='$letra' class='btn btn-link'>$letra</button>";
              }
              
            }
            ?>
          </div>
        </form>
      </section>
      <section class="products">
        <?php if ($_SESSION['priority'] > 0) { /* SELLER OR ADMIN */ ?>
          <a href="<?php echo $_SERVER['PHP_SELF'] ?>?clear-cart" class="clear-cart btn btn-info">
            <svg width="20px" height="20px" viewBox="0 0 16 16" class="bi bi-x-square-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
            </svg>
          Limpar carrinho</a>
          <a href="<?php echo $_SERVER['PHP_SELF']; ?>?cart" class="open-cart btn btn-info">
            <svg width="25px" height="25px" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="shopping-cart" class="svg-inline--fa fa-shopping-cart fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
              <path fill="currentColor" d="M528.12 301.319l47.273-208C578.806 78.301 567.391 64 551.99 64H159.208l-9.166-44.81C147.758 8.021 137.93 0 126.529 0H24C10.745 0 0 10.745 0 24v16c0 13.255 10.745 24 24 24h69.883l70.248 343.435C147.325 417.1 136 435.222 136 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-15.674-6.447-29.835-16.824-40h209.647C430.447 426.165 424 440.326 424 456c0 30.928 25.072 56 56 56s56-25.072 56-56c0-22.172-12.888-41.332-31.579-50.405l5.517-24.276c3.413-15.018-8.002-29.319-23.403-29.319H218.117l-6.545-32h293.145c11.206 0 20.92-7.754 23.403-18.681z"></path>
            </svg>
          Ver carrinho</a>
        <?php } else { /* CLIENT */ ?>
          <a href="<?php echo $_SERVER['PHP_SELF'] ?>?clear-cart" class="clear-cart btn btn-info">
            <svg width="20px" height="20px" viewBox="0 0 16 16" class="bi bi-x-square-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
            </svg>
          Limpar carrinho</a>
          <a href="<?php echo $_SERVER['PHP_SELF']; ?>?cart" class="open-cart btn btn-info">
            <svg width="25px" height="25px" viewBox="0 0 16 16" class="bi bi-calculator-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm2 .5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zM4.5 9a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zM4 12.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zM7.5 6a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zM7 9.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm.5 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zM10 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm.5 2.5a.5.5 0 0 0-.5.5v4a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 0-.5-.5h-1z"/>
            </svg>
            Calcular preços
          </a>
        <?php } ?>
        <?php
        $gridCount = 0;
        foreach ($products as $product) { ?>
          <?php if ($gridCount == 0) { ?>
            <div class="row">
            <?php } ?>
            <?php if ($gridCount < 4) { ?>
              <div class="product col-sm <?php if ($product['estoque'] > 0 && $product['status'] == "A") { echo "border"; } ?> rounded">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                  <?php if ($_SESSION['priority'] >= 2) { ?>
                  <a href="produtos.php?edit-product&id=<?php echo $product['idproduto']; ?>" class="edit-product">
                    <svg width="20px" height="20px" viewBox="0 0 16 16" class="bi bi-pencil-square" fill="#72B7C1" xmlns="http://www.w3.org/2000/svg">
                      <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                      <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                    </svg>
                  </a>
                  <?php } ?>
                  <p class="h6"><?php echo $product['descricao']; ?></p>
                  <?php if ($product['desconto'] == 0) { ?>
                    <p>R$ <?php echo $product['valor']; ?></p>
                  <?php } else { ?>
                    <p class="old-price"><span class="strike">R$ <?php echo $product['valor']; ?></span></p>
                    <?php
                    // Calculate discounted price
                    $newPrice = $product['valor'] - ($product['desconto'] / 100) * $product['valor'];
                    ?>
                    <p><span class="deal">R$ <?php echo number_format((float)$newPrice, 2, '.', ''); ?></span></p>
                  <?php } ?>
                  
                  <?php if ($product['estoque'] > 0 && $product['status'] == "A") { ?>
                    <button type="submit" name="add-to-cart" class="btn btn-info" 
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "disabled";
                        }
                      }

                      ?>
                    >
                    <?php
                    if (!empty($_SESSION['cart'])) {
                      $empty = 0;
                      foreach ($_SESSION['cart'] as $cartItem) {
                        if ($cartItem['id'] === $product['idproduto']) {
                          $empty = 1;
                        }
                      }

                      if ($empty === 1) {
                        echo "Adicionado";
                      } else {
                        echo "Adicionar";
                      }
                    } else {
                      echo "Adicionar";
                    }
                    ?>
                    </button>
                    <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                    <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                    <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
                    </p>
                  <?php } else { ?>
                    <p class="out-of-stock">Indisponível</p>
                  <?php } ?>
                </form>
              </div>
              <?php $gridCount += 1; ?>
            <?php } else { ?>
              <div class="product col-sm <?php if ($product['estoque'] > 0 && $product['status'] == "A") { echo "border"; } ?> rounded">
              <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <?php if ($_SESSION['priority'] >= 2) { ?>
                  <a href="produtos.php?edit-product&id=<?php echo $product['idproduto']; ?>" class="edit-product">
                    <svg width="20px" height="20px" viewBox="0 0 16 16" class="bi bi-pencil-square" fill="#72B7C1" xmlns="http://www.w3.org/2000/svg">
                      <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                      <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                    </svg>
                  </a>
                  <?php } ?>
                  <p class="h6"><?php echo $product['descricao']; ?></p>
                  <p>R$ <?php echo $product['valor']; ?></p>
                  <?php if ($product['estoque'] > 0 && $product['status'] == "A") { ?>
                    <button type="submit" name="add-to-cart" class="btn btn-info" 
                      <?php
                      if (!empty($_SESSION['cart'])) {
                        $empty = 0;
                        foreach ($_SESSION['cart'] as $cartItem) {
                          if ($cartItem['id'] === $product['idproduto']) {
                            $empty = 1;
                          }
                        }

                        if ($empty === 1) {
                          echo "disabled";
                        }
                      }

                      ?>
                    >
                    <?php
                    if (!empty($_SESSION['cart'])) {
                      $empty = 0;
                      foreach ($_SESSION['cart'] as $cartItem) {
                        if ($cartItem['id'] === $product['idproduto']) {
                          $empty = 1;
                        }
                      }

                      if ($empty === 1) {
                        echo "Adicionado";
                      } else {
                        echo "Adicionar";
                      }
                    } else {
                      echo "Adicionar";
                    }
                    ?>
                    </button>
                    <input type="hidden" name="id-produto" value="<?php echo $product['idproduto']; ?>">
                    <input type="hidden" name="value" value="<?php echo $product['valor']; ?>">
                    <p class="in-stock">Disponível <?php echo $product['estoque'] ?>
                    </p>
                  <?php } else { ?>
                    <p class="out-of-stock">Indisponível</p>
                  <?php } ?>
                </form>
              </div>
            </div>
            <?php $gridCount = 0; ?>
          <?php } ?>
        <?php } ?>
      </section>
    </main>
  </body>

  </html>
<?php } ?>