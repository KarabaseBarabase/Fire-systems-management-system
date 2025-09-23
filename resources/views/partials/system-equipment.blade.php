<div class="section">
    <h3>Оборудование системы</h3>

    <!-- Отладочная информация -->
    <div style="display: none;">
        <p>Equipment data: {{ json_encode($equipment) }}</p>
        <p>Equipment count: {{ count($equipment ?? []) }}</p>
        @if(!empty($equipment) && count($equipment) > 0)
            <p>First item: {{ json_encode($equipment[0]) }}</p>
        @endif
    </div>

    <div class="section-toolbar">
        <div class="search-box">
            <input type="text" placeholder="Поиск оборудования..." id="equipmentSearch">
            <button type="button" class="search-btn">
                <i class="icon-search"></i>
            </button>
        </div>
        @if($canEdit ?? false)
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                <i class="icon-plus"></i>Добавить оборудование
            </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="info-table">
            <thead>
                <tr>
                    <th>Тип оборудования</th>
                    <th>Модель</th>
                    <th>Серийный номер</th>
                    <th>Количество</th>
                    <th>Год выпуска</th>
                    <th>Срок службы</th>
                    <th>Место установки</th>
                    <th>Статус</th>
                    <th>Последняя проверка</th>
                    <th>Результат проверки</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipment ?? [] as $item)
                    <tr>
                        <td><span class="badge">{{ $item['equipment_type_name'] ?? 'Не указан' }}</span></td>
                        <td>{{ $item['model'] ?? 'Не указана' }}</td>
                        <td><code>{{ $item['serial_number'] ?? 'Не указан' }}</code></td>
                        <td>{{ $item['quantity'] ?? 1 }}</td>
                        <td>{{ $item['production_year'] ?? 'Не указан' }}</td>
                        <td>
                            @if(isset($item['production_year']) && isset($item['service_life_years']))
                                До {{ ($item['production_year'] + $item['service_life_years']) }} года
                            @else
                                Не указан
                            @endif
                        </td>
                        <td>{{ $item['location'] ?? 'Не указано' }}</td>
                        <td>
                            @php
                                $expirationYear = ($item['production_year'] ?? 0) + ($item['service_life_years'] ?? 0);
                                $currentYear = date('Y');
                                $status = $expirationYear > $currentYear ? 'active' : 'expired';
                            @endphp
                            <span class="status-badge status-{{ $status }}">
                                {{ $status === 'active' ? 'Исправен' : 'Истёк срок' }}
                            </span>
                        </td>
                        <td>{{ $item['last_control_date'] ? \Carbon\Carbon::parse($item['last_control_date'])->format('d.m.Y') : 'Не указана' }}
                        </td>
                        <td>{{ $item['control_result'] ?? 'Не указан' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Оборудование не найдено</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>