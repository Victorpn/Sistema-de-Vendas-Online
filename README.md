# Sistema de Vendas Online  
**Projeto Acadêmico – PHP, HTML, CSS**

Este repositório contém um sistema web de vendas online desenvolvido como parte de um projeto acadêmico, utilizando PHP, HTML e CSS. O sistema permite o gerenciamento de usuários, cadastro e exibição de anúncios de produtos, incluindo upload e armazenamento de imagens.

---

## Funcionalidades Principais

- **Autenticação e Gerenciamento de Usuários**  
  - `login.php`: Permite login de usuários cadastrados.  
  - `logout.php`: Encerra a sessão do usuário.  
  - `cadastrar.php`: Cadastro de novos usuários.  
  - `gerenciarUsuario.php`: Permite que usuários e administradores gerenciem suas informações.

- **Gestão de Anúncios e Imagens**  
  - Interação com as tabelas `anuncios` e `fotos` para criar, exibir e gerenciar produtos.  
  - Imagens dos anúncios são armazenadas na pasta `uploads/`.

- **Menus Dinâmicos**  
  - `menuPrincipal.php`: Menu principal estilizado via `style.css`.  
  - `menuAdm.php`: Menu específico para administradores.  
  - `menuUser.php`: Menu para usuários comuns.

- **Configuração e Banco de Dados**  
  - `config.php`: Configurações essenciais do sistema, incluindo conexão com o banco.  
  - `sistema.sql`: Script para criação das tabelas `usuario`, `fotos` e `anuncios` no MySQL.

---

## Tecnologias Utilizadas

- **Backend:** PHP  
- **Frontend:** HTML5 e CSS3  
- **Banco de Dados:** MySQL

---

## Estrutura do Banco de Dados (`sistema.sql`)

- **usuario:** Armazena informações dos usuários (login, senha, perfil, etc.).  
- **fotos:** Gerencia imagens dos anúncios, referenciando arquivos na pasta `uploads/`.  
- **anuncios:** Contém os detalhes dos produtos (título, descrição, preço), relacionados às fotos.

---

## Como Executar o Projeto

1. **Configurar o Servidor Web**  
   Instale um servidor com suporte a PHP e MySQL (ex: XAMPP, WAMP, MAMP).

2. **Criar a Base de Dados**  
   - Crie um banco de dados MySQL vazio.  
   - Importe o arquivo `sistema.sql` para criar as tabelas necessárias.

3. **Configurar o Sistema**  
   - Atualize o arquivo `config.php` com as credenciais corretas do seu banco de dados.

4. **Implantar os Arquivos**  
   - Copie todos os arquivos e pastas (incluindo `uploads/`) para o diretório raiz do servidor web (ex: `htdocs` no XAMPP).

5. **Acessar o Sistema**  
   - No navegador, acesse `http://localhost/nome_da_pasta_do_projeto/login.php`.

---

## Contato

Se desejar, você pode abrir uma issue ou me contactar para dúvidas e sugestões.

---

*Projeto desenvolvido para fins acadêmicos.*

