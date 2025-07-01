<?php
require_once 'conexao.php';
$historico = [];
if (isset($_POST['buscar'])) {
    $aluno_id = $_POST['aluno_id'];
    $stmt = $pdo->prepare("SELECT e.*, l.titulo, a.nome as aluno_nome 
                          FROM emprestimos e 
                          JOIN livros l ON e.livro_id = l.id 
                          JOIN alunos a ON e.aluno_id = a.id 
                          WHERE e.aluno_id = ? 
                          ORDER BY e.data_emprestimo DESC");
    $stmt->execute([$aluno_id]);
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$alunos = $pdo->query("SELECT id, nome FROM alunos")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico por Aluno</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Histórico de Empréstimos por Aluno</h1>
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
        <h2>Buscar Histórico</h2>
        <form method="post">
            <label for="aluno_id">Aluno:</label>
            <select id="aluno_id" name="aluno_id" required>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?php echo $aluno['id']; ?>"><?php echo $aluno['nome'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="buscar">Buscar</button>
        </form>
        <?php if (!empty($historico)): ?>
            <h3>Histórico de Empréstimos</h3>
            <table border="1">
                <tr>
                    <th>Livro</th>
                    <th>Data Empréstimo</th>
                    <th>Devolução Prevista</th>
                    <th>Devolução Real</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($historico as $item): ?>
                    <tr>
                        <td><?php echo $item['titulo']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($item['data_emprestimo'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($item['data_devolucao_prevista'])); ?></td>
                        <td><?php echo $item['data_devolucao_real'] ? date('d/m/Y', strtotime($item['data_devolucao_real'])) : '-'; ?></td>
                        <td><?php echo $item['status_devolucao']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Biblioteca Escolar</p>
    </footer>
</body>
</html>