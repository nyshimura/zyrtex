# ⚡ Zyrtex SaaS — Gestão Inteligente para Assistências Técnicas & Oficinas

<p align="center">
  <img src="https://raw.githubusercontent.com/nyshimura/zyrtex/refs/heads/main/public/assets/images/pwa-icon-512.png" alt="Zyrtex Logo" width="120" onerror="this.style.display='none'">
</p>

<p align="center">
  <strong>Plataforma Multi-Tenant Server-Side de Alta Performance para Assistências Técnicas, Celulares, Informática, Centros Automotivos e Eletrodomésticos.</strong><br>
  <em>Arquitetura leve em PHP 8.2+ puro, zero atrito, emissão ágil de OS, PDV Touch-Screen, QR Codes térmicos, Portal do Cliente seguro com Pix Multi-OS e integração nativa com Mercado Pago &amp; Google AdSense.</em>
</p>

<p align="center">
  <a href="https://github.com/nyshimura/zyrtex/releases"><img src="https://img.shields.io/badge/versão-2.2.0-indigo.svg?style=flat-square" alt="Versão"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2%2B-blue.svg?style=flat-square" alt="PHP"></a>
  <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/MySQL-8.0%2B-orange.svg?style=flat-square" alt="MySQL"></a>
  <a href="https://tailwindcss.com/"><img src="https://img.shields.io/badge/TailwindCSS-v3-38bdf8.svg?style=flat-square" alt="Tailwind"></a>
  <a href="https://www.mercadopago.com.br/developers"><img src="https://img.shields.io/badge/Mercado_Pago-Pix_Multi--OS-009ee3.svg?style=flat-square" alt="Mercado Pago"></a>
  <a href="#-pwa-progressive-web-app"><img src="https://img.shields.io/badge/PWA-Offline_Ready-purple.svg?style=flat-square" alt="PWA"></a>
  <a href="#-qual-licen%C3%A7a-utilizar-no-github-recomenda%C3%A7%C3%A3o-estrat%C3%A9gica"><img src="https://img.shields.io/badge/licença-BSL_1.1_%2F_Comercial-emerald.svg?style=flat-square" alt="Licença"></a>
</p>

---

## 📖 Visão Geral

O **Zyrtex** é um ecossistema SaaS completo desenvolvido sob a premissa de **Zero Atrito e Alta Performance**. Enquanto sistemas legados dependem de frameworks pesados, SPAs lentas em conexões móveis e custos proibitivos de infraestrutura, o Zyrtex adota uma arquitetura **Server-Side Rendering (SSR)** em PHP moderno com carregamento ultra-rápido.

O modelo de negócio foi planejado para crescimento exponencial:
1. **Plano Gratuito Ilimitado (Freemium):** Sem limite de ordens de serviço ou clientes, viabilizado por publicidade programática (**Google AdSense**), cards de produtos de balcão e doações voluntárias via Pix.
2. **Plano Anual Sem Anúncios (R$ 197/ano):** Supressão total de publicidade (vitrine, rastreio de OS e portal do cliente), com subdomínio exclusivo (`oficina.zyrtex.com.br`) e URL amigável (`zyrtex.com.br/oficina`).
3. **Monetização de Serviços Extra:** Micro-taxas automatizadas via Pix Mercado Pago para alterações de slug de URL ou readequação de nicho de bancada.
4. **Motor de Indicações Viral (Growth):** Ganho de meses de plano premium por indicação, com validação anti-fraude vinculada à primeira OS real paga pela oficina indicada.

---

## 🚀 Funcionalidades & Arquitetura Atualizada

### 🏬 1. Multi-Tenant Nativo & Segmentação por Nicho
- **Isolamento Lógico:** Banco de dados relacional com separação estrita por `tenant_id` em todas as tabelas e consultas.
- **Roteamento Híbrido:** Suporte simultâneo a subdomínios Wildcard (`oficina.zyrtex.com.br`), parâmetros de desenvolvimento local (`localhost/zyrtex?tenant=slug`) e rotas amigáveis (`zyrtex.com.br/oficina`).
- **4 Nichos Especializados:**
  - 📱 **Smartphones & Tablets:** Controle de IMEI, padrão/desenho de tela, saúde de bateria e checklist de touch, câmeras, biometria e conectividade.
  - 💻 **PCs & Notebooks:** Número de série/Service Tag, processador, RAM, SSD/HD, carregador e teste de cooler/portas.
  - 🚗 **Oficinas Mecânicas & Centros Automotivos:** Placa, Chassi, Quilometragem (KM), nível de combustível e checklist de freios/suspensão.
  - 🔌 **Eletrodomésticos & Linha Branca:** Voltagem (110V/220V/Bivolt), modelo, componentes e laudo de bancada.

### 🧾 2. Ordens de Serviço (OS) Dinâmicas & Totem PDV
- **Workflow Completo de Bancada:** Orçamento, Em Análise, Aguardando Peça, Aprovado, Em Reparo, Pronto para Retirada e Entregue.
- **Totem PDV Touch-Screen:** Venda rápida de balcão para acessórios, cabos, películas e peças com baixa automática no estoque e suporte a leitor de código de barras.
- **Diagnóstico Humanizado com IA:** Motor inteligente multi-provedor (Groq, OpenAI, HuggingFace, Gemini) que converte termos técnicos em explicações simples para o cliente.
- **Consulta Pública Antifraude:** Verificação pública de histórico por IMEI, Número de Série ou Placa para coibir receptação de aparelhos ilícitos.

### 🌐 3. Portal do Cliente Autenticado & Seguro *(Recente)*
- **Acesso do Cliente:** Login seguro via CPF (com ou sem máscara) ou E-mail + Senha.
- **Ativação & Primeiro Acesso:** Fluxo de ativação com token criptográfico de uso único enviado por E-mail ou WhatsApp.
- **Blindagem Anti-Impersonation:** O lojista no painel administrativo **não** acessa a conta pessoal do cliente; links administrativos direcionam estritamente para o rastreio público autenticado (`/rastreio?os=...`).
- **Aprovação Digital com Registro de IP:** O cliente aprova orçamentos e peças adicionais em 1 clique, com auditoria jurídica conforme o Marco Civil da Internet.

### 💳 4. Pagamento Pix por OS & Multi-OS Agrupado *(Recente)*
- **Seleção Dinâmica de OSs:** O cliente seleciona uma ou mais OSs pendentes através de checkboxes interativos, com soma de valores em tempo real.
- **Modos de Recebimento do Lojista:**
  - **Pix Manual (Chave Pix da Oficina):** Geração determinística de payload padrão EMV BR Code com cálculo matemático de CRC16 no próprio servidor.
  - **Mercado Pago Automático:** Conexão direta via Access Token da conta jurídica do lojista.
- **Reutilização de QR Code & Idempotência:** Chave de cache determinística (`tenant_id + os_ids + amount`) para não poluir o Mercado Pago com ordens pendentes duplicadas se o cliente abrir o código repetidamente.
- **Baixa Simultânea em Lote no Webhook:** Notificação do Mercado Pago com `external_reference: "os_payment:1,3"`, dando baixa atômica simultânea em todas as OSs selecionadas dentro de transação ACID no MySQL.
- **Monitoramento em Tempo Real (Polling sem F5):** Endpoint dedicado `GET /portal/status-pagamento` que detecta a aprovação bancária em tempo real e atualiza a tela do cliente na hora com banner de sucesso.

### 📢 5. Sistema de Publicidade Inteligente *(Recente)*
- **Plano Gratuito:** Exibição harmoniosa de banners Google AdSense e vitrine de produtos de balcão da própria oficina.
- **Plano Premium (R$ 197/ano):** Supressão total de publicidade na vitrine pública da oficina, na página de rastreio de OS e no Portal do Cliente.

### 📱 6. PWA (Progressive Web App) *(Recente)*
- **Instalável em Celulares e Desktops:** Manifesto PWA (`manifest.json`) e Service Worker (`sw.js`) configurados para oferecer experiência de aplicativo nativo sem taxas das lojas de apps.
- **Cache Offline:** Estratégia de cache inteligente para ícones, folhas de estilo e recursos essenciais.

### 🔐 7. Segurança Reforçada & 2FA Zyrtex Guard *(Recente)*
- **Autenticação em Dois Fatores (2FA):** Envio de código de 6 dígitos via e-mail transacional para login de lojistas e administradores.
- **Proteção contra Força Bruta:** Limite estrito de tentativas, rate limiting por IP e tempo de expiração de tokens.

### ✉️ 8. Motor de E-mails Transacionais & SMTP *(Recente)*
- **Disparo Transacional Nativo:** Suporte a SMTP global ou individual por oficina.
- **Editor Visual no SuperAdmin:** Templates HTML responsivos para Boas-vindas, Código 2FA, Ativação de Portal, Notificação de OS Pronta e Recuperação de Senha.

### 🖨️ 9. Motor de Impressão Térmica de Alta Densidade
- **Impressoras Térmicas 80mm e 58mm:** Saída otimizada para bobinas contínuas com suporte a corte de papel e browser print direto.
- **Mini Etiquetas Adesivas com QR Code:** Formato compacto para colar na carcaça do aparelho ou no para-brisa do veículo.
- **Termos Legais CDC:** Inclusão automática das cláusulas de garantia legal do Código de Defesa do Consumidor (**Art. 26 - 90 dias**).

### 🛡️ 10. Painel Master SuperAdmin
- **Rota Secreta Customizável:** Caminho de acesso do SuperAdmin configurável diretamente no banco de dados.
- **Modo Shadow (Impersonation):** Acesso de suporte instantâneo à conta de qualquer lojista sem necessidade da senha dele, com aviso visual persistente e log de auditoria.
- **Dashboard Financeiro & MRR:** Acompanhamento de MRR, ARR, Faturamento Mensal, Ticket Médio, Taxa de Churn/Reembolso e extrato em CSV.
- **Central de Chamados & Broadcast:** Suporte integrado e comunicados globais com banners nos painéis dos lojistas.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia | Detalhes & Finalidade |
|---|---|---|
| **Back-end** | PHP 8.2+ Puro (OOP / PSR) | Alta performance, tipagem estrita e zero dependência de frameworks pesados |
| **Banco de Dados** | MySQL 8.0+ / MariaDB 10.3+ | Transações ACID, índices otimizados, InnoDB e colunas JSON nativas |
| **Front-end** | HTML5 Semântico + Tailwind CSS v3 | Design moderno, responsivo, modo escuro/claro nativo e micro-interações |
| **Arquitetura** | Server-Side Rendering (SSR) | Carregamento abaixo de 100ms sem overhead de hydration em client-side |
| **App Mobile** | PWA (Service Worker + Web Manifest) | Instalável na tela inicial de Android, iOS e Windows |
| **Pagamentos** | Mercado Pago API + Pix EMV BR Code | Pix instantâneo, conciliação por Webhook, idempotência e baixa multi-OS |
| **E-mails** | SMTP Socket Nativo (MailService) | Templates HTML responsivos, 2FA, ativação e avisos de OS |
| **IA de Bancada** | Groq / OpenAI / HuggingFace / Gemini | Simplificação automática de diagnósticos técnicos para clientes |
| **Publicidade** | Google AdSense + Vitrine Interna | Monetização sustentável do plano freemium com supressão no plano pago |

---

## 📦 Estrutura de Pastas e Arquivos

```text
zyrtex/
├── config/                         # Configurações globais do sistema
│   ├── app.php                     # Parâmetros gerais da aplicação
│   ├── database.php                # Configuração de conexão PDO MySQL
│   ├── niches.php                  # Definições, campos e checklists dos 4 nichos
│   └── print_formats.php           # Dimensões e layouts de impressão térmica
│
├── crons/                          # Rotinas automáticas de segundo plano
│   └── purge_inactives.php         # Limpeza de dados temporários e planos expirados
│
├── database/                       # Estrutura do Banco de Dados
│   ├── schema.sql                  # Schema principal com todas as tabelas e índices
│   ├── seeds.sql                   # Dados padrão para desenvolvimento e testes
│   ├── migrate.php                 # Runner de migrações automáticas de banco
│   └── migrations/                 # Migrações incrementais do sistema
│       ├── add_banner_column.php
│       ├── add_email_module_and_2fa.php
│       ├── add_image_columns.php
│       └── add_whatsapp_message_column.php
│
├── public/                         # Raiz pública do servidor HTTP (DocumentRoot)
│   ├── index.php                   # Front Controller: Roteamento central e middlewares
│   ├── .htaccess                   # Regras de reescrita limpa de URLs (Apache)
│   ├── manifest.json               # Manifesto do Progressive Web App (PWA)
│   ├── sw.js                       # Service Worker com regras de cache offline
│   ├── assets/                     # Recursos visuais estáticos
│   │   └── images/                 # Ícones PWA (192px, 512px) e logotipo oficial
│   └── uploads/                    # Diretório local de logos e anexos de OS
│
├── src/                            # Código-fonte PHP orientado a objetos (PSR-4)
│   ├── autoload.php                # Autoloader nativo compatível com PSR-4
│   │
│   ├── Controllers/                # Controladores da aplicação
│   │   ├── Admin/                  # Painel de Gestão da Oficina
│   │   │   ├── ClientController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── PosController.php
│   │   │   ├── ReferralController.php
│   │   │   ├── ServiceOrderController.php
│   │   │   ├── SettingsController.php
│   │   │   ├── StockController.php
│   │   │   ├── SupportController.php
│   │   │   └── TeamController.php
│   │   │
│   │   ├── Public/                 # Rotas Públicas e Portal do Cliente
│   │   │   ├── AuthController.php       # Login, cadastro, 2FA e logout
│   │   │   ├── HomeController.php       # Landing page do SaaS e vitrine da oficina
│   │   │   ├── PortalController.php     # Portal do cliente, ativação e Pix multi-OS
│   │   │   ├── RecoveryController.php   # Recuperação de acesso
│   │   │   ├── TrackingController.php   # Rastreio público e aprovação de orçamento
│   │   │   └── WebhookController.php    # Webhooks oficiais do Mercado Pago
│   │   │
│   │   └── SuperAdmin/             # Painel Master da Plataforma SaaS
│   │       └── SuperAdminController.php # Métricas, MRR, tenants, e-mails e financeiro
│   │
│   ├── Core/                       # Núcleo estrutural do framework Zyrtex
│   │   ├── Audit.php               # Registro de logs de auditoria e segurança
│   │   ├── Auth.php                # Gerenciamento de sessões e permissões
│   │   ├── CSRF.php                # Proteção contra ataques Cross-Site Request Forgery
│   │   ├── Database.php            # Conexão PDO segura com queries preparadas
│   │   ├── Env.php                 # Leitor nativo de variáveis de ambiente (.env)
│   │   ├── Request.php             # Sanitização e captura de requisições HTTP
│   │   ├── Response.php            # Emissão de respostas HTML, JSON e redirects
│   │   ├── Router.php              # Despachante de rotas rápidas com suporte a regex
│   │   ├── Session.php             # Gerenciamento avançado de sessões PHP
│   │   ├── Tenant.php              # Identificação e isolamento multi-tenant
│   │   └── View.php                # Motor de renderização de templates com layouts
│   │
│   └── Services/                   # Serviços e integrações externas
│       ├── AIService.php           # Integração com IAs para laudos simplificados
│       ├── AutoUpdaterService.php  # Verificação contínua de releases no GitHub
│       ├── GoogleDriveService.php  # Armazenamento em nuvem Google Drive (BYOS)
│       ├── ImageCompressor.php     # Otimização de fotos de OS em WebP
│       ├── MailService.php         # Envio de e-mails transacionais via SMTP
│       ├── MercadoPagoService.php  # API Mercado Pago, Pix, idempotência e estornos
│       ├── PrintEngine.php         # Renderizador de impressão térmica e etiquetas
│       ├── QrCodeService.php       # Gerador de QR Codes de alta densidade
│       ├── ReferralService.php     # Motor do programa de indicações com anti-fraude
│       └── RemarketingService.php  # Automações de fidelização e avisos de retorno
│
├── views/                          # Camada de apresentação e templates HTML/PHP
│   ├── admin/                      # Telas administrativas da oficina
│   │   ├── dashboard.php
│   │   ├── settings.php
│   │   ├── clients/                # Listagem, cadastro, edição e perfil de cliente
│   │   ├── os/                     # Listagem, abertura e visualização completa de OS
│   │   ├── pos/                    # Totem de PDV rápido de balcão e cupom de venda
│   │   ├── referrals/              # Painel do programa de indicação da oficina
│   │   ├── stock/                  # Controle de produtos e peças com baixa de estoque
│   │   ├── support/                # Central de chamados com a equipe do SaaS
│   │   └── team/                   # Gestão de técnicos e níveis de acesso (RBAC)
│   │
│   ├── errors/                     # Páginas de erro customizadas (404, 500)
│   ├── layouts/                    # Layouts base (admin, pos, public, saas, superadmin)
│   ├── partials/                   # Componentes reutilizáveis
│   │   ├── adsense_slot.php        # Bloco de anúncios Google AdSense responsivo
│   │   ├── cookie_banner.php       # Banner de consentimento LGPD configurável
│   │   ├── flash_messages.php      # Notificações e toasts de sucesso/erro
│   │   ├── product_ad_card.php     # Vitrine de produtos em destaque da loja
│   │   ├── pwa_install_prompt.php  # Modal de incentivo à instalação do app PWA
│   │   └── tips_card.php           # Dicas de uso e boas práticas para o lojista
│   │
│   ├── print/                      # Modelos de impressão térmica e documental
│   │   ├── a4_document.php         # Relatório completo em folha A4
│   │   ├── sticker_labels.php      # Mini etiquetas adesivas com QR Code
│   │   ├── thermal_58mm.php        # Cupom térmico para impressoras de 58mm
│   │   └── thermal_80mm.php        # Cupom térmico para impressoras de 80mm
│   │
│   ├── public/                     # Telas públicas acessíveis aos clientes finais
│   │   ├── home.php                # Vitrine pública da assistência técnica
│   │   ├── login.php               # Login do lojista na plataforma
│   │   ├── portal.php              # Portal do cliente com extrato e Pix multi-OS
│   │   ├── recovery.php            # Redefinição de senha
│   │   ├── register.php            # Onboarding e cadastro de nova oficina
│   │   ├── terms.php               # Termos de Uso e Política de Privacidade LGPD
│   │   ├── tracking.php            # Rastreio de OS por QR Code com aprovação
│   │   ├── auth/two_factor.php     # Tela de validação do código 2FA
│   │   └── portal/activate.php     # Tela de primeiro acesso/definição de senha
│   │
│   ├── saas/                       # Landing Page institucional de conversão do Zyrtex
│   │   └── home.php
│   │
│   └── superadmin/                 # Telas do Painel Master
│       ├── broadcast.php           # Envio de avisos para todos os lojistas
│       ├── dashboard.php           # Métricas gerais de uso da plataforma
│       ├── financial.php           # Faturamento, MRR, ARR e extrato financeiro
│       ├── login.php               # Acesso seguro ao painel master
│       ├── settings.php            # Configurações globais do SaaS e AdSense
│       ├── tenants.php             # Gestão de oficinas, bloqueio e modo shadow
│       ├── tickets.php             # Atendimento a chamados de lojistas
│       └── emails/                 # Editor visual de templates transacionais
│
├── storage/                        # Armazenamento temporário de logs e arquivos
├── .env.example                    # Modelo de configuração de variáveis de ambiente
├── .gitignore                      # Regras de exclusão do controle de versão Git
├── .htaccess                       # Redirecionamento da raiz para /public
├── index.php                       # Roteador de fallback para ambientes locais
├── manifest.json                   # Manifesto PWA de raiz
├── sw.js                           # Service worker PWA de raiz
├── version.json                    # Controle de versão e atualização contínua
├── LICENSE                         # Termos de licença do software
└── README.md                       # Documentação técnica oficial
```

---

## ⚡ Instalação & Configuração Local (XAMPP / Apache / Nginx)

### 1. Pré-requisitos
- **PHP 8.2 ou superior** com extensões ativas no `php.ini`:
  `pdo_mysql`, `curl`, `mbstring`, `gd`, `openssl`.
- **Servidor Web:** Apache com `mod_rewrite` habilitado (ou Nginx).
- **Banco de Dados:** MySQL 8.0+ ou MariaDB 10.3+.

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

Edite o arquivo `.env` com os dados do seu ambiente:
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

# Credenciais Master do Mercado Pago (para receber planos e taxas do SaaS)
MP_ACCESS_TOKEN=APP_USR-seu-token-aqui
MP_PUBLIC_KEY=APP_USR-sua-chave-publica-aqui

# Inteligência Artificial para laudos de bancada (groq, openai, huggingface, gemini)
AI_PROVIDER=groq
AI_API_KEY=sua-chave-api-aqui

# Configuração SMTP para E-mails Transacionais e 2FA
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=seu-email@gmail.com
SMTP_PASS=sua-senha-de-app
SMTP_FROM=nao-responda@zyrtex.com.br
```

### 4. Inicializando o Banco de Dados & Migrações
Crie o banco de dados e execute o script de migração:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS zyrtex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root zyrtex_db < database/schema.sql
mysql -u root zyrtex_db < database/seeds.sql
php database/migrate.php
```

### 5. Acessos Iniciais no Ambiente Local
- **Landing Page do SaaS Zyrtex:** `http://localhost/zyrtex/`
- **Cadastro de Nova Oficina:** `http://localhost/zyrtex/cadastro`
- **Vitrine Pública da Oficina Demo:** `http://localhost/zyrtex/demo-assistencia`
- **Portal do Cliente (Mariana):** `http://localhost/zyrtex/portal` *(Login: `mariana@email.com` ou `12345678901` | Senha: `mariana123`)*
- **Painel Master SuperAdmin:** `http://localhost/zyrtex/superadmin/login`
  - *Credenciais Padrão:* `superadmin@zyrtex.com.br` | Senha: `superadmin123`

---

## 🔄 Rotina de Atualização Automática via GitHub

O Zyrtex conta com um serviço nativo de checagem contínua via [`AutoUpdaterService.php`](file:///c:/xampp/htdocs/zyrtex/src/Services/AutoUpdaterService.php) integrado ao repositório oficial [`https://github.com/nyshimura/zyrtex`](https://github.com/nyshimura/zyrtex).

O manifesto [`version.json`](version.json) define a versão em execução:
```json
{
  "name": "zyrtex-saas",
  "version": "2.2.0",
  "build": 20261015,
  "github_repo": "nyshimura/zyrtex",
  "raw_version_url": "https://raw.githubusercontent.com/nyshimura/zyrtex/main/version.json"
}
```

---

## ⚖️ Conformidade Legal Brasileira (Compliance)

O Zyrtex foi projetado em estrita conformidade com a legislação brasileira:
- **LGPD (Lei nº 13.709/18):** Clara distinção de papéis (Oficina como Controladora e Zyrtex como Operadora de dados), consentimento de cookies e termos de privacidade dedicados.
- **Marco Civil da Internet (Lei nº 12.965/14):** Guarda sigilosa de logs de auditoria por 6 meses e registro de aprovações com IP e data/hora.
- **Código de Defesa do Consumidor (Lei nº 8.078/90):**
  - **Art. 26:** Garantia legal obrigatória de 90 dias expressa nos comprovantes térmicos e impressos de bancada.
  - **Art. 49:** Direito de arrependimento com estorno automático de assinaturas em até 7 dias corridos via API do Mercado Pago.

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
