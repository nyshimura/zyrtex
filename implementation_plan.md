# Plano de Implementação: Zyrtex SaaS

Este documento estabelece o plano arquitetural e operacional para o desenvolvimento do **Zyrtex**, sistema SaaS multi-tenant para gestão de assistências técnicas e oficinas, baseado estritamente no documento de requisitos ([requisistos.md](file:///c:/xampp/htdocs/zyrtex/requisistos.md)).

---

## 1. Visão Geral da Arquitetura & Diretrizes Técnicas

### 1.1. Princípios Fundamentais
* **Backend:** PHP 8.x puro utilizando **Server-Side Rendering (SSR)**. Zero dependência de frameworks client-side ou JavaScript para regras de negócio.
* **Comunicação:** Padrão **PRG (Post/Redirect/Get)** estrito com mensagens de sessão (*Flash Messages*) e redirecionamento via `header('Location: ...')`.
* **Frontend & UX:** HTML5 semântico, Tailwind CSS, modais via CSS puro (pseudo-classe `:target` / inputs ocultos) e toasts nativos renderizados via PHP.
* **PDV Totem Fast-Food (RF04):** Interface pensada para telas grandes e totens touch screen (botões largos, cards de produtos/serviços com toque facilitado, carrinho visual e fluxo de checkout expresso).
* **Motor de Impressão Multiformato (Estilo iFood / Keeta) (RF16):** Aba dedicada em configurações permitindo ao lojista escolher seu formato de impressão física preferido: **Bobina Térmica 80mm**, **Bobina Térmica 58mm**, **Folha A4** (1 ou 2 vias) e **Etiquetas Adesivas** (50x30mm / 40x25mm).
* **Etiquetas Adesivas & Selos QR Code (RF14):** Geração de mini-etiquetas para colagem em carcaças ou peças trocadas. Escaneando o QR Code pelo celular, cliente ou técnico acessam na hora a ficha do serviço.
* **Portal do Cliente com Aceite Digital e Histórico Financeiro (RF15):** Área exclusiva do cliente final para aprovar orçamentos com 1 clique (registro legal de IP, Data/Hora e User-Agent), consultar histórico de aparelhos e pagar faturas via Pix ou Mercado Pago.
* **Hub Financeiro Flexível com Toggle Intuitivo (RF08):** Alternância instantânea entre **Pix Manual** (chave cadastrada, nome do favorecido e QR Code estático) e **Mercado Pago Automático** (QR Code dinâmico com baixa via Webhook), com badges visuais claros indicando o modo ativo.
* **Autoatendimento com Mini-Tutoriais Embutidos ("Tips") (RF12):** Telas de configuração técnica (Token Hugging Face, Credenciais Mercado Pago, Pasta Google Drive, URL de Webhook) acompanhadas de cards explicativos com passo a passo ilustrado e links externos diretos, garantindo que o lojista configure tudo sozinho.
* **Blindagem Jurídica na OS (RN04):** Termo oficial impresso contendo garantia de 90 dias (CDC) e cláusula de perda/abandono de equipamento após 90 dias de notificação.
* **Baixa Automática de Estoque (RN05):** Ao aprovar a OS, as peças utilizadas são debitadas automaticamente do estoque de balcão.
* **Inteligência Artificial (BYOK):** Motor de IA de texto desacoplado utilizando **Hugging Face Serverless Inference API / Router** via cURL (token gratuito `hf_...` com modelos open-source como `Qwen/Qwen2.5-7B-Instruct` ou `meta-llama/Llama-3.1-8B-Instruct`), com fallback compatível para OpenAI e Groq. Zero custo de IA para a Zyrtex e sem necessidade de cartão de crédito para o lojista.
* **Banco de Dados:** MySQL com isolamento lógico multi-tenant obrigatório por `tenant_id` em todas as tabelas operacionais.
* **Landing Page da Plataforma SaaS Zyrtex (Raiz):** A raiz principal da plataforma (`zyrtex.com.br` ou `localhost/zyrtex`) apresenta a plataforma Zyrtex aos donos de assistência técnica, convida para cadastro imediato gratuito, detalha o modelo freemium sustentado por anúncios do AdSense, vende o upgrade anual de Domínio Customizado (sem anúncios) e disponibiliza a sessão comunitária de apoio *"Buy Me a Coffee"* / Pix para doações voluntárias.
* **Vitrine e Landing Page da Oficina (`oficina.zyrtex.com.br` ou `?tenant=slug`):** Quando acessado sob o domínio/subdomínio do lojista, renderiza a vitrine de serviços, promoções em destaque, horários, endereço e consulta pública de OS.

---

## 2. Estrutura de Diretórios Proposta

```text
zyrtex/
├── .env.example
├── .env
├── config/
│   ├── app.php                 # Constantes e configurações gerais
│   ├── database.php            # Conexão PDO segura (Singleton/Helper)
│   ├── niches.php              # Definição e checklists de nichos (Celulares, Informática, etc.)
│   └── print_formats.php       # Definições de layouts térmicos (80mm, 58mm, A4, etiquetas)
├── database/
│   ├── schema.sql              # Estrutura completa de tabelas e índices
│   └── seeds.sql               # Super Admin padrão, dados de teste e configurações SaaS
├── src/
│   ├── Core/
│   │   ├── Database.php        # Wrapper PDO com suporte a prepared statements
│   │   ├── Router.php          # Roteador HTTP GET/POST leve
│   │   ├── Request.php         # Sanitização de inputs e métodos HTTP
│   │   ├── Response.php        # Redirecionamentos e headers
│   │   ├── Session.php         # Gestão de sessão e Flash Messages
│   │   ├── CSRF.php            # Proteção contra ataques CSRF via tokens de formulário
│   │   ├── Auth.php            # Controle de acesso, hash de senhas e ACL
│   │   └── Tenant.php          # Identificador e mantenedor do escopo do Tenant atual
│   ├── Controllers/
│   │   ├── Public/             # Landing page da loja, portal do cliente e rastreio
│   │   │   ├── HomeController.php      # Vitrine comercial e promoções
│   │   │   ├── PortalController.php    # Portal do cliente: aceite de orçamento e pagamentos
│   │   │   └── TrackingController.php  # Rastreio de OS e prontuário
│   │   ├── Admin/              # Painel do Lojista
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ServiceOrderController.php
│   │   │   ├── ClientController.php
│   │   │   ├── PosController.php       # PDV estilo Totem Fast-Food
│   │   │   ├── PrintController.php     # Roteador de impressão (80mm, 58mm, A4, etiquetas)
│   │   │   ├── TeamController.php
│   │   │   ├── SettingsController.php  # Empresa, Impressão, Pagamentos, IA, Tips
│   │   │   └── WebhookController.php   # Recepção do Webhook do Lojista
│   │   └── SuperAdmin/         # Painel do Dono do SaaS (Zyrtex)
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── TenantsController.php
│   │       ├── PricingController.php   # Preços de anuidade e taxa de nicho
│   │       ├── AdsController.php       # Gestor AdSense global
│   │       ├── BroadcastController.php
│   │       └── WebhookController.php   # Webhook global Zyrtex (Mercado Pago)
│   ├── Models/
│   │   ├── BaseModel.php       # Escopo de tenant automático
│   │   ├── Tenant.php
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── ServiceOrder.php
│   │   ├── Product.php         # Gestão de estoque de peças e produtos
│   │   ├── OsMedia.php
│   │   ├── Subscription.php
│   │   └── Setting.php
│   └── Services/
│       ├── PrintEngine.php     # Motor de renderização térmica (80mm, 58mm, CSS print)
│       ├── PdfGenerator.php    # Geração de PDF oficial da OS e etiquetas
│       ├── QrCodeService.php   # Gerador de QR Code em SVG/Base64 nativo em PHP
│       ├── ImageCompressor.php # Compressão via PHP GD com trava rígida de 15MB
│       ├── GoogleDrive.php     # Upload BYOS com exclusão obrigatória de /tmp
│       ├── MercadoPago.php     # Pagamentos (Lojista e SuperAdmin)
│       ├── PixService.php      # Gerador de chave copia-e-cola e QR Code estático
│       ├── WhatsApp.php        # Gerador de links wa.me formatados (OS pronta / orçamento)
│       └── AIService.php       # Tradução de laudos e remarketing via Hugging Face Serverless
├── views/
│   ├── layouts/
│   │   ├── public.php          # Layout da Landing Page Comercial da Oficina
│   │   ├── portal.php          # Layout do Portal do Cliente
│   │   ├── admin.php           # Layout do Painel da Oficina
│   │   ├── pos.php             # Layout Fullscreen Otimizado para Totem Touch Screen
│   │   ├── print_thermal.php   # Layout limpo para bobina 80mm e 58mm
│   │   └── superadmin.php      # Layout isolado da Zyrtex
│   ├── partials/
│   │   ├── flash_messages.php  # Toasts de sucesso/erro
│   │   ├── adsense_slot.php    # Injeção dinâmica de AdSense
│   │   ├── tips_card.php       # Componente de mini-tutorial explicativo
│   │   └── css_modals.php      # Componentes de modal CSS :target
│   ├── print/
│   │   ├── thermal_80mm.php    # Cupom balcão 80mm
│   │   ├── thermal_58mm.php    # Cupom mini 58mm
│   │   ├── sticker_labels.php  # Folha de etiquetas adesivas QR Code (50x30 / 40x25)
│   │   └── a4_document.php     # Termo e laudo oficial A4
│   ├── public/                 # Views públicas (vitrine, promoções, rastreio, portal do cliente)
│   ├── admin/                  # Views do lojista (dashboard, OS, configurações, PDV)
│   └── superadmin/             # Views do SaaS owner (métricas, tenants, preços, ads)
├── public/
│   ├── .htaccess               # Reescrita de URLs para o front-controller
│   ├── index.php               # Front Controller único
│   └── assets/
│       ├── css/style.css       # Tailwind CSS
│       └── img/                # SVGs e placeholders padrão
└── crons/
    └── purge_inactives.php     # Rotina de exclusão de contas fantasmas (90/120 dias)
```

---

## 3. Modelo de Dados (MySQL Schema)

1. **`tenants`**:
   - `id`, `business_name`, `slug`, `default_url`, `custom_domain`, `niche` (enum: cellphones, computers, automotive, etc.), `is_premium`, `theme_color`, `active`.
   - *Vitrine & Contato:* `logo_path`, `slogan`, `whatsapp_number`, `address_json` (rua, bairro, cidade, link_maps), `opening_hours_json`, `promotions_json` (lista de até 6 ofertas com título, descrição, preço).
   - *Configurações de Impressora:* `printer_settings_json` (formato padrão: `80mm`, `58mm`, `a4`, `label`; opções: show_logo, show_pix, show_qr, show_terms, font_size).
   - *Formas de Recebimento:* `payment_mode` (enum: manual_pix, mercadopago), `pix_key`, `pix_key_type`, `pix_beneficiary`, `mp_public_key`, `mp_access_token`.
   - *Integrações BYOS & BYOK:* `gdrive_folder_id`, `gdrive_token_json`, `ai_provider` (huggingface, openai, groq), `ai_api_key`.
   - `created_at`, `updated_at`.
2. **`users`**: `id`, `tenant_id`, `name`, `email`, `password_hash`, `role` (owner, manager, technician, attendant), `permissions_json`, `active`, `created_at`.
3. **`clients`**: `id`, `tenant_id`, `name`, `document` (CPF/CNPJ), `phone`, `email`, `address`, `created_at`.
4. **`service_orders`**:
   - `id`, `tenant_id`, `os_number`, `client_id`, `identifier_code` (IMEI / Serial / Placa), `equipment_info`, `checklist_json`, `defect_reported`, `technical_diagnosis`, `simplified_diagnosis_ai`.
   - *Fluxo e Aceite Legal:* `status` (budget, approved, in_progress, waiting_parts, ready, delivered, cancelled), `approval_status` (pending, approved, rejected), `approval_ip`, `approval_date`, `approval_user_agent`.
   - *Financeiro:* `labor_value`, `parts_value`, `total_value`, `payment_status` (pending, paid, refunded), `payment_method` (manual_pix, mercadopago, cash, card).
   - *Concorrência:* `updated_at`, `created_at`.
5. **`os_parts`**: `id`, `tenant_id`, `os_id`, `product_id`, `part_name`, `quantity`, `unit_cost`, `unit_price`, `created_at`.
6. **`products_pos`**: `id`, `tenant_id`, `name`, `sku`, `price`, `cost_price`, `stock`, `created_at`.
7. **`pos_sales`**: `id`, `tenant_id`, `total_amount`, `payment_method`, `items_json`, `created_at`.
8. **`os_media`**: `id`, `tenant_id`, `os_id`, `media_type`, `gdrive_file_id`, `thumbnail_base64`, `file_name`, `created_at`.
9. **`saas_subscriptions`**: `id`, `tenant_id`, `plan_type` (annual_domain, niche_change), `amount`, `mp_payment_id`, `status`, `purchased_at`, `expires_at`, `is_refunded`, `refunded_at`.
10. **`saas_settings`**: `id`, `config_key`, `config_value` (valores de anuidade de domínio, taxa de nicho, credenciais MP da Zyrtex, script AdSense).
11. **`tickets`**: `id`, `tenant_id`, `user_id`, `subject`, `status` (open, answered, closed), `messages_json`, `created_at`.
12. **`audit_logs`**: `id`, `tenant_id`, `user_id`, `action`, `details_json`, `ip_address`, `created_at`.

---

## 4. Fases de Execução & Tarefas Detalhadas

### Fase 1: Fundação, MVC & Ambiente Local [CONCLUÍDO]
- [x] Criar estrutura de diretórios e arquivos base (`config/`, `src/`, `views/`, `public/`).
- [x] Implementar carregador de `.env` simples sem bibliotecas externas pesadas (`src/Core/Env.php`).
- [x] Implementar `src/Core/Database.php` com conexão PDO segura, `utf8mb4` e tratamento de erros.
- [x] Implementar `src/Core/Router.php`, `Request.php` e `Response.php`.
- [x] Configurar `public/.htaccess` e `.htaccess` raiz para roteamento limpo pelo `index.php`.
- [x] Configurar layout base com **Tailwind CSS** (cores `indigo`, `emerald`, `slate`), componente de **Flash Messages** e componente visual de **Tips/Mini-tutoriais** (`views/partials/tips_card.php`).

### Fase 2: Banco de Dados, Multi-Tenancy & Autenticação [CONCLUÍDO]
- [x] Criar script `database/schema.sql` com todas as tabelas e índices de `tenant_id`.
- [x] Criar `database/seeds.sql` com dados do Super Admin e lojas de exemplo.
- [x] Implementar `src/Core/Tenant.php`:
  - Reconhecimento automático por domínio / subdomínio com fallback gracioso em caso de expiração (RN-Shield03).
  - Fallback no ambiente local para `?tenant=slug` ou sessão para desenvolvimento ágil.
- [x] Implementar `src/Core/CSRF.php` (geração e validação de tokens).
- [x] Implementar fluxo de cadastro do Lojista com **Trava de Nicho (RF02)**.
- [x] Implementar autenticação de usuários, sessões e ACL granular por cargo/permissões (RF03).

### Fase 3: Core Operacional da Oficina & Configurações (`/admin`) [CONCLUÍDO]
- [x] Dashboard administrativo com cards de resumo (OS abertas, faturamento, tickets).
- [x] Módulo de Gestão de Clientes (CRUD completo com validação server-side).
- [x] **Motor de Ordem de Serviço Dinâmica (RF04):**
  - Carregamento de formulário e checklist de acordo com o nicho da loja (`config/niches.php`).
  - Geração de código único de OS.
  - Verificação de concorrência com timestamp `updated_at` para evitar sobrescrita simultânea.
- [x] **Baixa Automática de Estoque na OS (RN05):** Ao aprovar a OS, abate peças vinculadas da tabela `products_pos`.
- [x] **Prontuário Antifraude (RN01):** Busca por IMEI/Serial/Placa exibindo histórico de intervenções anteriores.
- [x] **PDV Totem Fast-Food (RF04):**
  - Tela otimizada para telas grandes e touch screen (`views/layouts/pos.php`).
  - Cards grandes de produtos e serviços, toque rápido para adicionar ao carrinho, checkout veloz em tela única.
- [x] **Central de Configurações do Lojista (RF12):**
  - Aba Empresa: Upload de logotipo, horário de atendimento, endereço e dados de contato.
  - Aba Vitrine: Cadastro de até 6 promoções em destaque para a Landing Page.
  - Aba Impressão (Estilo iFood/Keeta): Seleção de formato padrão (80mm, 58mm, A4, Etiquetas), opções de dados no cupom e botão "Imprimir Teste de Alinhamento".
  - Aba Formas de Recebimento: **Chave Seletora (Toggle) entre Pix Manual e Mercado Pago Automático** com badges informativos de status ativo.
  - Aba IA: Token Hugging Face com botão "Testar Conexão" + **Mini Tutorial com link direto para `huggingface.co/settings/tokens`**.
  - Aba Mercado Pago: Chaves de API + URL do Webhook pronta para copiar + **Mini Tutorial com link direto para `mercadopago.com.br/developers`**.
  - Aba Google Drive: Conexão da pasta de backup + **Mini Tutorial explicativo**.
- [x] **Gestão Avançada de Peças, Aditivos e Auditoria da OS:**
  - Campo pesquisável (Autocomplete Datalist + Dropdown tradicional) em `/admin/os/nova` e `/admin/os/ver`.
  - Inclusão dinâmica de peças e insumos já na abertura da OS (`views/admin/os/create.php`).
  - Lógica de Aditivos pós-aprovação: peças adicionadas após a OS aprovada entram com status `pending_approval`.
  - Registro de auditoria (`audit_logs`) com data, hora e qual funcionário incluiu ou removeu cada item.
  - **Isolamento de Visão Cliente vs. Oficina:** O cliente vê no rastreio apenas a versão salva atual (itens aprovados e itens pendentes de aprovação); o admin da oficina tem acesso à Linha do Tempo completa com histórico de correções e exclusões.

### Fase 4: Motor de Impressão Multiformato & Etiquetas Adesivas QR Code [CONCLUÍDO]
- [x] Implementar `src/Services/PrintEngine.php` e `src/Services/QrCodeService.php` (geração de QR Code leve sem bibliotecas pesadas).
- [x] **Impressão Térmica de Cupom de Balcão (RF16):**
  - Template 80mm (`views/print/thermal_80mm.php`).
  - Template 58mm (`views/print/thermal_58mm.php`).
- [x] **Gerador de Etiquetas Adesivas & Selo QR Code (RF14):**
  - Template para mini-etiquetas adesivas (50x30mm e 40x25mm) com QR Code direto para o rastreio da OS, aviso de garantia e número da OS (`views/print/sticker_labels.php`).
- [x] **Documento Oficial em PDF A4 com Blindagem Legal (RN04):**
  - PDF contendo garantia legal de 90 dias (CDC) e cláusula legal de abandono do equipamento após 90 dias da notificação.

### Fase 5: Mídias & Upload Server-Side (GD + Google Drive BYOS) [CONCLUÍDO]
- [x] Implementar formulário de upload `multipart/form-data` nas OSs.
- [x] Implementar `src/Services/ImageCompressor.php`:
  - Validação rigorosa de tamanho máximo (limite rígido de 15MB por foto).
  - Redimensionamento e compressão JPEG/WebP via PHP GD.
  - Conversão em thumbnail leve em Base64 para exibição rápida e persistência no banco.
- [x] Conector Google Drive (BYOS - RF10) com **exclusão incondicional em bloco `try/finally` do arquivo temporário local**, prevenindo estouro de disco (RN-Shield03).

### Fase 6: Landing Page Pública, Mapas & Portal do Cliente (`/`) [CONCLUÍDO]
- [x] Implementar rota raiz `/` renderizando a **Vitrine Comercial Completa (RF11)**:
  - Cabeçalho com Logotipo da oficina, slogan e dados de contato.
  - Grade de Serviços e Promoções em Destaque cadastradas pelo lojista com fotos de capa.
  - Grade de Peças & Produtos em Estoque com foto para balcão.
  - Horários de funcionamento e dados de contato.
  - Botão flutuante de WhatsApp com mensagem personalizada de pré-atendimento.
  - Seção de Rastreio de OS em tempo real e consulta pública do Prontuário Antifraude.
- [x] **Integração com Google Maps / OpenStreetMap & Localização Interativa:**
  - Campo de Link do Google Maps / Iframe Embed no painel administrativo de configurações (`views/admin/settings.php`).
  - Mapa interativo embutido (iframe Google Maps / OpenStreetMap) na seção "Onde Estamos" da vitrine pública (`views/public/home.php`) com botões diretos "🗺️ Google Maps", "🚗 Waze" e "📋 Copiar Endereço".
- [x] **Aprovação Digital de Orçamento em 1 Clique (RF15):**
  - Botão de aceite do orçamento inicial com registro de IP, data/hora, user-agent e auditoria irrevogável (`audit_logs`).
  - Baixa automática no estoque das peças ao aprovar a OS (`products_pos`).
  - Fluxo de aprovação ou recusa de novos itens aditivos pós-orçamento (`/rastreio/aprovar-adicional`).
- [x] **Portal do Cliente Completo (RF15):**
  - Acesso por link direto seguro enviado no WhatsApp ou consulta por Documento (CPF/CNPJ) ou número da OS (`/portal`).
  - Painel do Cliente com Histórico de manutenções de todos os seus aparelhos na oficina (multi-device).
  - Extrato financeiro: faturas pagas e pendentes, com pagamento direto via Pix Copia-e-Cola e QR Code SVG nítido.
  - Layout dedicado e controlador [`src/Controllers/Public/PortalController.php`].
- [x] **Customização de Tema do Lojista (Claro / Escuro / Automático) & Detecção de Sistema do Usuário:**
  - Opção no painel administrativo (`views/admin/settings.php`) para o lojista escolher: **Automático (Sistema do Usuário)**, **Forçar Modo Claro** ou **Forçar Modo Escuro**.
  - Script no `<head>` do layout público (`views/layouts/public.php`) com prevenção de FOUC, leitura de `prefers-color-scheme` e listener em tempo real.
  - Botão de alternância rápida 🌙/☀️ no cabeçalho público para o visitante.
- [x] **Notificação Ativa no WhatsApp em 1 Clique (RF17):**
  - Card dedicado na lateral da OS (`views/admin/os/show.php`) para disparar links diretos do Portal do Cliente via WhatsApp:
    - 📋 "Orçamento Pronto" (com link de aceite em 1 clique).
    - ✅ "Aparelho Pronto" (com valor e aviso de retirada).
    - ⚠️ "Aditivo de Bancada" (com link de autorização).
    - 💳 "Cobrança & Chave Pix" (com dados e chave Pix).
- [x] **Barra de Métricas & Prova Social Dinâmica e Personalizável:**
  - Modo Automático: cálculo em tempo real das ordens de serviço do banco (`repaired_count`, taxa de aprovação, garantia legal CDC de 90 dias e aprovação no WhatsApp), com fallback inteligente para oficinas novas (evitando vergonha de "0 aparelhos").
  - Modo Personalizado: o lojista pode definir seus próprios números e rótulos nas Configurações da Loja (`/admin/configuracoes#publica`), ou até mesmo ocultar a barra se preferir.

### Fase 7: Cobranças, IA & Webhooks [CONCLUÍDO]
- [x] Cobrança de OS pelo Lojista (RF08):
  - No modo **Pix Manual**: Geração de chave Pix, nome do favorecido e QR Code estático.
  - No modo **Mercado Pago**: Geração de QR Code dinâmico e link de pagamento via cURL com baixa automática por Webhook.
- [x] **Motor de IA Descentralizada (Hugging Face Serverless - RF09 e RN02):**
  - Implementação de `src/Services/AIService.php` via cURL consumindo endpoint do Hugging Face (token `hf_...` com `Qwen/Qwen2.5-7B-Instruct` ou `Llama-3.1`), Groq, OpenAI ou fallback heurístico offline.
  - Tradução e simplificação de laudo técnico em linguagem clara para o cliente (RF09).
  - Geração de mensagens persuasivas de remarketing para manutenção preditiva via WhatsApp (RN02).
- [x] Assinaturas do SaaS pelo Super Admin:
  - Assinatura anual de domínio customizado (RF06).
  - Taxa de alteração de nicho (RF07).
  - Mecanismo de estorno automatizado em até 7 dias (RN03 - CDC).

### Fase 8: Super Admin (`/superadmin`) & Gestão Global do SaaS [CONCLUÍDO]
- [x] Rota isolada `/superadmin` com autenticação master password.
- [x] Dashboard de KPIs (Lojas ativas, receita, churn, assinaturas).
- [x] **Central de Precificação e Parâmetros do SaaS (RF13):**
  - Formulário para editar valores da anuidade de domínio e taxa de nicho.
  - Cadastro de credenciais do Mercado Pago da Zyrtex e URL de Webhook do SaaS.
  - Gerenciador dinâmico de anúncios do Google AdSense com exclusão automática para lojas pagas.
- [x] **Controle de Crise & Tenants:**
  - Botão de suspensão imediata de lojas com tela de bloqueio personalizada.
  - **Shadow Login (Impersonation):** Acesso administrativo à conta do lojista em 1 clique via injeção de sessão com aviso de auditoria e banner flutuante para saída rápida.
- [x] Central de avisos (Broadcast) e suporte via Tickets (CRM integrado com respostas).
- [x] Script Cron `crons/purge_inactives.php` para varredura de contas inativas (90 dias alerta, 120 dias exclusão - RN-Shield01).

---

## 5. Critérios de Aceite & Validação
1. **Zero JavaScript obrigatório na regra de negócios:** Todo o sistema deve funcionar perfeitamente com formulários e navegação nativa do PHP.
2. **Impressão Térmica Flexível:** Suporte real e testável para bobinas 80mm, 58mm, A4 e etiquetas adesivas (estilo iFood).
3. **Portal do Cliente com Aceite Válido:** Orçamento aprovado digitalmente com registro legal de IP e data/hora.
4. **Independência do Lojista (Zero Chamados de Suporte):** Todas as integrações técnicas possuem mini-tutoriais embutidos com links diretos para que o lojista configure e teste seus tokens sozinho.
5. **Flexibilidade Financeira:** Alternância suave entre Pix Manual e Mercado Pago Automático através de toggle com badges claros.
6. **Controle Físico com Selos QR Code:** Etiquetas adesivas impressas e escaneáveis pela câmera de qualquer celular abrindo na hora a OS.
7. **Emissão de OS com Blindagem Legal:** PDF da OS contendo termos de 90 dias de garantia e cláusula de abandono.
8. **Isolamento de Tenants:** Nenhuma consulta SQL pode vazar dados de outro `tenant_id`.
9. **Custo Zero de IA (Hugging Face):** Chamadas de IA funcionando via cURL com token gratuito de usuário do Hugging Face.
10. **Segurança de Servidor:** Arquivos temporários de upload limpos incondicionalmente no PHP, com limite rígido de 15MB.
