<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система {{ $system->name ?? 'Без названия' }}</title>
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
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