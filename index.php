<?php
require_once 'conexao.php';
$alunos = $pdo->query("SELECT id, nome FROM alunos")->fetchAll(PDO::FETCH_ASSOC);
$autores = $pdo->query("SELECT id, nome FROM autores")->fetchAll(PDO::FETCH_ASSOC);
$editoras = $pdo->query("SELECT id, nome FROM editoras")->fetchAll(PDO::FETCH_ASSOC);
$livros = $pdo->query("SELECT * FROM livros")->fetchAll(PDO::FETCH_ASSOC);
$emprestimos_ativos = $pdo->query("SELECT e.id, l.titulo, a.nome as aluno_nome, e.data_emprestimo, e.data_devolucao_prevista 
                                  FROM emprestimos e 
                                  JOIN livros l ON e.livro_id = l.id 
                                  JOIN alunos a ON e.aluno_id = a.id 
                                  WHERE e.status_devolucao = 'emprestado' OR e.status_devolucao = 'atrasado'")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->query("SELECT l.titulo, l.autor_id, COUNT(e.id) as total_emprestimos 
                    FROM emprestimos e 
                    JOIN livros l ON e.livro_id = l.id 
                    GROUP BY e.livro_id 
                    ORDER BY total_emprestimos DESC 
                    LIMIT 10");
$livros_mais_emprestados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hoje = date('Y-m-d');
$stmt = $pdo->prepare("SELECT e.*, l.titulo, a.nome as aluno_nome
                      FROM emprestimos e 
                      JOIN livros l ON e.livro_id = l.id 
                      JOIN alunos a ON e.aluno_id = a.id 
                      WHERE (e.status_devolucao = 'emprestado' AND e.data_devolucao_prevista < :hoje) OR e.status_devolucao = 'atrasado'");
$stmt->execute([':hoje' => $hoje]);
$atrasos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($emprestimos_ativos as $emprestimo) {
    $days_diff = strtotime($emprestimo['data_devolucao_prevista']) - time();
    if ($days_diff < 0) {
        $stmt = $pdo->prepare("UPDATE emprestimos SET status_devolucao = ? WHERE id = ?");
        $stmt->execute(["atrasado",$emprestimo['id']]);
    }
}

function getAuthorName($id, $autores) {
    $autor = "";
    foreach ($autores as $autorr) {
        if ($autorr["id"] == $id) {
            $autor = $autorr["nome"];
            break;
        }
    }
    return $autor;
}

$mensagem = '';
// Processamento para empréstimo
if (isset($_POST['realizar_emprestimo'])) {
    $livro_id = $_POST['livro_id'];
    $aluno_id = $_POST['aluno_id'];
    $data_emprestimo = date($_POST['data_emprestimo']);
    $data_devolucao_prevista = date($_POST['data_devolucao_prevista']);
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
            header("refresh: 0");
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
    $data_devolucao_real = date($_POST['data_devolucao_real']);
    // Buscar o empréstimo
    $stmt = $pdo->prepare("SELECT livro_id, data_devolucao_prevista FROM emprestimos WHERE id = ?");
    $stmt->execute([$emprestimo_id]);
    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);
    $livro_id = $emprestimo['livro_id'];
    // Atualizar o empréstimo
    $status = ($data_devolucao_real > $emprestimo['data_devolucao_prevista']) ? 'devolvido_atrasado' : 'devolvido';
    $stmt = $pdo->prepare("UPDATE emprestimos SET data_devolucao_real = ?, status_devolucao = ? WHERE id = ?");
    if ($stmt->execute([$data_devolucao_real, $status, $emprestimo_id])) {
        // Atualizar a quantidade do livro
        $stmt = $pdo->prepare("UPDATE livros SET quantidade = quantidade + 1 WHERE id = ?");
        $stmt->execute([$livro_id]);
        $mensagem = "Devolução realizada com sucesso!";
        header("refresh: 0");
    } else {
        $mensagem = "Erro ao realizar devolução.";
    }
}

// Historico de emprestimos
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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Biblioteca Escolar</title>
    <link rel="stylesheet" href="./index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-book"></i>
                <h1>Biblioteca Escolar</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="cadastrar.php"><i class="fas fa-book"></i> Cadastros</a></li>
                    <?php 
                    /* <li><a href="#"><i class="fas fa-book"></i> Livros</a></li>
                    <li><a href="#"><i class="fas fa-user"></i> Alunos</a></li>
                    <li><a href="#"><i class="fas fa-exchange-alt"></i> Empréstimos</a></li>
                    <li><a href="#"><i class="fas fa-chart-bar"></i> Relatórios</a></li> */ 
                    ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <div class="number"><?php echo count($livros)?></div>
                <div class="label">Livros Cadastrados</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="number"><?php echo count($alunos)?></div>
                <div class="label">Alunos Cadastrados</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-exchange-alt"></i>
                <div class="number"><?php echo count($emprestimos_ativos)?></div>
                <div class="label">Empréstimos Ativos</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="number"><?php echo count($atrasos)?></div>
                <div class="label">Atrasos</div>
            </div>
        </div>

        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-exchange-alt"></i> Gerenciar Empréstimos</h2>
                </div>
                
                <div class="tabs">
                    <div class="tab active" data-tab="emprestimo">Realizar Empréstimo</div>
                    <div class="tab" data-tab="devolucao">Registrar Devolução</div>
                </div>
                <?php if ($mensagem): ?>
                    <p><?php echo $mensagem; ?></p>
                <?php endif; ?>
                <div class="tab-content active" id="emprestimo-tab">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <div class="form-group">
                            <label for="aluno">Aluno</label>
                            <select class="form-control" id="aluno_id" name="aluno_id" required>
                                <option value="">Selecione um aluno...</option>
                                <?php foreach ($alunos as $aluno): ?>
                                    <option value="<?php echo $aluno['id']; ?>"><?php echo $aluno['nome']; ?></option>
                                <?php endforeach; ?>                            
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="livro">Livro</label>
                            <select class="form-control" id="livro_id" name="livro_id" required>
                                <option value="">Selecione um livro...</option>
                                <?php foreach ($livros as $livro): ?>
                                    <option value="<?php echo $livro['id']; ?>"><?php echo $livro['titulo'] . " - " . getAuthorName($livro['autor_id'], $autores)?></option>
                                <?php endforeach; ?>   
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="data-emprestimo">Data do Empréstimo</label>
                            <input type="date" class="form-control" id="data-emprestimo" name="data_emprestimo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="data-devolucao">Data Prevista de Devolução</label>
                            <input type="date" class="form-control" id="data-devolucao" name="data_devolucao_prevista" required>
                        </div>
                        
                        <button type="submit" name="realizar_emprestimo" class="btn btn-primary">
                            <i class="fas fa-check"></i> Registrar Empréstimo
                        </button>
                    </form>
                </div>
                
                <div class="tab-content" id="devolucao-tab">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <div class="form-group">
                            <label for="emprestimo">Selecione o Empréstimo</label>
                            <select class="form-control" id="emprestimo_id" name="emprestimo_id" required>
                                <option value="">Selecione um empréstimo...</option>
                                <?php foreach ($emprestimos_ativos as $emprestimo): ?>
                                    <option value="<?php echo $emprestimo['id']; ?>">
                                        Livro: <?php echo $emprestimo['titulo']; ?> - 
                                        Aluno: <?php echo $emprestimo['aluno_nome']; ?> - 
                                        Empréstimo: <?php echo date('d/m/Y', strtotime($emprestimo['data_emprestimo'])); ?> - 
                                        Devolução Prevista: <?php echo date('d/m/Y', strtotime($emprestimo['data_devolucao_prevista'])); ?>
                                    </option>
                                <?php endforeach; ?>                            
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="data-devolucao-real">Data da Devolução</label>
                            <input type="date" class="form-control" id="data_devolucao_real" name="data_devolucao_real" required>
                        </div>
                        
                        <button type="submit" name="realizar_devolucao" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Registrar Devolução
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-book"></i> Livros Disponiveis</h2>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Editora</th>
                            <th>Ano</th>
                            <th>Disponível</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($livros as $livro): ?>
                        <tr>
                            <td><?php echo $livro['isbn']?></td>
                            <td><?php echo $livro['titulo']?></td>
                            <td><?php echo getAuthorName($livro['autor_id'], $autores)?></td>
                            <td><?php echo getAuthorName($livro['editora_id'], $editoras)?></td>
                            <td><?php echo $livro['ano_publicacao']?></td>
                            <td><?php echo $livro['quantidade']?></td>

                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Consultar Emprestimos</h2>
                </div>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="form-group">
                        <label for="aluno_id">Aluno</label>
                        <select class="form-control" id="aluno_id" name="aluno_id" required>
                            <option value="">Selecione um aluno...</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?php echo $aluno['id']; ?>"><?php echo $aluno['nome']; ?></option>
                            <?php endforeach; ?>                            
                        </select>
                    </div>
                    <button type="submit" name="buscar" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Buscar
                    </button>
                </form>
                <?php 
                ?>
                <?php if (count($historico) > 0): ?>
                    <table>
                    <thead>
                        <tr>
                            <th>Livro</th>
                            <th>Data Empréstimo</th>
                            <th>Devolução Prevista</th>
                            <th>Devolução Real</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $livro): ?>
                            <tr>
                                <td><?php echo $livro['titulo'] ?></td>
                                <td><?php echo $livro['data_emprestimo'] ?></td>
                                <td><?php echo $livro['data_devolucao_prevista'] ?></td>
                                <td><?php if (isset($livro['data_devolucao_real'])) { echo $livro['data_devolucao_real'];} else { echo "-";} ?></td>
                                <td>
                                    <span class= <?php 
                                            $statusLivro = $livro['status_devolucao'];
                                            if ($statusLivro == "emprestado") {
                                                echo "status-emprestado";
                                            } else if ($statusLivro == "devolvido") {
                                                echo "status-devolvido";
                                            } else if ($statusLivro == "devolvido_atrasado") {
                                                echo "status-devolvido-atrasado";
                                            } else {
                                                echo "status-atrasado";
                                            }
                                        ?>>
                                        <?php
                                            $statusLivro = $livro['status_devolucao'];
                                            if ($statusLivro == "emprestado") {
                                                echo "Emprestado";
                                            } else if ($statusLivro == "devolvido") {
                                                echo "Devolvido";
                                            } else {
                                                $days = date_diff(date_create($livro['data_devolucao_real']), date_create($livro['data_devolucao_prevista']));
                                                if ($statusLivro == "devolvido_atrasado") {

                                                    echo "Devolvido Atrasado (" . $days->format('%d') . " dias)";
                                                } else {
                                                    echo "Atrasado (" . $days->format('%d') . " dias)";
                                                }
                                            }
                                        ?>
                                    </span>
                                </td>
                            </tr>        
                        <?php endforeach?>
                    </tbody>
                </table>
            <?php endif?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-bar"></i> Relatórios</h2>
                </div>
                
                <div class="tabs">
                    <div class="tab active" data-tab="livros-populares">Livros Mais Emprestados</div>
                    <div class="tab" data-tab="atrasos">Atrasos</div>
                </div>
                
                <div class="tab-content active" id="livros-populares-tab">
                    <table>
                        <thead>
                            <tr>
                                <th>Posição</th>
                                <th>Livro</th>
                                <th>Autor</th>
                                <th>Total de Empréstimos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $posicaoMaisEmprestados = 0;?>
                            <?php foreach($livros_mais_emprestados as $livro): ?>
                                <tr> 
                                    <td><?php $posicaoMaisEmprestados+=1; echo $posicaoMaisEmprestados;?></td>
                                    <td><?php echo $livro['titulo']?></td>
                                    <td><?php echo getAuthorName($livro['autor_id'], $autores) ?></td>
                                    <td><?php echo $livro['total_emprestimos'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-content" id="atrasos-tab">
                    <table>
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Livro</th>
                                <th>Data Empréstimo</th>
                                <th>Devolução Prevista</th>
                                <th>Dias de Atraso</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php foreach ($atrasos as $atraso): 
                                $dias_atraso = (strtotime($hoje) - strtotime($atraso['data_devolucao_prevista'])) / (60 * 60 * 24);
                                $dias_atraso = floor($dias_atraso);
                            ?>
                                <tr>
                                    <td><?php echo $atraso['aluno_nome']; ?></td>
                                    <td><?php echo $atraso['titulo']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($atraso['data_emprestimo'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($atraso['data_devolucao_prevista'])); ?></td>
                                    <td><?php echo $dias_atraso . " dias"; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>Sistema de Gerenciamento de Biblioteca Escolar &copy; 2023 - Todos os direitos reservados</p>
        </div>
    </footer>
    
    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Show corresponding content
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
        
        // Set today's date as default for loan date
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('data-emprestimo').value = today;
        
        // Set return date to 7 days from now
        const nextWeek = new Date();
        nextWeek.setDate(nextWeek.getDate() + 7);
        const nextWeekFormatted = nextWeek.toISOString().split('T')[0];
        document.getElementById('data-devolucao').value = nextWeekFormatted;
        document.getElementById('data_devolucao_real').value = today;
    </script>
</body>
</html>