document.querySelectorAll('.mod-opening-hours').forEach((root) => {
    const panels = root.querySelectorAll('.mod-opening-hours__week-panel');
    const titleEl = root.querySelector('.mod-opening-hours__week-title');
    const infoEl = root.querySelector('.mod-opening-hours__paging-info');
    const prevBtn = root.querySelector('.mod-opening-hours__paging-link--prev');
    const nextBtn = root.querySelector('.mod-opening-hours__paging-link--next');

    if (!panels.length || !titleEl) return;

    const total = panels.length;
    let current = parseInt(root.dataset.currentWeekIndex ?? '0', 10);
    if (Number.isNaN(current) || current < 0) current = 0;
    if (current >= total) current = total - 1;

    function showWeek(index) {
        current = Math.max(0, Math.min(index, total - 1));
        panels.forEach((panel, i) => {
            const hidden = i !== current;
            panel.hidden = hidden;
            panel.setAttribute('aria-hidden', hidden ? 'true' : 'false');
        });
        const active = panels[current];
        if (active) titleEl.textContent = active.dataset.weekLabel ?? '';
        if (infoEl) infoEl.textContent = `${current + 1} / ${total}`;
        if (prevBtn) {
            prevBtn.disabled = current === 0;
        }
        if (nextBtn) {
            nextBtn.disabled = current === total - 1;
        }
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
