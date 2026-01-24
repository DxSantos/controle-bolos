-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 22/01/2026 às 14:47
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `controle_bolos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `controle_entrada`
--

CREATE TABLE `controle_entrada` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `hora` time NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `controle_saida`
--

CREATE TABLE `controle_saida` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `inventario_log`
--

CREATE TABLE `inventario_log` (
  `id` int(11) NOT NULL,
  `codigo_inventario` varchar(50) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `saldo_anterior` decimal(10,2) DEFAULT 0.00,
  `saldo_inventario` decimal(10,2) DEFAULT 0.00,
  `data_inventario` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `inventario_log`
--

INSERT INTO `inventario_log` (`id`, `codigo_inventario`, `produto_id`, `saldo_anterior`, `saldo_inventario`, `data_inventario`) VALUES
(1420, 'INV-20260120-002016', 33, 0.00, 3.00, '2026-01-19 20:21:47'),
(1421, 'INV-20260120-002016', 21, 0.00, 8.00, '2026-01-19 20:21:47'),
(1422, 'INV-20260120-002016', 18, 0.00, 7.00, '2026-01-19 20:21:47'),
(1423, 'INV-20260120-002016', 15, 0.00, 7.00, '2026-01-19 20:21:47'),
(1424, 'INV-20260120-002016', 20, 0.00, 1.00, '2026-01-19 20:21:47'),
(1425, 'INV-20260120-002016', 19, 0.00, 11.00, '2026-01-19 20:21:47'),
(1426, 'INV-20260120-002016', 23, 0.00, 7.00, '2026-01-19 20:21:47'),
(1427, 'INV-20260120-002016', 24, 0.00, 4.00, '2026-01-19 20:21:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lojas`
--

CREATE TABLE `lojas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `lojas`
--

INSERT INTO `lojas` (`id`, `nome`, `ativo`, `criado_em`) VALUES
(1, 'LOJA GRAGERU', 1, '2025-10-31 23:36:24'),
(2, 'LOJA SOCORRO', 1, '2025-10-31 23:36:34'),
(3, 'LOJA ARUANA', 1, '2025-10-31 23:36:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfis`
--

CREATE TABLE `perfis` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `pode_cadastrar` tinyint(1) DEFAULT 0,
  `pode_editar` tinyint(1) DEFAULT 0,
  `pode_excluir` tinyint(1) DEFAULT 0,
  `pode_visualizar` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `perfis`
--

INSERT INTO `perfis` (`id`, `nome`, `descricao`, `pode_cadastrar`, `pode_editar`, `pode_excluir`, `pode_visualizar`) VALUES
(1, 'Administrador', 'Acesso total ao sistema', 1, 1, 1, 1),
(2, 'Gerente', 'Pode editar e visualizar dados', 1, 1, 0, 1),
(3, 'Operador', 'Pode cadastrar e visualizar dados', 1, 0, 0, 1),
(4, 'Visualizador', 'Somente leitura', 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `chave` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `permissoes`
--

INSERT INTO `permissoes` (`id`, `nome`, `chave`) VALUES
(1, 'Gerenciar cadastro de Tipos', 'tipos'),
(2, 'Gerenciar cadastro de Produtos', 'produtos'),
(3, 'Gerenciar cadastro de Lojas', 'lojas'),
(4, 'Gerenciar Inventário', 'inventario'),
(5, 'Visualizar Relatórios Saldos', 'saldos'),
(6, 'Controle de Saídas', 'saidas'),
(7, 'Controle de Entradas', 'entradas'),
(29, 'gerenciar permissões', 'permissoes'),
(30, 'gerenciar movimentações', 'movimentacao'),
(31, 'Visualizar Relatórios Dashboard', 'dashboard'),
(32, 'Visualizar Relatórios movimentacao', 'rmovimentacao'),
(33, 'Visualizar Relatórios Analitico', 'analitico'),
(34, 'controle guardar valores movim.', 'guardar_valores'),
(35, 'controle para salvar no banco movim.', 'salvar_banco');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `tipo`) VALUES
(3, 'MASSA G BRANCA', '7'),
(4, 'MASSA G PRETA', '7'),
(5, 'MASSA M BRANCA', '7'),
(6, 'MASSA M PRETA', '7'),
(7, 'MASSA 30X40 BRANCA', '7'),
(8, 'MASSA 30X40 PRETA', '7'),
(9, 'MASSA RETANGULAR PRETA', '7'),
(10, 'MASSA RETANGULAR BRANCA', '7'),
(11, 'MASSA MINI BRANCA', '7'),
(12, 'MASSA MINI PRETA', '7'),
(13, 'MASSA P BRANCA', '7'),
(14, 'MASSA P PRETA', '7'),
(15, 'BOLO DE CHOCOLATE', '6'),
(18, 'BOLO DE CENOURA', '6'),
(19, 'BOLO DE LIMÃO', '6'),
(20, 'BOLO DE LARANJA', '6'),
(21, 'BOLO DE BRIGADEIRO', '6'),
(22, 'BOLO MESCLADO', '6'),
(23, 'BOLO ROMEU', '6'),
(24, 'BOLO ROMEU COM CALDA', '6'),
(25, 'MASSA RECHEADA BRIGADEIRO MINI', '8'),
(26, 'MASSA RECHEADA BRIGADEIRO P', '8'),
(27, 'MASSA RECHEADA BRIGADEIRO M', '8'),
(28, 'MASSA RECHEADA BRIGADEIRO G', '8'),
(29, 'MASSA RECHEADA MORANGO MINI', '8'),
(30, 'MASSA RECHEADA MORANGO P', '8'),
(31, 'MASSA RECHEADA MORANGO M', '8'),
(32, 'MASSA RECHEADA MORANGO G', '8'),
(33, 'BOLO DE BANANA', '6'),
(34, 'BOLO DE OVOS', '6'),
(35, 'MASSA 30X40 PRETA METADE', '7'),
(36, 'MASSA RECHEADA ALPINO MINI', '8'),
(37, 'MASSA RECHEADA ALPINO P', '8'),
(38, 'MASSA RECHEADA ALPINO M', '8'),
(39, 'MASSA RECHEADA ALPINO G', '8'),
(40, 'MASSA RECHEADA AMEIXA MINI', '8'),
(41, 'MASSA RECHEADA AMEIXA P', '8'),
(42, 'MASSA RECHEADA AMEIXA M', '8'),
(43, 'MASSA RECHEADA AMEIXA G', '8'),
(44, 'MASSA RECHEADA CHARGE MINI', '8'),
(45, 'MASSA RECHEADA CHARGE P', '8'),
(46, 'MASSA RECHEADA CHARGE M', '8'),
(47, 'MASSA RECHEADA CHARGE G', '8'),
(48, 'MASSA RECHEADA CHOCOLATE C/ MORANGO MINI', '8'),
(49, 'MASSA RECHEADA CHOCOLATE C/ MORANGO P', '8'),
(50, 'MASSA RECHEADA CHOCOLATE C/ MORANGO M', '8'),
(51, 'MASSA RECHEADA CHOCOLATE C/ MORANGO G', '8'),
(52, 'MASSA RECHEADA FRUTAS MINI', '8'),
(53, 'MASSA RECHEADA FRUTAS P', '8'),
(54, 'MASSA RECHEADA FRUTAS M', '8'),
(55, 'MASSA RECHEADA FRUTAS G', '8'),
(56, 'MASSA RECHEADA MESCLADA MINI', '8'),
(57, 'MASSA RECHEADA MESCLADA P', '8'),
(58, 'MASSA RECHEADA MESCLADA M', '8'),
(59, 'MASSA RECHEADA MESCLADA G', '8'),
(60, 'MASSA RECHEADA MOUSSE CHOCOLATE MINI', '8'),
(61, 'MASSA RECHEADA MOUSSE CHOCOLATE P', '8'),
(62, 'MASSA RECHEADA MOUSSE CHOCOLATE M', '8'),
(63, 'MASSA RECHEADA MOUSSE CHOCOLATE G', '8'),
(64, 'MASSA RECHEADA NEGA MINI', '8'),
(65, 'MASSA RECHEADA NEGA P', '8'),
(66, 'MASSA RECHEADA NEGA M', '8'),
(67, 'MASSA RECHEADA NEGA G', '8'),
(68, 'MASSA RECHEADA NEGRESCO MINI', '8'),
(69, 'MASSA RECHEADA NEGRESCO P', '8'),
(70, 'MASSA RECHEADA NEGRESCO M', '8'),
(71, 'MASSA RECHEADA NEGRESCO G', '8'),
(72, 'MASSA RECHEADA NINHO MINI', '8'),
(73, 'MASSA RECHEADA NINHO P', '8'),
(74, 'MASSA RECHEADA NINHO M', '8'),
(75, 'MASSA RECHEADA NINHO G', '8'),
(76, 'MASSA RECHEADA PRESTÍGIO MINI', '8'),
(77, 'MASSA RECHEADA PRESTÍGIO P', '8'),
(78, 'MASSA RECHEADA PRESTÍGIO M', '8'),
(79, 'MASSA RECHEADA PRESTÍGIO G', '8'),
(80, 'MASSA RECHEADA SONHO DE VALSA MINI', '8'),
(81, 'MASSA RECHEADA SONHO DE VALSA P', '8'),
(82, 'MASSA RECHEADA SONHO DE VALSA M', '8'),
(83, 'MASSA RECHEADA SONHO DE VALSA G', '8'),
(84, 'MASSA RECHEADA NOZES MINI', '8'),
(85, 'MASSA RECHEADA NOZES P', '8'),
(86, 'MASSA RECHEADA NOZES M', '8'),
(87, 'MASSA RECHEADA NOZES G', '8'),
(88, 'TESTE', '6');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saldo_produtos`
--

CREATE TABLE `saldo_produtos` (
  `produto_id` int(11) NOT NULL,
  `inventario` int(11) DEFAULT 0,
  `entradas` int(11) DEFAULT 0,
  `saidas` int(11) DEFAULT 0,
  `saldo` int(11) DEFAULT 0,
  `data_ultimo_inventario` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `saldo_produtos`
--

INSERT INTO `saldo_produtos` (`produto_id`, `inventario`, `entradas`, `saidas`, `saldo`, `data_ultimo_inventario`) VALUES
(15, 7, 0, 0, 7, '2026-01-19 20:21:47'),
(18, 7, 0, 0, 7, '2026-01-19 20:21:47'),
(19, 11, 0, 0, 11, '2026-01-19 20:21:47'),
(20, 1, 0, 0, 1, '2026-01-19 20:21:47'),
(21, 8, 0, 0, 8, '2026-01-19 20:21:47'),
(23, 7, 0, 0, 7, '2026-01-19 20:21:47'),
(24, 4, 0, 0, 4, '2026-01-19 20:21:47'),
(33, 3, 0, 0, 3, '2026-01-19 20:21:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos`
--

CREATE TABLE `tipos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipos`
--

INSERT INTO `tipos` (`id`, `nome`) VALUES
(6, 'CASEIRO'),
(7, 'MASSA PARA TORTA'),
(8, 'TORTA RECHEADA');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expira` datetime DEFAULT NULL,
  `perfil_id` int(11) DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `ativo`, `criado_em`, `reset_token`, `reset_expira`, `perfil_id`) VALUES
(1, 'Adelmo Santos', 'dexter.craus@gmail.com', '$2y$10$NWGo.nstV.yRqgaCiExlWONXddYl730ZcqMwEbMAPF2SbA5KRwi5W', 1, '2025-10-30 20:58:26', NULL, NULL, 1),
(2, 'DAVY LUCAS', 'davy@gmail.com', '$2y$10$lk6aMAryybzA3clYX5vAuuNLg29MBreVT.hlm9cUAcwJNZXuBUncG', 1, '2025-10-31 03:21:35', NULL, NULL, 3),
(4, 'TESTE45', 'sad.zonasul@gmail.com', '$2y$10$/R75DsctLKmRxmfimrPY8uxy3Z4bLTUPLCL29IeAdVx2JAgr9UfIO', 1, '2026-01-18 15:46:29', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_tokens`
--

CREATE TABLE `usuarios_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL DEFAULT current_timestamp(),
  `usado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_tokens`
--

INSERT INTO `usuarios_tokens` (`id`, `usuario_id`, `token`, `expira_em`, `usado`) VALUES
(1, 1, '0bd3fe25f6a95833c113c04264574f6181e4cda952142fc2bf7a8f9b2f9c54c9', '2025-10-31 02:15:06', 0),
(2, 1, 'e300137717c5aa95f4ec9d699a802ec743bdfb4f21a8f14985746b7668bd2a55', '2025-10-31 02:25:04', 0),
(3, 1, 'f7eb1ff25687657e7014fe3c105cef74dcf67fc3627a61e391e80d385224f32c', '2025-10-31 02:26:41', 0),
(4, 1, '8ff6920bd135ce24cdb0609a87766d54b666fd247c3f23db8266700a526b64c6', '2025-10-31 02:35:15', 1),
(5, 1, '4fd2920dd89a02dec8762fe80138f3b8eb2cc6938b305fdbdb3292d76d876bd3', '2025-10-31 08:26:49', 0),
(6, 1, 'c8ec09309ff82f6df69659147a038d52cab4ed45fa05311819417fec04e44700', '2025-10-31 08:26:56', 0),
(7, 1, 'e6844a446a1e3b44b9e672d261ea30965a4aa7c744a437b639f77b96944b0c9d', '2025-11-03 02:27:47', 0),
(8, 1, 'f3e357015764fbfc32c13497c28d2a5919b33202296c1d8cd59f174cae5f8bde', '2025-11-06 01:45:01', 1),
(9, 1, '39bbaef85393b4e4f9a90bb467fecbd68a52f3cc652f557b830e50ebf8efb9a0', '2025-11-06 02:06:38', 0),
(10, 1, 'ce3dfcefb49428ba2d57ae4bbe9974fe6c9a9f31c2088473b010c25c8ee9be81', '2025-11-06 02:11:42', 0),
(11, 1, 'bbd4dcc03ec7e00ef8ef96551c7c427bd00580ebe03c9e476785024d333d4bf2', '2025-11-06 02:24:20', 0),
(12, 1, '779d61105aac4e070cd852b5adb25d7726bb1dd37fad56b494acd54c08f5d522', '2025-11-06 21:32:16', 0),
(13, 1, '2eedef0db864ad2dffa08c1973e0f5d7ca854283de92fb950842f709445b8b67', '2025-11-06 21:35:36', 0),
(26, 4, '950be45835cf799a898b95c4fb782b33d79ad4f0c316ef4ca86d0aadcc6adc8d', '2026-01-18 18:10:55', 0),
(27, 4, '91e89a70704fe1a586f2375724b838a037875872c965de071bfac2b7f671c02e', '2026-01-18 18:11:43', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_permissoes`
--

CREATE TABLE `usuario_permissoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario_permissoes`
--

INSERT INTO `usuario_permissoes` (`id`, `usuario_id`, `permissao_id`) VALUES
(72, 2, 7),
(73, 2, 6),
(74, 2, 34),
(75, 2, 35),
(76, 2, 30);

-- --------------------------------------------------------

--
-- Estrutura para tabela `valores_guardados`
--

CREATE TABLE `valores_guardados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `quantidade` decimal(10,2) NOT NULL DEFAULT 0.00,
  `data_guardado` datetime NOT NULL DEFAULT current_timestamp(),
  `codigo_guardado` varchar(50) NOT NULL
) ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `controle_entrada`
--
ALTER TABLE `controle_entrada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `idx_entrada_usuario` (`usuario_id`);

--
-- Índices de tabela `controle_saida`
--
ALTER TABLE `controle_saida`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `idx_saida_usuario` (`usuario_id`);

--
-- Índices de tabela `inventario_log`
--
ALTER TABLE `inventario_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `lojas`
--
ALTER TABLE `lojas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `perfis`
--
ALTER TABLE `perfis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `permissoes`
--
ALTER TABLE `permissoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `saldo_produtos`
--
ALTER TABLE `saldo_produtos`
  ADD PRIMARY KEY (`produto_id`);

--
-- Índices de tabela `tipos`
--
ALTER TABLE `tipos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `perfil_id` (`perfil_id`);

--
-- Índices de tabela `usuarios_tokens`
--
ALTER TABLE `usuarios_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuario_permissoes`
--
ALTER TABLE `usuario_permissoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `permissao_id` (`permissao_id`);

--
-- Índices de tabela `valores_guardados`
--
ALTER TABLE `valores_guardados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_usuario_produto_tipo` (`usuario_id`,`produto_id`,`tipo`),
  ADD UNIQUE KEY `uniq_usuario_produto_tipo` (`usuario_id`,`produto_id`,`tipo`),
  ADD KEY `fk_valores_guardados_produto` (`produto_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `controle_entrada`
--
ALTER TABLE `controle_entrada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de tabela `controle_saida`
--
ALTER TABLE `controle_saida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de tabela `inventario_log`
--
ALTER TABLE `inventario_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1428;

--
-- AUTO_INCREMENT de tabela `lojas`
--
ALTER TABLE `lojas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `perfis`
--
ALTER TABLE `perfis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `permissoes`
--
ALTER TABLE `permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de tabela `tipos`
--
ALTER TABLE `tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios_tokens`
--
ALTER TABLE `usuarios_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `usuario_permissoes`
--
ALTER TABLE `usuario_permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de tabela `valores_guardados`
--
ALTER TABLE `valores_guardados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `controle_entrada`
--
ALTER TABLE `controle_entrada`
  ADD CONSTRAINT `controle_entrada_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `controle_saida`
--
ALTER TABLE `controle_saida`
  ADD CONSTRAINT `controle_saida_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `inventario_log`
--
ALTER TABLE `inventario_log`
  ADD CONSTRAINT `inventario_log_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `saldo_produtos`
--
ALTER TABLE `saldo_produtos`
  ADD CONSTRAINT `saldo_produtos_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `valores_guardados`
--
ALTER TABLE `valores_guardados`
  ADD CONSTRAINT `fk_valores_guardados_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
