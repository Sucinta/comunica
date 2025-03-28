# 📘️ Guia de Branches e Versionamento (Git Flow Simplificado)

## 🧱 Estrutura de Branches

| Branch        | Função                                              |
|---------------|-----------------------------------------------------|
| `main`        | Código estável, pronto para produção                |
| `develop`     | Integração de funcionalidades em desenvolvimento    |
| `feature/*`   | Desenvolvimento de novas funcionalidades/módulos    |
| `hotfix/*`    | Correções rápidas e críticas em produção            |
| `refactor/*`  | Refatorações e melhorias técnicas                   |

---

## 🗂️ Estrutura de Branches

Este projeto utiliza uma estrutura de branches simples e organizada para facilitar o desenvolvimento:

| Branch            | Finalidade                                                                 |
|-------------------|----------------------------------------------------------------------------|
| `main`            | Branch principal. Contém o código em produção. Apenas versões testadas e estáveis são enviadas para cá. |
| `develop`         | Branch de desenvolvimento contínuo. Todas as novas funcionalidades são integradas aqui antes de irem para produção. |
| `feature/sabium`  | Branch dedicada às integrações com o ERP Sabium. Todas as funcionalidades relacionadas a esse sistema ficam concentradas aqui. |

---

## 🔄 Fluxo de Trabalho com o ERP Sabium

1. Todas as funcionalidades relacionadas ao ERP Sabium são desenvolvidas dentro da branch `feature/sabium`.
2. Cada commit segue o padrão [Conventional Commits](https://www.conventionalcommits.org/).
3. Ao finalizar um conjunto de tarefas, é criado um **pull request com opção _squash_** para a branch `develop`.
4. Após testes e validação, a branch `develop` pode ser integrada à `main` para produção.

---

## ✅ Padrão de Commits

Este projeto segue o padrão [Conventional Commits](https://www.conventionalcommits.org/), que padroniza as mensagens de commit e facilita o entendimento do histórico.

| Tipo        | Descrição                                                                 |
|-------------|--------------------------------------------------------------------------|
| `feat`      | Nova funcionalidade adicionada ao sistema                                |
| `fix`       | Correção de bugs                                                         |
| `docs`      | Alterações na documentação (ex: README, comentários)                     |
| `style`     | Alterações visuais no código (espaços, identação, ponto e vírgula)       |
| `refactor`  | Refatoração de código sem alteração de comportamento                     |
| `perf`      | Melhorias de performance                                                 |
| `test`      | Adição ou alteração de testes                                            |
| `chore`     | Tarefas técnicas ou de manutenção (builds, configs, dependências)        |
| `revert`    | Reversão de um commit anterior                                           |
| `ci`        | Alterações em configurações de integração contínua (GitHub Actions, etc) |
| `build`     | Alterações que afetam o processo de build (ex: Webpack, Composer, etc)   |

---

### ✍️ Exemplo de Commit:

```bash
git commit -m "feat: sincroniza empresas com ERP Sabium"
```

---

## 🛠️ Criar uma funcionalidade

```bash
git checkout develop
git pull origin develop

git checkout -b feature/nome-da-feature
```

---

## 📂 Trabalhar na branch de feature

1. Faça os seus commits normalmente:
   ```bash
   git add .
   git commit -m "Implementa cadastro de produtos"
   ```

2. Ao final do desenvolvimento:
   ```bash
   git push origin feature/nome-da-feature
   ```

---

## 🔀 Mesclar uma feature na `develop`

```bash
git checkout develop
git pull origin develop

git merge feature/nome-da-feature
git push origin develop
```

> **Dica:** se quiser deletar a branch depois:
```bash
git branch -d feature/nome-da-feature       # local
git push origin --delete feature/nome-da-feature  # remoto
```

---

## 🚀 Subir para produção (mesclar `develop` na `main`)

```bash
git checkout main
git pull origin main

git merge develop
git push origin main
```

---

## 🔥 Correções rápidas em produção

```bash
git checkout main
git pull origin main

git checkout -b hotfix/corrige-bug-critico
# ... corrige ...
git commit -m "Corrige bug no login"
git push origin hotfix/corrige-bug-critico

git checkout main
git merge hotfix/corrige-bug-critico
git push origin main

git checkout develop
git merge hotfix/corrige-bug-critico
git push origin develop
```

---

## 📌 Convenção de nomes de branches

| Tipo       | Exemplo                            |
|------------|------------------------------------|
| Feature    | `feature/integracao-erp`           |
| Refactor   | `refactor/estrutura-filas`         |
| Hotfix     | `hotfix/ajuste-login`              |
| Testes     | `feature/teste-integracao-produto` |

---

Este guia serve como referência para manter consistência, organização e clareza no ciclo de vida do projeto. Sempre que possível, prefira criar um Pull Request para revisão antes de mesclar com `develop` ou `main`.

