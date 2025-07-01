<?php
require_once 'conexao.php';
// Inicializa variáveis
$mensagem = '';
// Processamento para cadastro de autor
if (isset($_POST['cadastrar_autor'])) {
    $nome = $_POST['nome_autor'];
    $stmt = $pdo->prepare("INSERT INTO autores (nome) VALUES (?)");
    if ($stmt->execute([$nome])) {
        $mensagem = "Autor cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar autor.";
    }
}
// Processamento para cadastro de editora
if (isset($_POST['cadastrar_editora'])) {
    $nome = $_POST['nome_editora'];
    $stmt = $pdo->prepare("INSERT INTO editoras (nome) VALUES (?)");
    if ($stmt->execute([$nome])) {
        $mensagem = "Editora cadastrada com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar editora.";
    }
}
// Processamento para cadastro de livro
if (isset($_POST['cadastrar_livro'])) {
    $titulo = $_POST['titulo'];
    $autor_id = $_POST['autor_id'];
    $editora_id = $_POST['editora_id'];
    $ano = $_POST['ano'];
    $isbn = $_POST['isbn'];
    $quantidade = $_POST['quantidade'];
    $stmt = $pdo->prepare("INSERT INTO livros (titulo, autor_id, editora_id, ano_publicacao, isbn, quantidade) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$titulo, $autor_id, $editora_id, $ano, $isbn, $quantidade])) {
        $mensagem = "Livro cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar livro.";
    }
}
// Processamento para cadastro de aluno
if (isset($_POST['cadastrar_aluno'])) {
    $nome = $_POST['nome_aluno'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    
    $stmt = $pdo->prepare("INSERT INTO alunos (nome, email, telefone) VALUES (?, ?, ?)");
    if ($stmt->execute([$nome, $email, $telefone])) {
        $mensagem = "Aluno cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar aluno.";
    }
}
// Buscar autores e editoras para o formulário de livros
$autores = $pdo->query("SELECT id, nome FROM autores")->fetchAll(PDO::FETCH_ASSOC);
$editoras = $pdo->query("SELECT id, nome FROM editoras")->fetchAll(PDO::FETCH_ASSOC);
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="cadastrar.php" class="active"><i class="fas fa-book"></i> Cadastros</a></li>
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
    <main>
        <div class="container">
            <div class="content">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-book"></i> Cadastrar um Novo:</h2>
                    </div>
                    <div class="tabs">
                        <div class="tab active" data-tab="autor">Autor</div>
                        <div class="tab" data-tab="editora">Editora</div>
                        <div class="tab" data-tab="livro">Livro</div>
                        <div class="tab" data-tab="aluno">Aluno</div>
                    </div>
                    <div class="tab-content active" id="autor-tab">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <div class="form-group">
                                <label for="nome_autor">Nome do Autor </label>
                                <input type="text" id="nome_autor" name="nome_autor" required>
                            </div>
                            <button type="submit" name="cadastrar_autor" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Registrar Autor
                            </button>
                        </form>
                    </div>
                    <div class="tab-content" id="editora-tab">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <div class="form-group">
                                <label for="nome_autor">Nome da Editora </label>
                                <input type="text" id="nome_editora" name="nome_editora" required>
                            </div>
                            <button type="submit" name="cadastrar_editora" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Registrar Editora
                            </button>
                        </form>
                    </div>
                    <div class="tab-content" id="livro-tab">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <div class="form-group">
                                <label for="titulo">Nome do Livro </label>
                                <input class="form-control" type="text" id="titulo" name="titulo" required>
                            </div>

                            <div class="form-group">
                                <label for="autor_id">Autor</label>
                                <select class="form-control" id="autor_id" name="autor_id">
                                    <?php foreach ($autores as $autor): ?>
                                        <option value="<?php echo $autor['id']; ?>"><?php echo $autor['nome']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="editora_id">Editora</label>
                                <select class="form-control" id="editora_id" name="editora_id">
                                    <?php foreach ($editoras as $editora): ?>
                                        <option value="<?php echo $editora['id']; ?>"><?php echo $editora['nome']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for ="ano">Ano de Publicação</label>
                                <input class="form-control" type="number" id="ano" name="ano" required>
                            </div>

                            <div class="form-group">
                                <label for ="isbn">ISBN</label>
                                <input class="form-control" type="text" id="isbn" name="isbn" required>
                            </div>

                            <div class="form-group">
                                <label for ="ano">Quantidade</label>
                                <input class="form-control" type="number" id="quantidade" name="quantidade" required min=1>
                            </div>
                            <button type="submit" name="cadastrar_livro" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Registrar Livro
                            </button>
                        </form>
                    </div>
                    <div class="tab-content" id="aluno-tab">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <div class="form-group">
                                <label for ="nome_aluno">Nome</label>
                                <input class="form-control" type="text" id="nome_aluno" name="nome_aluno" required>
                            </div>
                            <div class="form-group">
                                <label for ="email">Email</label>
                                <input class="form-control" type="text" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for ="telefone">Telefone</label>
                                <input class="form-control" type="text" id="telefone" name="telefone" required>
                            </div>
                            <button type="submit" name="cadastrar_aluno" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Registrar Aluno
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
   <footer>
        <div class="container">
            <p>Sistema de Gerenciamento de Biblioteca Escolar &copy; 2023 - Todos os direitos reservados</p>
        </div>
    </footer>
    <script>
        checkBottomBarIsInBottom()
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
                checkBottomBarIsInBottom();
            });
        });
        window.addEventListener("resize", function () {
            checkBottomBarIsInBottom()
        });
        // Make sure bottom bar is at the bottom
        function checkBottomBarIsInBottom() {
            const bar = document.querySelector("footer");
            if (document.body.getBoundingClientRect().height <= window.innerHeight) {
                bar.style.position = "absolute";
                bar.style.bottom = "0px";
                bar.style.width = "100vw";
            } else {
                bar.style.position = "static";
            }
  
        }
    </script>
</body>
</html>