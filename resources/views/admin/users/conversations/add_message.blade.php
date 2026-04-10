<div id="filter" class="collapse padding-horizontal-0 mt-3">
    <h6>Message</h6>
    {{ $value->textarea }}
    {{ $value->viewField }}
    {{ $value->attachments ?? '' }}
</div>

<div role="button" data-bs-toggle="collapse" href="#filter" class="m-b-15" aria-controls="filter" aria-expanded="false">
    <div class="btn-only-open">{{ $value->btn }}</div>
    <div class="mr-btn-success toggle-message-label"></div>
</div>

<style>
    /* По умолчанию скрыт */
    #filter + [data-bs-toggle="collapse"] .btn-only-open {
        display: none;
    }

    /* Показываем, когда collapse открыт (.show добавляет Bootstrap) */
    #filter.show + [data-bs-toggle="collapse"] .btn-only-open {
        display: block;
    }

    #filter + [data-bs-toggle="collapse"] .toggle-message-label::before {
        content: "Add message";
    }

    #filter + [data-bs-toggle="collapse"][aria-expanded="true"] .toggle-message-label::before {
        content: "Отменить";
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const collapse = document.getElementById('filter');
        const toggle = document.querySelector('[data-bs-toggle="collapse"][href="#filter"]');
        const label = toggle ? toggle.querySelector('.toggle-message-label') : null;

        if (!collapse || !label) {
            return;
        }

        const syncState = () => {
            const isOpen = collapse.classList.contains('show');
            label.classList.toggle('mr-btn-danger', isOpen);
            label.classList.toggle('mr-btn-success', !isOpen);
        };

        collapse.addEventListener('shown.bs.collapse', syncState);
        collapse.addEventListener('hidden.bs.collapse', syncState);
        syncState();
    });
</script>
