<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мониторинг пожарных систем</title>
    <style>
        .app-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        .logout-btn {
            padding: 8px 15px;
            background-color: #dc3545;
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
        }

        .menu-item:hover,
        .menu-item.active {
            background-color: #34495e;
        }

        .content-area {
            flex: 1;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .systems-table {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-controls {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .filters {
            display: flex;
            gap: 10px;
            flex: 1;
        }

        .search-input,
        .status-filter,
        .branch-filter {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .export-btn {
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            cursor: pointer;
        }

        th:hover {
            background-color: #e9ecef;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
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

        .btn-view,
        .btn-edit {
            padding: 4px 8px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }

        .btn-view {
            background-color: #007bff;
            color: white;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .pagination {
            display: flex;
            gap: 5px;
        }

        .page-nav,
        .page-num {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background-color: white;
            cursor: pointer;
        }

        .page-nav:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .page-num.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-left">
                <img src="logo.png" alt="Логотип" class="logo">
                <h1>Мониторинг пожарных систем</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($userFullName) ?></span>
                    <span class="user-role"><?= htmlspecialchars($userRole) ?></span>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Выход</button>
                </form>
            </div>
        </header>

        <div class="main-content">
            <nav class="sidebar">
                <ul class="menu">
                    <li class="menu-item active">
                        <img src="icons/view-all.svg" alt="">
                        <span>Все филиалы</span>
                    </li>
                    <li class="menu-item">
                        <img src="icons/analytics.svg" alt="">
                        <span>Аналитика</span>
                    </li>
                    <li class="menu-item" data-role="engineer,chief">
                        <img src="icons/edit.svg" alt="">
                        <span>Редактирование</span>
                    </li>
                    <li class="menu-item" data-role="chief,admin">
                        <img src="icons/approve.svg" alt="">
                        <span>Подтверждение</span>
                    </li>
                </ul>
            </nav>

            <div class="content-area">
                <div class="systems-table">
                    <div class="table-controls">
                        <div class="filters">
                            <input type="text" placeholder="Поиск по названию/инв.номеру" class="search-input">
                            <select class="branch-filter">
                                <option value="">Все филиалы</option>
                                <?php if (!empty($branches)): ?>
                                <?php    foreach ($branches as $branch): ?>
                                <option value="<?= htmlspecialchars($branch['name']) ?>">
                                    <?= htmlspecialchars($branch['name']) ?>
                                </option>
                                <?php    endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button class="export-btn">
                            <span>Экспорт</span>
                        </button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th data-sort="id">ID</th>
                                    <th data-sort="name">Название</th>
                                    <th>Тип системы</th>
                                    <th data-sort="last-check">Последняя проверка</th>
                                    <th>Филиал</th>
                                    <th>Ответственный</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($fireSystems)): ?>
                                <?php    foreach ($fireSystems as $system): ?>
                                <tr data-id="<?= htmlspecialchars($system['id']) ?>"
                                    data-branch-id="<?= htmlspecialchars($branch['id'] ?? '') ?>">
                                    <td><?= htmlspecialchars($system['id']) ?></td>
                                    <td><?= htmlspecialchars($system['name']) ?></td>
                                    <td><?= htmlspecialchars($system['type']) ?></td>
                                    <td><?= $system['last_check_date'] ? htmlspecialchars($system['last_check_date']) : 'Нет данных' ?>
                                    </td>
                                    <td><?= htmlspecialchars($system['branch_name']) ?></td>
                                    <td><?= htmlspecialchars($system['responsible_person']) ?></td>
                                    <td>
                                        <button class="btn-view"
                                            onclick="window.location.href='{{ route('system.show', $system['id']) }}'">
                                            Просмотр
                                        </button>
                                        <button class="btn-edit" data-role="engineer">Ред.</button>
                                    </td>
                                </tr>
                                <?php    endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">Нет данных о пожарных системах</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <span class="pagination-info">Показано <?= count($fireSystems) ?> записей</span>
                        <div class="pagination">
                            <button class="page-nav" disabled>&larr;</button>
                            <button class="page-num active">1</button>
                            <button class="page-num">&rarr;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPage(systemId) {
            window.location.href = "modal_window.php?id=" + systemId;
        }

        // Простая сортировка таблицы
        document.querySelectorAll('th[data-sort]').forEach(header => {
            header.addEventListener('click', () => {
                const sortBy = header.getAttribute('data-sort');
                alert('Сортировка по: ' + sortBy);
                // добавить логику сортировки
            });
        });



        // Фильтрация по филиалам
        function applyFilters() {
            const searchTerm = document.querySelector('.search-input').value.toLowerCase();
            const selectedBranch = document.querySelector('.branch-filter').value;
            const rows = document.querySelectorAll('tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const branchNameCell = row.cells[4];
                const branchName = branchNameCell.textContent.trim();
                const rowText = row.textContent.toLowerCase();

                // Проверяем оба фильтра
                const matchesSearch = searchTerm === '' || rowText.includes(searchTerm);
                const matchesBranch = selectedBranch === '' || branchName === selectedBranch;

                if (matchesSearch && matchesBranch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateRecordCount(visibleCount);
        }

        // Функция для обновления счетчика
        function updateRecordCount(count) {
            const counterElement = document.querySelector('.pagination-info');
            if (counterElement) {
                counterElement.textContent = `Показано ${count} записей`;
            }
        }

        // Вешаем обработчики на все фильтры
        document.querySelector('.search-input').addEventListener('input', applyFilters);
        document.querySelector('.branch-filter').addEventListener('change', applyFilters);

    </script>
</body>

</html>