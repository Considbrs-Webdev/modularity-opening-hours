document.querySelectorAll('.mod-opening-hours').forEach((root) => {
    const lists = root.querySelectorAll('.mod-opening-hours__list[data-week-index]');
    const titleEl = root.querySelector('[data-week-title]');
    const infoEl = root.querySelector('.mod-opening-hours__paging-info');
    const prevBtn = root.querySelector('.mod-opening-hours__paging-link--prev');
    const nextBtn = root.querySelector('.mod-opening-hours__paging-link--next');

    const total = lists.length;
    if (total === 0) return;

    let current = parseInt(root.dataset.currentWeekIndex ?? '0', 10);
    if (Number.isNaN(current) || current < 0) current = 0;
    if (current >= total) current = total - 1;

    const todayDateKey = root.dataset.todayDateKey || '';

    function applyActiveDay() {
        root.querySelectorAll('.mod-opening-hours__row[data-date-key]').forEach((row) => {
            row.classList.toggle('is-active-day', (row.dataset.dateKey || '') === todayDateKey);
        });
    }

    function showWeek(index) {
        current = Math.max(0, Math.min(index, total - 1));

        lists.forEach((list) => {
            const listIndex = parseInt(list.dataset.weekIndex, 10);
            list.hidden = listIndex !== current;

            if (listIndex === current && titleEl) {
                titleEl.textContent = list.dataset.weekLabel || '';
            }
        });

        if (infoEl) infoEl.textContent = `${current + 1} / ${total}`;
        if (prevBtn) prevBtn.disabled = current === 0;
        if (nextBtn) nextBtn.disabled = current === total - 1;

        applyActiveDay();
    }

    prevBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        if (current > 0) showWeek(current - 1);
    });

    nextBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        if (current < total - 1) showWeek(current + 1);
    });

    showWeek(current);
});
