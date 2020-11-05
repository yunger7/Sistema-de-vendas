-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05-Nov-2020 às 16:17
-- Versão do servidor: 10.4.14-MariaDB
-- versão do PHP: 7.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistemavendas`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes`
--

CREATE TABLE `clientes` (
  `idcliente` int(11) NOT NULL,
  `renda` decimal(10,2) DEFAULT NULL,
  `credito` decimal(10,2) NOT NULL,
  `fk_idpessoa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `clientes`
--

INSERT INTO `clientes` (`idcliente`, `renda`, `credito`, `fk_idpessoa`) VALUES
(10, '4000.00', '2000.00', 19),
(11, '6000.00', '3000.00', 20),
(12, '2000.00', '1000.00', 21),
(13, '10000.00', '4000.00', 23),
(15, '6000.00', '3000.00', 28),
(16, '8500.00', '4000.00', 29),
(17, '6000.00', '1000.00', 30),
(18, '9000.00', '8000.00', 31);

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_pedidos`
--

CREATE TABLE `itens_pedidos` (
  `fk_idpedido` int(11) NOT NULL,
  `fk_idproduto` int(11) NOT NULL,
  `qtd` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `itens_pedidos`
--

INSERT INTO `itens_pedidos` (`fk_idpedido`, `fk_idproduto`, `qtd`, `valor`) VALUES
(19, 4, 1, '4.00'),
(19, 7, 2, '16.00'),
(19, 11, 5, '27.50'),
(20, 1, 1, '7.00'),
(20, 3, 2, '16.00'),
(22, 1, 1, '7.00'),
(22, 3, 2, '16.00'),
(22, 7, 1, '8.00'),
(23, 2, 5, '5.00'),
(23, 4, 1, '4.00'),
(23, 7, 1, '8.00'),
(24, 2, 5, '5.00'),
(24, 4, 1, '4.00'),
(24, 7, 3, '24.00'),
(28, 2, 3, '4.50'),
(28, 3, 1, '6.40'),
(28, 7, 1, '8.00'),
(28, 12, 1, '4.99'),
(28, 15, 5, '2.50'),
(28, 22, 5, '29.95'),
(29, 1, 1, '6.50'),
(29, 2, 3, '4.50'),
(29, 3, 1, '6.40'),
(29, 7, 1, '8.00'),
(29, 11, 1, '5.50'),
(29, 12, 1, '4.99'),
(29, 15, 5, '2.50'),
(29, 22, 5, '29.95'),
(30, 3, 1, '6.40'),
(30, 7, 1, '8.00'),
(30, 12, 2, '9.98'),
(30, 16, 3, '5.67'),
(31, 1, 2, '13.00'),
(31, 2, 5, '7.50'),
(31, 7, 1, '8.00'),
(31, 25, 2, '7.98'),
(32, 24, 10, '39.90'),
(32, 25, 5, '19.95'),
(33, 6, 1, '100.00'),
(33, 15, 5, '2.50'),
(34, 2, 1, '1.50'),
(34, 7, 1, '8.00'),
(34, 19, 1, '7.20'),
(34, 20, 1, '4.99'),
(35, 2, 3, '4.50'),
(35, 11, 1, '5.50'),
(35, 17, 1, '4.80'),
(35, 22, 5, '29.95'),
(36, 1, 5, '32.50'),
(36, 3, 1, '6.40'),
(36, 4, 1, '2.00'),
(36, 12, 1, '4.99'),
(36, 24, 1, '3.99'),
(37, 16, 3, '5.67'),
(37, 18, 1, '5.50'),
(37, 22, 6, '35.94'),
(38, 1, 1, '6.50'),
(38, 7, 1, '8.00'),
(38, 18, 1, '5.50'),
(38, 22, 1, '5.99'),
(38, 25, 1, '3.99'),
(39, 2, 10, '15.00'),
(39, 17, 1, '4.80'),
(39, 18, 1, '5.50'),
(39, 19, 1, '7.20'),
(40, 15, 6, '3.00'),
(40, 19, 2, '14.40'),
(40, 25, 1, '3.99'),
(40, 26, 3, '12.60'),
(41, 11, 1, '5.50'),
(41, 15, 3, '1.50'),
(41, 16, 1, '1.89'),
(42, 2, 1, '1.50'),
(42, 7, 1, '8.00'),
(42, 12, 1, '4.99'),
(42, 20, 1, '4.99');

-- --------------------------------------------------------

--
-- Estrutura da tabela `lixeira`
--

CREATE TABLE `lixeira` (
  `id` int(11) NOT NULL,
  `idpessoa` int(11) DEFAULT NULL,
  `nome` varchar(60) DEFAULT NULL,
  `cpf` bigint(11) DEFAULT NULL,
  `status` enum('A','I') DEFAULT NULL,
  `senha` varchar(60) DEFAULT NULL,
  `idcliente` int(11) DEFAULT NULL,
  `renda` decimal(10,2) DEFAULT NULL,
  `credito` decimal(10,2) DEFAULT NULL,
  `idvendedor` int(11) DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `idproduto` int(11) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `estoque` int(11) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `idpedido` int(11) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `qtd` int(11) DEFAULT NULL,
  `data_exclusao` timestamp NOT NULL DEFAULT current_timestamp(),
  `idusuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `lixeira`
--

INSERT INTO `lixeira` (`id`, `idpessoa`, `nome`, `cpf`, `status`, `senha`, `idcliente`, `renda`, `credito`, `idvendedor`, `salario`, `idproduto`, `descricao`, `estoque`, `valor`, `idpedido`, `data`, `qtd`, `data_exclusao`, `idusuario`) VALUES
(25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, '5.00', 16, NULL, 5, '2020-10-28 12:22:06', 2),
(26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, '16.00', 16, NULL, 2, '2020-10-28 12:22:06', 2),
(28, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '14.00', 17, NULL, 2, '2020-10-29 16:44:43', 2),
(29, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, '20.00', 17, NULL, 5, '2020-10-29 16:44:43', 2),
(30, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, '8.00', 17, NULL, 1, '2020-10-29 16:44:43', 2),
(31, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, NULL, '12.00', 17, NULL, 3, '2020-10-29 16:44:43', 2),
(33, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, '7.50', 25, NULL, 5, '2020-10-29 20:09:57', 2),
(34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, '4.00', 25, NULL, 1, '2020-10-29 20:09:57', 2),
(35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, '16.00', 25, NULL, 2, '2020-10-29 20:09:57', 2),
(36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, NULL, '12.00', 25, NULL, 3, '2020-10-29 20:09:57', 2),
(37, NULL, NULL, NULL, 'A', NULL, NULL, NULL, NULL, NULL, NULL, 8, 'Leite', 100, '4.00', NULL, NULL, NULL, '2020-10-30 11:24:13', 2),
(38, NULL, NULL, NULL, 'A', NULL, NULL, NULL, NULL, NULL, NULL, 13, 'Tubaina', 200, '4.50', NULL, NULL, NULL, '2020-10-30 12:07:31', 2),
(42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, '4.50', 26, NULL, 3, '2020-10-30 20:42:56', 18),
(43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, '12.80', 26, NULL, 2, '2020-10-30 20:42:56', 18),
(44, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, '8.00', 26, NULL, 2, '2020-10-30 20:42:56', 18),
(45, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, '5.50', 26, NULL, 1, '2020-10-30 20:42:56', 18),
(47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, '1.00', 21, NULL, 1, '2020-10-30 21:50:18', 2),
(48, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, '20.00', 21, NULL, 5, '2020-10-30 21:50:18', 2),
(49, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, '16.00', 21, NULL, 2, '2020-10-30 21:50:18', 2),
(55, 25, 'Osvaldo comprador', 74859483098, 'A', '', 14, '4000.00', '3000.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-10-31 13:45:42', 2),
(57, NULL, NULL, NULL, 'A', NULL, 12, NULL, NULL, 3, NULL, NULL, NULL, NULL, '26.29', 27, '2020-10-31', NULL, '2020-10-31 13:58:24', 18),
(58, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, '4.50', 27, NULL, 3, '2020-10-31 13:58:24', 18),
(59, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, '4.00', 27, NULL, 1, '2020-10-31 13:58:24', 18),
(60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, NULL, NULL, '12.80', 27, NULL, 2, '2020-10-31 13:58:24', 18),
(61, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 12, NULL, NULL, '4.99', 27, NULL, 1, '2020-10-31 13:58:24', 18);

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `idpedido` int(11) NOT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor` decimal(10,2) NOT NULL,
  `status` enum('A','I') NOT NULL DEFAULT 'A',
  `fk_idvendedor` int(11) NOT NULL,
  `fk_idcliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `pedidos`
--

INSERT INTO `pedidos` (`idpedido`, `data`, `valor`, `status`, `fk_idvendedor`, `fk_idcliente`) VALUES
(19, '2020-10-24 20:48:48', '59.48', 'A', 4, 11),
(20, '2020-10-20 20:49:56', '23.00', 'A', 4, 12),
(21, '2020-10-24 03:00:00', '49.00', 'A', 4, 11),
(22, '2020-11-24 22:59:03', '40.60', 'A', 4, 11),
(23, '2020-10-25 13:05:15', '29.80', 'A', 4, 13),
(24, '2020-10-25 13:11:10', '37.00', 'A', 4, 13),
(26, '2020-10-26 03:00:00', '30.80', 'A', 3, 13),
(28, '2020-11-05 14:21:11', '56.34', 'A', 4, 10),
(29, '2020-11-05 14:26:14', '68.34', 'A', 4, 12),
(30, '2020-06-05 14:52:02', '30.05', 'A', 5, 16),
(31, '2020-09-05 14:52:36', '36.48', 'A', 5, 18),
(32, '2020-07-05 14:53:02', '59.85', 'A', 5, 17),
(33, '2020-07-05 14:54:15', '102.50', 'A', 5, 15),
(34, '2020-07-05 14:56:51', '21.69', 'A', 5, 13),
(35, '2020-07-05 15:03:45', '44.75', 'A', 6, 11),
(36, '2020-05-05 15:04:04', '49.88', 'A', 6, 11),
(37, '2020-04-05 15:04:24', '47.11', 'A', 6, 12),
(38, '2020-03-05 15:04:50', '29.98', 'A', 6, 15),
(39, '2020-03-05 15:06:11', '32.50', 'A', 8, 18),
(40, '2020-03-05 15:06:31', '33.99', 'A', 8, 11),
(41, '2020-01-05 15:06:59', '8.89', 'A', 8, 17),
(42, '2020-01-05 15:07:20', '19.48', 'A', 8, 10);

-- --------------------------------------------------------

--
-- Estrutura da tabela `pessoas`
--

CREATE TABLE `pessoas` (
  `idpessoa` int(11) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `cpf` bigint(11) UNSIGNED NOT NULL,
  `status` enum('A','I') NOT NULL DEFAULT 'A',
  `senha` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `pessoas`
--

INSERT INTO `pessoas` (`idpessoa`, `nome`, `cpf`, `status`, `senha`) VALUES
(2, 'Admin', 12345678901, 'A', 'YWRtaW4='),
(18, 'Chris Vendas Rápidas', 45987654688, 'A', 'Y2hyaXM='),
(19, 'Roberto dos Santos', 45876598305, 'I', 'cm9iZXJ0bw=='),
(20, 'Robson Miguel', 12345678903, 'A', 'Y2xpZW50ZQ=='),
(21, 'Nelson Mandela', 12345612302, 'A', 'bmVsc29u'),
(22, 'Otávio Mono Vendas', 10129122269, 'A', 'b3Rhdmlv'),
(23, 'Post Malone', 15468754231, 'A', 'cG9zdG1hbG9uZQ=='),
(24, 'Romário Vendas', 12345678902, 'A', 'dmVuZGVkb3I='),
(27, 'Vendedoctor', 15462598657, 'I', 'ZG9jdG9ybmFv'),
(28, 'Fred Mercury', 12563259851, 'A', 'ZnJlZA=='),
(29, 'Zé Carioca', 12385698461, 'A', 'emVjYXJpb2Nh'),
(30, 'Zeca Urubu', 99986532651, 'A', 'emVjYXVydWJ1'),
(31, 'Ayrton Senna', 88785643259, 'A', 'YXlydG9u');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `idproduto` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `estoque` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `desconto` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('A','I') NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`idproduto`, `descricao`, `estoque`, `valor`, `desconto`, `status`) VALUES
(1, 'Pão integral', 200, '6.50', 0, 'A'),
(2, 'Maçã verde', 80, '1.50', 0, 'A'),
(3, 'Arroz 5KG', 150, '8.00', 20, 'A'),
(4, 'Feijão', 50, '4.00', 50, 'A'),
(5, 'Tapioca', 0, '5.00', 0, 'I'),
(6, 'Banana', 1, '100.00', 0, 'A'),
(7, 'Café', 50, '8.00', 0, 'A'),
(10, 'Bolacha maisena', 0, '4.00', 0, 'I'),
(11, 'Coca-Cola (2L)', 200, '5.50', 0, 'A'),
(12, 'Pão bisnaguinha', 50, '4.99', 0, 'A'),
(15, 'Pão francês', 300, '0.50', 0, 'A'),
(16, 'Bolacha Negresco', 100, '1.89', 0, 'A'),
(17, 'Presunto fatiado', 50, '4.80', 0, 'A'),
(18, 'Queijo Mozarela', 50, '5.50', 0, 'A'),
(19, 'Papel higiênico', 300, '7.20', 0, 'A'),
(20, 'Cenoura', 100, '4.99', 0, 'A'),
(22, 'Tomate', 100, '5.99', 0, 'A'),
(23, 'Energético Monster', 0, '7.50', 0, 'I'),
(24, 'Mentos', 100, '3.99', 0, 'A'),
(25, 'Coca-Cola (500ml)', 400, '3.99', 0, 'A'),
(26, 'Leite Loga Vida', 200, '4.20', 0, 'A');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendedores`
--

CREATE TABLE `vendedores` (
  `idvendedor` int(11) NOT NULL,
  `salario` decimal(10,2) NOT NULL,
  `fk_idpessoa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `vendedores`
--

INSERT INTO `vendedores` (`idvendedor`, `salario`, `fk_idpessoa`) VALUES
(3, '2000.00', 18),
(4, '10000.00', 2),
(5, '10000.00', 22),
(6, '3000.00', 24),
(8, '522.50', 27);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcliente`,`fk_idpessoa`),
  ADD KEY `fk_idpessoa` (`fk_idpessoa`);

--
-- Índices para tabela `itens_pedidos`
--
ALTER TABLE `itens_pedidos`
  ADD PRIMARY KEY (`fk_idpedido`,`fk_idproduto`),
  ADD KEY `fk_idproduto` (`fk_idproduto`);

--
-- Índices para tabela `lixeira`
--
ALTER TABLE `lixeira`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`idpedido`,`fk_idvendedor`,`fk_idcliente`),
  ADD KEY `fk_idvendedor` (`fk_idvendedor`),
  ADD KEY `fk_idcliente` (`fk_idcliente`);

--
-- Índices para tabela `pessoas`
--
ALTER TABLE `pessoas`
  ADD PRIMARY KEY (`idpessoa`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`idproduto`);

--
-- Índices para tabela `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`idvendedor`,`fk_idpessoa`),
  ADD KEY `fk_idpessoa` (`fk_idpessoa`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `lixeira`
--
ALTER TABLE `lixeira`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `idpedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de tabela `pessoas`
--
ALTER TABLE `pessoas`
  MODIFY `idpessoa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `idproduto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `idvendedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`fk_idpessoa`) REFERENCES `pessoas` (`idpessoa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `itens_pedidos`
--
ALTER TABLE `itens_pedidos`
  ADD CONSTRAINT `itens_pedidos_ibfk_1` FOREIGN KEY (`fk_idpedido`) REFERENCES `pedidos` (`idpedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `itens_pedidos_ibfk_2` FOREIGN KEY (`fk_idproduto`) REFERENCES `produtos` (`idproduto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`fk_idvendedor`) REFERENCES `vendedores` (`idvendedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`fk_idcliente`) REFERENCES `clientes` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `vendedores`
--
ALTER TABLE `vendedores`
  ADD CONSTRAINT `vendedores_ibfk_1` FOREIGN KEY (`fk_idpessoa`) REFERENCES `pessoas` (`idpessoa`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
