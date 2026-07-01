# 🔒 Política de Segurança - SinAgro

**Última atualização:** 17 de abril de 2026  
**Versão:** 1.0

---

## 📋 Índice
1. [Versões Suportadas](#versões-suportadas)
2. [Reportando uma Vulnerabilidade](#reportando-uma-vulnerabilidade)
3. [Processo de Resposta](#processo-de-resposta)
4. [Práticas de Segurança](#práticas-de-segurança)
5. [Divulgação Responsável](#divulgação-responsável)
6. [Recompensas](#recompensas)
7. [Contato](#contato)

---

## 📌 Versões Suportadas

Abaixo estão as versões do SinAgro que atualmente recebem suporte com atualizações de segurança:

| Versão | Suportada | Observações |
|--------|-----------|-------------|
| 1.0.x  | ✅ Sim    | Versão estável atual |
| 0.x.x  | ❌ Não    | Versões de desenvolvimento |

> ⚠️ **Recomendação:** Sempre utilize a versão mais recente disponível para garantir as correções de segurança mais atualizadas.

---

## 🚨 Reportando uma Vulnerabilidade

### Como Reportar

**ATENÇÃO:** Para reportar vulnerabilidades de segurança, **NÃO abra uma Issue pública**. Isso pode expor o problema antes da correção.

**Use um dos canais abaixo:**

1. **📧 Email Oficial:** `sinagro-security@unasp.edu.br`
   - Assunto: `[VULNERABILIDADE] - [Descrição Resumida]`
   - Exemplo: `[VULNERABILIDADE] - SQL Injection no formulário de login`

2. **🔐 GitHub Security Advisory**
   - Acesse: `https://github.com/imenesyasmin-falha/sinagro/security/advisories/new`
   - Preencha o formulário com detalhes da vulnerabilidade

3. **💬 Contato Direto**
   - Membro da equipe de desenvolvimento
   - Professor orientador: Samuel Elias

### ⏱️ Prazo de Resposta

| Etapa | Prazo | Descrição |
|-------|-------|-----------|
| ⏳ Confirmação de Recebimento | 24 horas | Confirmamos que recebemos seu relato |
| 🔍 Análise Inicial | 48 horas | Avaliamos a validade e impacto da vulnerabilidade |
| 🛠️ Desenvolvimento da Correção | 5-7 dias úteis | Desenvolvemos e testamos o patch |
| 🚀 Lançamento da Correção | 10 dias úteis | Publicamos a versão corrigida |

---

## 🔄 Processo de Resposta

### 1️⃣ Triagem (24-48h)
```mermaid
graph LR
    A[Relato Recebido] --> B{Validação}
    B -->|Válido| C[Classificar Severidade]
    B -->|Inválido| D[Feedback ao Reportante]
    C --> E[Atribuir Responsável]
    E --> F[Iniciar Correção]
