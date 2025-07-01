<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Biblioteca Escolar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Biblioteca Escolar</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cadastrar.php">Cadastros</a></li>
                <li><a href="emprestimo.php">Empréstimos/Devoluções</a></li>
                <li><a href="historico.php">Histórico</a></li>
                <li><a href="relatorios.php">Relatórios</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Bem-vindo ao Sistema de Biblioteca</h2>
        <p>Selecione uma opção no menu acima.</p>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Biblioteca Escolar</p>
    </footer>
</body>
</html>