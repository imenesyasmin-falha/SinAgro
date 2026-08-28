<div align="center">

![SinAgro Logo](https://img.icons8.com/color/96/000000/wheat.png)

# 🌾 SinAgro - Sistema de Gestão Rural Modular

**Solução completa para gestão de pequenas propriedades rurais**

[![Status do Projeto](https://img.shields.io/badge/status-em%20desenvolvimento-yellow?style=for-the-badge)](https://github.com/imenesyasmin-falha/sinagro)
[![Versão](https://img.shields.io/badge/version-1.0.0-blue?style=for-the-badge)](https://github.com/imenesyasmin-falha/sinagro/releases)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Licença](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/imenesyasmin-falha/sinagro?style=for-the-badge)](https://github.com/imenesyasmin-falha/sinagro/issues)
[![GitHub stars](https://img.shields.io/github/stars/imenesyasmin-falha/sinagro?style=for-the-badge)](https://github.com/imenesyasmin-falha/sinagro/stargazers)

</div>

---

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Motivação](#-motivação)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias Utilizadas](#-tecnologias-utilizadas)
- [Arquitetura do Sistema](#-arquitetura-do-sistema)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Pré-requisitos](#-pré-requisitos)
- [Instalação e Configuração](#-instalação-e-configuração)
- [Banco de Dados](#-banco-de-dados)
- [Guia de Uso](#-guia-de-uso)
- [Módulos do Sistema](#-módulos-do-sistema)
- [Requisitos do Sistema](#-requisitos-do-sistema)
- [Regras de Negócio](#-regras-de-negócio)
- [Fluxos do Sistema](#-fluxos-do-sistema)
- [Segurança](#-segurança)
- [Contribuição](#-contribuição)
- [Equipe de Desenvolvimento](#-equipe-de-desenvolvimento)
- [Roadmap](#-roadmap)
- [Licença](#-licença)
- [Agradecimentos](#-agradecimentos)
- [Contato](#-contato)

---

## 🌾 Sobre o Projeto

O **SinAgro** (Sistema de Gestão Rural Modular) é uma plataforma web desenvolvida para atender as necessidades de **pequenos produtores rurais** brasileiros que ainda gerenciam suas propriedades de forma manual.

### 🎯 Objetivo

Transformar a gestão rural, substituindo anotações em cadernos, paredes de galpões e informações fragmentadas por um **sistema digital organizado, intuitivo e acessível**, permitindo que o produtor tome decisões baseadas em **dados concretos e confiáveis**.

### 💡 Problema que Resolve

> *"O produtor acorda cedo, separa ração, verifica o solo, acompanha a irrigação, negocia compra de insumos, vende parte da produção e, ao final do dia, sabe que trabalhou muito, mas não consegue afirmar com precisão se ganhou ou perdeu dinheiro."*

O SinAgro elimina essa incerteza ao fornecer:

- 📊 **Dados centralizados** em um único local
- 📈 **Indicadores claros** de desempenho
- 🔄 **Histórico completo** das operações
- 📱 **Acesso facilitado** via web (celular, tablet ou computador)

---

## 🧠 Motivação

### O Problema Real

Em muitas pequenas propriedades rurais brasileiras:

| Problema | Consequência |
|----------|--------------|
| 📝 Anotações em cadernos de bolso | Dados perdidos ou ilegíveis |
| 🧠 Gestão baseada em memória | Decisões imprecisas |
| 📄 Papéis avulsos | Informações desorganizadas |
| ❌ Ausência de histórico | Impossibilidade de análise |
| 💰 Controle financeiro precário | Dificuldade em saber se há lucro |

### A Solução SinAgro

| Benefício | Como o Sistema Ajuda |
|-----------|---------------------|
| ✅ **Organização** | Todas as informações em um único lugar |
| ✅ **Análise** | Indicadores e gráficos para tomada de decisão |
| ✅ **Histórico** | Registro completo de todas as operações |
| ✅ **Controle** | Gestão financeira, estoque e produção |
| ✅ **Acessibilidade** | Interface simples e responsiva |

---

## ✨ Funcionalidades

### 📊 Módulo Base

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| 🔐 **Cadastro de Usuários** | Criação de conta com nome, e-mail e senha | ✅ Concluído |
| 🔑 **Login/Logout** | Autenticação segura com sessão | ✅ Concluído |
| 📧 **Recuperação de Senha** | Fluxo controlado de redefinição | 🔄 Em desenvolvimento |
| 📊 **Dashboard** | Painel com indicadores e gráficos | 🔄 Em desenvolvimento |
| 📝 **Logs do Sistema** | Registro de operações sensíveis | 🔄 Em desenvolvimento |

---

### 🏠 Propriedades

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| ➕ **Cadastrar Propriedade** | Registrar fazendas, sítios ou chácaras | ✅ Concluído |
| ✏️ **Editar Propriedade** | Atualizar dados da propriedade | ✅ Concluído |
| 🗑️ **Excluir Propriedade** | Remoção lógica com auditoria | ✅ Concluído |
| 📍 **Áreas Internas** | Cadastro de setores/talhões | 🔄 Em desenvolvimento |

---

### 🌱 Plantação/Culturas

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| 🌾 **Cadastrar Culturas** | Registrar tipos de plantio | ✅ Concluído |
| 🔄 **Ciclos de Plantio** | Abertura e encerramento de ciclos | 🔄 Em desenvolvimento |
| 💧 **Insumos** | Registro de aplicações | 🔄 Em desenvolvimento |
| 📦 **Colheita** | Registro de produtividade e perdas | 🔄 Em desenvolvimento |

---

### 💰 Financeiro

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| 💵 **Lançar Receitas** | Registrar entradas de dinheiro | 🔄 Em desenvolvimento |
| 💳 **Lançar Despesas** | Registrar saídas com categorias | 🔄 Em desenvolvimento |
| 📊 **Extrato Financeiro** | Consulta por período | 🔄 Em desenvolvimento |
| 📈 **Fluxo de Caixa** | Análise de movimentações | 🔄 Em desenvolvimento |

---

### 📦 Estoque

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| 📦 **Cadastrar Itens** | Insumos, sementes, rações | ✅ Concluído |
| 📤 **Entradas/Saídas** | Movimentação do estoque | 🔄 Em desenvolvimento |
| ⚠️ **Alertas** | Estoque crítico | 🔄 Em desenvolvimento |
| 📊 **Relatórios** | Consumo e necessidade | 🔄 Em desenvolvimento |

---

### 🔧 Equipamentos

| Funcionalidade | Descrição | Status |
|----------------|-----------|--------|
| 🚜 **Cadastrar Máquinas** | Tratores, implementos, ferramentas | ✅ Concluído |
| 🛠️ **Manutenções** | Preventivas e corretivas | 🔄 Em desenvolvimento |
| 📊 **Custos** | Integração com financeiro | 🔄 Em desenvolvimento |
| 📋 **Status** | Disponível, manutenção, inativo | 🔄 Em desenvolvimento |

---

## 🛠️ Tecnologias Utilizadas

### Backend

┌─────────────────────────────────────────────────────────────┐
│ 🖥️ BACKEND │
├─────────────────────────────────────────────────────────────┤
│ • PHP 8.0+ - Linguagem principal │
│ • MySQL 8.0+ - Banco de dados relacional │
│ • PDO - Prepared statements para segurança │
│ • MVC - Arquitetura Model-View-Controller │
│ • Composer - Gerenciador de dependências │
└─────────────────────────────────────────────────────────────┘

---

### FrontEnd

┌─────────────────────────────────────────────────────────────┐
│ 🎨 FRONTEND │
├─────────────────────────────────────────────────────────────┤
│ • HTML5 - Estrutura semântica │
│ • CSS3 - Estilização com Flexbox/Grid │
│ • JavaScript - Interatividade e validações │
│ • Chart.js - Gráficos e visualizações │
│ • Responsivo - Mobile-first design │
└─────────────────────────────────────────────────────────────┘

---

### Ferramentas e DevOps

┌─────────────────────────────────────────────────────────────┐
│ 🛠️ FERRAMENTAS │
├─────────────────────────────────────────────────────────────┤
│ • Git - Versionamento de código │
│ • GitHub - Repositório e colaboração │
│ • Docker - Containerização (opcional) │
│ • phpMyAdmin - Gerenciamento do banco de dados │
│ • VS Code - IDE principal │
└─────────────────────────────────────────────────────────────┘

---

## 🏗️ Arquitetura do Sistema

### Diagrama de Arquitetura

┌─────────────────────────────────────────────────────────────────────────────┐
│ CLIENTE (NAVEGADOR) │
│ ┌─────────────────┐ ┌─────────────────┐ ┌──────────────────────────┐ │
│ │ Desktop │ │ Tablet │ │ Smartphone │ │
│ │ (1920x1080) │ │ (768x1024) │ │ (360x640) │ │
│ └─────────────────┘ └─────────────────┘ └──────────────────────────┘ │
└──────────────────────────────────┬──────────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ FRONTEND (HTML/CSS/JS) │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ INTERFACE DO USUÁRIO │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ Login │ Dashboard │ Propriedades │ Plantação │ Animais │ │
│ ├─────────┼────────────┼────────────────┼─────────────┼────────────┤ │
│ │ Financ. │ Estoque │ Equipamentos │ Relatórios │ Config. │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────┬──────────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ BACKEND (PHP 8.0+) │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ CONTROLLERS │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ AuthController │ PropertyController │ CropController │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ AnimalController │ FinanceController │ StockController │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ EquipmentController │ ReportController │ LogController │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ MODELS (ORM/DAO) │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ User │ Property │ Area │ Crop │ Animal │ Stock │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ Equipment │ Maintenance │ Transaction │ Log │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ VALIDAÇÕES E REGRAS DE NEGÓCIO │ │
│ ├─────────────────────────────────────────────────────────────────────┤ │
│ │ • Validação de dados • Regras de integridade • Sanitização │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────┬──────────────────────────────────────────┘
│
▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ BANCO DE DADOS (MySQL 8.0+) │
│ ┌─────────────────────────────────────────────────────────────────────┐ │
│ │ 🗄️ SCHEMA SINAGRO │ │
│ ├──────────────┬──────────────┬──────────────┬──────────────────────┤ │
│ │ usuarios │ propriedades │ areas │ culturas │ │
│ ├──────────────┼──────────────┼──────────────┼──────────────────────┤ │
│ │ ciclos │ insumos │ colheitas │ animais │ │
│ ├──────────────┼──────────────┼──────────────┼──────────────────────┤ │
│ │ lotes │ eventos │ estoque │ movimentos │ │
│ ├──────────────┼──────────────┼──────────────┼──────────────────────┤ │
│ │ financeiro │ categorias │ equipamentos│ manutencoes │ │
│ ├──────────────┼──────────────┼──────────────┼──────────────────────┤ │
│ │ logs │ permissoes │ ... │ ... │ │
│ └──────────────┴──────────────┴──────────────┴──────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘

---

### Padrão MVC (Model-View-Controller)

┌─────────────────────────────────────────────────────────────────────────────┐
│ FLUXO MVC - SINAGRO │
├─────────────────────────────────────────────────────────────────────────────┤
│ │
│ ┌──────────┐ 1. Requisição ┌────────────┐ │
│ │ USUÁRIO │ ──────────────────> │ ROTEADOR │ │
│ └──────────┘ └──────┬─────┘ │
│ │ │
│ │ 2. Roteia │
│ ▼ │
│ ┌────────────┐ │
│ │ CONTROLLER │ │
│ └──────┬─────┘ │
│ │ │
│ ┌──────────────────────┼──────────────────────┐ │
│ │ │ │ │
│ ▼ ▼ ▼ │
│ ┌─────────────────────┐ ┌─────────────────────┐ ┌─────────────────┐ │
│ │ 3. MODEL (DAO) │ │ 4. Regras de │ │ 5. VALIDAÇÃO │ │
│ │ • Busca dados no │ │ Negócio │ │ • Sanitização │ │
│ │ banco de dados │ │ • Cálculos │ │ • Validação │ │
│ │ • Insere/Atualiza │ │ • Lógica │ │ • Filtros │ │
│ └─────────────────────┘ └─────────────────────┘ └─────────────────┘ │
│ │ │
│ │ 6. Retorna dados │
│ ▼ │
│ ┌────────────┐ │
│ │ VIEW │ │
│ │ (Página) │ │
│ └──────┬─────┘ │
│ │ │
│ │ 7. Renderiza │
│ ▼ │
│ ┌────────────┐ │
│ │ USUÁRIO │ │
│ └────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
