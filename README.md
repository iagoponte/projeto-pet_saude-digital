# Cartilha Inteligente

Plataforma de materiais educativos voltados à promoção da saúde, alimentação e qualidade de vida, desenvolvida com WordPress e Bedrock.

## Sobre o projeto

O **Cartilha Inteligente** é um projeto desenvolvido para organizar e disponibilizar materiais educativos em diferentes formatos, facilitando o acesso a conteúdos de educação em saúde.

A aplicação utiliza um tema WordPress próprio e um tipo de conteúdo personalizado para cadastrar, organizar e apresentar materiais educativos.

### Funcionalidades

- Catálogo de materiais educativos.
- Organização por tipos e temas.
- Cadastro de materiais por meio do painel administrativo.
- Suporte a documentos PDF, áudio, vídeo e jogos educativos.
- Exibição de recursos acessíveis, incluindo transcrições e legendas quando disponíveis.
- Interface responsiva para diferentes tamanhos de tela.

## Tecnologias utilizadas

| Tecnologia | Finalidade |
|---|---|
| WordPress | Sistema de gerenciamento de conteúdo (CMS) |
| Bedrock | Estrutura de projeto WordPress com Composer e configuração por ambiente |
| PHP-FPM | Execução da aplicação PHP |
| Nginx | Servidor web e encaminhamento de requisições PHP |
| MariaDB 11.4 | Banco de dados relacional |
| Docker Compose | Orquestração do ambiente local |
| Composer | Gerenciamento de dependências PHP |
| Git | Controle de versão e colaboração |

## Arquitetura

A aplicação é executada localmente por meio de três serviços principais:

1. **Nginx:** recebe as requisições HTTP na porta `8000` e encaminha as requisições PHP para o PHP-FPM.
2. **PHP-FPM:** executa o WordPress e os componentes do projeto.
3. **MariaDB:** armazena os dados persistentes da aplicação.

O Bedrock organiza o projeto e gerencia o WordPress Core por meio do Composer.

### Fluxo de requisição

```text
Navegador
    |
    | HTTP :8000
    v
  Nginx
    |
    | FastCGI :9000
    v
  PHP-FPM
    |
    +----> WordPress / Bedrock
    |
    +----> MariaDB
```

Os serviços se comunicam por meio da rede Docker `cartilha_network`.

## Pré-requisitos

Para executar o ambiente local, instale:

- [Git](https://git-scm.com/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- WSL2 com uma distribuição Linux, caso esteja utilizando Windows com WSL2.

O Docker Desktop deve estar em execução e, no Windows com WSL2, integrado à distribuição Linux utilizada.

Não é necessário instalar PHP, Composer, Nginx ou MariaDB diretamente no sistema hospedeiro para executar o ambiente pelo Docker Compose.

## Instalação e configuração

### 1. Clonar o repositório

Clone o repositório Git do projeto e entre na pasta criada:

```bash
git clone <URL_DO_REPOSITORIO>
cd cartilha-inteligente
```

Substitua `<URL_DO_REPOSITORIO>` pela URL real do repositório.

### 2. Criar o arquivo de ambiente

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

O arquivo `.env` contém as configurações locais da aplicação e não deve ser versionado.

Edite-o:

```bash
nano .env
```

Configure as variáveis do banco de dados:

```dotenv
DB_NAME='cartilha_inteligente'
DB_USER='cartilha_user'
DB_PASSWORD='SENHA_FORTE_DO_BANCO'
DB_HOST='db'
DB_PREFIX='wp_'
DB_ROOT_PASSWORD='SENHA_FORTE_DO_ROOT'
```

Substitua os valores de senha por valores fortes, aleatórios e exclusivos. As senhas devem ser diferentes entre o usuário da aplicação e o administrador do banco.

Gere valores aleatórios, por exemplo:

```bash
openssl rand -base64 32
```

O comando pode ser executado separadamente para cada senha.

O arquivo também precisa conter as variáveis `WP_ENV`, `WP_HOME`, `WP_SITEURL` e as chaves e salts de autenticação do WordPress. Para desenvolvimento local, mantenha:

```dotenv
WP_ENV='development'
WP_HOME='http://localhost:8000'
WP_SITEURL="${WP_HOME}/wp"
```

Substitua os valores de exemplo das chaves e salts por valores aleatórios exclusivos para o seu ambiente.

**Importante:** o `.env.example` contém valores ilustrativos. Não utilize suas senhas de exemplo em ambientes compartilhados ou de produção.

### 3. Construir e iniciar os serviços

Na raiz do projeto, execute:

```bash
docker compose config --quiet
docker compose up -d --build
```

O primeiro comando valida a configuração do Docker Compose. O segundo constrói a imagem PHP e inicia os serviços.

Na primeira inicialização, a instalação das dependências Composer pode levar alguns minutos.

### 4. Verificar os serviços

Execute:

```bash
docker compose ps
```

O resultado deve mostrar os serviços `php`, `nginx` e `db` em execução. O banco de dados deve apresentar o estado `healthy`.

Para consultar os registros dos serviços:

```bash
docker compose logs --tail=100
```

### 5. Acessar a aplicação

Após a inicialização, acesse:

- **Site:** http://localhost:8000
- **Painel administrativo:** http://localhost:8000/wp/wp-admin/

As credenciais de acesso ao painel WordPress são definidas durante a instalação inicial e são diferentes das credenciais do banco de dados.

## Estrutura do projeto

```text
cartilha-inteligente/
├── config/                     # Configurações do Bedrock
├── docker/
│   ├── Dockerfile              # Imagem PHP-FPM
│   └── nginx/
│       └── default.conf        # Configuração do Nginx
├── web/
│   ├── app/
│   │   ├── mu-plugins/         # Funcionalidades personalizadas
│   │   ├── themes/
│   │   │   └── cartilha-inteligente/
│   │   │                         # Tema próprio
│   │   └── uploads/            # Arquivos enviados ao WordPress
│   ├── wp/                     # WordPress Core gerenciado pelo Composer
│   └── wp-config.php           # Configuração de entrada do Bedrock
├── composer.json               # Dependências e scripts Composer
├── composer.lock               # Versões resolvidas das dependências
├── docker-compose.yml          # Serviços e volumes Docker
├── .env.example                # Modelo de variáveis de ambiente
├── .gitignore                  # Arquivos excluídos do Git
└── README.md                   # Documentação do projeto
```

A estrutura acima representa os diretórios principais. Outros arquivos e diretórios do projeto podem existir além dos apresentados.

## Desenvolvimento

### Tema WordPress

O tema personalizado está localizado em:

```text
web/app/themes/cartilha-inteligente/
```

É nesse diretório que devem ser feitas as alterações de apresentação, templates, estilos e demais componentes do tema.

### Funcionalidades personalizadas

As funcionalidades específicas do projeto, incluindo o tipo de conteúdo para materiais educativos e seus campos, são implementadas no MU-plugin localizado em:

```text
web/app/mu-plugins/cartilha-content.php
```

Plugins instalados pelo Composer e MU-plugins instalados como dependências podem ter estruturas próprias. Consulte o `composer.json` antes de modificar componentes gerenciados pelo Composer.

### Comandos Docker úteis

Parar os serviços:

```bash
docker compose stop
```

Iniciar novamente os serviços:

```bash
docker compose start
```

Reiniciar os serviços:

```bash
docker compose restart
```

Reconstruir a imagem PHP:

```bash
docker compose build php
docker compose up -d
```

Acompanhar os logs:

```bash
docker compose logs -f
```

Validar a configuração do Nginx:

```bash
docker compose exec nginx nginx -t
```

**Atenção:** evite `docker compose down -v` durante o desenvolvimento. A opção `-v` remove os volumes nomeados associados ao projeto e pode causar perda de dados.

## Composer, lint e testes

O projeto utiliza Composer para gerenciar suas dependências PHP.

O arquivo `composer.json` define os seguintes scripts:

```bash
composer lint
composer lint:fix
composer test
```

- `composer lint`: verifica o padrão de código usando Laravel Pint.
- `composer lint:fix`: aplica correções de formatação.
- `composer test`: executa os testes definidos com Pest.

Para executar esses comandos no contêiner PHP:

```bash
docker compose exec php composer lint
docker compose exec php composer lint:fix
docker compose exec php composer test
```

Os testes dependem de estarem implementados e configurados no projeto. Um comando executado com sucesso não substitui testes funcionais completos da aplicação.

## Git e colaboração

O Git é utilizado para versionar o código e compartilhar alterações entre os integrantes da equipe.

### Fluxo de trabalho sugerido

Atualize a branch principal:

```bash
git switch master
git pull
```

Crie uma branch para sua tarefa:

```bash
git switch -c feat/nome-da-funcionalidade
```

Faça as alterações e revise-as:

```bash
git status
git diff
git diff --check
```

Registre as alterações:

```bash
git add <arquivos-alterados>
git commit -m "feat: descreve a funcionalidade"
```

Envie a branch para o repositório remoto:

```bash
git push -u origin feat/nome-da-funcionalidade
```

O nome `master` deve ser ajustado caso o repositório remoto utilize outra branch principal. Prefira integrar as alterações por meio de Pull Requests ou do processo de revisão definido pela equipe.

### O que não deve ser enviado ao Git

Não versione:

- O arquivo `.env` com credenciais reais.
- Senhas, tokens ou chaves privadas.
- Backups contendo dados do banco.
- Arquivos de log.
- Dependências instaladas localmente, como `vendor/`, quando excluídas pelo `.gitignore`.
- Arquivos de upload do WordPress, salvo quando houver uma decisão explícita da equipe de versionar algum recurso específico.

O arquivo `.env.example` deve permanecer no repositório, desde que contenha somente valores ilustrativos.

## Persistência e compartilhamento de dados

O Docker Compose utiliza volumes nomeados para:

- `mariadb_data`: arquivos do banco MariaDB.
- `wordpress_core`: arquivos do WordPress Core.
- `bedrock_vendor`: dependências Composer.

O diretório `web/app/uploads/` é montado a partir do projeto local e não é incluído no Git conforme a configuração atual.

Consequentemente, clonar o repositório não garante a recuperação automática do banco de dados, dos materiais cadastrados ou dos arquivos enviados ao WordPress.

A equipe deve definir um procedimento para compartilhar dados de demonstração e conteúdos necessários ao desenvolvimento, sem distribuir credenciais privadas ou dados pessoais indevidamente.

### Backup do banco

Para exportar o banco em um ambiente local:

```bash
umask 077
docker compose exec db mariadb-dump -u root -p \
  --single-transaction --routines --triggers cartilha_inteligente \
  > ../cartilha-inteligente-backup.sql
```

O comando solicitará a senha de `root`. O arquivo será criado no diretório pai do projeto.

Mantenha backups fora do repositório e em local seguro. Um backup do banco não inclui automaticamente os arquivos de upload; estes precisam ser copiados separadamente.

## Solução de problemas

### O site não abre

Verifique o estado dos serviços:

```bash
docker compose ps
docker compose logs --tail=100 nginx php db
```

### O banco não fica saudável

Consulte os logs:

```bash
docker compose logs --tail=100 db
```

Confirme se as variáveis do banco no `.env` correspondem às credenciais existentes. Alterar as variáveis de ambiente depois que o MariaDB já foi inicializado não altera automaticamente as senhas dos usuários existentes.

### Alterações no Nginx não funcionam

Valide e recarregue a configuração:

```bash
docker compose exec nginx nginx -t
docker compose exec nginx nginx -s reload
```

### Reconstruir o ambiente

Quando houver alterações no Dockerfile ou nas dependências incluídas na imagem:

```bash
docker compose up -d --build
```

Não remova os volumes para tentar corrigir problemas sem antes identificar a causa e garantir um backup.

## Segurança

- Nunca compartilhe o `.env` com credenciais reais.
- Utilize senhas fortes e exclusivas.
- Não publique backups com dados sensíveis.
- Mantenha as dependências e imagens atualizadas de forma controlada.
- Antes de hospedar publicamente, revise as configurações de produção, HTTPS, backups, permissões e proteção dos dados.

O ambiente descrito neste documento é destinado ao desenvolvimento local. A implantação em produção requer configurações adicionais.

## Licença

Consulte o arquivo [`LICENSE.md`](LICENSE.md) para conhecer os termos de licenciamento do repositório e de seus componentes.
