# ⚡ Zyrtex SaaS — Gestão Inteligente para Assistências Técnicas & Oficinas

<p align="center">
  <img src="https://raw.githubusercontent.com/nyshimura/zyrtex/main/public/assets/img/logo.png" alt="Zyrtex Logo" width="120" onerror="this.style.display='none'">
</p>

<p align="center">
  <strong>Plataforma Multi-Tenant Server-Side de Alta Performance para Assistências Técnicas, Celulares, Informática e Centros Automotivos.</strong><br>
  <em>Arquitetura leve em PHP 8.2+ puro, zero atrito, emissão ágil de OS, PDV Touch-Screen, QR Codes térmicos e integração nativa com Mercado Pago &amp; Google AdSense.</em>
</p>

<p align="center">
  <a href="https://github.com/nyshimura/zyrtex/releases"><img src="https://img.shields.io/badge/versão-1.2.0-indigo.svg?style=flat-square" alt="Versão"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2%2B-blue.svg?style=flat-square" alt="PHP"></a>
  <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/MySQL-8.0%2B-orange.svg?style=flat-square" alt="MySQL"></a>
  <a href="https://tailwindcss.com/"><img src="https://img.shields.io/badge/TailwindCSS-v3-38bdf8.svg?style=flat-square" alt="Tailwind"></a>
  <a href="https://www.mercadopago.com.br/developers"><img src="https://img.shields.io/badge/Mercado_Pago-Pix_Automático-009ee3.svg?style=flat-square" alt="Mercado Pago"></a>
  <a href="#licença"><img src="https://img.shields.io/badge/licença-BSL_1.1_%2F_Comercial-emerald.svg?style=flat-square" alt="Licença"></a>
</p>

---

## 📖 Visão Geral

O **Zyrtex** é um ecossistema SaaS completo desenvolvido sob a premissa de **Zero Atrito e Alta Performance**. Enquanto sistemas concorrentes dependem de frameworks pesados, SPAs lentas em conexões 3G/4G e custos proibitivos de servidor para o lojista, o Zyrtex adota uma arquitetura **Server-Side Rendering (SSR)** em PHP moderno.

O modelo de negócio foi planejado para crescimento exponencial no Brasil:
1. **Plano Gratuito Ilimitado (Freemium):** Sem limite de ordens de serviço, viabilizado por publicidade programática (**Google AdSense**) e doações voluntárias via Pix.
2. **Plano com Domínio Próprio:** R$ 197/ano para conexões de domínios customizados (`www.suaoficina.com.br`) com supressão total de anúncios.
3. **Monetização de Serviços Extra:** Micro-taxas automatizadas via Pix Mercado Pago para alterações de rota/slug ou readequação de nicho de bancada.

---

## 🚀 Principais Funcionalidades

### 🏬 1. Multi-Tenant Nativo & Segmentação por Nicho
- **Isolamento Lógico:** Banco de dados seguro com separação rigorosa por `tenant_id`.
- **Roteamento Híbrido:** Suporte tanto a subdomínios (`oficina.zyrtex.com.br`), parâmetros de desenvolvimento (`localhost/zyrtex?tenant=slug`) e domínios próprios (`www.minhaoficina.com.br`).
- **4 Nichos Especializados:**
  - 📱 **Smartphones & Tablets:** Controle de IMEI, padrão/senha de desenho de tela, saúde de bateria e checklist de touch, câmeras e conectividade.
  - 💻 **PCs & Notebooks:** Número de série/Service Tag, processador, RAM, SSD/HD, carregador e teste de cooler/portas.
  - 🚗 **Oficinas Mecânicas & Centros Automotivos:** Placa, Chassi, Quilometragem (KM), nível de combustível e checklist de freios/suspensão.
  - 🔌 **Eletrodomésticos & Linha Branca:** Voltagem (110V/220V/Bivolt), modelo, componentes e laudo de bancada.

### 🧾 2. Ordens de Serviço (OS) Dinâmicas & Totem PDV
- **Workflow Completo de Bancada:** Orçamento, Em Análise, Aguardando Peça, Aprovado, Em Reparo, Pronto para Retirada e Entregue.
- **Totem PDV Touch-Screen:** Venda rápida de balcão para acessórios, cabos, películas e peças com baixa automática no estoque e suporte a leitor de código de barras.
- **Diagnóstico com IA:** Motor inteligente de simplificação técnica que converte laudos complexos em explicações claras para o cliente final.
- **Histórico Antifraude:** Consulta pública de número de série/IMEI/Placa para verificar histórico de manutenções pretéritas e evitar recepção de aparelhos ilícitos.

### 🖨️ 3. Motor de Impressão Térmica de Alta Densidade
- **Bobinas Térmicas Padrão:** Compatibilidade nativa com impressoras térmicas de **80mm** e **58mm** (ESC/POS e browser print direto).
- **Mini Etiquetas Adesivas com QR Code:** Adesivos para colar diretamente na carcaça do aparelho ou para-brisa do veículo.
- **Termos Legais Integrados:** Comprovantes impressos com termos de garantia legal do **CDC (Art. 26 - 90 dias)**.

### 🌐 4. Portal do Cliente & Aprovação 1-Clique no WhatsApp
- **Transparência Total:** O cliente da oficina acompanha fotos do defeito, peças orçadas e status do reparo pelo link do WhatsApp.
- **Aprovação Digital com Registro de IP:** O cliente aprova o orçamento ou autoriza novos serviços com assinatura digital auditada conforme o Marco Civil da Internet.
- **Pagamento Integrado via Pix:** QR Code dinâmico do Mercado Pago gerado direto na tela do cliente com baixa imediata na OS.

### 💳 5. Monetização Automatizada & Mercado Pago
- **Cobranças Automáticas:** Geração de Pix Copia e Cola com QR Code dinâmico para planos e taxas do SaaS.
- **Webhooks Oficiais:** Notificações em tempo real com conciliação automática e auditoria de assinaturas.
- **Garantia de 7 Dias (CDC Art. 49):** Botão de estorno com 1 clique para o lojista, integrado à API de estornos do Mercado Pago.

### 🛡️ 6. Painel Master SuperAdmin
- **Blindagem de Segurança:** Rota do SuperAdmin 100% customizável via banco (`/superadmin` pode ser alterado para qualquer slug secreto).
- **Modo Shadow (Impersonation):** Acesso de suporte instantâneo à conta de qualquer lojista sem necessidade de saber a senha dele, com banner de aviso e registro em auditoria.
- **Dashboard Financeiro & MRR:** Monitoramento em tempo real de MRR, ARR, Faturamento do Mês, Ticket Médio, Taxa de Reembolso, Gráfico de evolução semestral e exportação de extrato em CSV.
- **Personalização Visual da Vitrine & Rodapé:** Controle total sobre os textos da Landing Page pública e dados de contato sem tocar no código.
- **Central de Chamados:** Atendimento integrado de tickets de suporte abertos pelos lojistas.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia | Justificativa |
|---|---|---|
| **Linguagem** | PHP 8.2+ (Puro / OOP) | Velocidade bruta, baixa pegada de memória e compatibilidade universal |
| **Banco de Dados** | MySQL 8.0+ / MariaDB 10.3+ | Transações ACID, índices otimizados e colunas JSON nativas |
| **Estilização** | Tailwind CSS v3 | Design moderno, responsivo e sistema de modo escuro/claro nativo |
| **Arquitetura** | Server-Side Rendering (SSR) | Carregamento abaixo de 100ms, sem peso de hydration em client-side |
| **Pagamentos** | Mercado Pago SDK / REST API | Pix dinâmico, cartões e conciliação instantânea via Webhooks |
| **Monetização** | Google AdSense | Banners programáticos não-invasivos no plano gratuito |

---

## 📦 Estrutura do Projeto

```text
zyrtex/
├── config/             # Configurações globais e banco de dados
├── crons/              # Rotinas agendadas (vencimento de planos, backups)
├── database/
│   └── schema.sql      # Schema completo do MySQL com índices
├── public/             # Raiz pública do servidor web
│   ├── .htaccess       # Regras de reescrita limpa de URLs (Apache)
│   ├── assets/         # Imagens, ícones e fontes estáticas
│   └── index.php       # Ponto de entrada (Front Controller) e Rotas
├── src/
│   ├── Controllers/    # Lógica de controle (Public, Admin, SuperAdmin)
│   ├── Core/           # Núcleo (Database, Router, Request, Response, Tenant, Auth, CSRF)
│   └── Services/       # Serviços (AIService, MercadoPagoService, QrCodeService)
├── views/              # Templates PHP limpos
│   ├── admin/          # Painel do Lojista (OS, PDV, Clientes, Configurações)
│   ├── layouts/        # Layouts mestres (saas, admin, superadmin, public)
│   ├── partials/       # Componentes reutilizáveis (toasts, cookies, ads, tips)
│   ├── public/         # Vitrines das lojas, portal do cliente e termos legais
│   ├── saas/           # Landing page de conversão do SaaS Zyrtex
│   └── superadmin/     # Painel Master (Tenants, Financeiro, Broadcast, Suporte)
├── .env.example        # Exemplo de configuração de ambiente
├── version.json        # Manifesto de versão e repositório oficial
└── README.md           # Documentação oficial do projeto
```

---

## ⚡ Instalação & Configuração Local (XAMPP / Windows / Linux)

### 1. Pré-requisitos
- **PHP 8.2 ou superior** com as extensões ativas no `php.ini`:
  `pdo_mysql`, `curl`, `mbstring`, `gd`, `openssl`.
- **Servidor Web:** Apache com módulo `mod_rewrite` ativado.
- **Banco de Dados:** MySQL 5.7+ ou MariaDB 10.3+.

### 2. Clonando o Repositório
```bash
cd c:/xampp/htdocs
git clone https://github.com/nyshimura/zyrtex.git
cd zyrtex
```

### 3. Configurando as Variáveis de Ambiente
Copie o arquivo `.env.example` para `.env`:
```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas credenciais:
```ini
APP_NAME=Zyrtex
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/zyrtex

DB_HOST=localhost
DB_PORT=3306
DB_NAME=zyrtex_db
DB_USER=root
DB_PASS=

# Credenciais Master do Mercado Pago (para receber doações e planos)
MP_ACCESS_TOKEN=APP_USR-seu-token-aqui
MP_PUBLIC_KEY=APP_USR-sua-chave-publica-aqui

# Inteligência Artificial para laudos de bancada (HuggingFace / Groq / OpenAI)
AI_PROVIDER=huggingface
AI_API_KEY=sua-chave-aqui
```

### 4. Criando o Banco de Dados
Importe o arquivo [`database/schema.sql`](database/schema.sql) no seu servidor MySQL:
```bash
mysql -u root -p < database/schema.sql
```

### 5. Primeiro Acesso
- **Landing Page Pública do SaaS:** `http://localhost/zyrtex/`
- **Cadastro de Oficina:** `http://localhost/zyrtex/cadastro`
- **Painel Master SuperAdmin:** `http://localhost/zyrtex/superadmin/login`
  - *Credenciais Padrão:* `superadmin@zyrtex.com.br` | Senha: `superadmin123`

---

## 🔄 Rotina de Atualização Automática via GitHub

O Zyrtex possui integração preparada para sincronização contínua com este repositório oficial (`https://github.com/nyshimura/zyrtex`). 

O arquivo canônico [`version.json`](version.json) compara a versão local em execução contra a última release publicada no GitHub:
```json
{
  "name": "zyrtex-saas",
  "version": "1.2.0",
  "build": 20261014,
  "github_repo": "nyshimura/zyrtex",
  "raw_version_url": "https://raw.githubusercontent.com/nyshimura/zyrtex/main/version.json"
}
```

---

## ⚖️ Conformidade Legal Brasileira (Compliance)

O Zyrtex foi projetado em estrita observância à legislação brasileira:
- **LGPD (Lei nº 13.709/18):** Clareza de papéis (Lojista como Controlador e Zyrtex como Operadora), consentimento explícito e banner de cookies configurável.
- **Marco Civil da Internet (Lei nº 12.965/14):** Guarda sigilosa de logs de acesso por 6 meses e auditoria de aprovações com IP.
- **Código de Defesa do Consumidor (Lei nº 8.078/90):** Garantia legal obrigatória de 90 dias nos comprovantes de serviço e botão de estorno de planos em até 7 dias corridos.

---

## 📄 Qual Licença Utilizar no GitHub? (Recomendação Estratégica)

Como o **Zyrtex** é um produto **SaaS comercial** que gera receita via taxas de planos, micro-transações e AdSense, a escolha da licença do repositório no GitHub deve ser estratégica para proteger seu modelo de negócio:

### 🏆 Opção Recomendada: BSL 1.1 (Business Source License) ou Source-Available
- **Por que usar:** O código permanece aberto para inspeção, auditoria, transparência para clientes e portfólio no seu GitHub, mas **impede legalmente que terceiros ou empresas concorrentes baixem o sistema, subam em um servidor e comercializem o Zyrtex como concorrente direto da sua plataforma**.
- **Projetos consagrados que utilizam:** *Sentry, MariaDB, CockroachDB e HashiCorp*.
- **Como funciona:** Uso livre para fins de teste, estudo ou auto-hospedagem interna de uma única loja, mas restrição expressa para exploração comercial como SaaS multi-tenant concorrente.

### 🔒 Opção Alternativa: Proprietária / All Rights Reserved
- Se você não deseja conceder permissão de uso livre para ninguém além dos seus próprios clientes, adicione uma licença clássica de software proprietário:
  > *"Copyright © 2026 Nyshimura. Todos os direitos reservados. O código-fonte é disponibilizado publicamente apenas para fins de referência e auditoria. É estritamente proibida a cópia, modificação, redistribuição ou exploração comercial deste software sem autorização prévia por escrito."*

### ⚠️ Por que NÃO recomendamos MIT ou Apache 2.0 para este projeto?
- Licenças excessivamente permissivas (como MIT ou Apache 2.0) autorizam qualquer indivíduo a clonar todo o repositório, trocar a logo e vender assinaturas para assistências técnicas sem pagar nada a você e de forma 100% legal.

---

<p align="center">
  Desenvolvido com excelência por <strong><a href="https://github.com/nyshimura">Nyshimura</a></strong> &bull; Zyrtex SaaS
</p>
