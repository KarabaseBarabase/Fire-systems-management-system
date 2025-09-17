<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система {{ $system->name ?? 'Без названия' }}</title>
    <style>
        .app-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }

        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f0f0f0;
            border-bottom: 1px solid #ccc;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-weight: bold;
        }

        .user-role {
            font-size: 0.9em;
            color: #666;
        }

        .back-btn {
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .main-content {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            padding: 20px 0;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
            gap: 10px;
            cursor: pointer;
        }

        .menu-item:hover {
            background-color: #34495e;
        }

        .content-area {
            flex: 1;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .system-details {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .system-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .system-title {
            margin: 0;
            color: #2c3e50;
        }

        .modal-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-size: 14px;
        }

        .tab.active {
            border-bottom: 3px solid #2c7be5;
            font-weight: bold;
            color: #2c7be5;
            display: block;
        }

        /* ИСПРАВЛЕННЫЕ СТИЛИ ДЛЯ ТАБОВ */
        .tab-content {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            height: 0;
            overflow: hidden;
        }

        .tab-content.active {
            display: block;
            opacity: 1;
            height: auto;
            overflow: visible;
        }

        .section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background-color: #f8f9fa;
        }

        .section h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .grid-3col {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 200px;
        }

        .info-value {
            color: #333;
            text-align: right;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            display: inline-block;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .equipment-table th,
        .equipment-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .equipment-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .equipment-table tr:hover {
            background-color: #f5f5f5;
        }

        .document-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .doc-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background-color: white;
            transition: background-color 0.2s;
        }

        .doc-item:hover {
            background-color: #f8f9fa;
        }

        .doc-icon {
            font-size: 18px;
        }

        .doc-link {
            color: #007bff;
            text-decoration: none;
            flex: 1;
        }

        .doc-link:hover {
            text-decoration: underline;
        }

        .doc-date {
            color: #6c757d;
            font-size: 0.9em;
        }

        .timeline {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .event {
            display: flex;
            gap: 20px;
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background-color: white;
        }

        .date {
            font-weight: bold;
            min-width: 100px;
            color: #2c3e50;
        }

        .history-content {
            flex: 1;
            color: #333;
        }

        .plan-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        .plan-card {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background-color: white;
        }

        .plan-card h4 {
            margin-top: 0;
            color: #2c3e50;
        }

        .plan-card p {
            margin: 5px 0;
            color: #555;
        }

        /* Стили для отладки */
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 12px;
        }

        @media (max-width: 768px) {

            .grid-2col,
            .grid-3col {
                grid-template-columns: 1fr;
            }

            .main-content {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .modal-tabs {
                flex-wrap: wrap;
            }

            .tab {
                padding: 8px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-left">
                <img src="/logo.png" alt="Логотип" class="logo">
                <h1>Мониторинг пожарных систем</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <span class="user-name">{{ $userFullName }}</span>
                    <span class="user-role">{{ $userRole }}</span>
                </div>
                <button class="back-btn" onclick="window.history.back()">Назад к списку</button>
            </div>
        </header>

        <div class="main-content">
            <nav class="sidebar">
                <ul class="menu">
                    <li class="menu-item" onclick="window.location.href='/'">
                        <span>Все филиалы</span>
                    </li>
                    <li class="menu-item">
                        <span>Аналитика</span>
                    </li>
                    <li class="menu-item">
                        <span>Редактирование</span>
                    </li>
                </ul>
            </nav>

            <div class="content-area">
                <div class="system-details">
                    <div class="system-header">
                        <h2 class="system-title">{{ $system->name ?? 'Система без названия' }}</h2>
                        <span class="status-badge success">Исправна</span>
                    </div>

                    <div class="modal-tabs">
                        <button class="tab active" data-tab="main">Основное</button>
                        <button class="tab" data-tab="equipment">Оборудование</button>
                        <button class="tab" data-tab="docs">Документы</button>
                        <button class="tab" data-tab="history">История обслуживания</button>
                        <button class="tab" data-tab="plans">Планы работ</button>
                    </div>

                    <!-- Основная информация -->
                    <div class="tab-content active" id="main">
                        @include('partials.system-main')
                        <div class="debug-info">
                            Вкладка "Основное" загружена | ID: main | Контент: {{ strlen($system->name ?? '') }}
                            символов
                        </div>
                    </div>

                    <!-- Оборудование -->
                    <div class="tab-content" id="equipment">
                        @include('partials.system-equipment')
                        <div class="debug-info">
                            Вкладка "Оборудование" загружена | ID: equipment
                        </div>
                    </div>

                    <!-- Документы -->
                    <div class="tab-content" id="docs">
                        @include('partials.system-docs')
                        <div class="debug-info">
                            Вкладка "Документы" загружена | ID: docs
                        </div>
                    </div>

                    <!-- История -->
                    <div class="tab-content" id="history">
                        @include('partials.system-history')
                        <div class="debug-info">
                            Вкладка "История" загружена | ID: history
                        </div>
                    </div>

                    <!-- Планы -->
                    <div class="tab-content" id="plans">
                        @include('partials.system-plans')
                        <div class="debug-info">
                            Вкладка "Планы" загружена | ID: plans
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Инициализация системы вкладок...');

            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');

            console.log('Найдено вкладок:', tabs.length);
            console.log('Найдено блоков контента:', tabContents.length);

            // Покажем информацию о каждом табе
            tabContents.forEach(content => {
                const hasContent = content.innerHTML.trim().length > 100; // Проверяем, что есть контент
                console.log(`Таб "${content.id}": ${hasContent ? 'С контентом' : 'Пустой или мало контента'}`);

                if (!hasContent) {
                    content.innerHTML += '<div style="color: red; padding: 20px; border: 2px dashed red; margin: 10px;">' +
                        'ВНИМАНИЕ: Этот партиал не загрузил контент или пустой</div>';
                }
            });

            // Функция для переключения вкладок
            function switchTab(tabName) {
                console.log(`🔄 Переключаем на вкладку: ${tabName}`);

                // 1. Снимаем активные классы
                tabs.forEach(tab => tab.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // 2. Находим нужные элементы
                const targetTab = document.querySelector(`.tab[data-tab="${tabName}"]`);
                const targetContent = document.getElementById(tabName);

                if (!targetTab || !targetContent) {
                    console.error(`Не найдены элементы для вкладки: ${tabName}`);
                    return;
                }

                // 3. Активируем
                targetTab.classList.add('active');
                targetContent.classList.add('active');

                // 4. Показываем отладочную информацию
                document.querySelectorAll('.debug-info').forEach(info => {
                    info.style.display = 'none';
                });
                targetContent.querySelector('.debug-info').style.display = 'block';

                console.log(`✅ Успешно переключено на: ${tabName}`);
            }

            // Вешаем обработчики на вкладки
            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });

            // Автоматическое открытие таба из URL hash
            const hash = window.location.hash.substring(1);
            if (hash && document.getElementById(hash)) {
                console.log(`Открываем вкладку из URL: ${hash}`);
                switchTab(hash);
            }

            // Показываем начальное состояние
            const activeTab = document.querySelector('.tab.active');
            const activeContent = document.querySelector('.tab-content.active');

            console.log('Начальная активная вкладка:', activeTab?.getAttribute('data-tab'));
            console.log('Начальный активный контент:', activeContent?.id);

            // Показываем отладочную информацию для активного таба
            if (activeContent) {
                activeContent.querySelector('.debug-info').style.display = 'block';
            }

            console.log('Система вкладок инициализирована');
        });

        // Обработка ошибок
        window.addEventListener('error', function (e) {
            console.error('Ошибка JavaScript:', e.error);
        });
    </script>
</body>

</html>