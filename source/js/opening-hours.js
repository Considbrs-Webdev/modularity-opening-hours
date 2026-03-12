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
    }

    prevBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        if (current > 0) showWeek(current - 1);
    });

    nextBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        if (current < total - 1) showWeek(current + 1);
    });

    // Dual-day featured area (when highlight_today is enabled)
    const featured = root.querySelector('.mod-opening-hours__featured');
    if (!featured) return;

    let allDays = [];
    try {
        allDays = JSON.parse(root.dataset.allDays || '[]');
    } catch (_) {
        allDays = [];
    }
    const todayDateKey = root.dataset.todayDateKey || '';
    const showTomorrow = root.dataset.showTomorrow === '1';
    const labelToday = root.dataset.labelToday || 'Today';
    const labelTomorrow = root.dataset.labelTomorrow || 'Tomorrow';
    const labelClosed = root.dataset.labelClosed || 'Closed';
    const dayPrevBtn = featured.querySelector('.mod-opening-hours__day-nav-link--prev');
    const dayNextBtn = featured.querySelector('.mod-opening-hours__day-nav-link--next');
    const slots = featured.querySelectorAll('.mod-opening-hours__featured-slot');
    const rows = root.querySelectorAll('.mod-opening-hours__row[data-date-key]');

    if (allDays.length === 0) return;

    let activeIndex = 0;
    const initialKey = root.dataset.initialActiveDateKey || '';
    const found = allDays.findIndex((d) => (d.dateKey || '') === initialKey);
    if (found >= 0) activeIndex = found;

    function getTomorrowDateKey() {
        if (!todayDateKey) return '';
        const d = new Date(todayDateKey + 'T12:00:00');
        d.setDate(d.getDate() + 1);
        return d.toISOString().slice(0, 10);
    }

    function getLabel(day) {
        if (!day) return '';
        const key = day.dateKey || '';
        if (key === todayDateKey) return labelToday;
        if (key === getTomorrowDateKey()) return labelTomorrow;
        return day.name || '';
    }

    function formatSlots(slotsData) {
        if (!slotsData || slotsData.length === 0) return `<span class="mod-opening-hours__closed">${labelClosed}</span>`;
        return slotsData
            .map((s) => {
                if (s.closed) return `<span class="mod-opening-hours__closed">${labelClosed}</span>`;
                return `<span class="mod-opening-hours__range">${s.open} – ${s.close}</span>`;
            })
            .join(', ');
    }

    function updateFeatured() {
        const day1 = allDays[activeIndex] || null;
        const day2 = showTomorrow && activeIndex + 1 < allDays.length ? allDays[activeIndex + 1] : null;

        const slot0 = slots[0];
        if (slot0 && day1) {
            const labelEl = slot0.querySelector('[data-slot-label]');
            const hoursEl = slot0.querySelector('[data-slot-hours]');
            const label = getLabel(day1);
            if (labelEl) labelEl.innerHTML = `${label}${label ? ', ' : ''}<span class="mod-opening-hours__today-name" data-slot-name>${day1.name || ''} ${day1.date || ''}</span>`;
            if (hoursEl) hoursEl.innerHTML = formatSlots(day1.slots);
            slot0.dataset.dateKey = day1.dateKey || '';
            slot0.hidden = false;
        } else if (slot0) {
            slot0.hidden = true;
        }

        const slot1 = slots[1];
        if (slot1) {
            if (day2) {
                const labelEl = slot1.querySelector('[data-slot-label]');
                const hoursEl = slot1.querySelector('[data-slot-hours]');
                const label = getLabel(day2);
                if (labelEl) labelEl.innerHTML = `${label}${label ? ', ' : ''}<span class="mod-opening-hours__today-name" data-slot-name>${day2.name || ''} ${day2.date || ''}</span>`;
                if (hoursEl) hoursEl.innerHTML = formatSlots(day2.slots);
                slot1.dataset.dateKey = day2.dateKey || '';
                slot1.hidden = false;
            } else {
                slot1.hidden = true;
            }
        }

        rows.forEach((row) => {
            const key = row.dataset.dateKey || '';
            row.classList.toggle('is-active-day', key === (day1?.dateKey || ''));
        });

        const weekIndex = day1?.weekIndex ?? 0;
        if (weekIndex !== current) {
            showWeek(weekIndex);
        }

        if (dayPrevBtn) dayPrevBtn.disabled = activeIndex <= 0;
        if (dayNextBtn) dayNextBtn.disabled = activeIndex >= allDays.length - 1;
    }

    dayPrevBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        if (activeIndex > 0) {
            activeIndex--;
            updateFeatured();
        }
    });

    dayNextBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        if (activeIndex < allDays.length - 1) {
            activeIndex++;
            updateFeatured();
        }
    });

    updateFeatured();
});
