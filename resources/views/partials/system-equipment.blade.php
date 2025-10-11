<div class="section">
    <h3>Оборудование системы</h3>

    <!-- Отладочная информация -->
    <!-- <div class="debug-info" style="display: block; background: #fff3cd; padding: 10px; margin: 10px 0;">
        <strong>Отладка оборудования:</strong><br>
        Equipment exists: {{ isset($equipment) ? 'YES' : 'NO' }}<br>
        Equipment count: {{ count($equipment ?? []) }}<br>
        @if(isset($equipment) && count($equipment) > 0)
            First item type: {{ gettype($equipment[0]) }}<br>
            First item class: {{ get_class($equipment[0]) }}<br>
            First item properties:
            @foreach(get_object_vars($equipment[0]) as $key => $value)
                {{ $key }}: {{ $value }},
            @endforeach
        @endif
    </div> -->

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
                        <td><span class="badge">{{ $item->equipment_type_name ?? 'Не указан' }}</span></td>
                        <td>{{ $item->model ?? 'Не указана' }}</td>
                        <td><code>{{ $item->serialNumber ?? 'Не указан' }}</code></td>
                        <td>{{ $item->quantity ?? 1 }}</td>
                        <td>{{ $item->productionYear ?? 'Не указан' }}</td>
                        <td>
                            @if(isset($item->productionYear) && isset($item->serviceLifeYears))
                                До {{ ($item->productionYear + $item->serviceLifeYears) }} года
                            @else
                                Не указан
                            @endif
                        </td>
                        <td>{{ $item->location ?? 'Не указано' }}</td>
                        <td>
                            @php
                                $expirationYear = ($item->productionYear ?? 0) + ($item->serviceLifeYears ?? 0);
                                $currentYear = date('Y');
                                $status = $expirationYear > $currentYear ? 'active' : 'expired';
                            @endphp
                            <span class="status-badge status-{{ $status }}">
                                {{ $status === 'active' ? 'Исправен' : 'Истёк срок' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $lastControlDate = $item->lastControlDate ?? null;
                            @endphp
                            @if(!empty($lastControlDate))
                                {{ \Carbon\Carbon::parse($lastControlDate)->format('d.m.Y') }}
                            @else
                                Не указана
                            @endif
                        </td>
                        <td>{{ $item->controlResult ?? 'Не указан' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Оборудование не найдено</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>