<div class="section">
    <table class="info-table">
        <thead>
            <tr>
                <th>Код документа</th>
                <th>Наименование</th>
                <th>Ссылка</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents ?? [] as $document)
                @if(is_object($document))
                    <tr>
                        <td><code>{{ property_exists($document, 'code') ? $document->code : 'Не указан' }}</code></td>
                        <td>{{ property_exists($document, 'name') ? $document->name : 'Без названия' }}</td>
                        <td>
                            @if(property_exists($document, 'file_link') && !empty($document->file_link))
                                <a href="{{ $document->file_link }}" target="_blank" class="btn-icon">
                                    <i class="icon-eye"></i>
                                </a>
                            @else
                                <span class="text-muted">Нет файла</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Документы не найдены</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>