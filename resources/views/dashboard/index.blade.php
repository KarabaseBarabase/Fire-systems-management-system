<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мониторинг пожарных систем</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
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
                        <div>
                            <button class="export-btn">
                                <span>Экспорт</span>
                            </button>
                            <button class="add-system-btn" onclick="addNewSystem()">
                                <span>Добавить систему</span>
                            </button>
                        </div>
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
                                    <td>
                                        <button class="btn-view"
                                            onclick="window.location.href='{{ route('system.show', $system['id']) }}'">
                                            Просмотр
                                        </button>
                                        <button class="btn-edit" data-role="engineer"
                                            onclick="editSystem(<?= $system['id'] ?>)">
                                            Ред.
                                        </button>
                                        <button class="btn-delete"
                                            onclick="confirmDelete('<?= $system['uuid'] ?>', '<?= htmlspecialchars($system['name']) ?>')">
                                            Удалить
                                        </button>
                                    </td>
                                </tr>
                                <?php    endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">Нет данных о пожарных системах</td>
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

    <!-- Модальное окно подтверждения удаления -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Подтверждение удаления</h3>
            <p>Вы уверены, что хотите удалить систему "<span id="systemNameToDelete"></span>"?</p>
            <p class="text-danger">Это действие нельзя отменить!</p>
            <div class="modal-buttons">
                <button onclick="cancelDelete()"
                    style="padding: 8px 15px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Отмена
                </button>
                <button onclick="deleteSystem()"
                    style="padding: 8px 15px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Удалить
                </button>
            </div>
        </div>
    </div>

    <script>
        let systemToDelete = null;

        function openPage(systemId) {
            window.location.href = "modal_window.php?id=" + systemId;
        }

        // Функции для работы с системами
        function addNewSystem() {
            alert('Добавление новой системы');
        }

        function editSystem(systemId) {
            alert('Редактирование системы ID: ' + systemId);
        }

        function confirmDelete(systemId, systemName) {
            console.log("confirmDelete called with:", systemId, systemName);
            systemToDelete = systemId;
            document.getElementById('systemNameToDelete').textContent = systemName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function cancelDelete() {
            systemToDelete = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        function deleteSystem() {
            console.log("deleteSystem called");
            console.log("systemToDelete:", systemToDelete);

            if (systemToDelete) {
                console.log("Sending DELETE request for UUID:", systemToDelete);

                fetch(`/systems/${systemToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        console.log("Response status:", response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log("Response data:", data);
                        if (data.success) {
                            alert(data.message || 'Система успешно удалена');
                            location.reload();
                        } else {
                            alert(data.error || 'Ошибка при удалении системы');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ошибка при удалении системы');
                    });

                cancelDelete();
            } else {
                console.error("systemToDelete is null or undefined");
                alert("Ошибка: система для удаления не выбрана");
            }
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

        // Закрытие модального окна при клике вне его
        window.addEventListener('click', function (event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                cancelDelete();
            }
        });

    </script>
</body>

</html>