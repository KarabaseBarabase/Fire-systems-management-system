<div class="section">
    <h3>История изменений</h3>

    <!-- Фильтры для истории -->
    <div class="section-toolbar">
        <div class="btn-group">
            <button class="btn btn-outline-primary active">Все</button>
            <button class="btn btn-outline-primary">Изменения</button>
            <button class="btn btn-outline-primary">Подтверждения</button>
        </div>
    </div>

    <div class="timeline">
        @forelse($history ?? [] as $event)
            <div
                class="timeline-item {{ $event['action'] === 'DELETE' ? 'timeline-item-danger' : ($event['action'] === 'INSERT' ? 'timeline-item-success' : 'timeline-item-info') }}">
                <div class="timeline-point"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <h5>
                            @if(isset($event['action']))
                                @if($event['action'] === 'INSERT')
                                    <i class="icon-plus"></i> Создание
                                @elseif($event['action'] === 'UPDATE')
                                    <i class="icon-edit"></i> Изменение
                                @else
                                    <i class="icon-trash"></i> Удаление
                                @endif
                                - {{ $event['table_name'] }}
                            @elseif(isset($event['new_status']))
                                <i class="icon-check"></i> Подтверждение: {{ $event['new_status'] }}
                            @endif
                        </h5>
                        <span class="timeline-date">
                            {{ \Carbon\Carbon::parse($event['changed_at'] ?? $event['approved_at'])->format('d.m.Y H:i') }}
                        </span>
                    </div>
                    <div class="timeline-body">
                        @if(isset($event['user_name']))
                            <p><strong>Пользователь:</strong> {{ $event['user_name'] }}</p>
                        @endif

                        @if(isset($event['record_uuid']))
                            <p><strong>UUID записи:</strong> <code class="small">{{ $event['record_uuid'] }}</code></p>
                        @endif

                        @if(isset($event['changed_fields']) && !empty($event['changed_fields']))
                            <div class="changed-fields">
                                <h6><i class="icon-list"></i> Измененные поля:</h6>
                                <div class="field-changes">
                                    @foreach($event['changed_fields'] as $field => $value)
                                        <div class="field-change">
                                            <span class="field-name">{{ $field }}:</span>
                                            <span class="field-value">
                                                @if(is_array($value) || is_object($value))
                                                    {{ json_encode($value) }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(isset($event['comment']))
                            <div class="timeline-comment">
                                <h6><i class="icon-message"></i> Комментарий:</h6>
                                <p class="comment-text">{{ $event['comment'] }}</p>
                            </div>
                        @endif

                        <!-- Дополнительная информация -->
                        @if(isset($event['curator_type']))
                            <p><strong>Тип куратора:</strong> {{ $event['curator_type'] }}</p>
                        @endif

                        @if(isset($event['old_status']))
                            <p><strong>Бывший статус:</strong> {{ $event['old_status'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="icon-history"></i>
                <p>История изменений отсутствует</p>
                <small class="text-muted">Здесь будут отображаться все изменения системы</small>
            </div>
        @endforelse
    </div>

    <!-- Пагинация если много записей -->
    @if(count($history ?? []) > 10)
        <div class="timeline-pagination">
            <button class="btn btn-outline-primary">Загрузить еще</button>
        </div>
    @endif
</div>