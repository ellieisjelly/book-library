<?php
require_once 'conexao.php';
$mensagem = '';
// Processamento para empréstimo
if (isset($_POST['realizar_emprestimo'])) {
    $livro_id = $_POST['livro_id'];
    $aluno_id = $_POST['aluno_id'];
    $data_emprestimo = date('Y-m-d');
    $data_devolucao_prevista = date('Y-m-d', strtotime('+7 days'));
    // Verificar se há exemplares disponíveis
    $stmt = $pdo->prepare("SELECT quantidade FROM livros WHERE id = ?");
    $stmt->execute([$livro_id]);
    $livro = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($livro['quantidade'] > 0) {
        // Realizar empréstimo
        $stmt = $pdo->prepare("INSERT INTO emprestimos (livro_id, aluno_id, data_emprestimo, data_devolucao_prevista, status_devolucao) VALUES (?, ?, ?, ?, 'emprestado')");
        if ($stmt->execute([$livro_id, $aluno_id, $data_emprestimo, $data_devolucao_prevista])) {
            // Atualizar a quantidade do livro
            $nova_quantidade = $livro['quantidade'] - 1;
            $stmt = $pdo->prepare("UPDATE livros SET quantidade = ? WHERE id = ?");
            $stmt->execute([$nova_quantidade, $livro_id]);
            $mensagem = "Empréstimo realizado com sucesso!";
        } else {
            $mensagem = "Erro ao realizar empréstimo.";
        }
    } else {
        $mensagem = "Não há exemplares disponíveis deste livro.";
    }
}
// Processamento para devolução
if (isset($_POST['realizar_devolucao'])) {
    $emprestimo_id = $_POST['emprestimo_id'];
    $data_devolucao_real = date('Y-m-d');
    // Buscar o empréstimo
    $stmt = $pdo->prepare("SELECT livro_id, data_devolucao_prevista FROM emprestimos WHERE id = ?");
    $stmt->execute([$emprestimo_id]);
    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);
    $livro_id = $emprestimo['livro_id'];
    // Atualizar o empréstimo
    $status = ($data_devolucao_real > $emprestimo['data_devolucao_prevista']) ? 'atrasado' : 'devolvido';
    $stmt = $pdo->prepare("UPDATE emprestimos SET data_devolucao_real = ?, status_devolucao = ? WHERE id = ?");
    if ($stmt->execute([$data_devolucao_real, $status, $emprestimo_id])) {
        // Atualizar a quantidade do livro
        $stmt = $pdo->prepare("UPDATE livros SET quantidade = quantidade + 1 WHERE id = ?");
        $stmt->execute([$livro_id]);
        $mensagem = "Devolução realizada com sucesso!";
    } else {
        $mensagem = "Erro ao realizar devolução.";
    }
}
// Buscar alunos e livros para o formulário de empréstimo
$alunos = $pdo->query("SELECT id, nome FROM alunos")->fetchAll(PDO::FETCH_ASSOC);
$livros = $pdo->query("SELECT id, titulo FROM livros WHERE quantidade > 0")->fetchAll(PDO::FETCH_ASSOC);
// Buscar empréstimos ativos para devolução
$emprestimos_ativos = $pdo->query("SELECT e.id, l.titulo, a.nome as aluno_nome, e.data_emprestimo, e.data_devolucao_prevista 
                                  FROM emprestimos e 
                                  JOIN livros l ON e.livro_id = l.id 
                                  JOIN alunos a ON e.aluno_id = a.id 
                                  WHERE e.status_devolucao = 'emprestado' OR e.status_devolucao = 'atrasado'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Empréstimos e Devoluções</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Empréstimos e Devoluções</h1>
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
        <h2>Realizar Empréstimo</h2>
        <?php if ($mensagem): ?>
            <p><?php echo $mensagem; ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="aluno_id">Aluno:</label>
            <select id="aluno_id" name="aluno_id" required>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?php echo $aluno['id']; ?>"><?php echo $aluno['nome']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="livro_id">Livro:</label>
            <select id="livro_id" name="livro_id" required>
                <?php foreach ($livros as $livro): ?>
                    <option value="<?php echo $livro['id']; ?>"><?php echo $livro['titulo']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="realizar_emprestimo">Realizar Empréstimo</button>
        </form>
        <h2>Realizar Devolução</h2>
        <form method="post">
            <label for="emprestimo_id">Empréstimo:</label>
            <select id="emprestimo_id" name="emprestimo_id" required>
                <?php foreach ($emprestimos_ativos as $emprestimo): ?>
                    <option value="<?php echo $emprestimo['id']; ?>">
                        Livro: <?php echo $emprestimo['titulo']; ?> - 
                        Aluno: <?php echo $emprestimo['aluno_nome']; ?> - 
                        Empréstimo: <?php echo date('d/m/Y', strtotime($emprestimo['data_emprestimo'])); ?> - 
                        Devolução Prevista: <?php echo date('d/m/Y', strtotime($emprestimo['data_devolucao_prevista'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="realizar_devolucao">Registrar Devolução</button>
        </form>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Biblioteca Escolar</p>
    </footer>
</body>
</html>