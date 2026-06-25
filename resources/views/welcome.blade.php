<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IntelliBase</title>
    <style>
        :root {
            --bg: #f6f8fb;
            --panel: #ffffff;
            --panel-2: #eef3f8;
            --text: #172033;
            --muted: #667085;
            --line: #d9e2ec;
            --accent: #0f73d8;
            --accent-2: #12a594;
            --warn: #c27a1a;
            --shadow: 0 18px 45px rgba(16, 24, 40, .12);
        }

        [data-theme="navy"] {
            --bg: #071526;
            --panel: #0c2035;
            --panel-2: #102b45;
            --text: #edf6ff;
            --muted: #97adbf;
            --line: #1b3954;
            --accent: #40a6ff;
            --accent-2: #30d6bd;
            --warn: #f3b64b;
            --shadow: 0 20px 55px rgba(0, 0, 0, .35);
        }

        [data-theme="violet"] {
            --bg: #160f26;
            --panel: #211638;
            --panel-2: #2d1e4b;
            --text: #fbf8ff;
            --muted: #b7a8d4;
            --line: #493769;
            --accent: #a77cff;
            --accent-2: #55dbc5;
            --warn: #ffc86b;
            --shadow: 0 20px 55px rgba(7, 3, 18, .45);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        button, input, textarea { font: inherit; }
        button { cursor: pointer; }

        .app {
            display: grid;
            grid-template-columns: 248px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            border-right: 1px solid var(--line);
            background: color-mix(in srgb, var(--panel) 88%, var(--bg));
            padding: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            font-weight: 750;
        }

        .logo {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--accent);
            color: white;
        }

        .nav {
            display: grid;
            gap: 6px;
        }

        .nav button, .theme button, .chip, .primary, .ghost, .icon-button {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: transparent;
            color: var(--text);
        }

        .nav button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px;
            text-align: left;
            color: var(--muted);
        }

        .nav button.active {
            background: var(--panel-2);
            color: var(--text);
            border-color: color-mix(in srgb, var(--accent) 45%, var(--line));
        }

        .sidebar-foot {
            margin-top: 28px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel-2);
        }

        .sidebar-foot b { display: block; margin-bottom: 8px; }
        .sidebar-foot p { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.45; }

        .main {
            min-width: 0;
            padding: 24px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .title h1 {
            margin: 0 0 4px;
            font-size: 28px;
            line-height: 1.15;
        }

        .title p {
            margin: 0;
            color: var(--muted);
        }

        .toolbar, .theme, .lang, .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .theme button, .lang button, .ghost, .primary, .icon-button {
            padding: 9px 12px;
            min-height: 38px;
        }

        .theme button, .lang button {
            background: var(--panel);
            color: var(--text);
            font-weight: 650;
        }

        .theme button.active, .lang button.active, .primary {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        .screen { display: none; }
        .screen.active { display: block; }

        .grid {
            display: grid;
            gap: 16px;
        }

        .cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .cols-2 { grid-template-columns: minmax(0, 1.45fr) minmax(320px, .8fr); }

        .card, .chat, .upload {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: var(--shadow);
        }

        .card { padding: 18px; }
        .stat { display: flex; justify-content: space-between; gap: 14px; }
        .stat strong { display: block; font-size: 26px; line-height: 1; margin-top: 10px; }
        .label, .meta { color: var(--muted); font-size: 13px; }

        .stack {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .chip {
            padding: 6px 9px;
            background: var(--panel-2);
            color: var(--muted);
            font-size: 13px;
        }

        .chat {
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 560px;
            overflow: hidden;
        }

        .chat-head, .chat-input, .table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .messages {
            display: grid;
            align-content: start;
            gap: 14px;
            padding: 16px;
        }

        .msg {
            max-width: 780px;
            padding: 13px 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel-2);
            line-height: 1.5;
        }

        .msg.user {
            justify-self: end;
            background: color-mix(in srgb, var(--accent) 14%, var(--panel));
        }

        .sources {
            display: grid;
            gap: 8px;
            margin-top: 12px;
        }

        .source {
            padding: 9px;
            border-left: 3px solid var(--accent-2);
            background: color-mix(in srgb, var(--panel) 70%, var(--panel-2));
            color: var(--muted);
            font-size: 13px;
        }

        .chat-input {
            border-top: 1px solid var(--line);
            border-bottom: 0;
        }

        .chat-input input, .search, textarea {
            width: 100%;
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel-2);
            color: var(--text);
            padding: 11px 12px;
            outline: none;
        }

        .upload {
            padding: 18px;
        }

        .drop {
            display: grid;
            place-items: center;
            min-height: 176px;
            border: 1px dashed color-mix(in srgb, var(--accent) 50%, var(--line));
            border-radius: 8px;
            background: var(--panel-2);
            text-align: center;
        }

        .doc-list, .user-list, .kb-list {
            display: grid;
            gap: 10px;
        }

        .row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: color-mix(in srgb, var(--panel) 74%, var(--panel-2));
        }

        .row h3 {
            margin: 0 0 4px;
            font-size: 15px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--accent-2);
            font-size: 13px;
            white-space: nowrap;
        }

        .status.warn { color: var(--warn); }
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }

        .diagram {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 16px;
        }

        .node {
            min-height: 74px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel-2);
        }

        .node b { display: block; margin-bottom: 5px; }
        .node span { color: var(--muted); font-size: 12px; }

        .mobile-menu { display: none; }

        @media (max-width: 900px) {
            .app { grid-template-columns: 1fr; }
            .sidebar {
                position: sticky;
                top: 0;
                z-index: 2;
                padding: 12px;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }
            .brand { margin-bottom: 12px; }
            .nav { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .nav button { justify-content: center; padding: 9px; font-size: 0; }
            .nav button span { font-size: 18px; }
            .sidebar-foot { display: none; }
            .main { padding: 16px; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .cols-3, .cols-2, .diagram { grid-template-columns: 1fr; }
            .chat { min-height: 620px; }
        }
    </style>
</head>
<body data-theme="light">
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">IB</div>
            <div>IntelliBase</div>
        </div>

        <nav class="nav" aria-label="Main screens">
            <button class="active" data-screen="dashboard"><span>⌘</span> <b data-i18n="nav.dashboard">Dashboard</b></button>
            <button data-screen="chat"><span>?</span> <b data-i18n="nav.chat">AI chat</b></button>
            <button data-screen="docs"><span>▤</span> <b data-i18n="nav.docs">Documents</b></button>
            <button data-screen="admin"><span>◇</span> <b data-i18n="nav.admin">Admin</b></button>
        </nav>

        <div class="sidebar-foot">
            <b data-i18n="aside.title">Static prototype</b>
            <p data-i18n="aside.text">Data, answers, and statuses are placeholders for a quick UI direction check.</p>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="title">
                <h1 id="screen-title">Knowledge base dashboard</h1>
                <p id="screen-subtitle">RAG search, documents, permissions, and indexing in one working interface.</p>
            </div>
            <div class="toolbar">
                <div class="lang" aria-label="Language">
                    <button class="active" data-lang="en">EN</button>
                    <button data-lang="uk">UA</button>
                </div>
                <div class="theme" aria-label="Color scheme">
                    <button class="active" data-theme="light" data-i18n="theme.light">Light</button>
                    <button data-theme="navy" data-i18n="theme.navy">Dark navy</button>
                    <button data-theme="violet" data-i18n="theme.violet">Dark violet</button>
                </div>
            </div>
        </header>

        <section class="screen active" id="dashboard">
            <div class="grid cols-3">
                <div class="card stat"><div><span class="label" data-i18n="stats.kb">Knowledge bases</span><strong>8</strong></div><span class="status"><i class="dot"></i> active</span></div>
                <div class="card stat"><div><span class="label" data-i18n="stats.docs">Documents</span><strong>342</strong></div><span class="status warn"><i class="dot"></i> 12 processing</span></div>
                <div class="card stat"><div><span class="label" data-i18n="stats.questions">Questions today</span><strong>1 284</strong></div><span class="status"><i class="dot"></i> SSE online</span></div>
            </div>

            <div class="grid cols-2" style="margin-top:16px">
                <div class="card">
                    <div class="table-head" style="padding:0 0 14px;border-bottom:0">
                        <div><b data-i18n="dashboard.archTitle">Search architecture</b><div class="meta" data-i18n="dashboard.archText">Laravel API publishes events, AI Service builds chunks and searches through FTS + Qdrant.</div></div>
                    </div>
                    <div class="diagram" aria-label="Component diagram">
                        <div class="node"><b>Web SPA</b><span data-i18n="dashboard.web">chat, documents, admin</span></div>
                        <div class="node"><b>Laravel API</b><span>Auth, KB, Documents, Chat</span></div>
                        <div class="node"><b>AI Service</b><span>RAG pipeline, parsing</span></div>
                        <div class="node"><b>PostgreSQL</b><span>users, docs, FTS</span></div>
                        <div class="node"><b>RabbitMQ</b><span>document.uploaded</span></div>
                        <div class="node"><b>Qdrant</b><span>vectors by kb_id</span></div>
                    </div>
                </div>

                <div class="card">
                    <b data-i18n="dashboard.events">Recent events</b>
                    <div class="doc-list" style="margin-top:14px">
                        <div class="row"><div><h3>prod-db-access.pdf</h3><div class="meta" data-i18n="dashboard.event1">document.indexed -> owner notification</div></div><span class="status"><i class="dot"></i> indexed</span></div>
                        <div class="row"><div><h3>hr-sick-days.docx</h3><div class="meta">chunking + embeddings</div></div><span class="status warn"><i class="dot"></i> processing</span></div>
                        <div class="row"><div><h3>new.user@company.dev</h3><div class="meta" data-i18n="dashboard.event3">waiting for admin approval</div></div><span class="status warn"><i class="dot"></i> pending</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="screen" id="chat">
            <div class="grid cols-2">
                <div class="chat">
                    <div class="chat-head">
                        <div><b data-i18n="chat.dialog">Conversation: Infrastructure KB</b><div class="meta" data-i18n="chat.meta">Answers with document citations</div></div>
                        <button class="ghost" data-i18n="chat.new">New chat</button>
                    </div>
                    <div class="messages">
                        <div class="msg user" data-i18n="chat.question">What access is available for the production DB?</div>
                        <div class="msg"><span data-i18n="chat.answer">
                            Production DB access is granted only through bastion and the temporary `prod_readonly` role. The request must be approved by the service owner and SRE.
                        </span>
                            <div class="sources">
                                <div class="source" data-i18n="chat.source1">prod-db-access.pdf, p. 4: temporary access workflow</div>
                                <div class="source" data-i18n="chat.source2">security-regulations.docx, section 2.1: required audit trail</div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-input">
                        <input value="How many paid sick days are available?" aria-label="Question" data-i18n-value="chat.input">
                        <button class="primary" data-i18n="chat.ask">Ask</button>
                    </div>
                </div>

                <div class="card">
                    <b data-i18n="chat.context">Answer context</b>
                    <div class="stack">
                        <span class="chip">kb: infrastructure</span>
                        <span class="chip">hybrid search</span>
                        <span class="chip">top_k: 8</span>
                    </div>
                    <div class="doc-list" style="margin-top:18px">
                        <div class="row"><div><h3>prod-db-access.pdf</h3><div class="meta">score 0.92 · 6 chunks</div></div><button class="icon-button">↗</button></div>
                        <div class="row"><div><h3>vpn-runbook.md</h3><div class="meta">score 0.78 · 3 chunks</div></div><button class="icon-button">↗</button></div>
                        <div class="row"><div><h3>incident-playbook.pdf</h3><div class="meta">score 0.61 · 2 chunks</div></div><button class="icon-button">↗</button></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="screen" id="docs">
            <div class="grid cols-2">
                <div class="upload">
                    <b data-i18n="docs.upload">Document upload</b>
                    <p class="meta" data-i18n="docs.uploadText">PDF, DOCX, and TXT are stored in S3/MinIO, then sent to AI Service through RabbitMQ.</p>
                    <div class="drop">
                        <div>
                            <div style="font-size:34px;margin-bottom:8px">▥</div>
                            <b data-i18n="docs.drop">Drop a file here</b>
                            <div class="meta" data-i18n="docs.pick">or choose a file for indexing</div>
                        </div>
                    </div>
                    <div class="stack">
                        <span class="chip">HR</span>
                        <span class="chip">Infrastructure</span>
                        <span class="chip">Security</span>
                    </div>
                </div>

                <div class="card">
                    <div class="table-head" style="padding:0 0 14px;border-bottom:0">
                        <b data-i18n="docs.queue">Indexing queue</b>
                        <input class="search" value="regulations" aria-label="Document search">
                    </div>
                    <div class="doc-list">
                        <div class="row"><div><h3>security-regulations.docx</h3><div class="meta">2.4 MB · Security KB</div></div><span class="status"><i class="dot"></i> indexed</span></div>
                        <div class="row"><div><h3>hr-benefits-2026.pdf</h3><div class="meta">842 KB · HR KB</div></div><span class="status warn"><i class="dot"></i> processing</span></div>
                        <div class="row"><div><h3>onboarding-checklist.txt</h3><div class="meta">18 KB · Public KB</div></div><span class="status"><i class="dot"></i> indexed</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="screen" id="admin">
            <div class="grid cols-2">
                <div class="card">
                    <b data-i18n="admin.pending">Users pending approval</b>
                    <div class="user-list" style="margin-top:14px">
                        <div class="row"><div><h3>Holovach Lena</h3><div class="meta">lena@company.dev · Google OAuth</div></div><button class="primary" data-i18n="admin.approve">Approve</button></div>
                        <div class="row"><div><h3>Denis Petuh</h3><div class="meta">denis@company.dev · email/password</div></div><button class="primary" data-i18n="admin.approve">Approve</button></div>
                        <div class="row"><div><h3>QA Contractor</h3><div class="meta">contractor@vendor.dev · limited access</div></div><button class="ghost" data-i18n="admin.review">Review</button></div>
                    </div>
                </div>

                <div class="card">
                    <b data-i18n="admin.permissions">Knowledge base permissions</b>
                    <div class="kb-list" style="margin-top:14px">
                        <div class="row"><div><h3>Infrastructure</h3><div class="meta">42 readers · 7 writers · private</div></div><span class="chip">admin</span></div>
                        <div class="row"><div><h3>HR Policies</h3><div class="meta">128 readers · 3 writers · private</div></div><span class="chip">member</span></div>
                        <div class="row"><div><h3>Public Onboarding</h3><div class="meta">is_public=true</div></div><span class="chip">all</span></div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
    const copy = {
        en: {
            'nav.dashboard': 'Dashboard', 'nav.chat': 'AI chat', 'nav.docs': 'Documents', 'nav.admin': 'Admin',
            'aside.title': 'Static prototype', 'aside.text': 'Data, answers, and statuses are placeholders for a quick UI direction check.',
            'theme.light': 'Light', 'theme.navy': 'Dark navy', 'theme.violet': 'Dark violet',
            'stats.kb': 'Knowledge bases', 'stats.docs': 'Documents', 'stats.questions': 'Questions today',
            'dashboard.archTitle': 'Search architecture', 'dashboard.archText': 'Laravel API publishes events, AI Service builds chunks and searches through FTS + Qdrant.',
            'dashboard.web': 'chat, documents, admin', 'dashboard.events': 'Recent events',
            'dashboard.event1': 'document.indexed -> owner notification', 'dashboard.event3': 'waiting for admin approval',
            'chat.dialog': 'Conversation: Infrastructure KB', 'chat.meta': 'Answers with document citations', 'chat.new': 'New chat',
            'chat.question': 'What access is available for the production DB?',
            'chat.answer': 'Production DB access is granted only through bastion and the temporary `prod_readonly` role. The request must be approved by the service owner and SRE.',
            'chat.source1': 'prod-db-access.pdf, p. 4: temporary access workflow', 'chat.source2': 'security-regulations.docx, section 2.1: required audit trail',
            'chat.input': 'How many paid sick days are available?', 'chat.ask': 'Ask', 'chat.context': 'Answer context',
            'docs.upload': 'Document upload', 'docs.uploadText': 'PDF, DOCX, and TXT are stored in S3/MinIO, then sent to AI Service through RabbitMQ.',
            'docs.drop': 'Drop a file here', 'docs.pick': 'or choose a file for indexing', 'docs.queue': 'Indexing queue',
            'admin.pending': 'Users pending approval', 'admin.approve': 'Approve', 'admin.review': 'Review', 'admin.permissions': 'Knowledge base permissions'
        },
        uk: {
            'nav.dashboard': 'Огляд', 'nav.chat': 'AI чат', 'nav.docs': 'Документи', 'nav.admin': 'Адмінка',
            'aside.title': 'Статичний прототип', 'aside.text': 'Дані, відповіді та статуси тут є заглушками для швидкої перевірки напряму інтерфейсу.',
            'theme.light': 'Світла', 'theme.navy': 'Темно-синя', 'theme.violet': 'Темно-фіолетова',
            'stats.kb': 'Бази знань', 'stats.docs': 'Документи', 'stats.questions': 'Питання за день',
            'dashboard.archTitle': 'Архітектура пошуку', 'dashboard.archText': 'Laravel API публікує події, AI Service будує чанки та шукає через FTS + Qdrant.',
            'dashboard.web': 'чат, документи, адмінка', 'dashboard.events': 'Останні події',
            'dashboard.event1': 'document.indexed -> сповіщення власнику', 'dashboard.event3': 'очікує підтвердження адміністратора',
            'chat.dialog': 'Діалог: Infrastructure KB', 'chat.meta': 'Відповіді з цитатами з документів', 'chat.new': 'Новий чат',
            'chat.question': 'Які доступи є до production DB?',
            'chat.answer': 'Доступ до production DB надається лише через bastion і тимчасову роль `prod_readonly`. Запит має підтвердити власник сервісу та SRE.',
            'chat.source1': 'prod-db-access.pdf, с. 4: порядок тимчасового доступу', 'chat.source2': 'security-regulations.docx, розділ 2.1: обов’язковий audit trail',
            'chat.input': 'Скільки оплачуваних лікарняних доступно?', 'chat.ask': 'Запитати', 'chat.context': 'Контекст відповіді',
            'docs.upload': 'Завантаження документів', 'docs.uploadText': 'PDF, DOCX і TXT зберігаються в S3/MinIO, потім надсилаються в AI Service через RabbitMQ.',
            'docs.drop': 'Перетягніть файл сюди', 'docs.pick': 'або виберіть файл для індексації', 'docs.queue': 'Черга індексації',
            'admin.pending': 'Користувачі на підтвердження', 'admin.approve': 'Approve', 'admin.review': 'Review', 'admin.permissions': 'Права на бази знань'
        }
    };

    const titles = {
        en: {
            dashboard: ['Knowledge base dashboard', 'RAG search, documents, permissions, and indexing in one working interface.'],
            chat: ['AI chat', 'Sample answer with sources, relevance scores, and selected knowledge base.'],
            docs: ['Documents', 'File upload, indexing queue, and document.uploaded/document.indexed statuses.'],
            admin: ['Admin', 'User approval and knowledge base access management.']
        },
        uk: {
            dashboard: ['Огляд бази знань', 'RAG-пошук, документи, права доступу та індексація в одному робочому інтерфейсі.'],
            chat: ['AI чат', 'Приклад відповіді з джерелами, оцінками релевантності та вибраною базою знань.'],
            docs: ['Документи', 'Завантаження файлів, черга індексації та статуси document.uploaded/document.indexed.'],
            admin: ['Адмінка', 'Підтвердження користувачів і керування доступом до knowledge bases.']
        }
    };
    let currentLang = 'en';
    let currentScreen = 'dashboard';

    function applyLang(lang) {
        currentLang = lang;
        document.documentElement.lang = lang;
        document.querySelectorAll('[data-i18n]').forEach((el) => el.textContent = copy[lang][el.dataset.i18n]);
        document.querySelectorAll('[data-i18n-value]').forEach((el) => el.value = copy[lang][el.dataset.i18nValue]);
        document.getElementById('screen-title').textContent = titles[lang][currentScreen][0];
        document.getElementById('screen-subtitle').textContent = titles[lang][currentScreen][1];
    }

    document.querySelectorAll('[data-screen]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('[data-screen], .screen').forEach((el) => el.classList.remove('active'));
            currentScreen = button.dataset.screen;
            button.classList.add('active');
            document.getElementById(currentScreen).classList.add('active');
            applyLang(currentLang);
        });
    });

    document.querySelectorAll('.theme [data-theme]').forEach((button) => {
        button.addEventListener('click', () => {
            document.body.dataset.theme = button.dataset.theme;
            document.querySelectorAll('.theme button').forEach((el) => el.classList.remove('active'));
            button.classList.add('active');
        });
    });

    document.querySelectorAll('.lang [data-lang]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.lang button').forEach((el) => el.classList.remove('active'));
            button.classList.add('active');
            applyLang(button.dataset.lang);
        });
    });
</script>
</body>
</html>
