# Setup
```sql
CREATE DATABASE biblioteca;
USE biblioteca;
CREATE TABLE alunos(id int primary key auto_increment, nome varchar(100) not null, email varchar(100) not null, telefone varchar(100) not null);
CREATE TABLE autores(id int primary key auto_increment, nome varchar(100) not null);
CREATE TABLE editoras(id int primary key auto_increment, nome varchar(100) not null);
CREATE TABLE emprestimos(id int primary key auto_increment, livro_id int not null, aluno_id int not null, data_emprestimo date not null, data_devolucao_prevista date not null, data_devolucao_real date, status_devolucao enum("emprestado", "devolvido", "atrasado", "devolvido_atrasado") not null);
CREATE TABLE livros(id int primary key auto_increment, titulo varchar(100) not null, autor_id int not null, editora_id int not null, ano_publicacao int not null, isbn varchar(100) not null, quantidade int not null);
```
