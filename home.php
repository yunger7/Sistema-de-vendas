<?php
session_start();

if ($_SESSION['status'] !== "logged") {
  header('location: index.php');
}

/* SEND PAGES */
if (isset($_GET['show-data'])) { /* Graph page */ ?>
  <!DOCTYPE html>
  <html lang="pt-br">
  <head>
    <?php include 'templates/head.php'; ?>
    <link rel="stylesheet" href="styles/pages/home.css">
  </head>
  <body id="graph">
    <?php include 'templates/navbar.php'; ?>
    <?php include 'templates/topbar.php'; ?>
    <main>
      <section class="users">
        <?php
        /* CREATE GRAPHIC */
        require_once 'phplot/phplot.php';

        $graphic = new PHPlot(400, 600);
        $graphic->SetFailureImage(False);
        $graphic->SetPrintImage(False);

        $graphic->SetPlotType('bars');

        $graphic->SetTitle("Pessoas cadastradas");
        $graphic->SetXTitle("Tipo");
        $graphic->SetYTitle("Quantidade");

        // Get data from database
        include 'config/connection.php';

        // Clients
        $res = mysqli_query($conn, "SELECT COUNT(idcliente) AS quant_clientes FROM clientes");
        $quant_clientes = mysqli_fetch_assoc($res);

        // Sellers
        $res = mysqli_query($conn, "SELECT COUNT(idvendedor) AS quant_vendedores FROM vendedores");
        $quant_vendedores = mysqli_fetch_assoc($res);

        mysqli_close($conn);

        // Insert data into array
        $data = [
          ['Clientes', $quant_clientes['quant_clientes']],
          ['Vendedores', $quant_vendedores['quant_vendedores']]
        ];

        $graphic->SetDataValues($data);
        $graphic->DrawGraph();
        ?>
        <img id="graphic" src="<?php echo $graphic->EncodeImage(); ?>" alt="Gráfico">
      </section>
      <section class="products">
        <?php
        /* CREATE GRAPHIC */
        require_once 'phplot/phplot.php';

        $graphic = new PHPlot(200, 570);
        $graphic->SetFailureImage(False);
        $graphic->SetPrintImage(False);

        $graphic->SetPlotType('bars');

        $graphic->SetTitle("Produtos cadastrados");
        $graphic->SetYTitle("Quantidade");

        // Get data from database
        include 'config/connection.php';

        // Products
        $res = mysqli_query($conn, "SELECT COUNT(idproduto) AS quant_produtos FROM produtos");
        $quant_produtos = mysqli_fetch_assoc($res);

        mysqli_close($conn);

        // Insert data into array
        $data = [
          ['Produtos', $quant_produtos['quant_produtos']]
        ];

        $graphic->SetDataValues($data);
        $graphic->DrawGraph();
        ?>
        <img id="graphic" src="<?php echo $graphic->EncodeImage(); ?>" alt="Gráfico">
      </section>
      <section class="sold-products">
        <?php
        /* CREATE GRAPHIC */
        require_once 'phplot/phplot.php';

        $graphic = new PHPlot(200, 570);
        $graphic->SetFailureImage(False);
        $graphic->SetPrintImage(False);

        $graphic->SetPlotType('bars');

        $graphic->SetTitle("Produtos vendidos");
        $graphic->SetYTitle("Quantidade");

        // Get data from database
        include 'config/connection.php';

        // Products
        $res = mysqli_query($conn, "SELECT COUNT(fk_idpedido) AS quant_produtos_vendidos FROM itens_pedidos");
        $quant_produtos_vendidos = mysqli_fetch_assoc($res);

        mysqli_close($conn);

        // Insert data into array
        $data = [
          ['Produtos', $quant_produtos_vendidos['quant_produtos_vendidos']]
        ];

        $graphic->SetDataValues($data);
        $graphic->DrawGraph();
        ?>
        <img id="graphic" src="<?php echo $graphic->EncodeImage(); ?>" alt="Gráfico">
      </section>
      <section class="orders-date">
        <?php
          /* CREATE GRAPHIC */
          require_once 'phplot/phplot.php';

          $graphic = new PHPlot(700, 600);
          $graphic->SetFailureImage(False);
          $graphic->SetPrintImage(False);

          $graphic->SetPlotType('lines');

          $currentYear = date("Y");
          $graphic->SetTitle("Pedidos realizados ($currentYear)");
          $graphic->SetXTitle("Mes");
          $graphic->SetYTitle("Quantidade");
          $graphic->SetLineWidths(3);

          // Get data from database
          include 'config/connection.php';

          // Jan
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_jan FROM pedidos WHERE MONTH(data) = 1 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_jan = mysqli_fetch_assoc($res);

          // Fev
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_fev FROM pedidos WHERE MONTH(data) = 2 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_fev = mysqli_fetch_assoc($res);

          // Mar
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_mar FROM pedidos WHERE MONTH(data) = 3 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_mar = mysqli_fetch_assoc($res);

          // Abr
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_abr FROM pedidos WHERE MONTH(data) = 4 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_abr = mysqli_fetch_assoc($res);

          // Mai
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_mai FROM pedidos WHERE MONTH(data) = 5 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_mai = mysqli_fetch_assoc($res);

          // Jun
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_jun FROM pedidos WHERE MONTH(data) = 6 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_jun = mysqli_fetch_assoc($res);

          // Jul
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_jul FROM pedidos WHERE MONTH(data) = 7 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_jul = mysqli_fetch_assoc($res);

          // Ago
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_ago FROM pedidos WHERE MONTH(data) = 8 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_ago = mysqli_fetch_assoc($res);

          // Set
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_set FROM pedidos WHERE MONTH(data) = 9 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_set = mysqli_fetch_assoc($res);

          // Out
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_out FROM pedidos WHERE MONTH(data) = 10 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_out = mysqli_fetch_assoc($res);

          // Nov
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_nov FROM pedidos WHERE MONTH(data) = 11 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_nov = mysqli_fetch_assoc($res);

          // Dez
          $res = mysqli_query($conn, "SELECT COUNT(idpedido) AS quant_pedidos_dez FROM pedidos WHERE MONTH(data) = 12 AND YEAR(data) = YEAR(CURRENT_DATE())");
          $quant_pedidos_dez = mysqli_fetch_assoc($res);

          mysqli_close($conn);

          // Insert data into array
          $data = [
            ['Janeiro', $quant_pedidos_jan['quant_pedidos_jan']],
            ['Fevereiro', $quant_pedidos_fev['quant_pedidos_fev']],
            ['Marco', $quant_pedidos_mar['quant_pedidos_mar']],
            ['Abril', $quant_pedidos_abr['quant_pedidos_abr']],
            ['Maio', $quant_pedidos_mai['quant_pedidos_mai']],
            ['Junho', $quant_pedidos_jun['quant_pedidos_jun']],
            ['Julho', $quant_pedidos_jul['quant_pedidos_jul']],
            ['Agosto', $quant_pedidos_ago['quant_pedidos_ago']],
            ['Setembro', $quant_pedidos_set['quant_pedidos_set']],
            ['Outubro', $quant_pedidos_out['quant_pedidos_out']],
            ['Novembro', $quant_pedidos_nov['quant_pedidos_nov']],
            ['Dezembro', $quant_pedidos_dez['quant_pedidos_dez']],
          ];

          $graphic->SetDataValues($data);
          $graphic->DrawGraph();
          ?>
          <img id="graphic" src="<?php echo $graphic->EncodeImage(); ?>" alt="Gráfico">
      </section>
    </main>
  </body>
  </html>
<?php } else { /* Index page */ ?>
  <?php
  /* FETCH DEALS */
  include 'config/connection.php';

  $res = mysqli_query($conn, "SELECT * FROM produtos WHERE desconto != 0 ORDER BY desconto DESC LIMIT 15");

  if (mysqli_num_rows($res) == 0) {
    $res = mysqli_query($conn, "SELECT * FROM produtos ORDER BY valor LIMIT 15");
    $deals = mysqli_fetch_all($res, MYSQLI_ASSOC);

    mysqli_free_result($res);
  } else if (mysqli_num_rows($res) < 15) {
    $totalFetched = mysqli_num_rows($res);
    $totalToFetch = 15 - $totalFetched;
    $deals = mysqli_fetch_all($res, MYSQLI_ASSOC);

    $res = mysqli_query($conn, "SELECT * FROM produtos WHERE desconto = 0 ORDER BY valor LIMIT $totalToFetch" );
    $missingDeals=mysqli_fetch_all($res, MYSQLI_ASSOC);

    foreach($missingDeals as $missingDeal) {
      array_push($deals, $missingDeal);
    } 
    
  } else {
    $deals=mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_free_result($res);
  } 
    
    mysqli_close($conn);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
      <?php include 'templates/head.php'; ?>
      <link rel="stylesheet" href="styles/pages/home.css">
    </head>

    <body>
      <?php include 'templates/navbar.php'; ?>
      <?php include 'templates/topbar.php'; ?>
      <main>
        <h1>Bem-vindo <?php echo $_SESSION['user']; ?>!</h1>
        <section class="deals">
          <h2>Confira as melhores ofertas</h2>
          <?php
          $girdCount = 0;

          foreach ($deals as $deal) {
            if ($girdCount == 0) {
              echo "<div class='row'>";
            }

            if ($girdCount < 4) {
              echo "<div class='product col-sm border rounded'>";
              ?>
              <p class="h6"><?php echo $deal['descricao']; ?></p>
              <?php if ($deal['desconto'] == 0) { ?>
                <p>R$ <?php echo $deal['valor']; ?></p>
              <?php } else { ?>
                <p class="old-price"><span class="strike">R$ <?php echo $deal['valor']; ?></span></p>
                <?php
                // Calculate discounted price
                $newPrice = $deal['valor'] - ($deal['desconto'] / 100) * $deal['valor'];
                ?>
                <p><span class="deal">R$ <?php echo number_format((float)$newPrice, 2, '.', ''); ?></span></p>
              <?php } ?>
              <?php if ($deal['estoque'] == 0) { ?>
                <p class="out-of-stock">Indisponível</p>
              <?php } else { ?>
                <p class="in-stock">Disponível: <?php echo $deal['estoque']; ?></p>
              <?php } ?>
              <?php
              echo "</div>";
              $girdCount += 1;
            } else {
              echo "<div class='product col-sm border rounded'>";
              ?>
              <p class="h6"><?php echo $deal['descricao']; ?></p>
              <?php if ($deal['desconto'] == 0) { ?>
                <p>R$ <?php echo $deal['valor']; ?></p>
              <?php } else { ?>
                <p class="old-price"><span class="strike">R$ <?php echo $deal['valor']; ?></span></p>
                <?php
                // Calculate discounted price
                $newPrice = $deal['valor'] - ($deal['desconto'] / 100) * $deal['valor'];
                ?>
                <p><span class="deal">R$ <?php echo number_format((float)$newPrice, 2, '.', ''); ?></span></p>
              <?php } ?>
              <?php if ($deal['estoque'] == 0) { ?>
                <p class="out-of-stock">Indisponível</p>
              <?php } else { ?>
                <p class="in-stock">Disponível: <?php echo $deal['estoque']; ?></p>
              <?php } ?>
            <?php
              echo "</div>";
              echo "</div>";
              $girdCount = 0;
            }
          }
          ?>
        </section>
      </main>
    </body>

    </html>
  <?php } ?>