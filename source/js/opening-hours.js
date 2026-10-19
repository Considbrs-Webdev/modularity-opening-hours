const ROOT_SELECTOR = '.mod-opening-hours';
const INIT_FLAG = 'openingHoursInit';

// Listeners are tied to the controls found on the last pass. The init flag
// stays on the root when a preview swaps that markup, so it is not enough on
// its own to decide that this root is current.
const bindings = new WeakMap();

function boundToCurrentMarkup(root, prevBtn, nextBtn, lists) {
    const binding = bindings.get(root);
    if (!binding || binding.prevBtn !== prevBtn || binding.nextBtn !== nextBtn) return false;
    if (binding.lists.length !== lists.length) return false;

    return binding.lists.every((list, index) => list === lists[index]);
}

function clearBinding(root) {
    bindings.get(root)?.controller.abort();
    bindings.delete(root);
    delete root.dataset[INIT_FLAG];
}

function initOpeningHours(root) {
    const lists = root.querySelectorAll('.mod-opening-hours__list[data-week-index]');
    const titleEl = root.querySelector('[data-week-title]');
    const titleFullEl = root.querySelector('[data-week-title-full]');
    const infoEl = root.querySelector('.mod-opening-hours__paging-info');
    const prevBtn = root.querySelector('.mod-opening-hours__paging-link--prev');
    const nextBtn = root.querySelector('.mod-opening-hours__paging-link--next');

    const total = lists.length;

    // data-opening-hours-init blocks another pass. Honour it only while the
    // nodes from that pass are still in the root. ACF's JSX preview keeps the
    // root (and this attribute) and replaces the inner markup.
    if (root.dataset[INIT_FLAG] === '1') {
        if (!bindings.has(root) || boundToCurrentMarkup(root, prevBtn, nextBtn, lists)) return;
    }

    if (total === 0) {
        clearBinding(root);
        return;
    }

    clearBinding(root);

    const controller = new AbortController();
    bindings.set(root, {
        controller,
        prevBtn,
        nextBtn,
        lists: Array.from(lists),
    });
    root.dataset[INIT_FLAG] = '1';
    const { signal } = controller;

    let current = parseInt(root.dataset.currentWeekIndex ?? '0', 10);
    if (Number.isNaN(current) || current < 0) current = 0;
    if (current >= total) current = total - 1;

    const todayDateKey = root.dataset.todayDateKey || '';
    const pagingMode = root.dataset.pagingMode || 'index';

    function applyActiveDay() {
        root.querySelectorAll('.mod-opening-hours__row[data-date-key]').forEach((row) => {
            row.classList.toggle('is-active-day', (row.dataset.dateKey || '') === todayDateKey);
        });
    }

    function renderPagingInfo() {
        if (!infoEl) return;

        if (pagingMode === 'week-no') {
            infoEl.classList.add('mod-opening-hours__paging-info--week-nos');
            infoEl.replaceChildren();

            if (current > 0) {
                const prevList = root.querySelector(`.mod-opening-hours__list[data-week-index="${current - 1}"]`);
                if (prevList?.dataset.weekNo) {
                    const span = document.createElement('span');
                    span.className = 'mod-opening-hours__paging-week';
                    span.textContent = prevList.dataset.weekNo;
                    infoEl.appendChild(span);
                }
            }

            const activeList = root.querySelector(`.mod-opening-hours__list[data-week-index="${current}"]`);
            const currentSpan = document.createElement('span');
            currentSpan.className = 'mod-opening-hours__paging-week mod-opening-hours__paging-week--current';
            currentSpan.textContent = activeList?.dataset.weekNo || String(current + 1);
            currentSpan.setAttribute('aria-current', 'true');
            infoEl.appendChild(currentSpan);

            if (current < total - 1) {
                const nextList = root.querySelector(`.mod-opening-hours__list[data-week-index="${current + 1}"]`);
                if (nextList?.dataset.weekNo) {
                    const span = document.createElement('span');
                    span.className = 'mod-opening-hours__paging-week';
                    span.textContent = nextList.dataset.weekNo;
                    infoEl.appendChild(span);
                }
            }

            return;
        }

        infoEl.classList.remove('mod-opening-hours__paging-info--week-nos');
        infoEl.textContent = `${current + 1} / ${total}`;
    }

    function showWeek(index) {
        current = Math.max(0, Math.min(index, total - 1));

        lists.forEach((list) => {
            const listIndex = parseInt(list.dataset.weekIndex, 10);
            list.hidden = listIndex !== current;

            if (listIndex === current) {
                if (titleEl) titleEl.textContent = list.dataset.weekLabel || '';
                if (titleFullEl) {
                    titleFullEl.textContent = list.dataset.weekLabelFull || list.dataset.weekLabel || '';
                }
            }
        });

        renderPagingInfo();
        if (prevBtn) prevBtn.disabled = current === 0;
        if (nextBtn) nextBtn.disabled = current === total - 1;

        applyActiveDay();
    }

    prevBtn?.addEventListener(
        'click',
        (e) => {
            e.preventDefault();
            if (current > 0) showWeek(current - 1);
        },
        { signal },
    );

    nextBtn?.addEventListener(
        'click',
        (e) => {
            e.preventDefault();
            if (current < total - 1) showWeek(current + 1);
        },
        { signal },
    );

    showWeek(current);
}

function initAll(scope) {
    // A child added under a reused root is not itself .mod-opening-hours.
    const host = scope.closest?.(ROOT_SELECTOR);
    if (host) initOpeningHours(host);
    scope.querySelectorAll(ROOT_SELECTOR).forEach(initOpeningHours);
}

/**
 * The block editor re-renders ACF blocks in place. JSX preview keeps the
 * existing .mod-opening-hours root and replaces its inner markup, so paging
 * is rebound when those children appear as well as when a new root is added.
 */
function observeEditorRenders() {
    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) initAll(node);
            });
        });
    }).observe(document.documentElement, { childList: true, subtree: true });
}

initAll(document);

if (window.frameElement?.name === 'editor-canvas') {
    observeEditorRenders();
}
