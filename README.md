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

