<?php
require_once 'conexao.php';
// Livros mais emprestados
$stmt = $pdo->query("SELECT l.titulo, COUNT(e.id) as total_emprestimos 
                    FROM emprestimos e 
                    JOIN livros l ON e.livro_id = l.id 
                    GROUP BY e.livro_id 
                    ORDER BY total_emprestimos DESC 
                    LIMIT 10");
$livros_mais_emprestados = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Empréstimos em atraso
$hoje = date('Y-m-d');
$stmt = $pdo->prepare("SELECT e.*, l.titulo, a.nome as aluno_nome
                      FROM emprestimos e 
                      JOIN livros l ON e.livro_id = l.id 
                      JOIN alunos a ON e.aluno_id = a.id 
                      WHERE (e.status_devolucao = 'emprestado' AND e.data_devolucao_prevista < :hoje) OR e.status_devolucao = 'atrasado'");
$stmt->execute([':hoje' => $hoje]);
$atrasos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Relatórios</h1>
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
        <h2>Livros Mais Emprestados</h2>
        <table border="1">
            <tr>
                <th>Livro</th>
                <th>Total de Empréstimos</th>
            </tr>
            <?php foreach ($livros_mais_emprestados as $livro): ?>
                <tr>
                    <td><?php echo $livro['titulo']; ?></td>
                    <td><?php echo $livro['total_emprestimos']; ?></td>
                </tr>
            <?php endforeach;?>
        </table>
        <h2>Empréstimos em Atraso</h2>
        <table border="1">
            <tr>
                <th>Livro</th>
                <th>Aluno</th>
                <th>Data Empréstimo</th>
                <th>Devolução Prevista</th>
                <th>Dias de Atraso</th>
            </tr>
            <?php foreach ($atrasos as $atraso): 
                $dias_atraso = (strtotime($hoje) - strtotime($atraso['data_devolucao_prevista'])) / (60 * 60 * 24);
                $dias_atraso = floor($dias_atraso);
            ?>
                <tr>
                    <td><?php echo $atraso['titulo']; ?></td>
                    <td><?php echo $atraso['aluno_nome']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($atraso['data_emprestimo'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($atraso['data_devolucao_prevista'])); ?></td>
                    <td><?php echo $dias_atraso; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Biblioteca Escolar</p>
    </footer>
</body>
</html>