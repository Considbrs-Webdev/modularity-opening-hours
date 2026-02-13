document.querySelectorAll('.mod-opening-hours').forEach((root) => {
    const dataEl = root.querySelector('.mod-opening-hours__data');
    const listContainer = root.querySelector('.mod-opening-hours__list-container');
    const titleEl = root.querySelector('.mod-opening-hours__week-title');
    const infoEl = root.querySelector('.mod-opening-hours__paging-info');
    const prevBtn = root.querySelector('.mod-opening-hours__paging-link--prev');
    const nextBtn = root.querySelector('.mod-opening-hours__paging-link--next');

    let weeks = [];
    if (dataEl && dataEl.textContent) {
        try {
            weeks = JSON.parse(dataEl.textContent);
        } catch {
            weeks = [];
        }
    }

    const total = weeks.length || parseInt(root.dataset.totalWeeks ?? '0', 10) || 0;
    if (total === 0 || !listContainer) return;

    let current = parseInt(root.dataset.currentWeekIndex ?? '0', 10);
    if (Number.isNaN(current) || current < 0) current = 0;
    if (current >= total) current = total - 1;

    const closedLabel = root.dataset.closedLabel ?? 'Closed';

    function slotHtml(slot) {
        if (slot.closed) {
            return `<span class="mod-opening-hours__closed">${escapeHtml(closedLabel)}</span>`;
        }
        return `<span class="mod-opening-hours__range">${escapeHtml(slot.open)} – ${escapeHtml(slot.close)}</span>`;
    }

    function daySlotsHtml(slots) {
        return slots
            .map((slot, i) => {
                const sep = i < slots.length - 1 ? '<span class="mod-opening-hours__sep">, </span>' : '';
                return slotHtml(slot) + sep;
            })
            .join('');
    }

    function renderWeek(week) {
        if (!week || !listContainer) return;
        const dl = listContainer.querySelector('.mod-opening-hours__list');
        if (!dl) return;
        const rows = week.days
            .map(
                (day) =>
                    `<div class="mod-opening-hours__row">
  <dt class="mod-opening-hours__day">${escapeHtml(day.name)}</dt>
  <dd class="mod-opening-hours__slots">${daySlotsHtml(day.slots)}</dd>
</div>`
            )
            .join('');
        dl.innerHTML = rows;
    }

    function showWeek(index) {
        current = Math.max(0, Math.min(index, total - 1));
        const week = weeks[current];
        if (week) {
            if (titleEl) titleEl.textContent = week.weekLabel;
            renderWeek(week);
        }
        if (infoEl) infoEl.textContent = `${current + 1} / ${total}`;
        if (prevBtn) prevBtn.disabled = current === 0;
        if (nextBtn) nextBtn.disabled = current === total - 1;
    }

    function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
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
